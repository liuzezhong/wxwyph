<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/8/1
 * Time: 22:40
 */

namespace Home\Model;


use Think\Model;

class LoanModel extends Model {
    private $_db = '';

    public function __construct() {
        $this->_db = M('loan');
    }

    public function selectAll($condition = array(), $page = 1, $pageSize = 10) {
        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        $offset = ($page -1) * $pageSize;
        return $this->_db->where($condition)->order('create_time desc')->limit($offset,$pageSize)->select();
    }

    public function selectAllDue($condition = array(), $page = 1, $pageSize = 10) {
        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        $offset = ($page -1) * $pageSize;
        return $this->_db->where($condition)->order('gmt_overdue desc,create_time')->limit($offset,$pageSize)->select();
    }

    public function selectAllBycondition($condition = array()) {
        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        $res = $this->_db->where($condition)->order('create_time desc')->select();
        return $res;
    }

    public function countLoans($condition = array()) {
        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
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
        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
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
        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return $this->_db->where($condition)->select();
    }

    /**
     * 根据具体时间计算当时间在还款时间范围内的 非结清的客户信息
     * @param string $data
     * @return mixed
     */
    public function selectLoanOfRepaymentByDate($condition = array()) {


        // 去除已经结清的客户
        $condition['loan_status'] = array('NEQ',1);
        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
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
        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        $res =  $this->_db->where($condition)->sum('expenditure');
        return $res;
    }

    public function sumLoanExpenditureByCondition($condition = array()) {

        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        $res =  $this->_db->where($condition)->sum('expenditure');
        return $res;
    }

    public function sumLoansByCondition($condition = array(), $filed = '') {
        // 去除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return $this->_db->where($condition)->sum($filed);
    }

    public function sumLoanBondCondition($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        return $this->_db->where($condition)->sum('bond');
    }

    /**
     * 按客户姓名逻辑删除借款信息
     * @param int $customer_id
     * @return bool
     */
    public function logicDeleteByCustomer($customer_id = 0) {
        if(!$customer_id) {
            throw_exception('错误：函数logicDeleteByCustomer查询条件为空！');
        }
        $condition['customer_id'] = $customer_id;
        $data = array(
            'is_delete' => 1,
        );
        return $this->_db->where($condition)->save($data);
    }

    /**
     * 按借款ID逻辑删除信息
     * @param int $loan_id
     * @return bool
     */
    public function logicDeleteByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('错误：函数logicDeleteByLoanID查询条件为空！');
        }
        $condition['loan_id'] = $loan_id;
        $data = array(
            'is_delete' => 1,
        );
        return $this->_db->where($condition)->save($data);
    }

    public function findLoanByALLCondition($condition = array()) {
        return $this->_db->where($condition)->find();
    }

    public function updateLoanByCondition($conditionArray = array(), $updateArray = array()) {
        return $this->_db->where($conditionArray)->save($updateArray);
    }

    public function selectLoanByCondition($condition = array()) {
        return $this->_db->where($condition)->find();
    }
}