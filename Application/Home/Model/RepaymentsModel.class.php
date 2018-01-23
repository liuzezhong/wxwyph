<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/11/29
 * Time: 16:36
 */

namespace Home\Model;


use Think\Model;

class RepaymentsModel extends Model
{
    /**
     * 获取还款记录信息
     * @return mixed
     */
    public function listRepayments($condition = array(), $page = 1, $pageSize = 10) {
        // id去重
        if($condition['loan_id']) {
            $condition['loan_id'] = array('IN',array_unique($condition['loan_id']));
        }
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        $offset = ($page -1) * $pageSize;
        $res =  $this->where($condition)->order('gmt_repay desc')->limit($offset,$pageSize)->select();
        return $res;
    }

    /**
     * 统计记录个数
     * @param $condition
     * @return mixed
     */
    public function getCountRepayments($condition = array()) {
        // id去重
        if($condition['loan_id']) {
            $condition['loan_id'] = array('IN',array_unique($condition['loan_id']));
        }
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->count();
    }
    /**
     * 根据借款ID获取已还款期数
     * @param int $loan_id
     * @return mixed
     */
    public function countRepayMents($loan_id = 0) {
        if($loan_id == 0) {
            throw_exception('Home Model RepaymentsModel countRepayMents loan_id is 0');
        }
        $condition['loan_id'] = $loan_id;
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->count();
    }

    /**
     * 新增还款信息
     * @param array $data
     * @return mixed
     */
    public function addRepayments($data = array()) {
        if(!$data) {
            throw_exception('Home Model RepaymentsModel addRepayments data is null');
        }
        return $this->add($data);
    }

    /**
     * 检测该周期是否存在
     * @param int $loan_id
     * @param int $cycles
     * @return mixed
     */
    public function checkCycles($loan_id = 0, $cycles = 0) {
        if(!$loan_id) {
            throw_exception('Home Model RepaymentsModel checkCycles loan_id is null');
        }
        if(!$cycles) {
            throw_exception('Home Model RepaymentsModel checkCycles cycles is null');
        }
        $condition['loan_id'] = $loan_id;
        $condition['cycles'] = $cycles;
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->find();
    }

    /**
     * 删除一条还款记录
     * @param int $repayments_id
     * @return mixed
     */
    public function deleteRepaymentsByID($repayments_id = 0) {
        if(!$repayments_id) {
            throw_exception('Home Model RepaymentsModel deleteRepaymentsByID repayments_id is null');
        }
        $condition['repayments_id'] = $repayments_id;
        return $this->where($condition)->delete();
    }

    /**
     * 获取还款记录
     * @param int $repayments_id
     * @return mixed
     */
    public function getRepayments($repayments_id = 0) {
        if(!$repayments_id) {
            throw_exception('Home Model RepaymentsModel getRepayments repayments_id is null');
        }
        $condition['repayments_id'] = $repayments_id;
        return $this->where($condition)->find();
    }

    /**
     * 根据还款ID跟新还款信息
     * @param int $repayments_id
     * @param array $data
     * @return bool
     */
    public function updateRepayments($repayments_id = 0, $data = array()) {
        if (!$repayments_id) {
            throw_exception('Home Model RepaymentsModel updateRepayments repayments_id is null');
        }
        if (!$data) {
            throw_exception('Home Model RepaymentsModel updateRepayments data is null');
        }
        $condition['repayments_id'] = $repayments_id;
        return $this->where($condition)->save($data);
    }

    /**
     * 获取应还金额合计
     * @param array $condition
     * @return mixed
     */
    public function getSumOfSmoney($condition = array()) {
        // id去重
        if($condition['loan_id']) {
            $condition['loan_id'] = array('IN',array_unique($condition['loan_id']));
        }
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return  $this->where($condition)->sum('s_money');
    }

    /**
     * 获取实际还款合计
     * @param array $condition
     * @return mixed
     */
    public function getSumOfRmoney($condition = array()) {
        // id去重
        if($condition['loan_id']) {
            $condition['loan_id'] = array('IN',array_unique($condition['loan_id']));
        }
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return  $this->where($condition)->sum('r_money');
    }

