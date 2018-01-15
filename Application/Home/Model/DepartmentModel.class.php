<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 20:34
 */
namespace Home\Model;

use Think\Model;

class DepartmentModel extends Model {
    private $_db = '';

    public function __construct() {
        $this->_db = M('department');
    }

    public function findDataByCondition($field = '',$condition = '') {
        if(!$condition || !$field) {
            throw_exception('错误：函数findDataByCondition查询条件为空！');
        }
        $conditionData[$field] = $condition;
        return $this->_db->where($conditionData)->find();
    }

    public function selectAllDepartment() {
        return $this->_db->select();
    }



    public function addCustomer($data = array()) {
        return $this->_db->add($data);
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
}