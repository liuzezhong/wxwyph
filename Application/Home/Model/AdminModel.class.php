<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/11/14
 * Time: 14:15
 */

namespace Home\Model;


use Think\Model;

class AdminModel extends Model
{
    /**
     * 根据用户邮箱搜索用户信息
     * @param string $email
     * @return mixed
     */
    public function getAdminByEmail($email = '') {
        if(!$email) {
            throw_exception('Admin Model AdminModel getAdminByEmail email is null');
        }
        $condition['email'] = $email;
        return $this->where($condition)->find();
    }

    /**
     * 新增用户信息
     * @param array $data
     * @return mixed
     */
    public function createAdmin($data = array()) {
        if(!$data) {
            throw_exception('Admin Model AdminModel getAdminByEmail data is null');
        }
        return $this->add($data);
    }

    /**
     * 根据用户ID返回用户信息
     * @param int $user_id
     * @return mixed
     */
    public function getAdminByID($user_id = 0) {
        if(!$user_id) {
            throw_exception('Admin Model AdminModel getAdminByID user_id is null');
        }
        $condition['user_id'] = $user_id;
        return $this->where($condition)->find();
    }
}