    /**
     * 获取违约金额合计
     * @param array $condition
     * @return mixed
     */
    public function getSumOfBmoney($condition = array()) {
        // id去重
        if($condition['loan_id']) {
            $condition['loan_id'] = array('IN',array_unique($condition['loan_id']));
        }
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return  $this->where($condition)->sum('b_money');
    }

    /**
     * 根据借款ID计算实际还款总额
     * @param int $loan_id
     * @return mixed
     */
    public function getSumOfRmoneyByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('Home Model RepaymentsModel getSumOfRmoneyByLoanID data is null');
        }
        $condition['loan_id'] = $loan_id;
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->sum('r_money');
    }

    public function getSumofRemoneyByLoanIDArray($loanIDArray = array()) {
        if(!$loanIDArray ) {
            throw_exception('Home Model RepaymentsModel getSumofRemoneyByLoanIDArray data is null');
        }
        $condition['is_delete'] = array('neq',1);
        $condition['loan_id'] = array('IN',$loanIDArray);
        return $this->where($condition)->sum('r_money');



    }

    /**
     * 根据借款ID计算违约金总额
     * @param int $loan_id
     * @return mixed
     */
    public function getSumOfBmoneyByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('Home Model RepaymentsModel getSumOfBmoneyByLoanID data is null');
        }
        $condition['loan_id'] = $loan_id;
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->sum('b_money');
    }

    /**
     * 根据日期范围计算实收总额
     * @param array $data
     * @return mixed
     */
    public function sumRepaymentsRmoneyByDate($data = array()) {
        if(!$data) {
            throw_exception('Home Model RepaymentsModel sumRepaymentsRmoneyByDate data is null');
        }
        $condition['gmt_repay'] = array('BETWEEN',$data);
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        $res =  $this->where($condition)->sum('r_money');
        return $res;
    }

    /**
     * 列出日期范围内的还款记录
     * @param array $data
     * @return mixed
     */
    public function listRepaymentsByDate($data = array()) {
        if(!$data) {
            throw_exception('Home Model RepaymentsModel listRepaymentsByDate data is null');
        }
        $condition['gmt_repay'] = array('BETWEEN',$data);
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->select();
    }

    /**
     * 获取还款记录信息
     * @return mixed
     */
    public function listRepaymentsByCondition($condition = array()) {
        // id去重
        if($condition['loan_id']) {
            $condition['loan_id'] = array('IN',array_unique($condition['loan_id']));
        }
        // 剔除逻辑删除的数据
        $condition['is_delete'] = array('neq',1);
        $res =  $this->where($condition)->order('gmt_create desc')->select();
        return $res;
    }

    /**
     * 根据借款ID逻辑删除还款记录
     * @param int $loan_id
     * @return bool
     */
    public function logicDeleteByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('Home Model RepaymentsModel logicDeleteByLoanID loan_id is null');
        }
        $condition['loan_id'] = $loan_id;
        $data = array(
            'is_delete' => 1,
        );
        return $this->where($condition)->save($data);
    }

    /**
     * 根据还款ID逻辑删除还款记录
     * @param int $repayments_id
     * @return bool
     */
    public function logicDeleteByRepayID($repayments_id = 0) {
        if(!$repayments_id) {
            throw_exception('Home Model RepaymentsModel logicDeleteByLoanID repayments_id is null');
        }
        $condition['repayments_id'] = $repayments_id;
        $data = array(
            'is_delete' => 1,
        );
        $res = $this->where($condition)->save($data);
        return $res;
    }

    public function listRepayByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('Home Model RepaymentsModel listRepayByLoanID loan_id is null');
        }
        $condition['loan_id'] = $loan_id;
        return $this->where($condition)->order('gmt_repay desc')->select();
    }

    public function deleteRepayByLoanID($loan_id = 0) {
        $condition['loan_id'] = $loan_id;
        return $this->where($condition)->delete();
    }

}