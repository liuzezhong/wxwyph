<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/5
 * Time: 15:02
 */

namespace Home\Controller;


use Think\Controller;
use Think\Exception;
use Think\Page2;

class MessageController extends CommonController
{

    public function index() {

        $condition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();

        //客户列表
        if($userInfo['jurisdiction'] == 1) {
            $customerCondition['company_id'] = $userInfo['company_id'];
        }else if($userInfo['jurisdiction'] == 2) {
            $companyIDs = array_column($companys,'company_id');
            $customerCondition['company_id'] = array('IN',$companyIDs);
        }
        $customers = D('Customer')->selectALLCustomer($customerCondition);

        if(I('get.search_customer_phone','','string')) {
            $phone = I('get.search_customer_phone','','string');
            $condition['phone'] = $phone;
            $this->assign('input_customer_phone',$phone);
        }
        if(I('get.search_datepicker','','string')) {
            $search_datepicker = I('get.search_datepicker','','string');
            $s_time = substr($search_datepicker,0,19);
            $e_time = substr($search_datepicker,24,19);
            $condition['gmt_create'] = array('BETWEEN',array($s_time,$e_time));
            $this->assign('input_datepicker',$search_datepicker);
        }

        if(I('get.company_id','','string') && I('get.company_id','','string') != 'undefined') {
            $companySearchArray = explode(',',I('get.company_id','','string'));
            foreach ($companys as $key => $item) {
                $companys[$key]['selected'] = 0;
                foreach ($companySearchArray as $i => $j) {
                    if($item['company_id'] == $j) {
                        $companys[$key]['selected'] = 1;
                    }
                }
            }
            $condition['company_id'] = array('IN',$companySearchArray);
        }else {
            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
            if($userInfo['jurisdiction'] == 1) {
                $condition['company_id'] = $userInfo['company_id'];
            }else if($userInfo['jurisdiction'] == 2){
                $companyIDs = array_column($companys,'company_id');
                $condition['company_id'] = array('IN',$companyIDs);
                foreach ($companys as $key => $item) {
                    $companys[$key]['selected'] = 0;
                    foreach ($companyIDs as $i => $j) {
                        if($item['company_id'] == $j) {
                            $companys[$key]['selected'] = 1;
                        }
                    }
                }
            }
        }


        //1.2 获取当前页码
        $now_page = I('request.page',1,'intval');
        $page_size = I('request.pageSize',10,'intval');
        $page = $now_page ? $now_page : 1;
        //1.3 设置默认分页条数
        $pageSize = $page_size ? $page_size : 10;
        //1.4 数据库查询

        $messages = D('Message')->listMessages($condition,$page,$page_size);
        $countMessage = D('Message')->getCountMessage($condition);

        $noPageMessage = D('Message')->listMessagesByCondition($condition);

        //1.5 实例化一个分页对象
        $res = new Page2($countMessage,$pageSize);
        //1.6 调用show方法前台显示页码
        $pageRes = $res->show();
        //1.7 处理数据
        $sumMoney = 0;
        foreach ($noPageMessage as $key => $item) {
            if($item['template_id'] == 1) {
                $sumMoney = $sumMoney + 0.045 * 2;
            }else if($item['template_id'] == 2){
                $sumMoney = $sumMoney + 0.045;
            }
        }

        foreach ($messages as $key => $item) {
            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $messages[$key]['company_name'] = $company['smallname'];
            $messages[$key]['gmt_create'] = date('Y-m-d',strtotime($item['gmt_create']));

            // 获取客户或用户信息
            if($item['template_id'] == 1) {
                // 借款模板
                $customer = D('Customer')->getCustomerByID($item['customer_id']);
                $messages[$key]['name'] = $customer['name'];
                $messages[$key]['phone'] = $customer['phone'];
            }else if($item['template_id'] == 2) {
                $staff = D('Staff')->getStaffByID($item['customer_id']);
                $messages[$key]['name'] = $staff['staff_name'];
                $messages[$key]['phone'] = $staff['phone_number'];
            }

            $messages[$key]['send_status_name'] = ($item['send_status'] == 0) ? '正在发送' : (($item['send_status'] == 1) ? '发送成功' : '发送失败');
        }

        $this->assign(array(
            'messages' => $messages,
            'pageRes' => $pageRes,
            'userInfo' => $userInfo,
            'companys' => $companys,
            'countMessage' => $countMessage,
            'sumMoney' => $sumMoney,
            'customers' => $customers,
        ));

        $this->display();
    }

