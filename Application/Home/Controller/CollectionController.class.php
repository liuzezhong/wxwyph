<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/12/31
 * Time: 17:56
 */

namespace Home\Controller;


class CollectionController extends CommonController
{
    public function index() {

        if(session('adminUser')['jurisdiction'] == 3) {
            $this->redirect('home/overdue/index');
        }
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();

        $statusArray = array(
            '0' => array('id' => 2 , 'name' => '未还款'),
            '1' => array('id' => 1 , 'name' => '已还款'),
            '2' => array('id' => -1 , 'name' => '逾期中'),
        );


        if(I('get.loan_status','','string')) {
            $statusSearchArray = explode(',',I('get.loan_status','','string'));
            foreach ($statusArray as $key => $item) {
                $statusArray[$key]['selected'] = 0;
                foreach ($statusSearchArray as $i => $j) {
                    if($item['id'] == $j) {
                        $statusArray[$key]['selected'] = 1;
                    }
                }
            }
            foreach ($statusSearchArray as $i => $j) {
                if($j == 2) {
                    $statusSearchArray[$i] = 0;
                }
            }
        }else {
            $statusSearchArray = array(2,1);
            foreach ($statusArray as $key => $item) {
                $statusArray[$key]['selected'] = 0;
                foreach ($statusSearchArray as $i => $j) {
                    if($item['id'] == $j) {
                        $statusArray[$key]['selected'] = 1;
                    }
                }
            }
            foreach ($statusSearchArray as $i => $j) {
                if($j == 2) {
                    $statusSearchArray[$i] = 0;
                }
            }
        }

        if(I('get.reservationtime','','string')) {
            $today = I('get.reservationtime','','string');
            $this->assign('input_datepicker',$today);
            $today = str_replace('年','-',$today);
            $today = str_replace('月','-',$today);
            $today = str_replace('日','',$today);
        }else {
            $today = date('Y-m-d',time());
            $this->assign('input_datepicker',$today);
        }

        // 公司ID搜索
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

        // 获取当前日期的应还款金额
        // 到期时间大于当前时间
        $condition['create_time'] = array('ELT',strtotime($today));
        $condition['exp_time'] = array('EGT',strtotime($today));

        // 获取当前时间范围内的还款记录
        $repayToday = D('Loan')->selectLoanOfRepaymentByDate($condition);
        // 计算当前日期是周几
        $weekDay = date('w',strtotime($today)) > 0 ? date('w',strtotime($today)) : 7;

        // 清洗数据
        foreach ($repayToday as $key => $item) {
            if($item['product_id'] == 1 || $item['product_id'] == 4) {
                // 零用贷和车贷
                if($weekDay != $item['juti_data'] || strtotime($today) == strtotime(date('Y-m-d',$item['create_time']))) {
                    // 如果当前周值和还款周值不同，则剔除数据
                    unset($repayToday[$key]);
                }
            }else if($item['product_id'] == 3) {
                // 空放
                $phase_data = (strtotime($today) - $item['create_time']) / 86400;

                /*if(($phase_data + 1) % $item['juti_data'] != 0 || strtotime(date('Y-m-d',strtotime($today))) == strtotime(date('Y-m-d',$item['create_time']))) {
                    unset($repayToday[$key]);
                }*/
                if(((strtotime($today) - $item['create_time']) / 86400) % ($item['juti_data'] - 1) != 0 || strtotime(date('Y-m-d',strtotime($today))) == strtotime(date('Y-m-d',$item['create_time']))) {
                    unset($repayToday[$key]);
                }

            }
        }


        foreach ($repayToday as $key => $value) {

            // 获取公司信息
            $company = D('Company')->getCompanyByID($value['company_id']);
            $repayToday[$key]['company_name'] = $company['smallname'];

            // 获取客户基本信息
            if($value['customer_id'] != 0) {
                $customer = D('Customer')->findCustomerByCondition('id',$value['customer_id']);
                $repayToday[$key]['customer_name'] = $customer['name'];
                $repayToday[$key]['customer_phone'] = $customer['phone'];
            }

            // 获取产品信息
            if($value['product_id']) {
                // 获取产品名称
                $product = D('Product')->findProductByCondition('product_id',$value['product_id']);
                $repayToday[$key]['product_name'] = $product['product_name'];
                // 获取周期信息
                $cycle = D('Cycle')->findCycleByCondition('cycle_id',$product['cycle_id']);

                //  计算具体还款日期
                if($product['cycle_id'] == 1) {
                    // 日还
                    $repayToday[$key]['cycle_name'] = '每天';
                }else if($product['cycle_id'] == 2) {
                    // 周还
                    if($value['juti_data'] == 1) {
                        $repayToday[$key]['cycle_name'] = '每周一';
                    }else if($value['juti_data'] == 2) {
                        $repayToday[$key]['cycle_name'] = '每周二';
                    }else if($value['juti_data'] == 3) {
                        $repayToday[$key]['cycle_name'] = '每周三';
                    }else if($value['juti_data'] == 4) {
                        $repayToday[$key]['cycle_name'] = '每周四';
                    }else if($value['juti_data'] == 5) {
                        $repayToday[$key]['cycle_name'] = '每周五';
                    }else if($value['juti_data'] == 6) {
                        $repayToday[$key]['cycle_name'] = '每周六';
                    }else if($value['juti_data'] == 7) {
                        $repayToday[$key]['cycle_name'] = '每周日';
                    }
                }else if($product['cycle_id'] == 3) {
                    $repayToday[$key]['cycle_name'] = '每30天';
                }else if($product['cycle_id'] == 4) {
                    $repayToday[$key]['cycle_name'] = '每22天';
                }else if($product['cycle_id'] == 5) {
                    $repayToday[$key]['cycle_name'] = '每' . $value['juti_data'] . '天';
                }
            }

            if($value['staff_id'] != 0) {
                //获取业务经理姓名
                $staff = D('Staff')->findStaffByCondition('staff_id',$value['staff_id']);
                $repayToday[$key]['staff_name'] = $staff['staff_name'];
            }

            // 获取已还周期
            $now_cyclical = 0;
            // 当前时间
            $phase_data = (strtotime($today) - $value['create_time']) / 86400;
            if($value['product_id'] == 1 || $value['product_id'] == 4) {
                // 零用贷 车贷
                $now_cyclical = round($phase_data / 7);
                // $now_cyclical = intval($phase_data / 7);
                if($now_cyclical == 0 ) {
                    $now_cyclical = $now_cyclical + 1;
                }
            }else if($value['product_id'] == 2 || $value['product_id'] == 5) {
                // 打卡 // 红包贷
                $now_cyclical = round($phase_data + 1);
            }else if($value['product_id'] == 3) {
                // 空放
                $now_cyclical = round($phase_data / $value['juti_data']);
            }
            $repayToday[$key]['now_cyclical'] = $now_cyclical;

            // 当前时间还状态
            if($value['loan_status'] == -1) {
                $s_repay = D('Repayments')->checkCycles($value['loan_id'],$now_cyclical);
                if($s_repay) {
                    $r_money = $s_repay['r_money'];
                    $r_status = 1;
                }else {
                    $r_money = '0.00';
                    $r_status = -1;
                }
            }else {
                // 获取实收金额
                $s_repay = D('Repayments')->checkCycles($value['loan_id'],$now_cyclical);
                if($s_repay) {
                    $r_money = $s_repay['r_money'];
                    $r_status = 1;
                }else {
                    $r_money = '0.00';
                    $r_status = 0;
                }
            }

            $repayToday[$key]['r_money'] = $r_money;
            $repayToday[$key]['r_status'] = $r_status;
        }

        // 清洗数据
        $flag = 0;
        foreach ($repayToday as $key => $item) {
            $flag = 0;
            foreach($statusSearchArray as $i => $j) {
                if($item['r_status'] == $j) {
                    $flag = 1;
                    break;
                }
            }
            if($flag == 0) {
                unset($repayToday[$key]);
            }
        }

        // 计算总额和
        $sumSmoney = 0;
        $sumRmoney = 0;
        $countPeople = 0;
        foreach ($repayToday as $key => $item) {
            $sumSmoney += $item['cyc_principal'];
            $countPeople++;


            $now_cyclical = 0;
            $phase_data = (strtotime($today) - $item['create_time']) / 86400;
            if($item['product_id'] == 1 || $item['product_id'] == 4) {
                // 零用贷 // 车贷
                $now_cyclical = round($phase_data / 7);
                if($now_cyclical == 0 ) {
                    $now_cyclical = $now_cyclical + 1;
                }
            }else if($item['product_id'] == 2 || $item['product_id'] == 5) {
                // 打卡 // 红包贷
                $now_cyclical = round($phase_data + 1);
            }else if($item['product_id'] == 3) {
                // 空放
                $now_cyclical = round($phase_data / $item['juti_data']);
            }


            if($item['loan_status'] == -1) {
                $s_repay = D('Repayments')->checkCycles($item['loan_id'],$now_cyclical);
                if($s_repay) {
                    $r_money = $s_repay['r_money'];
                    $r_status = 1;
                }else {
                    $r_money = '0.00';
                    $r_status = -1;
                }
            }else {
                // 获取实收金额
                $s_repay = D('Repayments')->checkCycles($item['loan_id'],$now_cyclical);
                if($s_repay) {
                    $r_money = $s_repay['r_money'];
                    $r_status = 1;
                }else {
                    $r_money = '0.00';
                    $r_status = 0;
                }
            }

            $repayToday[$key]['r_money'] = $r_money;
            $repayToday[$key]['r_status'] = $r_status;
            $sumRmoney += $r_money;

            $messageCondition = array(
                'loan_id' => $item['loan_id'],
                'customer_id' => $item['customer_id'],
                'send_status' => 1,
                'nowdata' => date('Y-m-d H:i:s',strtotime($today)),
            );
            $message = D('Message')->getMessageByCondition($messageCondition);
            if($message) {
                $repayToday[$key]['sendMessage'] = 1;
            }else {
                $repayToday[$key]['sendMessage'] = 0;
            }

        }


        $todayWeek = date('w',strtotime($today));
        switch ($todayWeek) {
            case 0: $todayWeek = '周日'; break;
            case 1: $todayWeek = '周一'; break;
            case 2: $todayWeek = '周二'; break;
            case 3: $todayWeek = '周三'; break;
            case 4: $todayWeek = '周四'; break;
            case 5: $todayWeek = '周五'; break;
            case 6: $todayWeek = '周六'; break;
        }
        $todayInfo = date('Y年m月d日',strtotime($today)) . ' ' . $todayWeek;
        $this->assign(array(
            'repayToday' => $repayToday,
            'todayInfo' => $todayInfo,
            'sumSmoney' => number_format($sumSmoney,2),
            'sumRmoney' => number_format($sumRmoney,2),
            'countPeople' => $countPeople,
            'statusSearchArray' => $statusArray,
            'userInfo' => $userInfo,
            'companys' => $companys
        ));
        $this->display();
    }

