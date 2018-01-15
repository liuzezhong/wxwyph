<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/11/15
 * Time: 13:37
 */

namespace Home\Model;


use Think\Model;

class RegisterModel extends Model
{
    /**
     * 功能：根据比赛ID查找已经报名的用户信息
     * @param int $match_id
     * @return mixed
     */
    public function getRegisterByMatchID($match_id = 0) {
        if(!$match_id) {
            throw_exception('Admin Model getRegisterByMatchID match_id id is null');
        }
        $condition['match_id'] = $match_id;
        return $this->where($condition)->select();
    }

    /**
     * 根据报名信息删除一条报名记录
     * @param int $register_id
     * @return mixed
     */
    public function deleteRegister($register_id = 0) {
        if(!$register_id) {
            throw_exception('Admin Model deleteRegister register id is null');
        }
        $condition['register_id'] = $register_id;
        return $this->where($condition)->delete();
    }

    /**
     * 获取今天的新增报名人数
     * @return mixedhuo
     */
    public function countRegisterToday() {
        $s_time = date('Y-m-d',time()) . ' 00:00:00';
        $e_time = date('Y-m-d',time()) . ' 23:59:59';
        $condition['gmt_create'] = array('BETWEEN',array($s_time,$e_time));
        return $this->where($condition)->count();
    }
}