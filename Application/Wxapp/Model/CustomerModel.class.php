<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 20:34
 */
namespace Wxapp\Model;

use Think\Model;

class CustomerModel extends Model {
    private $_db = '';

    public function __construct() {
        $this->_db = M('customer');
    }

    public function addCustomer($data = array()) {
        return $this->_db->add($data);
    }


    public function selectALLCustomerByCondition($condition = array(), $page = 1, $pageSize = 10) {
        $offset = ($page -1) * $pageSize;
        return $this->_db->where($condition)->order('create_time desc')->limit($offset,$pageSize)->select();
    }

    public function selectALLCustomerOnlyByCondition($condition = array()) {
        return $this->_db->where($condition)->order('create_time desc')->select();
    }

    public function countCustomers($condition = array()) {
        return $this->_db->where($condition)->count();
    }

    public function selectALLCustomer() {
        return $this->_db->where('status !=  -1')->order('create_time desc')->select();
    }

    /**
     * 查找有借款信息的用户
     */
    public function selectLoanCustomer() {
        return $this->_db->where('status !=  -1')->order('create_time desc')->select();
    }

    public function updateOneCustomerFieldByID($id = 0,$field = '',$value = '') {
        $updata[$field] = $value;
        return $this->_db->where('id = ' . $id)->save($updata);
    }

    public function findCustomerByCondition($field = '',$condition = '') {
        if(!$condition || !$field) {
            throw_exception('错误：函数findCustomerByCondition查询条件为空！');
        }
        $conditionData[$field] = $condition;
        return $this->_db->where($conditionData)->find();
    }

    public function deleteOneCustomerByID($id = 0) {
        $data['id'] = $id;
        return $this->_db->where($data)->delete();
    }

    public function updateCustomerByID($id = 0,$data = array()) {
        if(!$id) {
            throw_exception('错误：函数updateCustomerByID查询条件为空！');
        }
        if(!$data || !is_array($data)) {
            throw_exception('错误：函数updateCustomerByID保存数据为空！');
        }
        return $this->_db->where('id = ' . $id)->save($data);
    }

    /**
     * 根据客户ID获取客户信息
     * @param int $customer_id
     * @return mixed
     */
    public function getCustomerByID($customer_id = 0) {
        if(!$customer_id) {
            throw_exception('Home Model CustomerModel getCustomerByID customer_id is null');
        }
        $condition['id'] = $customer_id;
        return $this->_db->where($condition)->find();
    }

    /**
     * 根据姓名模糊搜索
     * @param string $name
     * @return mixed
     */
    public function getCustomerByName($name = '') {
        if(!$name) {
            throw_exception('Home Model CustomerModel getCustomerByName name is null');
        }
        $condition['name'] = array('LIKE','%' . $name . '%');
        $condition['is_delete'] = array('neq',1);
        $res =  $this->_db->where($condition)->select();
        return $res;
    }

    public function getCustomerBycondition($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        $res =  $this->_db->where($condition)->select();
        return $res;
    }

    /**
     * 根据手机号码搜索用户信息
     * @param string $phone
     * @return mixed
     */
    public function getCustomerByPhone($phone = '') {
        if(!$phone) {
            throw_exception('Home Model CustomerModel getCustomerByPhone phone is null');
        }
        $condition['phone'] = $phone;
        return $this->_db->where($condition)->find();
    }

    /**
     * 根据客户ID将贷款次数自增加一
     * @param int $customer_id
     * @return bool
     */
    public function setIncLoanTimes($customer_id = 0) {
        if(!$customer_id) {
            throw_exception('Home Model CustomerModel setIncLoanTimes customer_id is null');
        }
        $condition['id'] = $customer_id;
        return $this->_db->where($condition)->setInc('loan_times');
    }
}