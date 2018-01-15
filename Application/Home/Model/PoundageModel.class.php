<?php
/**
 * Created by PhpStorm.
 * User: PC41
 * Date: 2017-08-06
 * Time: 13:31
 */

namespace Home\Model;


use Think\Model;

class PoundageModel extends Model {
    private $_db = '';

    public function __construct() {
        $this->_db = M('poundage');
    }

    public function selectALLPoundage() {
        return $this->_db->order('create_time desc')->select();
    }

    public function findPoundageByCondition($field = '',$condition = '') {
        if(!$condition || !$field) {
            throw_exception('错误：函数findPoundageByCondition查询条件为空！');
        }
        $conditionData[$field] = $condition;
        return $this->_db->where($conditionData)->find();
    }
}