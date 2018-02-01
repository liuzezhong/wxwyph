<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/16
 * Time: 11:26
 */

namespace Home\Controller;
use Think\Model;

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

            // 月单量
            $loanCondition['create_time'] = array('BETWEEN',array($startDate,$endDate));
            $loanCondition['company_id'] = $companyCondition;
            $loanTimes = D('Loan')->countLoans($loanCondition);
            if(!$loanTimes) {
                $loanTimes = 0;
            }

            // 月结清数

            $trueCondition['gmt_overdue'] = array('BETWEEN',array($startDate,$endDate));
            $trueCondition['company_id'] = $companyCondition;
            $trueCondition['loan_status'] = 1;
            $trueTimes = D('Loan')->countLoans($trueCondition);
            if(!$trueTimes) {
                $trueTimes = 0;
            }

            // 月逾期数
            $falseCondition['gmt_overdue'] = array('BETWEEN',array($startDate,$endDate));
            $falseCondition['company_id'] = $companyCondition;
            $falseCondition['loan_status'] = -1;
            $falseTimes = D('Loan')->countLoans($falseCondition);
            if(!$falseTimes) {
                $falseTimes = 0;
            }

            // 月收本金、利息、违约金
            /*$sumMoney = D('Repayments')->sumMoneyJoinLoan();*/

            $Model = new Model();
            $sql = "SELECT sum(bj_repayments.s_money),sum(bj_loan.cyc_principal),sum(bj_repayments.r_money),
                sum(bj_repayments.b_money),sum(bj_loan.cyc_interest),sum((bj_loan.cyc_principal - bj_loan.cyc_interest))
                 FROM `bj_repayments`,`bj_loan` WHERE gmt_repay BETWEEN '" . date('Y-m-d H:i:s',$startDate)
                    . "' AND '" . date('Y-m-d H:i:s',$endDate) . "' AND bj_repayments.loan_id = bj_loan.loan_id 
                    AND bj_repayments.is_delete != 1 AND bj_loan.is_delete != 1 AND 
                    bj_loan.company_id IN ( " . implode(',',$companyCondition[1]) . ") AND bj_loan.loan_status != 1 AND bj_loan.loan_status != -1";
            $voList = $Model->query($sql);


            //print_r($voList[0]['sum((bj_loan.cyc_principal - bj_loan.cyc_interest))']);

            // 月收本金
            $benjin = $voList[0]['sum((bj_loan.cyc_principal - bj_loan.cyc_interest))'];
            if(!$benjin) {
                $benjin = number_format(0,2);
            }
            // 月收利息
            $lixi = $voList[0]['sum(bj_loan.cyc_interest)'];
            if(!$lixi) {
                $lixi = number_format(0,2);
            }
            // 月收违约金
            $weiyuejin = $voList[0]['sum(bj_repayments.b_money)'];
            if(!$weiyuejin) {
                $weiyuejin = number_format(0,2);
            }


            // 结清利润
            $jieqingCondition = array(
                'loan_status' => 1,
                //'loan_id' => array('IN',array(4910)),
                //'product_id' => array('NOT IN',array(3,5)),
                'gmt_overdue' => array('BETWEEN',array($startDate,$endDate)),
                'company_id' => $companyCondition,
            );
            $jieqingLoan = D('Loan')->selectAllBycondition($jieqingCondition);
            $jieqinglirun = number_format(0,2);

            foreach ($jieqingLoan as $m => $n) {
                // 遍历每条结清的借款记录

                // 查询收回本金合计   = （还款总期数 - 1 ）* 本金
                $repayCount = D('Repayments')->countRepayMents($n['loan_id']);

                if(!$repayCount) {
                    $repayCount = 1;
                }
                $shouhuibenjin = ($repayCount - 1) * ($n['cyc_principal'] - $n['cyc_interest']);

                //收回利息合计（当前月）
                $nowRepayCondition = array(
                    'loan_id' => $n['loan_id'],
                    'gmt_repay' => array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate))),
                );
                $nowRepayCount = D('Repayments')->countRepaymentsByCondition($nowRepayCondition);
                if(!$nowRepayCount) {
                    $nowRepayCount = 1;
                }
                $shouhuilixi = ($nowRepayCount - 1) * $n['cyc_interest'];

                //违约金
                $weiyueCondition = array(
                    'loan_id' => $n['loan_id'],
                    'gmt_repay' => array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate))),
                );
                $weiyuejinjieqing = D('Repayments')->getSumOfBmoneyByCondition($weiyueCondition);

                // 保证金
                if($n['is_bond']) {
                    // 退还保证金
                    $baozhengjin = $n['bond'];
                }else {
                    $baozhengjin = 0;
                }

                // 结清金额
                $sql2 = "SELECT * FROM `bj_repayments` WHERE loan_id = " . $n['loan_id'] ." AND cycles 
                IN ( SELECT MAX(cycles) FROM `bj_repayments` WHERE loan_id = " . $n['loan_id'] . " AND is_delete != 1) AND is_delete != 1";
                $voList2 = $Model->query($sql2);
                $jieqingjine = $voList2[0]['r_money'];

                if($n['tour_id'] != 0) {

                    $tour = D('Tour')->getTourByID($n['tour_id']);
                    $tourMoney = $tour['money'];
                }else {
                    $tourMoney = 0;
                }


                // 扣除保证金

                // 实际支出
                $shijizhichu = $n['expenditure'];

                //print_r($shouhuibenjin . ',' . $shouhuilixi . ',' . $weiyuejinjieqing . ',' . $baozhengjin . ',' . $jieqingjine . ',' . $shijizhichu . ',' . $bondMoney . ',' . $tourMoney);
                // 结清利润
                $jieqinglirun = $jieqinglirun + $shouhuibenjin + $shouhuilixi + $weiyuejinjieqing - $baozhengjin + $jieqingjine - $shijizhichu + $tourMoney;

                if($n['product_id'] == 3 || $n['product_id'] == 5) {
                    $benjin = $benjin + $n['principal'];
                }else {
                    // 结清本金 = （（总周期 - 总已还周期 -1）+ 当月还款周期 ）* 周期本金
                    $jieqingbenjin = intval(($n['cyclical'] - $repayCount + 1) + ($nowRepayCount - 1)) * intval(($n['cyc_principal'] - $n['cyc_interest']));
                    $benjin = $benjin + $jieqingbenjin;
                }



            }


            /*$jieqing2Condition = array(
                'loan_status' => 1,
                'product_id' => array('IN',array(3,5)),
                'gmt_overdue' => array('BETWEEN',array($startDate,$endDate)),
                'company_id' => $companyCondition,
            );
            $jieqing2Loan = D('Loan')->selectAllBycondition($jieqing2Condition);
            foreach ($jieqing2Loan as $m => $n) {
                $sql3 = "SELECT sum(r_money - b_money),sum(b_money) FROM `bj_repayments` WHERE loan_id = ". $n['loan_id'] . " 
                AND is_delete != 1 AND cycles NOT IN (SELECT MAX(cycles) FROM `bj_repayments` WHERE loan_id = " . $n['loan_id'] ." 
                AND is_delete != 1) AND gmt_repay BETWEEN '" . date('Y-m-d H:i:s',$startDate) . "' AND '" . date('Y-m-d H:i:s',$endDate) . "'";
                $voList3 = $Model->query($sql3);
                $jieqinglirun = $jieqinglirun + $voList3[0]['sum(r_money - b_money)'];
                $weiyuejin = $weiyuejin + $voList3[0]['sum(b_money)'];

                // 加上本金
                $sql4 = "SELECT * FROM `bj_repayments` WHERE loan_id = " . $n['loan_id'] ." AND cycles 
                IN ( SELECT MAX(cycles) FROM `bj_repayments` WHERE loan_id = " . $n['loan_id'] . " AND is_delete != 1) AND is_delete != 1";
                $voList4 = $Model->query($sql4);
                $benjin = $benjin + $voList4['r_money'];
            }*/


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
            $sumWage = D('Wage')->sumWageMoney($wageCondition);
            if(!$sumWage) {
                $sumWage = number_format(0,2);
            }

            // 每月社保
            $insurCondition['gmt_wage'] = array('BETWEEN',array(date('Y-m-d H:i:s',$startDate),date('Y-m-d H:i:s',$endDate)));
            $insurCondition['company_id'] = $companyCondition;
            $sumInsur = D('Wage')->sumInsurMoney($insurCondition);
            if(!$sumInsur) {
                $sumInsur = number_format(0,2);
            }

            // 利润总额
            $profit = $lixi + $weiyuejin + $jieqinglirun - $sumCharge - $sumWage - $sumInsur + $sumTour;

            $profitArray[] = array(
                'month' => date('Y年m月',$startDate),
                'loanExpend' => $loanExpend,
                'loanTimes' => $loanTimes,   //月单量
                'trueTimes' => $trueTimes,   //结清数
                'falseTimes' => $falseTimes,   //逾期数
                'benjin' => number_format($benjin,2),   //月收本金
                'lixi' => number_format($lixi,2),   //月收利息
                'weiyuejin' => number_format($weiyuejin,2),   //月收违约金
                'jieqinglirun' => number_format($jieqinglirun,2),   //月收结清利润
                'sumRmoney' => $sumRmoney,
                'sumCharge' => number_format($sumCharge,2),   // 月现金支出
                'sumWage' => number_format($sumWage,2),  // 月工资支出
                'sumInsur' => number_format($sumInsur,2),  // 月社保支出
                'profit' => number_format($profit,2),
                'sumTour' => number_format($sumTour,2),
            );

            $begain_month = $begain_month + 1;
            if($begain_month > 12) {
                $begain_month = 1;
                $begain_year = $begain_year + 1;
            }
        }
        $sumOfdanliang = 0;
        $sumOfshouru = 0;
        $sumOfzhichu = 0;
        $sumOfProfit = 0;
        foreach ($profitArray as $p => $q) {
            $sumOfdanliang = $sumOfdanliang + $q['loanTimes'];
            $sumOfshouru = $sumOfshouru + floatval(str_replace(',','',$q['lixi'])) + floatval(str_replace(',','',$q['weiyuejin'])) + floatval(str_replace(',','',$q['jieqinglirun'])) + floatval(str_replace(',','',$q['sumTour']));
            $sumOfzhichu = $sumOfzhichu + floatval(str_replace(',','',$q['sumCharge'])) + floatval(str_replace(',','',$q['sumWage'])) + floatval(str_replace(',','',$q['sumInsur']));
            $sumOfProfit = $sumOfProfit + floatval(str_replace(',','',$q['profit']));

        }

        $this->assign(array(
            'profits' => $profitArray,
            'userInfo' => $userInfo,
            'companys' => $companys,
            'sumOfshouru' => number_format($sumOfshouru, 2),
            'sumOfdanliang' => $sumOfdanliang,
            'sumOfzhichu' => number_format($sumOfzhichu,2),
            'sumOfProfit' => number_format($sumOfProfit,2),
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