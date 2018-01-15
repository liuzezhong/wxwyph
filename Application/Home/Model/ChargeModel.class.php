<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/12/31
 * Time: 14:06
 */

namespace Home\Model;


use Think\Model;

class ChargeModel extends Model
{
    /**
     * 新增现金记录
     * @param array $data
     * @return mixed
     */
    public function addCharge($data = array()) {
        if(!$data) {
            throw_exception('Home Model ChargeModel addCharge data is NULL');
        }
        return $this->add($data);
    }

    /**
     * 列出所有的现金记账记录，并分页
     * @param array $condition
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function listCharges($condition = array(), $page = 1, $pageSize = 10) {
        $condition['status'] = array('neq',-1);
        $offset = ($page -1) * $pageSize;
        $res =  $this->where($condition)->order('gmt_create desc')->limit($offset,$pageSize)->select();
        return $res;
    }

    public function listChargesByCondition($condition = array()) {
        $condition['status'] = array('neq',-1);
        $res =  $this->where($condition)->order('gmt_create desc')->select();
        return $res;
    }



    /**
     * 获取现金记录的数量
     * @param array $condition
     * @return mixed
     */
    public function getCountCharges($condition = array()) {
        $condition['status'] = array('neq',-1);
        return $this->where($condition)->count();
    }

    /**
     * 获取现金记录的金额总计
     * @param array $condition
     * @return mixed
     */
    public function sumMoney($condition = array()) {
        $condition['status'] = array('neq',-1);
        return $this->where($condition)->sum('money');
    }

    /**
     * 逻辑删除现金记录
     * @param int $charge_id
     * @return bool
     */
    public function deleteChargeByID($charge_id = 0) {
        if(!$charge_id) {
            throw_exception('Home Model ChargeModel deleteChargeByID charge_id is NULL');
        }
        $condition['charge_id'] = $charge_id;
        $data['status'] = -1;
        return $this->where($condition)->save($data);
    }

    /**
     * 根据ID获取记账记录
     * @param int $charge_id
     * @return mixed
     */
    public function getChargeByID($charge_id = 0) {
        if(!$charge_id) {
            throw_exception('Home Model ChargeModel getChargeByID charge_id is NULL');
        }
        $condition['charge_id'] = $charge_id;
        return $this->where($condition)->find();
    }

    /**
     * 更新记账记录信息
     * @param int $charge_id
     * @param array $data
     * @return bool
     */
    public function updateCharge($charge_id = 0, $data = array()) {
        if(!$charge_id) {
            throw_exception('Home Model ChargeModel updateCharge charge_id is NULL');
        }
        if(!$data) {
            throw_exception('Home Model ChargeModel updateCharge data is NULL');
        }
        $condition['charge_id'] = $charge_id;
        return $this->where($condition)->save($data);
    }
}