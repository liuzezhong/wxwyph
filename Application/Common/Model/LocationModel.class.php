<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/3
 * Time: 20:18
 */

namespace Common\Model;


use Think\Model;

class LocationModel extends Model
{
    /**
     * 根据条件搜索位置信息
     * @param array $condition
     * @return mixed
     */
    public function selectLoactionByCondition($condition = array()) {
        return $this->where($condition)->order('location_id')->select();
    }

    /**
     * 根据位置ID获取位置信息
     * @param int $location_id
     * @return mixed
     */
    public function getLocationByID($location_id = 0) {
        if(!$location_id) {
            throw_exception('Home Model LocationModel getLocationByID location_id is null');
        }
        $condition['location_id'] = $location_id;
        return $this->where($condition)->find();

    }
}