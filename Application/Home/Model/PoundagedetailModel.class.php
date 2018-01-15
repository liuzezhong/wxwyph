<?php
/**
 * Created by PhpStorm.
 * User: PC41
 * Date: 2017-08-06
 * Time: 15:09
 */

namespace Home\Model;


use Think\Model;

class PoundagedetailModel extends Model {
    private $_db = '';

    public function __construct() {
        $this->_db = M('poundage_detail');
    }

    public function addPoundagedetail($data = array()) {
        return $this->_db->add($data);
    }

    public function selectPoundageByCondition($field = '',$condition = '') {
        if(!$condition || !$field) {
            throw_exception('错误：函数selectPoundageByCondition查询条件为空！');
        }
        $conditionData[$field] = $condition;
        return $this->_db->where($conditionData)->select();
    }

    public function deletePoundageDetailByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('错误：函数deletePoundageDetailByLoanID查询条件为空！');
        }
        return $this->_db->where('loan_id = ' . $loan_id)->delete();
    }
}