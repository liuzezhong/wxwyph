<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/11/13
 * Time: 15:46
 */
namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{
    /**
     * 管理员登录页面显示
     */
    public function index() {
        $this->display();
    }

    /**
     * 用户登录校验
     */
    public function loginCheck() {
        // 获取post数据
        $email = I('post.email','','trim,string');
        $password = I('post.password','','trim,string');
        // 验证数据有效性
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
            if(!$emailAdmin) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该电子邮箱未注册',
                ));
            }
            // 校验密码
            if(md5($password) != $emailAdmin['password']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '密码错误，请重试',
                ));
            }

            // 校验登录权限
            if($emailAdmin['jurisdiction'] == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '您未获得登录后台的权限，请联系管理员',
                ));
            }

            // 校验用户状态
            if($emailAdmin['status'] == -1) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '您账户已被冻结，请联系管理员',
                ));
            }
            $company = D('Company')->getCompanyByID($emailAdmin['company_id']);
            $emailAdmin['company'] = $company;
            // 写入session信息
            session('adminUser', $emailAdmin);
            // 返回登录结果
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '登录成功',
            ));
        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    /**
     * 功能：退出登陆
     */
    public function loginOut() {
        //1.1 session置空
        session('adminUser',null);
        //1.2 跳转至首页
        redirect(U('home/login/index'));
    }
}