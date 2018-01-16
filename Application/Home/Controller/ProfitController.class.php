<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/16
 * Time: 11:26
 */

namespace Home\Controller;


class ProfitController extends CommonController
{
    public function index() {
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


        if(I('get.search_datepicker_start','','string')) {
            $search_datepicker_start = I('get.search_datepicker_start','','string');
            $this->assign('input_datepicker_start',$search_datepicker_start);
        }else {
            // 今年的第一个月
            $search_datepicker_start = date('Y-m',strtotime(date('Y',time())));
            $this->assign('input_datepicker_start',$search_datepicker_start);
        }

        if(I('get.search_datepicker_end','','string')) {
            $search_datepicker_end = I('get.search_datepicker_end','','string');
            $this->assign('input_datepicker_end',$search_datepicker_end);
        }else {
            $search_datepicker_end = date('Y-m',time());
            $this->assign('input_datepicker_end',$search_datepicker_end);
        }

        $companyCondition = array();
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
            $companyCondition = array('IN',$companySearchArray);
        }else {
            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据

            if($userInfo['jurisdiction'] == 1) {
                $companyCondition = $userInfo['company_id'];
            }else if($userInfo['jurisdiction'] == 2){
                $companyIDs = array_column($companys,'company_id');
                $companyCondition = array('IN',$companyIDs);
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


        $date_begain = $search_datepicker_start;
        $date_end = $search_datepicker_end;

        $begainArray = explode('-',$date_begain);
        $begain_year = intval($begainArray[0]);
        $begain_month = intval($begainArray[1]);

        $date_begain_time = strtotime($date_begain);
        $date_end_time = strtotime($date_end);

        $year1 = date('Y',$date_begain_time);
        $month1 = date('m',$date_begain_time);
        $year2 = date('Y',$date_end_time);
        $month2 = date('m',$date_end_time);

        // 计算两个时间之间差几个月
        $month_number = ($year2 * 12 + $month2) - ($year1 * 12 + $month1);
        // 算上最后一个月

        $profitArray = array();

        for($month_number = $month_number + 1;$month_number > 0; $month_number --) {
            $startDate = strtotime($begain_year . '-' . $begain_month);
            $endDate = strtotime($begain_year . '-' . $begain_month . '+1 month -1 second');
            //print_r(date('Y-m-d H:i:s',$startDate) . ',' .date('Y-m-d H:i:s',$endDate));
            // 放款笔数
            $loanCondition['create_time'] = array('BETWEEN',array($startDate,$endDate));
            $loanCondition['company_id'] = $companyCondition;
            $loanTimes = D('Loan')->countLoans($loanCondition);
            if(!$loanTimes) {
                $loanTimes = 0;
            }

            // 放款支出
            $loanExpend = D('Loan')->sumLoanExpenditureByCondition($loanCondition);
            if(!$loanExpend) {
                $loanExpend = number_format(0,2);
            }

            // 还款笔数
            $repayCondition['gmt_repay'] = array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate)));
            $repayCondition['company_id'] = $companyCondition;
            $sumRmoney = D('Repayments')->getSumOfRmoney($repayCondition);
            if(!$sumRmoney) {
                $sumRmoney = number_format(0,2);
            }


