<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/4
 * Time: 16:36
 */

namespace Home\Controller;
use Think\Controller;
use Think\Exception;
use Think\Page2;

class OverdueController extends CommonController
{
    public function index() {
        $condition = array();
        $companyCondition = array();
        //客户列表
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
        if($userInfo['jurisdiction'] == 1) {
            $companyCondition['company_id'] = $userInfo['company_id'];
        }else if($userInfo['jurisdiction'] == 2) {
            $companyIDs = array_column($companys,'company_id');
            $companyCondition['company_id'] = array('IN',$companyIDs);
        }


        //客户列表
        // 查找所有逾期的的借款记录
        $loanCondition = array();
        $loanCondition['loan_status'] = array('eq',-1);
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
        //产品列表
        $products = D('Product')->selectALLProduct();
        //经理列表
        $staffs = D('Staff')->selectALLStaff($companyCondition);
        $foreign = $staffs;
        $statusArray = array(
            '0' => array('id' => 2 , 'name' => '还款中'),
            '1' => array('id' => 1 , 'name' => '已结清'),
            '2' => array('id' => -1 , 'name' => '已逾期'),
        );

        if(I('get.loan_id',0,'intval')) {
            $condition['loan_id'] = I('get.loan_id',0,'intval');
        }

        if(I('get.customer_id','','string')) {
            $customerSearchArray = explode(',',I('get.customer_id','','string'));
            $condition['customer_id'] = array('IN',$customerSearchArray);
            foreach ($customers as $key => $item) {
                $customers[$key]['selected'] = 0;
                foreach ($customerSearchArray as $i => $j) {
                    if($item['id'] == $j) {
                        $customers[$key]['selected'] = 1;
                    }
                }

            }
        }

        if(I('get.product_id','','string')) {
            $productSearchArray = explode(',',I('get.product_id','','string'));
            foreach ($products as $key => $item) {
                $products[$key]['selected'] = 0;
                foreach ($productSearchArray as $i => $j) {
                    if($item['product_id'] == $j) {
                        $products[$key]['selected'] = 1;
                    }
                }
            }
            $condition['product_id'] = array('IN',$productSearchArray);
        }

        if(I('get.staff_id','','string')) {
            $staffSearchArray = explode(',',I('get.staff_id','','string'));
            foreach ($staffs as $key => $item) {
                $staffs[$key]['selected'] = 0;
                foreach ($staffSearchArray as $i => $j) {
                    if($item['staff_id'] == $j) {
                        $staffs[$key]['selected'] = 1;
                    }
                }
            }
            $condition['staff_id'] = array('IN',$staffSearchArray);
        }

        if(I('get.reservationtime','','string')) {
            $search_datepicker = I('get.reservationtime','','string');
            // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
            // 分解为
            // 2017-11-17 12:30:00
            // 2017-11-25 15:30:20
            $s_time = strtotime(substr($search_datepicker,0,19));
            $e_time = strtotime(substr($search_datepicker,24,19));
            $condition['create_time'] = array('BETWEEN',array($s_time,$e_time));
            $this->assign('input_datepicker',$search_datepicker);
        }

        if(I('get.overduetime','','string')) {
            $search_datepicker = I('get.overduetime','','string');
            // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
            // 分解为
            // 2017-11-17 12:30:00
            // 2017-11-25 15:30:20
            $s_time = strtotime(substr($search_datepicker,0,19));
            $e_time = strtotime(substr($search_datepicker,24,19));
            $condition['gmt_overdue'] = array('BETWEEN',array($s_time,$e_time));
            $this->assign('input_overduetime',$search_datepicker);
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
        //1.4 获取信息
        // 逾期的标志
        $condition['loan_status'] = array('eq',-1);
        $loans = D('Loan')->selectAll($condition,$page,$page_size);
        $loansBak = D('Loan')->selectAllBycondition($condition);
        $countLoans = D('Loan')->countLoans($condition);

        $sum_principal = D('Loan')->sumLoansByCondition($condition,'principal');
        $sum_expenditure = D('Loan')->sumLoansByCondition($condition,'expenditure');

        $sum_principal = $sum_principal ? $sum_principal : 0;
        $sum_expenditure = $sum_expenditure ? $sum_expenditure : 0;

        //1.5 实例化一个分页对象
        $res = new Page2($countLoans,$pageSize);
        //1.6 调用show方法前台显示页码
        $pageRes = $res->show();



        //周期列表
        $cycles = D('Cycle')->selectALLCycle();
        $sum_rmoney = 0;
        $repayment_smoney = 0;
        $profit_money = 0;
        foreach ($loansBak as $key => $item) {
            // 获取已经收款金额
            $repayment_rmoney = D('Repayments')->getSumOfRmoneyByLoanID($item['loan_id']);
            if(!$repayment_rmoney) {
                $repayment_rmoney = 0;
            }
            $sum_rmoney = $sum_rmoney + $repayment_rmoney;
            // 获取剩余应还
            $repayment_smoney = $repayment_smoney + ($item['cyc_principal'] * $item['cyclical'] - $repayment_rmoney);
            // 利润
            $profit_money = $profit_money + ($repayment_rmoney - $item['expenditure']);
        }
        //手续费类型列表
        $poundages = D('Poundage')->selectALLPoundage();
        foreach($loans as $key => $value) {

            // 获取公司信息
            $company = D('Company')->getCompanyByID($value['company_id']);
            $loans[$key]['company_name'] = $company['smallname'];
            // 获取还款期数
            $repay_cyclical = D('Repayments')->countRepayMents($value['loan_id']);
            if(!$repay_cyclical) {
                $repay_cyclical = 0;
            }
            $loans[$key]['repay_cyclical'] = $repay_cyclical;

            // 获取已经收款金额
            $repayment_rmoney = D('Repayments')->getSumOfRmoneyByLoanID($value['loan_id']);
            if(!$repayment_rmoney) {
                $repayment_rmoney = 0;
            }
            $loans[$key]['repayment_rmoney'] = $repayment_rmoney;
            // 剩余应还 = 每期应还* 期数 - 已还
            $loans[$key]['repayment_smoney'] = $value['cyc_principal'] * $value['cyclical'] - $repayment_rmoney;

            // 单客人 收益  = 总已还金额 - 公司实际支出 + 上门费用
            /*$tour = 0;
            $tour = D('Tour')->getTourByLoanID($value['loan_id']);
            if($tour) {
                $tourMoney = $tour['money'];
            }else {
                $tourMoney = 0;
            }*/
            //$loans[$key]['profit_money'] = $repayment_rmoney - $value['expenditure'] + $tourMoney;
            $loans[$key]['profit_money'] = $repayment_rmoney - $value['expenditure'];


            if($value['product_id'] == 5) {
                // 红包贷
                $loans[$key]['sum_principal'] = $value['cyc_principal'] * $value['cyclical'] + $value['principal'];  // 应还款总额
            }else if($value['product_id'] == 3) {
                // 空放
                $loans[$key]['sum_principal'] = $value['cyc_principal'] * ($value['cyclical']-1) + $value['principal'];  // 应还款总额
            }

            else {
                $loans[$key]['sum_principal'] = $value['cyc_principal'] * $value['cyclical'];  // 应还款总额
            }


            $loans[$key]['repay_cyclical'] = $repay_cyclical . '/' . $value['cyclical'];  // 已还期数



            //获取客户姓名和产品姓名
            if($value['customer_id'] != 0) {
                $customer = D('Customer')->findCustomerByCondition('id',$value['customer_id']);
                $loans[$key]['customer_name'] = $customer['name'];
                $loans[$key]['customer_phone'] = $customer['phone'];
            }
            if($value['product_id']) {
                $product = D('Product')->findProductByCondition('product_id',$value['product_id']);
                $loans[$key]['product_name'] = $product['product_name'];
                $cycle = D('Cycle')->findCycleByCondition('cycle_id',$product['cycle_id']);
                if($product['cycle_id'] == 1) {
                    // 日还
                    $loans[$key]['cycle_name'] = '每天';
                }else if($product['cycle_id'] == 2) {
                    // 周还
                    if($value['juti_data'] == 1) {
                        $loans[$key]['cycle_name'] = '每周一';
                    }else if($value['juti_data'] == 2) {
                        $loans[$key]['cycle_name'] = '每周二';
                    }else if($value['juti_data'] == 3) {
                        $loans[$key]['cycle_name'] = '每周三';
                    }else if($value['juti_data'] == 4) {
                        $loans[$key]['cycle_name'] = '每周四';
                    }else if($value['juti_data'] == 5) {
                        $loans[$key]['cycle_name'] = '每周五';
                    }else if($value['juti_data'] == 6) {
                        $loans[$key]['cycle_name'] = '每周六';
                    }else if($value['juti_data'] == 7) {
                        $loans[$key]['cycle_name'] = '每周日';
                    }
                }else if($product['cycle_id'] == 3) {
                    $loans[$key]['cycle_name'] = '每30天';
                }else if($product['cycle_id'] == 4) {
                    $loans[$key]['cycle_name'] = '每22天';
                }else if($product['cycle_id'] == 5) {
                    $loans[$key]['cycle_name'] = '每' . $value['juti_data'] . '天';
                }
            }
            if($value['staff_id'] != 0) {
                //获取业务经理姓名
                $staff = D('Staff')->findStaffByCondition('staff_id',$value['staff_id']);
                $loans[$key]['staff_name'] = $staff['staff_name'];
            }
            if($value['foreign_id'] != 0) {
                //获取上门经理姓名
                $staff = D('Staff')->findStaffByCondition('staff_id',$value['foreign_id']);
                $loans[$key]['foreign_name'] = $staff['staff_name'];
            }
            if($value['gmt_overdue'] != 0) {
                $loans[$key]['gmt_overdue'] = date('Y-m-d',$value['gmt_overdue']);
            }else {
                $loans[$key]['gmt_overdue'] = '';
            }

        }

        $this->assign(array(
            'loans' => $loans,
            'customers' => $customers,
            'products' => $products,
            'cycles' => $cycles,
            'staffs' => $staffs,
            'foreigns' => $foreign,
            'poundages' => $poundages,
            'statusSearchArray' => $statusArray,
            'pageRes' => $pageRes,
            'sum_expenditure' => number_format($sum_expenditure,2),
            'countLoans' => $countLoans,
            'sum_rmoney' => number_format($sum_rmoney,2),
            'repayment_smoney' => number_format($repayment_smoney,2),
            'profit_money' => number_format($profit_money,2),
            'userInfo' => $userInfo,
            'companys' => $companys
        ));

        $this->display();
   }

    public function export() {
        $condition = array();
        $companyCondition = array();
        //客户列表
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
        if($userInfo['jurisdiction'] == 1) {
            $companyCondition['company_id'] = $userInfo['company_id'];
        }else if($userInfo['jurisdiction'] == 2) {
            $companyIDs = array_column($companys,'company_id');
            $companyCondition['company_id'] = array('IN',$companyIDs);
        }


        if(I('get.loan_id',0,'intval')) {
            $condition['loan_id'] = I('get.loan_id',0,'intval');
        }

        if(I('get.customer_id','','string')) {
            $customerSearchArray = explode(',', I('get.customer_id', '', 'string'));
            $condition['customer_id'] = array('IN', $customerSearchArray);
        }

        if(I('get.product_id','','string')) {
            $productSearchArray = explode(',',I('get.product_id','','string'));
            $condition['product_id'] = array('IN',$productSearchArray);
        }

        if(I('get.staff_id','','string')) {
            $staffSearchArray = explode(',',I('get.staff_id','','string'));
            $condition['staff_id'] = array('IN',$staffSearchArray);
        }

        if(I('get.reservationtime','','string')) {
            $search_datepicker = I('get.reservationtime','','string');
            $s_time = strtotime(substr($search_datepicker,0,19));
            $e_time = strtotime(substr($search_datepicker,24,19));
            $condition['create_time'] = array('BETWEEN',array($s_time,$e_time));
        }

        if(I('get.overduetime','','string')) {
            $search_datepicker = I('get.overduetime','','string');
            $s_time = strtotime(substr($search_datepicker,0,19));
            $e_time = strtotime(substr($search_datepicker,24,19));
            $condition['gmt_overdue'] = array('BETWEEN',array($s_time,$e_time));
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

        //1.4 获取信息
        // 逾期的标志
        $condition['loan_status'] = array('eq',-1);
        //1.4 获取信息
        $loans = D('Loan')->selectAllBycondition($condition);
        //周期列表
        $cycles = D('Cycle')->selectALLCycle();
        //手续费类型列表
        $poundages = D('Poundage')->selectALLPoundage();
        foreach($loans as $key => $value) {
            // 获取公司信息
            $company = D('Company')->getCompanyByID($value['company_id']);
            $loans[$key]['company_name'] = $company['smallname'];
            // 获取还款期数
            $repay_cyclical = D('Repayments')->countRepayMents($value['loan_id']);
            if(!$repay_cyclical) {
                $repay_cyclical = 0;
            }
            $loans[$key]['repay_cyclical'] = $repay_cyclical;

            // 获取已经收款金额
            $repayment_rmoney = D('Repayments')->getSumOfRmoneyByLoanID($value['loan_id']);
            if(!$repayment_rmoney) {
                $repayment_rmoney = 0;
            }
            $loans[$key]['repayment_rmoney'] = $repayment_rmoney;
            // 剩余应还 = 每期应还* 期数 - 已还
            $loans[$key]['repayment_smoney'] = $value['cyc_principal'] * $value['cyclical'] - $repayment_rmoney;

            $loans[$key]['profit_money'] = $repayment_rmoney - $value['expenditure'];

            if($value['product_id'] == 5) {
                // 红包贷
                $loans[$key]['sum_principal'] = $value['cyc_principal'] * $value['cyclical'] + $value['principal'];  // 应还款总额
            }else if($value['product_id'] == 3) {
                // 空放
                $loans[$key]['sum_principal'] = $value['cyc_principal'] * ($value['cyclical']-1) + $value['principal'];  // 应还款总额
            }

            else {
                $loans[$key]['sum_principal'] = $value['cyc_principal'] * $value['cyclical'];  // 应还款总额
            }

            $loans[$key]['repay_cyclical'] = $repay_cyclical . '/' . $value['cyclical'];  // 已还期数

            //获取客户姓名和产品姓名
            if($value['customer_id'] != 0) {
                $customer = D('Customer')->findCustomerByCondition('id',$value['customer_id']);
                $loans[$key]['customer_name'] = $customer['name'];
                $loans[$key]['customer_phone'] = $customer['phone'];
            }
            if($value['product_id']) {
                $product = D('Product')->findProductByCondition('product_id',$value['product_id']);
                $loans[$key]['product_name'] = $product['product_name'];
                $cycle = D('Cycle')->findCycleByCondition('cycle_id',$product['cycle_id']);
                if($product['cycle_id'] == 1) {
                    // 日还
                    $loans[$key]['cycle_name'] = '每天';
                }else if($product['cycle_id'] == 2) {
                    // 周还
                    if($value['juti_data'] == 1) {
                        $loans[$key]['cycle_name'] = '每周一';
                    }else if($value['juti_data'] == 2) {
                        $loans[$key]['cycle_name'] = '每周二';
                    }else if($value['juti_data'] == 3) {
                        $loans[$key]['cycle_name'] = '每周三';
                    }else if($value['juti_data'] == 4) {
                        $loans[$key]['cycle_name'] = '每周四';
                    }else if($value['juti_data'] == 5) {
                        $loans[$key]['cycle_name'] = '每周五';
                    }else if($value['juti_data'] == 6) {
                        $loans[$key]['cycle_name'] = '每周六';
                    }else if($value['juti_data'] == 7) {
                        $loans[$key]['cycle_name'] = '每周日';
                    }
                }else if($product['cycle_id'] == 3) {
                    $loans[$key]['cycle_name'] = '每30天';
                }else if($product['cycle_id'] == 4) {
                    $loans[$key]['cycle_name'] = '每22天';
                }else if($product['cycle_id'] == 5) {
                    $loans[$key]['cycle_name'] = '每' . $value['juti_data'] . '天';
                }
            }
            if($value['staff_id'] != 0) {
                //获取业务经理姓名
                $staff = D('Staff')->findStaffByCondition('staff_id',$value['staff_id']);
                $loans[$key]['staff_name'] = $staff['staff_name'];
            }
            if($value['foreign_id'] != 0) {
                //获取上门经理姓名
                $staff = D('Staff')->findStaffByCondition('staff_id',$value['foreign_id']);
                $loans[$key]['foreign_name'] = $staff['staff_name'];
            }
        }

        $id = 1;
        foreach ($loans as $key => $item) {
            $loans[$key]['id'] = $id++;
        }

        $xlsName  = '逾期汇总信息表';
        if($userInfo['jurisdiction'] == 2) {
            $xlsCell  = array(
                array('id','序号'),
                array('gmt_overdue','逾期日期'),
                array('create_time','借款日期'),
                array('customer_name','客户姓名'),
                array('customer_phone','手机号码'),
                array('product_name','借款类型'),
                array('principal','借款金额'),
                array('expenditure','实际支出'),
                array('cyc_principal','每期应还'),
                array('cycle_name','还款时间'),
                array('repay_cyclical','借还周期'),
                array('cyc_interest','每期利息'),
                array('repayment_rmoney','已还金额'),
                array('repayment_smoney','剩余应还'),
                array('profit_money','损益情况'),
                array('staff_name','客户经理'),

                array('company_name','所属公司'),
            );
        }else {
            $xlsCell  = array(
                array('id','序号'),
                array('gmt_overdue','逾期日期'),
                array('create_time','借款日期'),
                array('customer_name','客户姓名'),
                array('customer_phone','手机号码'),
                array('product_name','借款类型'),
                array('principal','借款金额'),
                array('expenditure','实际支出'),
                array('cyc_principal','每期应还'),
                array('cycle_name','还款时间'),
                array('repay_cyclical','借还周期'),
                array('cyc_interest','每期利息'),
                array('repayment_rmoney','已还金额'),
                array('repayment_smoney','剩余应还'),
                array('profit_money','损益情况'),
                array('staff_name','客户经理'),
            );
        }

        $xlsData = $loans;
        $excel = new PhpexcelController();
        $excel->exportExcel($xlsName,$xlsCell,$xlsData);
    }

}