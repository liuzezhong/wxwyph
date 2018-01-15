<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 14:22
 */

namespace Home\Controller;


use Think\Controller;

class CommonController extends Controller {
    /**
     * 构造方法
     * CommonController constructor.
     */
    public function __construct() {
        header("Content-type: text/html; charset=utf-8");
        parent::__construct();
        $this->_init();  //调用初始化方法
    }
    /**
     * 初始化
     * @return
     */
    private function _init() {
        //1.1 如果已经登录
        $isLogin = $this->isLogin();
        if($isLogin) {
            $user = D('Admin')->getAdminByID($_SESSION['adminUser']['user_id']);
            if(!$user) {
                //1.2 跳转到登录页面
                redirect(U('home/login/index'));
            }
        }
        if(!$isLogin) {
            //1.2 跳转到登录页面
            redirect(U('home/login/index'));
        }
    }
    /**
     * 获取登录用户信息
     * @return array
     */
    public function getLoginUser() {
        return session("adminUser");
    }
    /**
     * 判定是否登录
     * @return boolean
     */
    public function isLogin() {
        $user = $this->getLoginUser();
        if($user && is_array($user)) {
            return true;
        }
        return false;
    }

    // 数据库获取当前登录用户的信息
    public function getUserNowInfo() {
        $user_id = session('adminUser')['user_id'];
        $user = D('Admin')->getAdminByID($user_id);
        return $user;
    }
}