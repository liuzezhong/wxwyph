<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/15
 * Time: 23:00
 */

namespace Home\Model;


use Think\Model;

class WageModel extends Model
{
    public function addWage($data = array()) {
        if(!$data) {
            throw_exception('Home Model WageModel addWage data is null');
        }
        return $this->add($data);
    }

    /**
     * 根据公司号和月份查看是否存在
     * @param string $gmt_wage
     * @param int $company_id
     * @return mixed
     */
    public function checkExist($gmt_wage = '', $company_id = 0) {
        if(!$gmt_wage) {
            throw_exception('Home Model WageModel checkExist gmt_wage is null');
        }

        if(!$company_id) {
            throw_exception('Home Model WageModel checkExist company_id is null');
        }

        $condition = array(
            'gmt_wage' => $gmt_wage,
            'company_id' => $company_id,
        );

        return $this->where($condition)->find();
    }

    /**
     * 按条件搜索共计记录并分页显示
     * @param array $condition
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function listWagesLimit($condition = array(), $page = 1, $pageSize = 10) {
        $condition['is_delete'] = array('neq',1);
        $offset = ($page -1) * $pageSize;
        $res =  $this->where($condition)->order('gmt_wage')->limit($offset,$pageSize)->select();
        return $res;
    }

    public function listWages($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        $res =  $this->where($condition)->order('gmt_wage')->select();
        return $res;
    }

    /**
     * 根据条件统计记录个数
     * @return mixed
     */
    public function getCountWages($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->count();
    }

    /**
     * 总计金额合计
     * @param array $condition
     * @return mixed
     */
    public function sumTotalMoney($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->sum('total');
    }

    /**
     * 工资合计
     * @param array $condition
     * @return mixed
     */
    public function sumWageMoney($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->sum('wage');
    }

    /**
     * 社保金额合计
     * @param array $condition
     * @return mixed
     */
    public function sumInsurMoney($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->sum('insur');
    }

    /**
     * 根据ID号获取工资记录信息
     * @param int $wage_id
     * @return mixed
     */
    public function getWageByID($wage_id = 0) {
        if(!$wage_id) {
            throw_exception('Home Model WageModel getWageByID wage_id is null');
        }
        $condition['wage_id'] = $wage_id;
        return $this->where($condition)->find();
    }

    /**
     * 根据ID修改信息
     * @param int $wage_id
     * @param array $data
     * @return bool
     */
    public function updateWage($wage_id = 0, $data = array()) {
        if(!$wage_id) {
            throw_exception('Home Model WageModel updateWage wage_id is null');
        }
        if(!$data) {
            throw_exception('Home Model WageModel updateWage $data is null');
        }
        $condition['wage_id'] = $wage_id;
        return $this->where($condition)->save($data);
    }

    public function logicDeleteWageByID($wage_id = 0) {
        if(!$wage_id) {
            throw_exception('Home Model WageModel logicDeleteWageByID wage_id is null');
        }
        $condition['wage_id'] = $wage_id;
        $data['is_delete'] = 1;
        return $this->where($condition)->save($data);
    }
}