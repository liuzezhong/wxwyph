<?php
/**
 * Created by PhpStorm.
 * User: PC41
 * Date: 2017-08-02
 * Time: 16:09
 */

namespace Home\Model;


use Think\Model;

class CycleModel extends Model {
    private $_db = '';

    public function __construct() {
        $this->_db = M('cycle');
    }

    public function findCycleByCondition($field = '',$condition = '') {
        if(!$condition || !$field) {
            throw_exception('错误：函数findProductByCondition查询条件为空！');
        }
        $conditionData[$field] = $condition;
        return $this->_db->where($conditionData)->find();
    }

    public function selectALLCycle() {
        return $this->_db->order('create_time asc')->select();
    }
}