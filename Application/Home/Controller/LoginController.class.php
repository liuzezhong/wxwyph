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
        $mobile = I('post.mobile','','trim,string');
        $email = I('post.email','','trim,string');
        $password = I('post.password','','trim,string');
        $vercode = I('post.vercode','','trim,string');
        // 验证数据有效性
        if(!$email && !$mobile) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的手机号码或电子邮箱',
            ));
        }
        if(!$password) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的密码',
            ));
        }
        if(!$vercode){
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '验证码不能为空',
            ));
        }

        //1.2 检验验证码
        $result = $this->check_verify($vercode);
        if($result < 1){
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '验证码有误，请重新输入',
            ));
        }

        try {

            if($mobile) {
                $user = D('Admin')->getAdminByPhone($mobile);
                if(!$user) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '该账号不存在',
                    ));
                }
            }else if($email) {
                // 查看是否已经有此邮箱
                $user = D('Admin')->getAdminByEmail($email);
                if(!$user) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '该账号不存在',
                    ));
                }
            }

            // 校验密码
            if(md5($password) != $user['password']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '密码错误，请重试',
                ));
            }

            // 校验登录权限
            if($user['jurisdiction'] == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '您未获得登录后台的权限，请联系管理员',
                ));
            }

            // 校验用户状态
            if($user['status'] == -1) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '您账户已被冻结，请联系管理员',
                ));
            }
            $company = D('Company')->getCompanyByID($user['company_id']);
            $user['company'] = $company;
            // 写入session信息
            session('adminUser', $user);
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

    /**
     * 功能：验证码类
     */
    public function verify() {
        $config = array(
            'length' => 4,     // 验证码位数
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }
    /**
     * 功能：验证码检测
     * @param $code
     * @param string $id
     * @return bool
     */
    function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        $verify->reset = false;       //验证成功后是否重置
        return $verify->check($code, $id);
    }
}