    public function export() {
        $condition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();



        if(I('get.search_customer_phone','','string')) {
            $phone = I('get.search_customer_phone','','string');
            $condition['phone'] = $phone;
        }
        if(I('get.search_datepicker','','string')) {
            $search_datepicker = I('get.search_datepicker','','string');
            $s_time = substr($search_datepicker,0,19);
            $e_time = substr($search_datepicker,24,19);
            $condition['gmt_create'] = array('BETWEEN',array($s_time,$e_time));
        }

        if(I('get.company_id','','string') && I('get.company_id','','string') != 'undefined') {
            $companySearchArray = explode(',',I('get.company_id','','string'));
            $condition['company_id'] = array('IN',$companySearchArray);
        }else {
            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
            if($userInfo['jurisdiction'] == 1) {
                $condition['company_id'] = $userInfo['company_id'];
            }else if($userInfo['jurisdiction'] == 2){
                $companyIDs = array_column($companys,'company_id');
                $condition['company_id'] = array('IN',$companyIDs);
            }
        }

        $messages = D('Message')->listMessagesByCondition($condition);


        foreach ($messages as $key => $item) {
            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $messages[$key]['company_name'] = $company['smallname'];
            $messages[$key]['gmt_create'] = date('Y-m-d',strtotime($item['gmt_create']));

            // 获取客户或用户信息
            if($item['template_id'] == 1) {
                // 借款模板
                $customer = D('Customer')->getCustomerByID($item['customer_id']);
                $messages[$key]['name'] = $customer['name'];
                $messages[$key]['phone'] = $customer['phone'];
            }else if($item['template_id'] == 2) {
                $staff = D('Staff')->getStaffByID($item['customer_id']);
                $messages[$key]['name'] = $staff['staff_name'];
                $messages[$key]['phone'] = $staff['phone_number'];
            }
            $messages[$key]['send_status_name'] = ($item['send_status'] == 0) ? '正在发送' : (($item['send_status'] == 1) ? '发送成功' : '发送失败');
        }

        $xlsName  = '短信记录表';

        if($userInfo['jurisdiction'] == 2) {
            $xlsCell  = array(
                array('message_id','序号'),
                array('gmt_create','发送时间'),
                array('name','接受人名'),
                array('param_detail','具体内容'),
                array('send_status_name','发送状态'),
                array('remark','备注信息'),
                array('company_name','所属公司'),
            );
        }else {
            $xlsCell  = array(
                array('message_id','序号'),
                array('gmt_create','发送时间'),
                array('name','接受人名'),
                array('param_detail','具体内容'),
                array('send_status_name','发送状态'),
                array('remark','备注信息'),
            );
        }

        $xlsData = $messages;
        $excel = new PhpexcelController();
        $excel->exportExcel($xlsName,$xlsCell,$xlsData);

    }