    public function exportExcel() {
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        $statusArray = array(
            '0' => array('id' => 2 , 'name' => '未还款'),
            '1' => array('id' => 1 , 'name' => '已还款'),
            '2' => array('id' => -1 , 'name' => '逾期中'),
        );

        if(I('get.loan_status','','string')) {
            $statusSearchArray = explode(',',I('get.loan_status','','string'));
            foreach ($statusArray as $key => $item) {
                $statusArray[$key]['selected'] = 0;
                foreach ($statusSearchArray as $i => $j) {
                    if($item['id'] == $j) {
                        $statusArray[$key]['selected'] = 1;
                    }
                }
            }
            foreach ($statusSearchArray as $i => $j) {
                if($j == 2) {
                    $statusSearchArray[$i] = 0;
                }
            }

        }else {
            $statusSearchArray = array(2,1);
            foreach ($statusArray as $key => $item) {
                $statusArray[$key]['selected'] = 0;
                foreach ($statusSearchArray as $i => $j) {
                    if($item['id'] == $j) {
                        $statusArray[$key]['selected'] = 1;
                    }
                }
            }
            foreach ($statusSearchArray as $i => $j) {
                if($j == 2) {
                    $statusSearchArray[$i] = 0;
                }
            }
        }

        if(I('get.reservationtime','','string')) {
            $today = I('get.reservationtime','','string');
            $this->assign('input_datepicker',$today);
            $today = str_replace('年','-',$today);
            $today = str_replace('月','-',$today);
            $today = str_replace('日','',$today);

        }else {
            $today = date('Y-m-d',time());
            $input_year = substr($today,0,4);
            $input_month = substr($today,5,2);
            $input_day = substr($today,8,2);
            $input_datepicker = $input_year . '年' . $input_month . '月' . $input_day . '日';
            $this->assign('input_datepicker',$input_datepicker);
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


        // 获取当前日期的应还款金额
        $condition['create_time'] = array('ELT',strtotime($today));
        $condition['exp_time'] = array('EGT',strtotime($today));

        $repayToday = D('Loan')->selectLoanOfRepaymentByDate($condition);
        // 计算当前日期是周几

        $weekDay = date('w',strtotime($today)) > 0 ? date('w',strtotime($today)) : 7;
        foreach ($repayToday as $key => $item) {
            if($item['product_id'] == 1 || $item['product_id'] == 4 || strtotime($today) == strtotime(date('Y-m-d',$item['create_time']))) {
                // 零用贷和车贷
                if($weekDay != $item['juti_data']) {
                    // 如果当前周值和还款周值不同，则剔除数据
                    unset($repayToday[$key]);
                }
            }else if($item['product_id'] == 3) {
                // 空放
                $phase_data = (strtotime($today) - $item['create_time']) / 86400;
                if($phase_data % ($item['juti_data'] - 1) != 0 || strtotime(date('Y-m-d',strtotime($today))) == strtotime(date('Y-m-d',$item['create_time']))) {
                    unset($repayToday[$key]);
                }
            }
        }

        $sumSmoney = 0;
        $sumRmoney = 0;
        $countPeople = 0;
        foreach ($repayToday as $key => $value) {
            $sumSmoney += $value['cyc_principal'];

            $countPeople++;

            // 获取公司信息
            $company = D('Company')->getCompanyByID($value['company_id']);
            $repayToday[$key]['company_name'] = $company['smallname'];
            //获取客户姓名和产品姓名
            if($value['customer_id'] != 0) {
                $customer = D('Customer')->findCustomerByCondition('id',$value['customer_id']);
                $repayToday[$key]['customer_name'] = $customer['name'];
                $repayToday[$key]['customer_phone'] = $customer['phone'];
            }
            if($value['product_id']) {
                $product = D('Product')->findProductByCondition('product_id',$value['product_id']);
                $repayToday[$key]['product_name'] = $product['product_name'];
                $cycle = D('Cycle')->findCycleByCondition('cycle_id',$product['cycle_id']);
                if($product['cycle_id'] == 1) {
                    // 日还
                    $repayToday[$key]['cycle_name'] = '每天';
                }else if($product['cycle_id'] == 2) {
                    // 周还
                    if($value['juti_data'] == 1) {
                        $repayToday[$key]['cycle_name'] = '每周一';
                    }else if($value['juti_data'] == 2) {
                        $repayToday[$key]['cycle_name'] = '每周二';
                    }else if($value['juti_data'] == 3) {
                        $repayToday[$key]['cycle_name'] = '每周三';
                    }else if($value['juti_data'] == 4) {
                        $repayToday[$key]['cycle_name'] = '每周四';
                    }else if($value['juti_data'] == 5) {
                        $repayToday[$key]['cycle_name'] = '每周五';
                    }else if($value['juti_data'] == 6) {
                        $repayToday[$key]['cycle_name'] = '每周六';
                    }else if($value['juti_data'] == 7) {
                        $repayToday[$key]['cycle_name'] = '每周日';
                    }
                }else if($product['cycle_id'] == 3) {
                    $repayToday[$key]['cycle_name'] = '每30天';
                }else if($product['cycle_id'] == 4) {
                    $repayToday[$key]['cycle_name'] = '每22天';
                }else if($product['cycle_id'] == 5) {
                    $repayToday[$key]['cycle_name'] = '每' . $value['juti_data'] . '天';
                }
            }
            if($value['staff_id'] != 0) {
                //获取业务经理姓名
                $staff = D('Staff')->findStaffByCondition('staff_id',$value['staff_id']);
                $repayToday[$key]['staff_name'] = $staff['staff_name'];
            }

            $now_cyclical = 0;
            $phase_data = (strtotime($today) - $value['create_time']) / 86400;

            if($value['product_id'] == 1 || $value['product_id'] == 4) {
                // 零用贷 // 车贷
                $now_cyclical = intval($phase_data / 7);
                if($now_cyclical == 0 ) {
                    $now_cyclical = $now_cyclical + 1;
                }
            }else if($value['product_id'] == 2 || $value['product_id'] == 5) {
                // 打卡 // 红包贷
                $now_cyclical = intval($phase_data + 1);
            }else if($value['product_id'] == 3) {
                // 空放
                $now_cyclical = intval($phase_data / $value['juti_data'] + 1);
            }

            $repayToday[$key]['now_cyclical'] = $now_cyclical . '/' . $value['cyclical'];

            if($value['loan_status'] == -1) {
                // 借款记录已经逾期
                $r_status = -1;
                $r_money = '0.00';
            }else {
                // 获取实收金额
                $s_repay = D('Repayments')->checkCycles($value['loan_id'],$now_cyclical);
                if($s_repay) {
                    $r_money = $s_repay['r_money'];
                    $r_status = 1;
                }else {
                    $r_money = '0.00';
                    $r_status = 0;
                }
            }

            $repayToday[$key]['r_money'] = $r_money;
            $repayToday[$key]['r_status'] = $r_status;
            $sumRmoney += $r_money;
        }

        $flag = 0;
        foreach ($repayToday as $key => $item) {
            $flag = 0;
            foreach($statusSearchArray as $i => $j) {
                if($item['r_status'] == $j) {
                    $flag = 1;
                    break;
                }
            }
            if($flag == 0) {
                unset($repayToday[$key]);
            }

            $repayToday[$key]['r_status'] = ($item['r_status'] == 1) ? '已还款' : (($item['r_status'] == -1) ? '逾期' : '未还款');
        }

        $m = 0;
        foreach ($repayToday as $key => $item) {
            if($item['customer_name'] != '') {
                $newData[$m++] = $item;
            }

        }
        foreach ($newData as $key => $item) {
            $newData[$key]['id'] = $key + 1;
        }



        $xlsName  = '每日应还表';

        if($userInfo['jurisdiction'] == 2) {
            $xlsCell  = array(
                array('id','序号'),
                array('create_time','借款时间'),
                array('customer_name','客户姓名'),
                array('customer_phone','客户电话'),
                array('product_name','借款类型'),
                array('principal','借款本金(元)'),
                array('cycle_name','还款时间'),
                array('now_cyclical','本次周期'),
                array('cyc_principal','应还金额（元）'),
                array('r_money','实收金额（元）'),
                array('staff_name','客户经理'),
                array('r_status','还款状态'),
                array('company_name','所属公司'),
            );
        }else {
            $xlsCell  = array(
                array('id','序号'),
                array('create_time','借款时间'),
                array('customer_name','客户姓名'),
                array('customer_phone','客户电话'),
                array('product_name','借款类型'),
                array('principal','借款本金(元)'),
                array('cycle_name','还款时间'),
                array('now_cyclical','本次周期'),
                array('cyc_principal','应还金额（元）'),
                array('r_money','实收金额（元）'),
                array('staff_name','客户经理'),
                array('r_status','还款状态'),
            );
        }


        $xlsData = $newData;
        $excel = new PhpexcelController();
        $excel->exportExcel($xlsName,$xlsCell,$xlsData);

    }
}