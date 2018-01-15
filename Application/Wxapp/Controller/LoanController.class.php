<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/10
 * Time: 21:20
 */

namespace Wxapp\Controller;


use Think\Controller;
use Think\Exception;

class LoanController extends Controller
{
    public function searchLoanByName() {
        $customerName = I('post.customerName','','');
        $session3key = I('post.sessionKey','','');
        $condition = array();
        if(!$session3key) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'session3key不存在，无权限！',
            ));
        }
        if(!$customerName) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户姓名为空',
            ));
        }

        try {
            // 查询客户信息是否存在
            $session = D('session')->findSessionBySession3key($session3key);
            if(!$session) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session不存在，无权限！',
                ));
            }
            $openid = $session['openid'];
            $user = D('user')->getUserByOpenID($openid);
            if(!$user) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '用户不存在，无权限！',
                ));
            }
            if($user['status'] == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '无权限！',
                ));
            }

            $condition['name'] = array('LIKE','%' . $customerName . '%');
            if($user['depart_id'] == 3) {
                // 会计
                $condition['company_id'] = $user['company_id'];
            }
            $customers = D('Customer')->getCustomerBycondition($condition);

            if(!$customers) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '无此客户信息',
                ));
            }

            foreach ($customers as $key => $item) {
                $loans = array();
                $loans = D('Loan')->selectLoanByCustomerID($item['id']);
                if(!$loans) {
                    unset($customers[$key]);
                    continue;
                }
                $customers[$key]['countLoans'] = count($loans);
                $customers[$key]['loan_id'] = $loans[0]['loan_id'];

                $company = D('Company')->getCompanyByID($item['company_id']);
                $customers[$key]['company_name'] = $company['smallname'];

            }
            if(!$customers) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '此客户无借款记录',
                ));
            }

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '有此客户记录',
                'customers' => $customers,
                'depart_id' => $user['depart_id'],
            ));


        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function getLoansByCustomer() {
        $customer_id = I('post.customer_id',0,'intval');
        if(!$customer_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }

        try {
            $loans = D('Loan')->selectLoanByCustomerID($customer_id);

            foreach ($loans as $i => $j) {
                $loans[$i]['create_time'] = date('Y年m月d日',$j['create_time']);
            }
            $countLoans = count($loans);
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => 'ok',
                'countLoans' => $countLoans,
                'loans' => $loans,
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function findLoanInfo() {
        $customer_id = I('post.customer_id',0,'intval');
        $loan_id = I('post.loan_id',0,'intval');
        $latitude = 1000;
        $longitude = 1000;
        if(!$customer_id || !$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }

        try {
            // 客户信息
            $customer = D('Customer')->getCustomerByID($customer_id);

            if($customer['address'] != '') {
                $url = 'http://restapi.amap.com/v3/geocode/geo?address=' . $customer['address'] . '&output=JSON&key=1eb3dbf3760f7d9ccb2843cea67808ca';
                $output = json_decode(curlGet($url),true);
                $location = explode(',',$output['geocodes'][0]['location']);
                $latitude = $location[0];
                $longitude = $location[1];
            }

            $customer['address'] = ($customer['address'] == '') ? '暂无详细住址记录' : $customer['address'];



            $customer['idcard'] = ($customer['idcard'] == '') ? '暂无身份证信息' : $customer['idcard'];
            // 借款信息
            $loan = D('Loan')->getLoanByID($loan_id);
            // 借款类型
            if($loan) {
                $product = D('Product')->findProductByCondition('product_id',$loan['product_id']);
            }

            $loan['product_name'] = $product['product_name'];

            //$loan['principal'] = intval($loan['principal']);
            $loan['loan_status_name'] = $loan['loan_status'] == 0 ? '还款中' : (($loan['loan_status'] == 1) ? '已结清' : '已逾期');
            // 借款时间
            $loan['create_time'] = date('Y年m月d日',$loan['create_time']);
            $loan['exp_time'] = date('Y年m月d日',$loan['exp_time']);

            if($loan['product_id'] == 2 || $loan['product_id'] == 5) {
                //打卡 红包贷
                $loan['jiti_cycle'] = '每天';
            }else if($loan['product_id'] == 1 || $loan['product_id'] == 4) {
                //零用贷 车贷
                $day = '一';
                switch ($loan['juti_data']) {
                    case 1 : $day = '一'; break;
                    case 2 : $day = '二'; break;
                    case 3 : $day = '三'; break;
                    case 4 : $day = '四'; break;
                    case 5 : $day = '五'; break;
                    case 6 : $day = '六'; break;
                    case 7 : $day = '日'; break;
                    default: $day = '一'; break;
                }
                $loan['jiti_cycle'] = '每周' . $day;
            }else if($loan['product_id'] == 3) {
                // 空放
                $loan['jiti_cycle'] = '每' . $loan['juti_data'] . '天';
            }

            // 已还金额
            $loan['repay_money'] = D('Repayments')->getSumOfRmoneyByLoanID($loan['loan_id']);
            $loan['repay_money'] = ($loan['repay_money'] == null) ? '0' : $loan['repay_money'];
            // 已还期数
            $loan['repay_times'] = D('Repayments')->countRepayMents($loan['loan_id']);
            // 最后一次还款时间
            $loan['remark'] = ($loan['remark'] == null) ? '无' : $loan['remark'];

            if($loan['is_bond'] == 1) {
                $loan['profit_money'] = $loan['repay_money'] - $loan['expenditure'] - $loan['bond'];
            }else {
                $loan['profit_money'] = $loan['repay_money'] - $loan['expenditure'];
            }

            if($loan['profit_money'] > 0) {
                $loan['profit_money'] = '+' . $loan['profit_money'];
            }

            // 客户经理信息
            $staff = D('Staff')->getStaffByID($loan['staff_id']);
            $loan['staff_name'] = $staff['staff_name'];

            // 上门经理信息
            if($loan['foreign_id']) {
                $foreign = D('Staff')->getStaffByID($loan['foreign_id']);
                $loan['foreign_name'] = $foreign['staff_name'];
            }else {
                $loan['foreign_name'] = '无';
            }


            // 外访资料信息
            $loan['count_mages'] = D('Image')->countImageByLoanID($loan['loan_id']);

            // 公司信息
            $company = D('Company')->getCompanyByID($loan['company_id']);
            $loan['company_name'] = $company['name'];

            // 每期还款
            $loan['every_principal'] = number_format($loan['cyc_principal'] - $loan['cyc_interest'],2);
            
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '逾期信息搜索成功',
                'loan' => $loan,
                'customer' => $customer,
                'latitude' => floatval($latitude),
                'longitude' => floatval($longitude),
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function getLoanStaffInfo() {
        $loan_id = I('post.loan_id',0,'');
        $latitude = 1000;
        $longitude = 1000;

        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }

        try {
            // 借款信息
            $loan = D('Loan')->getLoanByID($loan_id);
            // 借款类型
            $staff = D('Staff')->getStaffByID($loan['staff_id']);

            if($staff['address'] != '') {
                $url = 'http://restapi.amap.com/v3/geocode/geo?address=' . $staff['address'] . '&output=JSON&key=1eb3dbf3760f7d9ccb2843cea67808ca';
                $output = json_decode(curlGet($url),true);
                $location = explode(',',$output['geocodes'][0]['location']);
                $latitude = $location[0];
                $longitude = $location[1];
            }

            $staff['address'] = ($staff['address'] == '') ? '暂无详细住址记录' : $staff['address'];
            $staff['idcard'] = ($staff['idcard'] == '') ? '暂无身份证信息' : $staff['idcard'];

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '客户经理信息搜索成功',
                'staff' => $staff,
                'latitude' => floatval($latitude),
                'longitude' => floatval($longitude),
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function getLoanForeignInfo() {
        $loan_id = I('post.loan_id',0,'');
        $latitude = 1000;
        $longitude = 1000;

        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }

        try {
            // 借款信息
            $loan = D('Loan')->getLoanByID($loan_id);
            // 借款类型
            if(!$loan['foreign_id']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '无外访信息',
                ));
            }

            $foreign = D('Staff')->getStaffByID($loan['foreign_id']);
            if($foreign['address'] != '') {
                $url = 'http://restapi.amap.com/v3/geocode/geo?address=' . $foreign['address'] . '&output=JSON&key=1eb3dbf3760f7d9ccb2843cea67808ca';
                $output = json_decode(curlGet($url),true);
                $location = explode(',',$output['geocodes'][0]['location']);
                $latitude = $location[0];
                $longitude = $location[1];
            }

            $foreign['address'] = ($foreign['address'] == '') ? '暂无详细住址记录' : $foreign['address'];
            $foreign['idcard'] = ($foreign['idcard'] == '') ? '暂无身份证信息' : $foreign['idcard'];

            // 上门外访记录
            if($loan['tour_id']) {
                $tour = D('Tour')->getTourByID($loan['tour_id']);
                $location = D('Location')->getLocationByID($tour['location_id']);

                $foreign['tour_money'] = $tour['money'];
                $foreign['tour_remark'] = $tour['remark'];
                $foreign['tour_local'] = $location['name'];
            }


            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '客户经理信息搜索成功',
                'foreign' => $foreign,
                'latitude' => floatval($latitude),
                'longitude' => floatval($longitude),
                'is_tour' => $loan['tour_id'],
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function getRepaymentsByLoanID() {
        $loan_id = I('post.loan_id',0,'intval');
        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }

        try {
            // 借款信息
            $loan = D('Loan')->getLoanByID($loan_id);

            // 总还款额
            $sumRepay = 0;
            // 还款信息
            $repays = D('Repayments')->listRepayByLoanID($loan_id);
            foreach ($repays as $key => $item) {
                $sumRepay = $sumRepay + $item['r_money'];
                $repays[$key]['gmt_repay'] = date('Y年m月d日',$item['gmt_repay']);
                if($item['r_money'] > 0) {
                    $repays[$key]['r_money'] = '+' . $item['r_money'];
                }
            }

            // 已还周期
            $coutRepay = count($repays);

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '还款信息获取成功',
                'repays' => $repays,
                'sumRepay' => number_format($sumRepay,2),
                'loan' => $loan,
                'coutRepay' => $coutRepay,
            ));

        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function findEarnings() {
        $loan_id = I('post.loan_id',0,'intval');
        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '借款ID不存在',
            ));
        }

        try{
            $earnArray = array();
            // 借款记录
            $loan = D('Loan')->getLoanByID($loan_id);
            // 上门费（收）
            if($loan['tour_id']) {
                $tour = D('Tour')->getTourByID($loan['tour_id']);
                if($tour) {
                    $earnArray[] = array(
                        'loss' => '1', // 0支出，1收入
                        'content' => '上门费用',
                        'money' => '+' . $tour['money'],
                    );
                }
            }
            // 本金（支）
            $earnArray[] = array(
                'loss' => '0', // 0支出，1收入
                'content' => '借款本金',
                'money' => '-' . $loan['principal'],
            );

            // 管理费（收）
            if($loan['poundage']) {
                $earnArray[] = array(
                    'loss' => '1', // 0支出，1收入
                    'content' => '管理费用',
                    'money' => '+' . $loan['poundage'],
                );
            }

            // 保证金（收）
            if($loan['bond']) {
                $earnArray[] = array(
                    'loss' => '1', // 0支出，1收入
                    'content' => '保证金',
                    'money' => '+' . $loan['bond'],
                );
            }

            // 同行返点（支）
            if($loan['rebate']) {
                $earnArray[] = array(
                    'loss' => '0', // 0支出，1收入
                    'content' => '业务返点',
                    'money' => '-' . $loan['rebate'],
                );
            }

            // 累计收款（收）
            $repays = D('Repayments')->getSumOfRmoneyByLoanID($loan_id);
            if($repays) {
                $earnArray[] = array(
                    'loss' => '1', // 0支出，1收入
                    'content' => '累计还款',
                    'money' => '+' . $repays,
                );
            }

            // 退还保证金（支）
            if($loan['is_bond'] == 1) {
                $earnArray[] = array(
                    'loss' => '0', // 0支出，1收入
                    'content' => '退还保证金',
                    'money' => '-' . $loan['bond'],
                );
            }

            $shouru = 0;
            $zhichu = 0;
            foreach ($earnArray as $key => $item) {
                if($item['loss'] == 0) {
                    $zhichu = $zhichu + $item['money'];
                }else if($item['loss'] == 1) {
                    $shouru = $shouru + $item['money'];
                }
            }

            $lirun = abs($shouru) - abs($zhichu);

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '收支信息搜索成功',
                'earnArray' => $earnArray,
                'loan' => $loan,
                'repays' => $repays,
                'zhichu' => abs($zhichu),
                'shouru' => $shouru,
                'lirun' => $lirun,
            ));

        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }

    }
}