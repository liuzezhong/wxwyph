<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/8/1
 * Time: 22:40
 */

namespace Wxapp\Model;


use Think\Model;

class LoanModel extends Model {
    private $_db = '';

    public function __construct() {
        $this->_db = M('loan');
    }

    public function selectAll($condition = array(), $page = 1, $pageSize = 10) {
        $offset = ($page -1) * $pageSize;
        return $this->_db->where($condition)->order('create_time desc')->limit($offset,$pageSize)->select();
    }

    public function selectAllBycondition($condition = array()) {
        return $this->_db->where($condition)->order('create_time desc')->select();
    }

    public function countLoans($condition = array()) {
        return $this->_db->where($condition)->count();
    }
    public function updateOneLoanFieldByID($id = 0,$field = '',$value = '') {
        $updata[$field] = $value;
        return $this->_db->where('loan_id = ' . $id)->save($updata);
    }

    public function deleteOneLoanByID($id = 0) {
        $data['loan_id'] = $id;
        return $this->_db->where($data)->delete();
    }

    public function addLoan($data = array()) {
        return $this->_db->add($data);
    }

    public function findLoanByCondition($field = '',$condition = '') {
        if(!$condition || !$field) {
            throw_exception('错误：函数findLoanByCondition查询条件为空！');
        }
        $conditionData[$field] = $condition;
        return $this->_db->where($conditionData)->find();
    }

    public function updateLoanByID($id = 0,$data = array()) {
        if(!$id) {
            throw_exception('错误：函数updateLoanByID查询条件为空！');
        }
        if(!$data || !is_array($data)) {
            throw_exception('错误：函数updateLoanByID保存数据为空！');
        }
        return $this->_db->where('loan_id = ' . $id)->save($data);
    }

    /**
     * 根据ID获取借款信息
     * @param int $loan_id
     * @return mixed
     */
    public function getLoanByID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('错误：函数getLoanByID查询条件为空！');
        }
        $condition['loan_id'] = $loan_id;
        return $this->_db->where($condition)->find();
    }

    /**
     * 根据客户ID获取客户的借款记录信息
     * @param int $customer_id
     * @return mixed
     */
    public function selectLoanByCustomerID($customer_id = 0) {
        if(!$customer_id) {
            throw_exception('错误：函数selectLoanByCustomerID查询条件为空！');
        }
        $condition['customer_id'] = $customer_id;
        // 去除已经结清的记录
        $condition['loan_status'] = array('neq',1);
        return $this->_db->where($condition)->select();
    }

    /**
     * 根据客户ID批量搜索借款信息
     * @param array $idList
     * @return mixed
     */
    public function selectLoanByCustomerIDList($idList = array()) {
        if(!$idList) {
            throw_exception('错误：函数selectLoanByCustomerIDList查询条件为空！');
        }
        $condition['customer_id'] = array('IN',$idList);
        return $this->_db->where($condition)->select();
    }

    /**
     * 根据具体时间计算当时间在还款时间范围内的 非结清的客户信息
     * @param string $data
     * @return mixed
     */
    public function selectLoanOfRepaymentByDate($data = '') {
        if(!$data) {
            throw_exception('错误：函数selectLoanOfRepaymentByDate查询条件为空！');
        }
        // 到期时间大于当前时间
        $condition['create_time'] = array('ELT',$data);
        $condition['exp_time'] = array('EGT',$data);
        // 去除已经结清的客户
        $condition['loan_status'] = array('NEQ',1);
        $res = $this->_db->where($condition)->select();
        return $res;
    }

    /**
     * 根据时间计算日期范围内实际放款总和
     * @param array $dateArray
     * @return mixed
     */
    public function sumLoanExpenditureByDate($dateArray = array()) {
        if(!$dateArray) {
            throw_exception('错误：函数sumLoanExpenditureByDate查询条件为空！');
        }
        $condition['create_time'] = array('BETWEEN',$dateArray);
        $res =  $this->_db->where($condition)->sum('expenditure');
        return $res;
    }

    public function sumLoansByCondition($condition = array(), $filed = '') {
        return $this->_db->where($condition)->sum($filed);
    }


}