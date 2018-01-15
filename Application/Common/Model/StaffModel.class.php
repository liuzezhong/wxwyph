<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 20:34
 */
namespace Common\Model;

use Think\Model;

class StaffModel extends Model {
    private $_db = '';

    public function __construct() {
        $this->_db = M('staff');
    }

    public function selectAllStaffByCondition($condition = array(), $page = 1, $pageSize = 10) {
        $offset = ($page -1) * $pageSize;
        $res = $this->_db->where($condition)->order('create_time desc')->limit($offset,$pageSize)->select();
        return $res;
    }

    public function selectAllStaffOnlyByCondition($condition = array()) {
        $res = $this->_db->where($condition)->order('create_time desc')->select();
        return $res;
    }

    public function countStaff($condition = array()) {
        return $this->_db->where($condition)->count();
    }

    public function selectAllStaff($condition = array()) {
        $condition['staff_status'] = array('neq',-1);
        return $this->_db->where($condition)->select();
    }

    public function updateOneStaffFieldByID($id = 0,$field = '',$value = '') {
        $updata[$field] = $value;
        return $this->_db->where('staff_id = ' . $id)->save($updata);
    }

    public function deleteOneStaffByID($id = 0) {
        $data['staff_id'] = $id;
        return $this->_db->where($data)->delete();
    }

    public function addStaff($data = array()) {
        return $this->_db->add($data);
    }

    public function findStaffByCondition($field = '',$condition = '') {

        $conditionData[$field] = $condition;
        return $this->_db->where($conditionData)->find();
    }

    public function updateStaffByID($id = 0,$data = array()) {
        if(!$id) {
            throw_exception('错误：函数updateStaffByID查询条件为空！');
        }
        if(!$data || !is_array($data)) {
            throw_exception('错误：函数updateStaffByID保存数据为空！');
        }
        return $this->_db->where('staff_id = ' . $id)->save($data);
    }

    /**
     * 根据员工ID获取员工信息
     * @param int $staff_id
     * @return mixed
     */
    public function getStaffByID($staff_id = 0) {
        if(!$staff_id) {
            throw_exception('错误：函数getStaffByID查询条件为空！');
        }
        $condition['staff_id'] = $staff_id;
        return $this->_db->where($condition)->find();
    }

    /**
     * 根据条件查找一条客户信息
     * @param string $idcard
     */
    public function findOneStaffByCondition($condition = array()) {
        if(!$condition) {
            throw_exception('Home Model Staff Model findOneStaffByCondition condition is null');
        }
        return $this->_db->where($condition)->find();
    }
}