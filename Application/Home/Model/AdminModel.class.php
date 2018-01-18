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

    public function getAdminByPhone($phone = '') {
        if(!$phone) {
            throw_exception('Admin Model AdminModel getAdminByPhone phone is null');
        }
        $condition['phone'] = $phone;
        return $this->where($condition)->find();
    }

    public function updateAdmin($user_id = 0, $data = array()) {
        if(!$user_id) {
            throw_exception('Admin Model AdminModel updateAdmin user_id is null');
        }
        if(!$data) {
            throw_exception('Admin Model AdminModel updateAdmin data is null');
        }
        $condition['user_id'] = $user_id;
        return $this->where($condition)->save($data);
    }

    public function listAdmin($condition = array(), $page = 1, $pageSize = 10) {
        $offset = ($page -1) * $pageSize;
        $res =  $this->where($condition)->order('gmt_create desc')->limit($offset,$pageSize)->select();
        return $res;
    }

    public function getCountAdmin($condition = array()) {
        return $this->where($condition)->count();
    }

    public function deleteUserByID($user_id = 0) {
        if(!$user_id) {
            throw_exception('Home Model AdminModel deleteUserByID user_id is null');
        }
        $condition['user_id'] = $user_id;
        return $this->where($condition)->delete();
    }
}