<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/3
 * Time: 20:17
 */

namespace Home\Model;


use Think\Model;

class TourModel extends Model
{
    /**
     * 新增外访资料
     * @param array $data
     * @return mixed
     */
    public function addTour($data = array()) {
        if(!$data) {
            throw_exception('Home Model TourModel addTour data is null');
        }
        return $this->add($data);
    }

    /**
     * 列出所有外访资料并翻页
     * @param array $condition
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function listTourLimit($condition = array(), $page = 1, $pageSize = 10) {
        $condition['is_delete'] = array('neq',1);
        $offset = ($page -1) * $pageSize;
        $res =  $this->where($condition)->order('gmt_tour desc')->limit($offset,$pageSize)->select();
        return $res;
    }

    /**
     * 列出所有外访资料
     * @param array $condition
     * @return mixed
     */
    public function listTour($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        $res =  $this->where($condition)->order('gmt_tour desc')->select();
        return $res;
    }

    /**
     * 获取外访资料数量
     * @return mixed
     */
    public function getCountTour($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->count();
    }
    /**
     * 汇总外访金额
     * @return mixed
     */
    public function sumMoney($condition = array()) {
        $condition['is_delete'] = array('neq',1);
        return $this->where($condition)->sum('money');
    }

    /*
     * 获取外访资料根据ID
     */
    public function getTourByID($tour_id = 0) {
        if(!$tour_id) {
            throw_exception('Home Model TourModel getTourByID tour_id is null');
        }
        $condition['tour_id'] = $tour_id;
        return $this->where($condition)->find();
    }

    /**
     * 更新外访记录信息
     * @param int $tour_id
     * @param array $data
     * @return bool
     */
    public function updateTour($tour_id = 0, $data = array()) {
        if(!$tour_id) {
            throw_exception('Home Model TourModel updateTour tour_id is null');
        }
        if(!$data) {
            throw_exception('Home Model TourModel updateTour data is null');
        }
        $condition['tour_id'] = $tour_id;
        return $this->where($condition)->save($data);
    }

    /**
     * 根据借款记录搜索外访资料
     * @param int $loan_id
     * @return bool
     */
    public function getTourByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('Home Model TourModel getTourByLoanID loan_id is null');
        }
        $condition['loan_id'] = $loan_id;
        return $this->where($condition)->find();
    }

    public function selectTourByCustomerStaffID($customer_id = 0, $staff_id = 0) {
        if(!$customer_id) {
            throw_exception('Home Model TourModel updateTour customer_id is null');
        }
        if(!$staff_id) {
            throw_exception('Home Model TourModel updateTour staff_id is null');
        }
        $condition['customer_id'] = $customer_id;
        $condition['staff_id'] = $staff_id;
        return $this->where($condition)->order('gmt_tour desc')->select();
    }


}