            // 现金支出
            $chargeCondition['gmt_create'] = array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate)));
            $chargeCondition['company_id'] = $companyCondition;

            $sumCharge = D('Charge')->sumMoney($chargeCondition);
            if(!$sumCharge) {
                $sumCharge = number_format(0,2);
            }

            // 上门费
            $tourCondition['gmt_tour'] = array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate)));
            $tourCondition['company_id'] = $companyCondition;
            $sumTour = D('Tour')->sumMoney($tourCondition);
            if(!$sumTour) {
                $sumTour = number_format(0,2);
            }

            // 每月工资
            $wageCondition['gmt_wage'] = array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate)));
            $wageCondition['company_id'] = $companyCondition;
            $sumWage = D('Wage')->sumTotalMoney($wageCondition);
            if(!$sumWage) {
                $sumWage = number_format(0,2);
            }


            // 利润总额
            $profit = $sumRmoney - $sumCharge - $sumWage - $loanExpend;

            $profitArray[] = array(
                'month' => date('Y年m月',$startDate),
                'loanExpend' => $loanExpend,
                'loanTimes' => $loanTimes,
                'sumRmoney' => $sumRmoney,
                'sumCharge' => $sumCharge,
                'sumWage' => $sumWage,
                'profit' => $profit,
                'sumTour' => $sumTour,
            );

            $begain_month = $begain_month + 1;
            if($begain_month > 12) {
                $begain_month = 1;
                $begain_year = $begain_year + 1;
            }
        }
        $sumOfExpend = 0;
        $sumOfRmoney = 0;
        $sumOfOther = 0;
        $sumOfProfit = 0;
        foreach ($profitArray as $key => $item) {
            $sumOfExpend = $sumOfExpend + $item['loanExpend'];
            $sumOfRmoney = $sumOfRmoney + $item['sumRmoney'];
            $sumOfOther = $sumOfOther + $item['sumCharge'] + $item['sumWage'];
            $sumOfProfit = $sumOfProfit + $item['profit'];
        }

        $this->assign(array(
            'profits' => $profitArray,
            'userInfo' => $userInfo,
            'companys' => $companys,
            'sumOfExpend' => $sumOfExpend,
            'sumOfRmoney' => $sumOfRmoney,
            'sumOfOther' => $sumOfOther,
            'sumOfProfit' => $sumOfProfit,
        ));

        $this->display();
    }

    public function export() {
        //2018-01   2018-09

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


        if(I('get.search_datepicker_start','','string')) {
            $search_datepicker_start = I('get.search_datepicker_start','','string');
        }else {
            // 今年的第一个月
            $search_datepicker_start = date('Y-m',strtotime(date('Y',time())));
        }

        if(I('get.search_datepicker_end','','string')) {
            $search_datepicker_end = I('get.search_datepicker_end','','string');
        }else {
            $search_datepicker_end = date('Y-m',time());
        }

        $companyCondition = array();
        if(I('get.company_id','','string') && I('get.company_id','','string') != 'undefined') {
            $companySearchArray = explode(',',I('get.company_id','','string'));
            $companyCondition = array('IN',$companySearchArray);
        }else {
            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
            if($userInfo['jurisdiction'] == 1) {
                $companyCondition = $userInfo['company_id'];
            }else if($userInfo['jurisdiction'] == 2){
                $companyIDs = array_column($companys,'company_id');
                $companyCondition = array('IN',$companyIDs);
            }
        }


        $date_begain = $search_datepicker_start;
        $date_end = $search_datepicker_end;

        $begainArray = explode('-',$date_begain);
        $begain_year = intval($begainArray[0]);
        $begain_month = intval($begainArray[1]);

        $date_begain_time = strtotime($date_begain);
        $date_end_time = strtotime($date_end);

        $year1 = date('Y',$date_begain_time);
        $month1 = date('m',$date_begain_time);
        $year2 = date('Y',$date_end_time);
        $month2 = date('m',$date_end_time);

        // 计算两个时间之间差几个月
        $month_number = ($year2 * 12 + $month2) - ($year1 * 12 + $month1);
        // 算上最后一个月

        $profitArray = array();

        for($month_number = $month_number + 1;$month_number > 0; $month_number --) {
            $startDate = strtotime($begain_year . '-' . $begain_month);
            $endDate = strtotime($begain_year . '-' . $begain_month . '+1 month -1 second');
            //print_r(date('Y-m-d H:i:s',$startDate) . ',' .date('Y-m-d H:i:s',$endDate));
            // 放款笔数
            $loanCondition['create_time'] = array('BETWEEN',array($startDate,$endDate));
            $loanCondition['company_id'] = $companyCondition;
            $loanTimes = D('Loan')->countLoans($loanCondition);
            if(!$loanTimes) {
                $loanTimes = 0;
            }

            // 放款支出
            $loanExpend = D('Loan')->sumLoanExpenditureByCondition($loanCondition);
            if(!$loanExpend) {
                $loanExpend = number_format(0,2);
            }

            // 还款笔数
            $repayCondition['gmt_repay'] = array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate)));
            $repayCondition['company_id'] = $companyCondition;
            $sumRmoney = D('Repayments')->getSumOfRmoney($repayCondition);
            if(!$sumRmoney) {
                $sumRmoney = number_format(0,2);
            }


            // 现金支出
            $chargeCondition['gmt_create'] = array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate)));
            $chargeCondition['company_id'] = $companyCondition;

            $sumCharge = D('Charge')->sumMoney($chargeCondition);
            if(!$sumCharge) {
                $sumCharge = number_format(0,2);
            }

            // 上门费
            $tourCondition['gmt_tour'] = array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate)));
            $tourCondition['company_id'] = $companyCondition;
            $sumTour = D('Tour')->sumMoney($tourCondition);
            if(!$sumTour) {
                $sumTour = number_format(0,2);
            }

            // 每月工资
            $wageCondition['gmt_wage'] = array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate)));
            $wageCondition['company_id'] = $companyCondition;
            $sumWage = D('Wage')->sumTotalMoney($wageCondition);
            if(!$sumWage) {
                $sumWage = number_format(0,2);
            }

            // 利润总额
            $profit = $sumRmoney - $sumCharge - $sumWage - $loanExpend;

            $profitArray[] = array(
                'month' => date('Y年m月',$startDate),
                'loanExpend' => $loanExpend,
                'loanTimes' => $loanTimes,
                'sumRmoney' => $sumRmoney,
                'sumCharge' => $sumCharge,
                'sumWage' => $sumWage,
                'profit' => $profit,
                'sumTour' => $sumTour,
            );

            $begain_month = $begain_month + 1;
            if($begain_month > 12) {
                $begain_month = 1;
                $begain_year = $begain_year + 1;
            }
        }


        $xlsName  = '利润表';

        $xlsCell  = array(
            array('month','月份'),
            array('loanTimes','月借款人次'),
            array('loanExpend','月放款总支出'),
            array('sumRmoney','月收款总金额'),
            array('sumTour','月外访总收入'),
            array('sumCharge','月现金支出'),
            array('sumWage','月工资社保'),
            array('profit','月利润总额'),
        );
        $xlsData = $profitArray;
        $excel = new PhpexcelController();
        $excel->exportExcel($xlsName,$xlsCell,$xlsData);
    }
}