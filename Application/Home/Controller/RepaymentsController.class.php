<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/11/29
 * Time: 16:27
 */

namespace Home\Controller;


use Think\Exception;
use Think\Page2;

class RepaymentsController extends CommonController
{
    public function index() {

        $condition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        try {
            //1.1 如果有数据则获取GET数据
            if(I('get.search_loan_id',0,'intval')) {
                $condition['loan_id'][] = I('get.search_loan_id',0,'intval');
                $this->assign('input_loan_id',I('get.search_loan_id',0,'intval'));
            }

            if(I('get.search_customer_name','','string')) {
                $name = I('get.search_customer_name','','string');
                // 根据名称搜索用户信息
                $search_customer = D('Customer')->getCustomerByName($name);

                if($search_customer) {
                    // 批量搜索借款ID
                    $search_loan = D('Loan')->selectLoanByCustomerIDList(array_column($search_customer,'id'));
                    if($search_loan) {
                        foreach ($search_loan as $key => $item) {
                            $condition['loan_id'][] = $item['loan_id'];
                        }
                    }
                }
                $this->assign('input_customer_name',$name);
            }

            if(I('get.search_customer_phone','','string')) {
                $phone = I('get.search_customer_phone','','string');

                // 根据名称搜索用户信息
                $search_customer = D('Customer')->getCustomerByPhone($phone);
                if($search_customer) {
                    // 批量搜索借款ID
                    $search_loan = D('Loan')->selectLoanByCustomerID($search_customer['id']);
                    if($search_loan) {
                        foreach ($search_loan as $key => $item) {
                            $condition['loan_id'][] = intval($item['loan_id']);
                        }
                    }
                }
                $this->assign('input_customer_phone',$phone);
            }

            if(I('get.search_datepicker','','string')) {
                $search_datepicker = I('get.search_datepicker','','string');
                // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
                // 分解为
                // 2017-11-17 12:30:00
                // 2017-11-25 15:30:20
                $s_time = substr($search_datepicker,0,19);
                $e_time = substr($search_datepicker,24,19);
                $condition['gmt_repay'] = array('BETWEEN',array($s_time,$e_time));
                $this->assign('input_datepicker',$search_datepicker);
            }else {
                /*$s_time = date('Y-m-d H:i:s',strtotime(date('Y-m',time())));
                $e_time = date('Y-m-d H:i:s',strtotime(date('Y-m',time()) . ' +1 month -1 second'));
                $condition['gmt_repay'] = array('BETWEEN',array($s_time,$e_time));
                //2018-01-01 00:00:00 至 2018-01-31 23:59:59
                $this->assign('input_datepicker',$s_time . ' 至 ' . $e_time);*/
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
            $sum_smoney = $sum_bmoney = $sum_rmoney = 0;
            $repayments = D('Repayments')->listRepayments($condition,$page,$page_size);
            $countRepayment = D('Repayments')->getCountRepayments($condition);
            $sum_smoney = D('Repayments')->getSumOfSmoney($condition);
            $sum_rmoney = D('Repayments')->getSumOfRmoney($condition);
            $sum_bmoney = D('Repayments')->getSumOfBmoney($condition);

            //1.5 实例化一个分页对象
            $res = new Page2($countRepayment,$pageSize);
            //1.6 调用show方法前台显示页码
            $pageRes = $res->show();
            //1.7 处理数据

            // 获取还款记录信息

            // 遍历还款记录表
            foreach($repayments as $key => $item) {
                // 获取借款信息
                $loan = D('Loan')->getLoanByID($item['loan_id']);
                // 获取客户信息
                $customer = D('Customer')->getCustomerByID($loan['customer_id']);
                // 获取收款人信息
                $staff = D('Staff')->getStaffByID($item['staff_id']);
                $repayments[$key]['loan'] = $loan;
                $repayments[$key]['customer'] = $customer;
                $repayments[$key]['staff'] = $staff;
                // 获取公司信息
                $company = D('Company')->getCompanyByID($item['company_id']);
                $repayments[$key]['company_name'] = $company['smallname'];
            }

            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
            $companyCondition = array();
            if($userInfo['jurisdiction'] == 1) {
                $companyCondition['company_id'] = $userInfo['company_id'];
            }else if($userInfo['jurisdiction'] == 2) {
                $companyIDs = array_column($companys,'company_id');
                $companyCondition['company_id'] = array('IN',$companyIDs);
            }

            //客户列表
            // 查找所有未结清的借款记录
            $loanCondition = array();
            $loanCondition['loan_status'] = array('neq',1);
            if($userInfo['jurisdiction'] == 1) {
                $loanCondition['company_id'] = $userInfo['company_id'];
            }else if($userInfo['jurisdiction'] == 2) {
                $companyIDs = array_column($companys,'company_id');
                $loanCondition['company_id'] = array('IN',$companyIDs);
            }

            $norepayLoans = D('Loan')->selectAllBycondition($loanCondition);
            if($norepayLoans) {
                $norepayCustomerIDs = array_unique(array_column($norepayLoans,'customer_id'));
                $customers = D('Customer')->selectALLCustomerOnlyByCondition(array('id' => array('IN',$norepayCustomerIDs)));
            }

            //经理列表
            $staffs = D('Staff')->selectALLStaff($companyCondition);

        }catch (Exception $exception) {
            throw_exception($exception->getMessage());
        }
        $this->assign(array(
            'repayments' => $repayments,
            'customers' => $customers,
            'staffs' => $staffs,
            'pageRes' => $pageRes,
            'sum_smoney' => number_format($sum_smoney,2),
            'sum_rmoney' => number_format($sum_rmoney,2),
            'sum_bmoney' => number_format($sum_bmoney,2),
            'countRepayment' => $countRepayment,
            'userInfo' => $userInfo,
            'companys' => $companys
        ));
        $this->display();
    }


    /**
     * 新增还款信息记录
     */
    public function checkAddEdit() {
        if(!$_POST) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post数据不存在！'
            ));
        }

        $loan_id = I('post.loan_id',0,'intval');
        $cycles = I('post.cycles',0,'intval');
        $s_money = I('post.s_money',0,'float');
        $r_money = I('post.r_money',0,'float');
        $b_money = I('post.b_money',0,'float');
        $gmt_repay = I('post.gmt_repay','','string');
        $gmt_create = date('Y-m-d H:i:s',time());
        $staff_id = I('post.staff_id',0,'intval');
        $pay_style = I('post.pay_style',0,'intval');
        $remark = I('post.remark','','string');

        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择一项贷款！'
            ));
        }
        if(!$cycles) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入本次还款周期！'
            ));
        }
        if(!$s_money) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入应还金额！'
            ));
        }
        if(!$r_money) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入实际还款金额！'
            ));
        }

        if(!$staff_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择收款人！'
            ));
        }
        if(!$pay_style) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择收款方式！'
            ));
        }
        if(!$gmt_repay) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择收款方式！'
            ));
        }

        $gmt_repay = date('Y-m-d H:i:s',strtotime($gmt_repay));

        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
        $userInfo = $this->getUserNowInfo();
        // 获取借款记录信息
        $loan = D('Loan')->getLoanByID($loan_id);
        if(!$loan) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '借款信息记录不存在！',
            ));
        }

        $data = array(
            'loan_id' => $loan_id,
            'cycles' => $cycles,
            's_money' => $s_money,
            'r_money' => $r_money,
            'b_money' => $b_money,
            'gmt_repay' => $gmt_repay,
            'gmt_create' => $gmt_create,
            'staff_id' => $staff_id,
            'pay_style' => $pay_style,
            'remark' => $remark,
            'company_id' => $loan['company_id'],
        );

        try {
            // 查看该期是否存在
            $repayment = D('Repayments')->checkCycles($loan_id,$cycles);
            if($repayment) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该周期已有还款记录！',
                ));
            }
            // 将还款记录写入数据库
            $repayments_id = D('Repayments')->addRepayments($data);
            if(!$repayments_id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '还款记录添加失败！',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '还款记录添加成功！',
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    /**
     * 删除还款记录
     */
    public function deleteRepayments() {
        $repayments_id = I('post.id',0,'intval');
        if($repayments_id) {
            try {
                //$res = D('Repayments')->deleteRepaymentsByID($repayments_id);
                $res = D('Repayments')->logicDeleteByRepayID($repayments_id);

                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '删除成功！',
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '删除失败，请稍后重试！',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '贷款记录ID不存在！',
            ));
        }
    }

    /**
     * 编辑还款信息
     */
    public function editRepayments() {
        $repayments_id = I('post.id',0,'intval');
        if(!$repayments_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '还款ID不存在！',
            ));
        }

        try {

            // 还款记录信息
            $repayments = D('Repayments')->getRepayments($repayments_id);
            if(!$repayments) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '还款记录不存在！',
                ));
            }
            // 借款信息
            $loan = D('Loan')->getLoanByID($repayments['loan_id']);
            $loan['create_time'] = date('Y-m-d H:i:s', $loan['create_time']);
            // 借款人信息
            $customer = D('Customer')->getCustomerByID($loan['customer_id']);


            // 还款周期下拉框数据
            $repayCycles = array();
            for ($i=1 ; $i < $loan['cyclical']+1; $i++) {
                // 查看该期是否存在
                $selected = 0;
                if($i == ($repayments['cycles'])) {
                    $selected = 1;
                }
                $repayment = D('Repayments')->checkCycles($repayments['loan_id'],$i);
                if($repayment && $repayment['repayments_id'] != $repayments['repayments_id']) {
                    $repayCycles[$i] = array(
                        'value' => $i,
                        'disabled' => '1',
                        'selected' => $selected,
                    );
                }else {
                    $repayCycles[$i] = array(
                        'value' => $i,
                        'disabled' => '0',
                        'selected' => $selected,
                    );
                }
            }

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '还款记录搜索成功！',
                'repayments' => $repayments,
                'loan' => $loan,
                'customer' => $customer,
                'repayCycles' => $repayCycles,
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function checkEditValue() {
        if(!$_POST) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post数据不存在！'
            ));
        }

        $repayments_id = I('post.edit_repayments_id',0,'intval');
        $cycles = I('post.edit_cycles',0,'intval');
        $r_money = I('post.edit_r_money',0,'float');
        $b_money = I('post.edit_b_money',0,'float');
        $gmt_repay = I('post.edit_gmt_repay','','string');
        $staff_id = I('post.edit_staff_id',0,'intval');
        $pay_style = I('post.edit_pay_style',0,'intval');
        $remark = I('post.edit_remark','','string');
        $gmt_modify = date('Y-m-d H:i:s',time());

        if(!$repayments_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '还款ID不存在！'
            ));
        }
        if(!$cycles) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入本次还款周期！'
            ));
        }
        if(!$r_money) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入实际还款金额！'
            ));
        }

        if(!$staff_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择收款人！'
            ));
        }
        if(!$pay_style) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择收款方式！'
            ));
        }
        if(!$gmt_repay) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择收款方式！'
            ));
        }

        $gmt_repay = date('Y-m-d H:i:s',strtotime($gmt_repay));

        $data = array(
            'cycles' => $cycles,
            'r_money' => $r_money,
            'b_money' => $b_money,
            'gmt_repay' => $gmt_repay,
            'staff_id' => $staff_id,
            'pay_style' => $pay_style,
            'remark' => $remark,
            'gmt_modify' => $gmt_modify,
        );

        try {
            // 还款记录信息
            $repayments = D('Repayments')->getRepayments($repayments_id);

            // 查看该期是否存在
            $repayment = D('Repayments')->checkCycles($repayments['repayments_id'],$cycles);
            if($repayment && $repayment['repayments_id'] != $repayments['repayments_id']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该周期已有还款记录！',
                ));
            }
            // 将还款记录写入数据库
            $repayments_id = D('Repayments')->updateRepayments($repayments_id,$data);
            if(!$repayments_id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '无任何修改！',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '修改成功！',
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    /**
     * 自动写入借款记录信息
     */
    public function addRepaymentsAuto() {

        $loan_id = I('post.loan_id',0,'intval');
        $now_cyclical = I('post.now_cyclical',0,'intval');
        // 新的状态  1 正常还款 2是超时还款 3 是未还款
        $new_re_status = I('post.new_re_status',0,'intval');
        $b_money = I('post.b_money',0,'intval');

        if($new_re_status == 1 || $new_re_status == 2) {
            // 正常还款或者超时逾期，逾期产生违约金

            // 判断是否该周期是否已经还款
            $now_repay = D('Repayments')->checkCycles($loan_id,$now_cyclical);
            if($now_repay) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该周期已有还款记录！',
                ));
            }
            // 获取借款记录信息
            $loan = D('Loan')->getLoanByID($loan_id);
            if(!$loan) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '借款信息记录不存在！',
                ));
            }

            if($new_re_status == 2) {
                //超时
                $r_money = $loan['cyc_principal'] + $b_money;
            }else {
                $r_money = $loan['cyc_principal'];
            }

            $userInfo = $this->getUserNowInfo();
            // 封装写入数据
            $autoData = array(
                'loan_id' => $loan_id,
                'cycles' => $now_cyclical,
                's_money' => $loan['cyc_principal'],
                'r_money' => $r_money,
                'b_money' => $b_money,
                'gmt_create' => date('Y-m-d H:i:s',time()),
                'staff_id' => $userInfo['staff_id'],    // 收款人，根据当前登录用户
                'pay_style' => 1,
                'remark' => '',
                'gmt_repay' => date('Y-m-d H:i:s',time()),
                'company_id' => $loan['company_id'],
            );
            // 写入数据库
            $newAdd = D('Repayments')->addRepayments($autoData);
            // 返回结果
            if(!$newAdd) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '还款记录写入失败！',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '还款成功！',
            ));

        }else if($new_re_status == -1) {
            // 逾期未还，将借款记录表设置为逾期状态
            $chageLoan = D('Loan')->updateOneLoanFieldByID($loan_id,'loan_status',-1);
            if(!$chageLoan) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '设置逾期失败，请稍后再试！'
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '设置逾期成功！'
            ));
        }


    }

    public function exportExcel() {
        $condition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        try {
            //1.1 如果有数据则获取GET数据
            if(I('get.search_loan_id',0,'intval')) {
                $condition['loan_id'][] = I('get.search_loan_id',0,'intval');
            }

            if(I('get.search_customer_name','','string')) {
                $name = I('get.search_customer_name','','string');
                // 根据名称搜索用户信息
                $search_customer = D('Customer')->getCustomerByName($name);

                if($search_customer) {
                    // 批量搜索借款ID
                    $search_loan = D('Loan')->selectLoanByCustomerIDList(array_column($search_customer,'id'));
                    if($search_loan) {
                        foreach ($search_loan as $key => $item) {
                            $condition['loan_id'][] = $item['loan_id'];
                        }
                    }
                }
            }

            if(I('get.search_customer_phone','','string')) {
                $phone = I('get.search_customer_phone','','string');

                // 根据名称搜索用户信息
                $search_customer = D('Customer')->getCustomerByPhone($phone);
                if($search_customer) {
                    // 批量搜索借款ID
                    $search_loan = D('Loan')->selectLoanByCustomerID($search_customer['id']);
                    if($search_loan) {
                        foreach ($search_loan as $key => $item) {
                            $condition['loan_id'][] = intval($item['loan_id']);
                        }
                    }
                }
            }

            if(I('get.search_datepicker','','string')) {
                $search_datepicker = I('get.search_datepicker','','string');
                // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
                // 分解为
                // 2017-11-17 12:30:00
                // 2017-11-25 15:30:20
                $s_time = substr($search_datepicker,0,19);
                $e_time = substr($search_datepicker,24,19);
                $condition['gmt_repay'] = array('BETWEEN',array($s_time,$e_time));
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


            //1.4 数据库查询
            $sum_smoney = $sum_bmoney = $sum_rmoney = 0;
            $repayments = D('Repayments')->listRepaymentsByCondition($condition);
            $countRepayment = D('Repayments')->getCountRepayments($condition);
            $sum_smoney = D('Repayments')->getSumOfSmoney($condition);
            $sum_rmoney = D('Repayments')->getSumOfRmoney($condition);
            $sum_bmoney = D('Repayments')->getSumOfBmoney($condition);


            // 遍历还款记录表
            foreach($repayments as $key => $item) {
                // 获取借款信息
                $loan = D('Loan')->getLoanByID($item['loan_id']);
                // 获取客户信息
                $customer = D('Customer')->getCustomerByID($loan['customer_id']);
                // 获取收款人信息
                $staff = D('Staff')->getStaffByID($item['staff_id']);
                // 获取产品信息
                $product = D('Product')->findProductByCondition('product_id',$loan['product_id']);
                // 获取客户经理信息
                $kehu_staff = D('Staff')->getStaffByID($loan['staff_id']);
                // 获取上门经理信息
                if($loan['foreign_id']) {
                    $sm_staff = D('Staff')->getStaffByID($loan['foreign_id']);
                }

                if($product['cycle_id'] == 1) {
                    // 日还
                   $cycle_name = '每天';
                }else if($product['cycle_id'] == 2) {
                    // 周还
                    if($loan['juti_data'] == 1) {
                        $cycle_name = '每周一';
                    }else if($loan['juti_data'] == 2) {
                        $cycle_name = '每周二';
                    }else if($loan['juti_data'] == 3) {
                        $cycle_name = '每周三';
                    }else if($loan['juti_data'] == 4) {
                        $cycle_name = '每周四';
                    }else if($loan['juti_data'] == 5) {
                        $cycle_name = '每周五';
                    }else if($loan['juti_data'] == 6) {
                        $cycle_name = '每周六';
                    }else if($loan['juti_data'] == 7) {
                        $cycle_name = '每周日';
                    }
                }else if($product['cycle_id'] == 3) {
                    $cycle_name = '每30天';
                }else if($product['cycle_id'] == 4) {
                    $cycle_name = '每22天';
                }else if($product['cycle_id'] == 5) {
                    $cycle_name = '每' . $loan['juti_data'] . '天';
                }



                // 获取公司信息
                $company = D('Company')->getCompanyByID($item['company_id']);
                $repayments[$key]['company_name'] = $company['smallname'];

                $repayments[$key]['id'] = $key+1;  // x序号
                $repayments[$key]['customer_name'] = $customer['name'];  // 客户姓名
                $repayments[$key]['customer_phone'] = $customer['phone'];  // 客户电话
                //$repayments[$key]['customer_idcard'] = $customer['idcard'];  // 客户身份证号码
                $repayments[$key]['loan_principal'] = $loan['principal'];  // 借款本金
                //$repayments[$key]['loan_cyc_interest'] = $loan['cyc_interest'];  // 每期利息
                //$repayments[$key]['loan_cyclical'] = $loan['cyclical'];  // 借款周期
                $repayments[$key]['loan_cyclical'] = $item['cycles'] . '/' . $loan['cyclical'];  // 借款周期
                //$repayments[$key]['loan_interest'] = $loan['interest'];  // 累计利息
                //$repayments[$key]['loan_cyc_principal'] = $loan['cyc_principal'];  // 每期还款
                $repayments[$key]['product_name'] = $product['product_name'];  // 产品名称
                $repayments[$key]['repay_data'] = $cycle_name;  // 具体还款日期
                //$repayments[$key]['loan_poundage'] = $loan['poundage'];  // 手续费
                //$repayments[$key]['loan_bond'] = $loan['bond'];  // 保证金
                //$repayments[$key]['loan_rebate'] = $loan['rebate'];  // 同行返点
                //$repayments[$key]['loan_arrival'] = $loan['arrival'];  // 实际到账
                //$repayments[$key]['loan_expenditure'] = $loan['expenditure'];  // 实际支出
                $repayments[$key]['loan_staff_name'] = $kehu_staff['staff_name'];  // 客户经理
                //$repayments[$key]['loan_staff_phone'] = $kehu_staff['phone_number'];  // 电话号码
                //$repayments[$key]['loan_staff_name'] = $sm_staff['staff_name'];  // 上门经理
                $repayments[$key]['loan_create_time'] = date('Y-m-d', $loan['create_time']);  // 借款日期
                $repayments[$key]['loan_exp_time'] = date('Y-m-d', $loan['exp_time']);  // 到期日期
                $repayments[$key]['loan_loan_status'] = $loan['loan_status'] == 0 ? '还款中' : ($loan['loan_status'] == 1 ? '已结清' : '已逾期');   // 借款状态
                $repayments[$key]['repay_staff_name'] = $staff['staff_name'];  // 上门经理
                $repayments[$key]['repay_pay_style'] = $item['pay_style'] == 1 ? '微信' : ($item['pay_style'] == 2 ? '支付宝' : ($item['pay_style'] == 3 ? '银行卡' : ($item['pay_style'] == 4 ? '现金' : '扣首期')));  // 上门经理
            }

            $xlsName  = '还款记录表信息';

            if($userInfo['jurisdiction'] == 2) {
                $xlsCell  = array(
                    array('id','序号'),
                    array('customer_name','客户姓名'),
                    array('customer_phone','客户电话'),
                    array('loan_principal','借款金额'),
                    array('product_name','借款类型'),
                    array('repay_data','还款日期'),
                    array('loan_create_time','借款日期'),
                    array('loan_exp_time','到期日期'),
                    array('loan_staff_name','客户经理'),
                    array('loan_loan_status','借款状态'),
                    array('loan_cyclical','还款周期'),
                    array('s_money','应还金额'),
                    array('r_money','实还金额'),
                    array('b_money','违约金额'),
                    array('repay_staff_name','收款人'),
                    array('repay_pay_style','收款方式'),
                    array('gmt_repay','还款时间'),
                    array('company_name','所属公司'),
                    //array('customer_idcard','客户身份证号码'),

                    //array('loan_cyc_interest','每期利息'),

                    //array('loan_interest','累计利息'),
                    //array('loan_cyc_principal','每期还款'),


                    //array('loan_poundage','手续费'),
                    //array('loan_bond','保证金'),
                    //array('loan_rebate','同行返点'),
                    //array('loan_arrival','实际到账'),
                    //array('loan_expenditure','实际支出'),

                    //array('loan_staff_phone','电话号码'),
                    //array('loan_staff_name','上门经理'),


                    //array('cycles','本次周期'),

                );

            }else {
                $xlsCell  = array(
                    array('id','序号'),
                    array('customer_name','客户姓名'),
                    array('customer_phone','客户电话'),
                    array('loan_principal','借款金额'),
                    array('product_name','借款类型'),
                    array('repay_data','还款日期'),
                    array('loan_create_time','借款日期'),
                    array('loan_exp_time','到期日期'),
                    array('loan_staff_name','客户经理'),
                    array('loan_loan_status','借款状态'),
                    array('loan_cyclical','还款周期'),
                    array('s_money','应还金额'),
                    array('r_money','实还金额'),
                    array('b_money','违约金额'),
                    array('repay_staff_name','收款人'),
                    array('repay_pay_style','收款方式'),
                    array('gmt_repay','还款时间'),
                );
            }



            $xlsData = $repayments;
            $excel = new PhpexcelController();
            $excel->exportExcel($xlsName,$xlsCell,$xlsData);

        }catch (Exception $exception) {
            throw_exception($exception->getMessage());
        }

    }
}