    public function addMessage() {
        $customer_id = I('post.customer_id',0,'intval');
        $phone = I('post.phone','','string');
        $company_id = I('post.company_id',0,'intval');
        $money = I('post.money',0,'intval');
        $gmt_create = I('post.gmt_create','','string');
        $param_yulan = I('post.param_yulan','','string');
        if(!$customer_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户ID不存在',
            ));
        }
        if(!$phone) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户手机号码不能为空',
            ));
        }
        if(!$company_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '所属公司ID不存在',
            ));
        }
        if(!$money) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '还款金额不能为空',
            ));
        }
        if(!$gmt_create) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '还款日期不能为空',
            ));
        }
        if(!$param_yulan) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '预览信息不能为空',
            ));
        }

        try {

            // 获取用户信息
            $customer = D('Customer')->getCustomerByID($customer_id);
            // 获取公司信息
            $company = D('Company')->getCompanyByID($company_id);


            //模板变量
            $templateParam = array(
                "name" => $customer['name'],
                "money" => number_format($money,2),
                "time" => date('Y年m月d日',strtotime($gmt_create)),
                "phone" => $phone,
            );
            /**
             * 发送记录写入数据库
             */
            $messageData = array(
                'loan_id' => 0,
                'customer_id' => $customer_id,
                'phone' => $phone,
                'template_id' => 1,
                'param_detail' => $param_yulan,
                'gmt_create' => date('Y-m-d H:i:s',time()),
                'send_status' => 0, // 发送中
                'company_id' => $company_id,
                'nowdata' => $gmt_create,
            );
            $message_id = D('Message')->addMessage($messageData);

            // 调用接口发送短信
            $sendMessage = $this->sendMessage($customer['phone'],$company['smallname'],
                C('ALIYUN_MESSAGE_CONFIG')['templateCode']['loan_template'],$templateParam,$message_id);
            // 返回结果
            $sendMessage = json_decode(json_encode($sendMessage),true);

            if($sendMessage['status'] == -1) {
                // 发送失败，错误信息写入数据库
                $sendData = array(
                    'send_status' => '-1',
                    'remark' => $sendMessage['message'],
                    'gmt_modify' => date('Y-m-d H:i:s',time()),
                );
                $updateMessage = D('Message')->updateMessage($message_id,$sendData);
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '发送失败，请稍后再试',
                ));
            }else {
                // 发送成功
                $sendData = array(
                    'send_status' => '1',
                    'gmt_modify' => date('Y-m-d H:i:s',time()),
                    'request_id' => $sendMessage['RequestId'],
                    'biz_id' => $sendMessage['BizId'],
                    'code' => $sendMessage['Code'],
                );
                $updateMessage = D('Message')->updateMessage($message_id,$sendData);
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '发送成功',
                ));
            }

        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }


    /**
     * 获取短信内容显示在页面上
     */
    public function getLoanMessage() {
        $loan_id = I('post.id',0,'intval');
        $style = I('post.style',0,'intval');
        $nowdata = I('post.nowdate','','string');
        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '借款ID为空',
            ));
        }

        try {
            // 获取借款记录信息
            $loan = D('Loan')->getLoanByID($loan_id);
            // 获取用户信息
            $customer = D('Customer')->getCustomerByID($loan['customer_id']);
            // 获取公司信息
            $company = D('Company')->getCompanyByID($loan['company_id']);

            if($style == 1) {
                // 30分钟内禁止重复发送短信通知
                $messageCondition = array(
                    'customer_id' => $loan['customer_id'],
                    'loan_id' => $loan_id,
                    'gmt_create' => array('BETWEEN',array(date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s',
                            time()) . '- 30 minutes')),date('Y-m-d H:i:s',time()))),
                    'send_status' => 1,
                );
                $messageValue = D('message')->getMessageByCondition($messageCondition);
                if($messageValue) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '30分钟内不可重复发送短信通知',
                    ));
                }
            }

            // 获取下一期还款时间
            if(!$loan) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '借款记录丢失或被删除',
                ));
            }

            // 还款日期
            if($style == 0 || $style == 2) {
                // 默认当天
                $time = date('Y年m月d日',strtotime($nowdata));
            }else if($style == 1) {
                $nowtime = date('Y-m-d',time());
                $time = date('Y年m月d日',strtotime($nowtime . '+ 1 day'));
            }

            $message = '【' . $company['smallname'] . '】尊敬的' . $customer['name'] . '：您有一期账单共计' . $loan['cyc_principal'] . '元，请在' . $time . '下午16：00前转账'
                    . '至本公司微信、支付宝或银行卡账户内。（若有疑问请致电客服' . $company['kefu_phone'] .'，若本期已转请忽略此条短信）';

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '短信内容生成成功',
                'phone_message' => $message,
                'customer_phone' => $customer['phone'],
            ));

        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    /**
     * 获取更新后短信内容并准备发送
     */
    public function checkLoanMessage() {
        $loan_id = I('post.id',0,'intval');
        $message = I('post.loan_message','','string');

        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '借款ID为空',
            ));
        }

        if(!$message) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '短信内容不能为空',
            ));
        }

        $string = str_replace('年','-',$message);
        $string = str_replace('月','-',$string);
        $string = str_replace('日','',$string);
        // 正则匹配 提取年月日
        $pattern='/\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}/';
        preg_match($pattern, $string, $matches);
        $date = $matches[0];

        try {
            // 获取借款记录信息
            $loan = D('Loan')->getLoanByID($loan_id);
            // 获取用户信息
            $customer = D('Customer')->getCustomerByID($loan['customer_id']);
            // 获取公司信息
            $company = D('Company')->getCompanyByID($loan['company_id']);

            if(!$loan) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '借款记录丢失或被删除',
                ));
            }
            //模板变量
            $templateParam = array(
                "name" => $customer['name'],
                "money" => number_format($loan['cyc_principal'],2),
                "time" => date('Y年m月d日',strtotime($date)),
                "phone" => $company['kefu_phone'],
            );
            /**
             * 发送记录写入数据库
             */
            $messageData = array(
                'loan_id' => $loan_id,
                'customer_id' => $loan['customer_id'],
                'phone' => $customer['phone'],
                'template_id' => 1,
                'param_detail' => $message,
                'gmt_create' => date('Y-m-d H:i:s',time()),
                'send_status' => 0, // 发送中
                'company_id' => $company['company_id'],
                'nowdata' => $date,
            );
            $message_id = D('Message')->addMessage($messageData);

            // 调用接口发送短信
            $sendMessage = $this->sendMessage($customer['phone'],$company['smallname'],
                C('ALIYUN_MESSAGE_CONFIG')['templateCode']['loan_template'],$templateParam,$message_id);
            // 返回结果
            $sendMessage = json_decode(json_encode($sendMessage),true);

            if($sendMessage['status'] == -1) {
                // 发送失败，错误信息写入数据库
                $sendData = array(
                    'send_status' => '-1',
                    'remark' => $sendMessage['message'],
                    'gmt_modify' => date('Y-m-d H:i:s',time()),
                );
                $updateMessage = D('Message')->updateMessage($message_id,$sendData);
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '发送失败，请稍后再试',
                ));
            }else {
                // 发送成功
                $sendData = array(
                    'send_status' => '1',
                    'gmt_modify' => date('Y-m-d H:i:s',time()),
                    'request_id' => $sendMessage['RequestId'],
                    'biz_id' => $sendMessage['BizId'],
                    'code' => $sendMessage['Code'],
                );
                $updateMessage = D('Message')->updateMessage($message_id,$sendData);
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '发送成功',
                ));
            }

        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }


    }

    /**
     * 发送短信
     * @param string $phoneNumbers
     * @param array $templateParam
     */
    private function sendMessage($phoneNumbers = '', $signName = '', $templateCode = '', $templateParam = array(), $outID = '') {
        //实例化方法
        if(!$phoneNumbers) {
            return array('status' => -1, 'message' => '手机号码为空');
        }
        if(!$signName) {
            return array('status' => -1, 'message' => '模板名为空');
        }
        if(!$templateCode) {
            return array('status' => -1, 'message' => '模板编号为空');
        }
        if(!$templateParam) {
            return array('status' => -1, 'message' => '模板内容为空');
        }
        if(!$outID) {
            return array('status' => -1, 'message' => '流水号为空');
        }
        $smsData = array(
            'phoneNumbers' => $phoneNumbers,
            'signName' => $signName,
            'templateCode' => $templateCode,
            'templateParam' => json_encode($templateParam, JSON_UNESCAPED_UNICODE),
            'outID' => $outID,
        );
        $message = A('Common/AliyunMessage')->sendSms($smsData);
        return $message;
    }
}