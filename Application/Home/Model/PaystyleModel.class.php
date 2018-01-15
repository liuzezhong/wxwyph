<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/3
 * Time: 20:20
 */

namespace Home\Model;


use Think\Model;

class PaystyleModel extends Model
{
    /**
     * 根据条件搜索支付方式信息
     * @param array $condition
     * @return mixed
     */
    public function selectPaystyleByCondition($condition = array()) {
        return $this->where($condition)->order('style_id')->select();
    }

    /**
     * 获取支付方式通过ID
     * @param int $style_id
     * @return mixed
     */
    public function getPaystyleByID($style_id = 0) {
        if(!$style_id) {
            throw_exception('Home Model PaystyleModel getPaystyleByID is null');
        }
        $condition['style_id'] = $style_id;
        return $this->where($condition)->find();
    }
}