<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/8/1
 * Time: 22:40
 */

namespace Home\Controller;


use Think\Controller;
use Think\Exception;
use Think\Page2;

class LoanController extends CommonController {
    public function index() {
        try {

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

            $customers = D('Customer')->selectALLCustomer($companyCondition);
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
                // 搜索客户信息
                /*$customerInfoCondition = array(
                    'id' => array('IN',$customerSearchArray),
                    'company_id' => $companyCondition['company_id'],
                );

                $customerInfo = D('Customer')->selectALLCustomerOnlyByCondition($customerInfoCondition);

                $customerNameArray = array_column($customerInfo,'name');
                $customerNameInfoCondition = array(
                    'name' => array('IN',$customerNameArray),
                    'company_id' => $companyCondition['company_id'],
                );
                $customerNameInfo = D('Customer')->selectALLCustomerOnlyByCondition($customerNameInfoCondition);
                $customerSearchArrayId = array_column($customerNameInfo,'id');*/

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

            if(I('get.foreign_id','','string')) {
                $foreignSearchArray = explode(',',I('get.foreign_id','','string'));
                foreach ($foreign as $key => $item) {
                    $foreign[$key]['selected'] = 0;
                    foreach ($foreignSearchArray as $i => $j) {
                        if($item['staff_id'] == $j) {
                            $foreign[$key]['selected'] = 1;
                        }
                    }
                }
                $condition['foreign_id'] = array('IN',$foreignSearchArray);
            }


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
                $condition['loan_status'] = array('IN',$statusSearchArray);
            }

            if(I('get.reservationtime','','string')) {
                $search_datepicker = I('get.reservationtime','','string');
                $s_time = strtotime(substr($search_datepicker,0,19));
                $e_time = strtotime(substr($search_datepicker,24,19));
                $condition['create_time'] = array('BETWEEN',array($s_time,$e_time));
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
            //1.4 获取信息
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

            $loansIdArray = array_column($loansBak,'loan_id');
            if($loansIdArray) {
                $sum_rmoney = D('Repayments')->getSumofRemoneyByLoanIDArray($loansIdArray);
            }else {
                $sum_rmoney = '0.00';
            }


            /*foreach ($loansBak as $key => $item) {
                // 获取已经收款金额
                $repayment_rmoney = D('Repayments')->getSumOfRmoneyByLoanID($item['loan_id']);
                if(!$repayment_rmoney) {
                    $repayment_rmoney = 0;
                }
                $sum_rmoney = $sum_rmoney + $repayment_rmoney;
            }*/
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

                if($value['is_bond'] == 1) {

                    if($value['tour_id'] != 0) {
                        $tour = D('Tour')->getTourByID($value['tour_id']);
                        $loans[$key]['profit_money'] = $repayment_rmoney - $value['expenditure'] - $value['bond'] + $tour['money'];
                    }else {
                        $loans[$key]['profit_money'] = $repayment_rmoney - $value['expenditure'] - $value['bond'];
                    }

                }else {
                    if($value['tour_id'] != 0) {
                        $tour = D('Tour')->getTourByID($value['tour_id']);
                        $loans[$key]['profit_money'] = $repayment_rmoney - $value['expenditure'] + $tour['money'];
                    }else {
                        $loans[$key]['profit_money'] = $repayment_rmoney - $value['expenditure'];
                    }
                }

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

                $loans[$key]['cyc_benjin'] = $value['cyc_principal'] - $value['cyc_interest'];

                // 查询图片信息
                $images = D('Image')->selectImageByLoanID($value['loan_id']);
                if(!$images) {
                    $loans[$key]['is_image'] = 0;
                }else {
                    $loans[$key]['is_image'] = 1;
                }

                $loans[$key]['is_bond_name'] = $value['is_bond'] == 0 ? '未退还保证金' : '已退还保证金';
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
                'sum_principal' => number_format($sum_principal,2),
                'sum_expenditure' => number_format($sum_expenditure,2),
                'countLoans' => $countLoans,
                'sum_rmoney' => number_format($sum_rmoney,2),
                'userInfo' => $userInfo,
                'companys' => $companys
            ));
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
        $this->display();
    }

    public function add() {
        try {

            $condition = array();
            $companys = D('Company')->selectAllCompany();
            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
            $userInfo = $this->getUserNowInfo();
            if($userInfo['jurisdiction'] == 1) {
                $condition['company_id'] = $userInfo['company_id'];
            }else if($userInfo['jurisdiction'] == 2) {
                $companyIDs = array_column($companys,'company_id');
                $condition['company_id'] = array('IN',$companyIDs);
            }


            //客户列表
            $customers = D('Customer')->selectALLCustomer($condition);
            //产品列表
            $products = D('Product')->selectALLProduct();
            //周期列表
            $cycles = D('Cycle')->selectALLCycle();
            //经理列表
            $staffs = D('Staff')->selectALLStaff($condition);
            //手续费类型列表
            $poundages = D('Poundage')->selectALLPoundage();
            $this->assign(array(
                'customers' => $customers,
                'products' => $products,
                'cycles' => $cycles,
                'staffs' => $staffs,
                'poundages' => $poundages,
                'companys' => $companys,
                'userInfo' => $userInfo,
            ));
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
        $this->display();
    }

    /**
     * 功能：导出记录
     */
    public function export() {
        try {

            $condition = array();
            $companys = D('Company')->selectAllCompany();
            $userInfo = $this->getUserNowInfo();

            if(I('get.customer_id','','string')) {
                $customerSearchArray = explode(',',I('get.customer_id','','string'));
                $condition['customer_id'] = array('IN',$customerSearchArray);
            }

            if(I('get.product_id','','string')) {
                $productSearchArray = explode(',',I('get.product_id','','string'));
                $condition['product_id'] = array('IN',$productSearchArray);
            }

            if(I('get.staff_id','','string')) {
                $staffSearchArray = explode(',',I('get.staff_id','','string'));
                $condition['staff_id'] = array('IN',$staffSearchArray);
            }

            if(I('get.loan_status','','string')) {
                $statusSearchArray = explode(',',I('get.loan_status','','string'));
                foreach ($statusSearchArray as $i => $j) {
                    if($j == 2) {
                        $statusSearchArray[$i] = 0;
                    }
                }
                $condition['loan_status'] = array('IN',$statusSearchArray);
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
            $loans = D('Loan')->selectAllBycondition($condition);

            foreach($loans as $key => $value) {
                // 获取公司信息
                $company = D('Company')->getCompanyByID($value['company_id']);
                $loans[$key]['company_name'] = $company['smallname'];
                // 获取客户信息
                $customer = D('Customer')->findCustomerByCondition('id',$value['customer_id']);
                // 产品信息
                $product = D('Product')->findProductByCondition('product_id',$value['product_id']);

                // 获取还款期数
                $repay_cyclical = D('Repayments')->countRepayMents($value['loan_id']);
                if(!$repay_cyclical) {
                    $repay_cyclical = 0;
                }

                // 获取已经收款金额
                $repayment_rmoney = D('Repayments')->getSumOfRmoneyByLoanID($value['loan_id']);
                if(!$repayment_rmoney) {
                    $repayment_rmoney = 0;
                }
                // 获取违约金总额
                $repayment_bmoney = D('Repayments')->getSumOfBmoneyByLoanID($value['loan_id']);
                if(!$repayment_bmoney) {
                    $repayment_bmoney = 0;
                }

                $loans[$key]['id'] = $key + 1;  // 客户姓名
                $loans[$key]['customer_name'] = $customer['name'];  // 客户姓名
                $loans[$key]['customer_phone'] = $customer['phone'];  // 客户手机号码
                $loans[$key]['customer_idcard'] = $customer['idcard'];  // 客户身份证号
                $loans[$key]['product_name'] = $product['product_name'];  // 借款类型
                $loans[$key]['repay_cyclical'] = $repay_cyclical . '/' . $value['cyclical'];  // 已还期数
                $loans[$key]['repayment_rmoney'] = $repayment_rmoney;  // 已还款金额
                $loans[$key]['sum_principal'] = $value['cyc_principal'] * $value['cyclical'];  // 已还款金额
                $loans[$key]['repayment_bmoney'] = $repayment_bmoney;  // 已还款违约金
                $loans[$key]['create_time'] = date('Y-m-d',$value['create_time']);  // 已还款违约金
                $loans[$key]['exp_time'] = date('Y-m-d',$value['exp_time']);  // 已还款违约金
                $loans[$key]['loan_status'] = $value['loan_status'] == 0 ? '还款中' : ($value['loan_status'] == 1 ? '已结清' : '已逾期');  // 已还款违约金

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

            $xlsName  = '借款记录表信息';
            if($userInfo['jurisdiction'] == 2) {
                $xlsCell  = array(
                    array('id','序号'),
                    array('loan_id','借款编号'),
                    array('create_time','借款日期'),

                    array('customer_name','客户姓名'),
                    array('customer_phone','手机号码'),
                    array('customer_idcard','身份证号码'),
                    array('principal','借款金额'),
                    array('product_name','借款类型'),
                    array('cycle_name','还款时间'),
                    array('cyclical','借款周期'),
                    array('cyc_principal','每期应还'),
                    array('cyc_interest','每期利息'),

                    array('poundage','手续费'),
                    array('bond','保证金'),
                    array('rebate','同行返点'),
                    array('arrival','实际到账'),
                    array('expenditure','实际支出'),
                    array('staff_name','客户经理'),
                    array('foreign_name','上门经理'),
                    array('foreign_id','上门经理ID'),
                    array('exp_time','到期日期'),
                    array('loan_status','借款状态'),
                    array('repay_cyclical','已还期数'),
                    array('sum_principal','应还总额'),
                    array('repayment_rmoney','已还金额'),
                    array('repayment_bmoney','违约金额'),
                    array('company_name','所属公司'),
                );
            }else {
                $xlsCell  = array(
                    array('id','序号'),
                    array('loan_id','借款编号'),
                    array('create_time','借款日期'),

                    array('customer_name','客户姓名'),
                    array('customer_phone','手机号码'),
                    array('customer_idcard','身份证号码'),
                    array('principal','借款金额'),
                    array('product_name','借款类型'),
                    array('cycle_name','还款时间'),
                    array('cyclical','借款周期'),
                    array('cyc_principal','每期应还'),
                    array('cyc_interest','每期利息'),

                    array('poundage','手续费'),
                    array('bond','保证金'),
                    array('rebate','同行返点'),
                    array('arrival','实际到账'),
                    array('expenditure','实际支出'),
                    array('staff_name','客户经理'),
                    array('foreign_name','上门经理'),
                    array('foreign_id','上门经理ID'),

                    array('exp_time','到期日期'),
                    array('loan_status','借款状态'),
                    array('repay_cyclical','已还期数'),
                    array('sum_principal','应还总额'),
                    array('repayment_rmoney','已还金额'),
                    array('repayment_bmoney','违约金额'),
                );
            }

            $xlsData = $loans;
            $excel = new PhpexcelController();
            $excel->exportExcel($xlsName,$xlsCell,$xlsData);

        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }



    }

    public function checkAdd() {

        $customer_id = I('post.customer_id',0,'intval');
        $product_id = I('post.product_id',0,'intval');
        $principal = I('post.principal',0,'intval');
        $interest = I('post.interest',0,'intval');
        $cyclical = I('post.cyclical',0,'intval');
        $cyc_principal = I('post.cyc_principal',0,'intval');
        $cyc_interest = I('post.cyc_interest',0,'intval');
        $bond = I('post.bond',0,'intval');
        $rebate = I('post.rebate',0,'intval');
        $arrival = I('post.arrival',0,'intval');
        $expenditure = I('post.expenditure',0,'intval');
        $staff_id = I('post.staff_id',0,'intval');
        $foreign_id = I('post.foreign_id',0,'intval');
        $create_time = I('post.create_time','','');
        $juti_data = I('post.juti_data',0,'intval');
        $valArr = I('post.valArr');
        //$company_id = I('post.company_id',0,'intval');

        if(!$customer_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择客户'
            ));
        }
        if(!$product_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择产品类型'
            ));
        }
        if(!$principal) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入贷款金额'
            ));
        }
        /*if(!$interest) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入利息！'
            ));
        }*/
        if(!$cyclical) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入还款周期'
            ));
        }
       /* if(!$cyc_principal) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入每周期本金！'
            ));
        }
        if(!$cyc_interest) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入每周期利息！'
            ));
        }
        if(!$arrival) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入客户实际到账金额！'
            ));
        }
        if(!$expenditure) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入公司实际支出金额！'
            ));
        }*/
        if(!$staff_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择客户经理'
            ));
        }
        if(!$create_time) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择借贷时间'
            ));
        }

        /*if(!$valArr) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请上传外访资料'
            ));
        }*/

        /*if(!$company_id) {
            $userInfo = $this->getUserNowInfo();
            $company_id = $userInfo['company_id'];
        }*/

        try {
            $poundages = D('Poundage')->selectALLPoundage();
            $poundages_id = array_column($poundages,'poundage_id');
            $post_keys = array_keys($_POST);
            $poundages_input = array_intersect($poundages_id,$post_keys);
            if(!$poundages_input) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请选择手续费！'
                ));
            }else if($poundages_input) {
                $poundage = 0;
                foreach ($poundages_input as $key => $value) {
                    $poundage += $_POST[$value];
                }
            }

        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        $create_time = str_replace('年','-',$create_time);
        $create_time = str_replace('月','-',$create_time);
        $create_time = str_replace('日','',$create_time);
        $create_time = strtotime($create_time);


        $weekDay = date("w",$create_time) > 0 ? date("w",$create_time) : 7;

        if($product_id == 1 || $product_id == 4) {
            // 零用贷或车贷
            $add_data = 7 + ($juti_data - $weekDay) + ($cyclical - 1) * 7;
            $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.$add_data.' day');
        }else if($product_id == 2 || $product_id == 5) {
            // 打卡或红包贷
            $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.($cyclical-1).' day');

        }else if($product_id == 3) {
            // 空放
            $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.($juti_data-1)*$cyclical.' day');
        }

        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
        $userInfo = $this->getUserNowInfo();
        if($userInfo['jurisdiction'] == 1) {
            $condition['company_id'] = $userInfo['company_id'];
        }

        // 查看客户信息（哪个公司的）
        $customer = D('Customer')->getCustomerByID($customer_id);

        if($foreign_id) {
            // 如果有外访经理的话
            $tour = D('Tour')->selectTourByCustomerStaffID($customer_id, $foreign_id);
            if($tour) {
                $tour_id = $tour[0]['tour_id'];
            }else {
                $tour_id = 0;
            }

        }else {
            $tour_id = 0;
        }


        $loanData = array(
            'customer_id' => $customer_id,
            'product_id' => $product_id,
            'principal' => $principal,
            'interest' => $interest,
            'cyclical' => $cyclical,
            'cyc_principal' => $cyc_principal,
            'cyc_interest' => $cyc_interest,
            'poundage' => $poundage,
            'bond' => $bond,
            'rebate' => $rebate,
            'arrival' => $arrival,
            'expenditure' => $expenditure,
            'staff_id' => $staff_id,
            'foreign_id' => $foreign_id,
            'create_time' => $create_time,
            'juti_data' => $juti_data,
            'exp_time' => $exp_time,
            'company_id' => $customer['company_id'],
            'tour_id' => $tour_id,

        );

        try {
            $loan_id = D('Loan')->addLoan($loanData);
            if($loan_id) {
                foreach ($poundages_input as $key => $value) {
                    $poundageDetail = array(
                        'loan_id' => $loan_id,
                        'poundage_id' => $value,
                        'money' => $_POST[$value],
                        'create_time' => time(),
                    );
                    $poundage_id = D('Poundagedetail')->addPoundagedetail($poundageDetail);
                    if(!$poundage_id) {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '手续费详情写入失败，请重新再试！'
                        ));
                    }
                }

                foreach ($valArr as $key => $item) {
                    $imageData = array(
                        'url' => $item,
                        'user_id' => $userInfo['user_id'],
                        'gmt_create' => date('Y-m-d H:i:s',time()),
                        'company_id' => $customer['company_id'],
                        'loan_id' => $loan_id,
                        'customer_id' => $customer['id'],
                    );
                    $image = D('Image')->addImage($imageData);
                }

                $setInc = D('Customer')->setIncLoanTimes($customer_id);

                if($foreign_id && $tour_id) {
                    $tourData = array(
                        'loan_id' => $loan_id,
                        'is_loan' => 1,
                    );
                    $update_tour = D('Tour')->updateTour($tour_id,$tourData);
                }

                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '成功添加一条记录！'
                ));

            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '添加失败，请稍后重试！'
                ));
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    public function deleteLoan() {
        $loan_id = I('post.id',0,'intval');
        if($loan_id) {
            try {
//                $res = D('Loan')->deleteOneLoanByID($loan_id);
                $loan = D('Loan')->getLoanByID($loan_id);
                // 用户借款次数减1
                $customer_times = D('Customer')->loanTimesSetDec($loan['customer_id']);
                $res = D('Loan')->logicDeleteByLoanID($loan_id);
                //逻辑删除对应的还款记录
                $logicRepay = D('Repayments')->logicDeleteByLoanID($loan_id);
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

    public function editLoan() {

        $loan_id = I('post.id',0,'intval');
        if($loan_id) {
            try {
                $loan = D('Loan')->findLoanByCondition('loan_id',$loan_id);

                if($loan) {
                    //转换时间格式
                    $loan['create_time'] = date('Y年m月d日',$loan['create_time']);
                    //查找手机号码和身份证号码

                    if($loan['customer_id'] != 0) {
                        $customer = D('Customer')->findCustomerByCondition('id',$loan['customer_id']);

                        $loan['phone'] = $customer['phone'];
                        $loan['idcard'] = $customer['idcard'];
                    }
                    //查找还款期限信息
                    if($loan['product_id'] != 0) {
                        $product = D('Product')->findProductByCondition('product_id',$loan['product_id']);
                        $loan['cycle_id'] = $product['cycle_id'];
                        /*print_r($product);
                        if($product) {
                            $cycle = D('Cycle')->findCycleByCondition('cycyle_id',$product['cycle_id']);
                            $loan['cycle_id'] = $cycle['cycle_id'];
                        }else {
                            $this->ajaxReturn(array(
                                'status' => 0,
                                'message' => '产品信息不存在！',
                            ));
                        }
                        print_r($cycle);*/
                    }
                    //查找手续费信息
                    unset($loan['poundage']);
                    $poundage_arrays = D('Poundagedetail')->selectPoundageByCondition('loan_id',$loan['loan_id']);

                    foreach ($poundage_arrays as $key => $item) {
                        $poundage_info = D('Poundage')->findPoundageByCondition('poundage_id',$item['poundage_id']);
                        $poundage_arrays[$key]['poundage_name'] = $poundage_info['poundage_name'];
                    }
                    $loan['poundage_arrays'] = $poundage_arrays;

                    // 图片信息
                    $image = D('Image')->selectImageByLoanID($loan_id);
                    if(!$image) {
                        $loan['is_image'] = 0;
                    }else {
                        $loan['is_image'] = 1;
                    }


                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '成功获取该记录！',
                        'data' => $loan,
                    ));

                }else if(!$loan) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '记录获取失败，请稍后重试！',
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

    public function checkEdit() {

        $loan_id = I('post.loan_id',0,'intval');
        $customer_id = I('post.customer_id',0,'intval');
        $product_id = I('post.product_id',0,'intval');
        $principal = I('post.principal',0,'intval');
        $interest = I('post.interest',0,'intval');
        $cyclical = I('post.cyclical',0,'intval');
        $cyc_principal = I('post.cyc_principal',0,'intval');
        $cyc_interest = I('post.cyc_interest',0,'intval');
        $bond = I('post.bond',0,'intval');
        $rebate = I('post.rebate',0,'intval');
        $arrival = I('post.arrival',0,'intval');
        $expenditure = I('post.expenditure',0,'intval');
        $staff_id = I('post.staff_id',0,'intval');
        $foreign_id = I('post.foreign_id',0,'intval');
        $create_time = I('post.create_time','','');
        $juti_data = I('post.juti_data',0,'intval');
        $remark = I('post.remark','','');
        $valArr = I('post.valArr');

        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '贷款记录ID为空，请稍后重试！'
            ));
        }
        if(!$customer_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择客户！'
            ));
        }
        if(!$product_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择产品类型！'
            ));
        }
        if(!$principal) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入贷款金额！'
            ));
        }
       /* if(!$interest) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入利息！'
            ));
        }*/
        if(!$cyclical) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入还款周期！'
            ));
        }
      /*  if(!$cyc_principal) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入每周期本金！'
            ));
        }
        if(!$cyc_interest) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入每周期利息！'
            ));
        }*/

        /*if(!$bond) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入保证金金额！'
            ));
        }*/
        /*if(!$rebate) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入同行返点金额！'
            ));
        }*/
        /*if(!$arrival) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入客户实际到账金额！'
            ));
        }
        if(!$expenditure) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入公司实际支出金额！'
            ));
        }*/
        if(!$staff_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择客户经理！'
            ));
        }
        if(!$create_time) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择借贷时间！'
            ));
        }

        try {
            $poundages = D('Poundage')->selectALLPoundage();
            $poundages_id = array_column($poundages,'poundage_id');
            $post_keys = array_keys($_POST);

            $poundages_input = array_intersect($poundages_id,$post_keys);
            if(!$poundages_input) {
                $poundage = 0;
                /*$this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请选择手续费！'
                ));*/
            }else if($poundages_input) {
                $poundage = 0;
                foreach ($poundages_input as $key => $value) {
                    $poundage += $_POST[$value];
                }
            }

        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        $create_time = str_replace('年','-',$create_time);
        $create_time = str_replace('月','-',$create_time);
        $create_time = str_replace('日','',$create_time);
        $create_time = strtotime($create_time);



        $weekDay = date("w",$create_time) > 0 ? date("w",$create_time) : 7;

        if($product_id == 1 || $product_id == 4) {
            // 零用贷或车贷
            $add_data = 7 + ($juti_data - $weekDay) + ($cyclical - 1) * 7;
            $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.$add_data.' day');
        }else if($product_id == 2 || $product_id == 5) {
            // 打卡或红包贷
            $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.($cyclical-1).' day');

        }else if($product_id == 3) {
            // 空放
            $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.($juti_data-1)*$cyclical.' day');
        }


        $loanData = array(
            'customer_id' => $customer_id,
            'product_id' => $product_id,
            'principal' => $principal,
            'interest' => $interest,
            'cyclical' => $cyclical,
            'cyc_principal' => $cyc_principal,
            'cyc_interest' => $cyc_interest,
            'poundage' => $poundage,
            'bond' => $bond,
            'rebate' => $rebate,
            'arrival' => $arrival,
            'expenditure' => $expenditure,
            'staff_id' => $staff_id,
            'foreign_id' => $foreign_id,
            'create_time' => $create_time,
            'update_time' => time(),
            'exp_time' => $exp_time,
            'juti_data' => $juti_data,
            'remark' => $remark,
        );

        try {
            //比对原始数据查看是否改变数据
            $loan = D('Loan')->findLoanByCondition('loan_id',$loan_id);
            /*if($loan['customer_id'] == $customer_id && $loan['product_id'] == $product_id
                && $loan['principal'] == $principal && $loan['interest'] == $interest
                && $loan['cyclical'] == $cyclical && $loan['cyc_principal'] == $cyc_principal
                && $loan['cyc_principal'] == $cyc_principal && $loan['cyc_interest'] == $cyc_interest
                && $loan['poundage'] == $poundage && $loan['bond'] == $bond
                && $loan['rebate'] == $rebate && $loan['arrival'] == $arrival
                && $loan['expenditure'] == $expenditure && $loan['staff_id'] == $staff_id
                && $loan['foreign_id'] == $foreign_id && $loan['create_time'] == $create_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '未作任何修改！'
                ));
            }*/

            $update_loan = D('Loan')->updateLoanByID($loan_id,$loanData);
            $customer = D('Customer')->getCustomerByID($customer_id);
            $userInfo = $this->getUserNowInfo();
            foreach ($valArr as $key => $item) {
                $imageData = array(
                    'url' => $item,
                    'user_id' => $userInfo['user_id'],
                    'gmt_create' => date('Y-m-d H:i:s',time()),
                    'company_id' => $customer['company_id'],
                    'loan_id' => $loan_id,
                    'customer_id' => $customer_id,
                );
                $image = D('Image')->addImage($imageData);
            }

            if($update_loan) {
                $delete = D('Poundagedetail')->deletePoundageDetailByLoanID($loan_id);

                foreach ($poundages_input as $key => $value) {
                    $poundageDetail = array(
                        'loan_id' => $loan_id,
                        'poundage_id' => $value,
                        'money' => $_POST[$value],
                        'create_time' => time(),
                    );
                    $poundage_id = D('Poundagedetail')->addPoundagedetail($poundageDetail);
                    if(!$poundage_id) {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '手续费详情写入失败，请重新再试！'
                        ));
                    }
                }

                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '修改成功！'
                ));
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '修改失败，请稍后重试！'
                ));
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    public function changeStatus() {
        $loan_id = I('post.loan_id',0,'intval');
        $loan_status = I('post.loan_status',0,'intval');
        $is_bond = I('post.bond',0,'intval');
        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '记录ID为空'
            ));
        }
        try {
            if($loan_status == -1) {
                $data = array(
                    'loan_status' => $loan_status,
                    'gmt_overdue' => time(),
                    'is_bond' => $is_bond,
                );
            }else {
                $data = array(
                    'loan_status' => $loan_status,
                    'is_bond' => $is_bond,
                );
            }

            //$chageLoan = D('Loan')->updateOneLoanFieldByID($loan_id,'loan_status',$loan_status);
            $chageLoan = D('Loan')->updateLoanByID($loan_id,$data);
            if($chageLoan) {
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '状态切换成功'
                ));
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '状态切换失败，请稍后重试'
                ));
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 根据借款ID获取借款信息
     */
    public function getLoanByID() {
        $loan_id = I('post.loan_id',0,'intval');
        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '记录ID为空！'
            ));
        }
        try {
            // 获取借款信息
            $loan = D('Loan')->getLoanByID($loan_id);

            // 获取还款信息
            $repayCount = D('Repayments')->countRepayMents($loan_id);
            if(!$loan) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '借款信息查找失败！'
                ));
            }

            $repayCycles = array();
            for ($i=1 ; $i < $loan['cyclical']+1; $i++) {
                // 查看该期是否存在
                $selected = 0;
                if($i == ($repayCount+1)) {
                    $selected = 1;
                }
                $repayment = D('Repayments')->checkCycles($loan_id,$i);
                if($repayment) {
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
                'message' => '借款信息查找成功！',
                'loan' => $loan,
                'repayCount' => $repayCount + 1,
                'repayCycles' => $repayCycles,
            ));
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}