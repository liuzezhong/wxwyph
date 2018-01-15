<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/11/13
 * Time: 15:49
 */

namespace Home\Controller;


use Think\Controller;
use Think\Exception;

class RegisterController extends Controller
{
    /**
     * 管理员注册页面显示
     */
    public function index() {
        $this->display();
    }

    public function registerCheck() {
        // 获取post数据
        $username = I('post.username','','trim,string');
        $email = I('post.email','','trim,string');
        $password = I('post.password','','trim,string');
        // 验证数据有效性
        if(!$username) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的姓名',
            ));
        }
        if(!$email) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的电子邮箱',
            ));
        }
        if(!$password) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的密码',
            ));
        }

        try {
            // 查看是否已经有此邮箱
            $emailAdmin = D('Admin')->getAdminByEmail($email);
            if($emailAdmin) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该电子邮箱已被注册',
                ));
            }

            // 没有此邮箱，写入注册信息
            $adminData = array(
                'username' => $username,
                'email' => $email,
                'password' => md5($password),
                'gmt_create' => date('Y-m-d H:i:s',time()),
            );
            // 将注册信息写入数据库
            $create = D('Admin')->createAdmin($adminData);
            // 返回结果
            if(!$create) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '注册失败，请稍后重试',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '注册成功，请联系管理员给予登录权限',
            ));

        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }


    }
}