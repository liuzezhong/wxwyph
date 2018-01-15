<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/12/30
 * Time: 15:10
 */

namespace Wxapp\Controller;


use Think\Controller;
use Think\Exception;

class YuqiController extends Controller
{
    public function findLoanInfo() {
        $customer_id = I('post.customer_id',0,'');
        $loan_id = I('post.loan_id',0,'');
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

            $loan['principal'] = intval($loan['principal']);
            // 借款时间
            $loan['create_time'] = date('Y年m月d日',$loan['create_time']);

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
            $loan['repay_money'] = D('Repayments')->getSumOfRmoneyByLoanID($loan_id);
            $loan['repay_money'] = ($loan['repay_money'] == null) ? '0' : $loan['repay_money'];
            // 已还期数
            $loan['repay_times'] = D('Repayments')->countRepayMents($loan['loan_id']);
            // 最后一次还款时间
            $loan['repay_max_data'] = D('Repayments')->getRepaymentsMaxTimeByLoanID($loan['loan_id']);
            $loan['repay_max_data'] = ($loan['repay_max_data'] == null) ? '' : $loan['repay_max_data'];
            $loan['remark'] = ($loan['remark'] == null) ? '无' : $loan['remark'];

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

        $this->ajaxReturn(array(
            'status' => 1,
            'message' => $customer_id,
        ));
    }


    public function searchYuqiByName() {
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
            /*

            // 查询客户信息是否存在
            $customers = D('Customer')->getCustomerByName($customerName);
            if(!$customers) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '无此客户信息',
                ));
            }*/
            // 查询是否有逾期客户
            foreach ($customers as $key => $item) {
                $loans = D('Loan')->selectLoanByCustomerID($item['id']);
                if(!$loans) {
                    unset($customers[$key]);
                    continue;
                }else {
                    foreach ($loans as $i => $j) {
                        if($j['loan_status'] != -1) {
                            unset($loans[$i]);
                        }
                    }
                    if(!$loans) {
                        unset($customers[$key]);
                        continue;
                    }else {
                        foreach ($loans as $i => $j) {
                            $last_loan = $j;
                        }
                        $customers[$key]['countLoans'] = count($loans);
                        $customers[$key]['loan_id'] = $last_loan['loan_id'];
                        $company = D('Company')->getCompanyByID($item['company_id']);
                        $customers[$key]['company_name'] = $company['smallname'];
                    }
                }

            }
            if(!$customers) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '此客户无逾期记录',
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

    public function loanImages() {
        $customer_id = I('post.customer_id',0,'');
        $loan_id = I('post.loan_id',0,'');

        if(!$customer_id || !$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }

        try {
            // 查找图片信息
            $images = D('Image')->selectImageByLoanID($loan_id);
            if(!$images) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '暂无外访资料信息',
                ));
            }
            $imageArray = array();
            foreach ($images as $key => $item) {
                $imageArray[] = $item['url'] . '?' . C('QINIU_SUOTU_WEIXIN');
            }

            $countImage = count($imageArray);
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '图片信息存在',
                'imageArray' => $imageArray,
                'countImage' => $countImage,
            ));
        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function getLoansByCustomer() {
        $customer_id = I('post.customer_id',0,'');
        if(!$customer_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }

        try {
            $loans = D('Loan')->selectLoanByCustomerID($customer_id);

            foreach ($loans as $i => $j) {
                if($j['loan_status'] != -1) {
                    unset($loans[$i]);
                    continue;
                }
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
}