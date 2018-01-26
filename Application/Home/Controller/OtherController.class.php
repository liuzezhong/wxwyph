<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/16
 * Time: 19:09
 */

namespace Home\Controller;


use Think\Exception;
use Think\Page2;

class OtherController extends CommonController
{
    /**
     * 个人信息
     */
    public function userInfo() {
        $user = $this->getUserNowInfo();
        $this->assign(array(
            'user' => $user,
        ));
        $this->display();
    }

    public function checkUserInfo() {
        $user = $this->getUserNowInfo();
        $username = I('post.username','','string,trim');
        $phone = I('post.phone','','string,trim');
        $email = I('post.email','','string,trim');
        $department = I('post.department','','string,trim');
        $position = I('post.position','','string,trim');
        $image = I('post.image');

        if(!$username) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的用户名',
            ));
        }

        if(!$phone) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的手机号码',
            ));
        }

        if(!$email) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的电子邮箱',
            ));
        }

        if(!$department) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的所在部门',
            ));
        }

        if(!$position) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的职位名称',
            ));
        }

        try {
            // 查看是否已经有此邮箱
            $emailAdmin = D('Admin')->getAdminByPhone($email);
            if($emailAdmin && $emailAdmin['user_id'] != $user['user_id']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该电子邮箱已被注册',
                ));
            }

            // 查看是否存在手机号码
            $phoneAdmin = D('Admin')->getAdminByPhone($phone);
            if($phoneAdmin && $phoneAdmin['user_id'] != $user['user_id']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该手机号码已被注册',
                ));
            }

            if($image) {
                $adminData = array(
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'department' => $department,
                    'position' => $position,
                    'gmt_modifiy' => date('Y-m-d H:i:s',time()),
                    'avatarUrl' => $image[0],
                );
            }else {
                $adminData = array(
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'department' => $department,
                    'position' => $position,
                    'gmt_modifiy' => date('Y-m-d H:i:s',time()),
                );
            }

            // 将注册信息写入数据库
            $update = D('Admin')->updateAdmin($user['user_id'],$adminData);
            // 返回结果
            if(!$update) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '修改失败',
                ));
            }
            $newUser = D('Admin')->getAdminByID($user['user_id']);
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '修改成功',
            ));

        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }


    /**
     * 修改密码
     */
    public function changePwd() {
        $this->display();
    }

    public function checkPwd() {
        $old_password = I('post.old_password','','string,trim');
        $new_password = I('post.new_password','','string,trim');
        $new_password2 = I('post.new_password2','','string,trim');

        if(!$old_password) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入旧密码',
            ));
        }

        if(!$new_password) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入新密码',
            ));
        }

        if(!$new_password2) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请再次输入新密码',
            ));
        }

        if($new_password != $new_password2) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '两次输入的密码不一致',
            ));
        }

        try {
            $user = $this->getUserNowInfo();
            if(md5($old_password) != $user['password']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '旧密码错误，请重试',
                ));
            }
            $data = array(
                'password' => md5($new_password),
            );

            $update = D('Admin')->updateAdmin($user['user_id'],$data);

            if(!$update) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '修改失败，请重试',
                ));
            }

            session('adminUser',null);
            //1.2 跳转至首页
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '修改成功',
            ));

        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function adminList() {

        $condition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        $quanxian = array(
            '0' => array(
                'id' => 0,
                'name' => '无后台权限',
            ),
            '1' => array(
                'id' => 1,
                'name' => '会计权限',
            ),
            '2' => array(
                'id' => 2,
                'name' => '老板权限',
            ),
            '3' => array(
                'id' => 3,
                'name' => '贷后权限',
            ),
        );


        if(I('get.company_id','','string') && I('get.company_id','','string') != 'undefined') {
            $companySearchArray = explode(',',I('get.company_id','','string'));
            foreach ($companys as $key => $item) {
                $companys[$key]['selected'] = 0;
                foreach ($companySearchArray as $i => $j) {
                    if($item['company_id'] == $j) {
                        $companys[$key]['selected'] = 1;
                    }
                }
            }
            $condition['company_id'] = array('IN',$companySearchArray);
        }else {
            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
            $companyIDs = array_column($companys,'company_id');
            $condition['company_id'] = array('IN',$companyIDs);
            foreach ($companys as $key => $item) {
                $companys[$key]['selected'] = 0;
                foreach ($companyIDs as $i => $j) {
                    if($item['company_id'] == $j) {
                        $companys[$key]['selected'] = 1;
                    }
                }
            }
        }

        //1.2 获取当前页码
        $now_page = I('request.page',1,'intval');
        $page_size = I('request.pageSize',10,'intval');
        $page = $now_page ? $now_page : 1;
        //1.3 设置默认分页条数
        $pageSize = $page_size ? $page_size : 10;
        //1.4 数据库查询

        $userList = D('Admin')->listAdmin($condition,$page,$page_size);
        $countList = D('Admin')->getCountAdmin($condition);

        //1.5 实例化一个分页对象
        $res = new Page2($countList,$pageSize);
        //1.6 调用show方法前台显示页码
        $pageRes = $res->show();
        //1.7 处理数据

        foreach ($userList as $key => $item) {
            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $userList[$key]['company_name'] = $company['smallname'];
            $userList[$key]['gmt_create'] = date('Y-m-d',strtotime($item['gmt_create']));

            if($item['jurisdiction'] == 1) {
                $userList[$key]['jurisdiction_name'] = '会计权限';
            }else if($item['jurisdiction'] == 2) {
                    $userList[$key]['jurisdiction_name'] = '老板权限';
            }else if($item['jurisdiction'] == 3) {
                $userList[$key]['jurisdiction_name'] = '贷后权限';
            }else {
                $userList[$key]['jurisdiction_name'] = '无登录权限';
            }
        }

        $this->assign(array(
            'userList' => $userList,
            'pageRes' => $pageRes,
            'userInfo' => $userInfo,
            'companys' => $companys,
            'quanxian' => $quanxian,
        ));

        $this->display();
    }

    public function editAdmin() {
        $user_id = I('post.id',0,'intval');
        if(!$user_id) {
           $this->ajaxReturn(array(
               'status' => 0,
               'message' => '用户ID为空'
           ));
        }

        try{
            $user = D('Admin')->getAdminByID($user_id);
            if(!$user) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '用户信息不存在'
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '用户信息查找成功',
                'user' => $user,
            ));

        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function checkAdminListValue() {
        $user_id = I('post.user_id',0,'intval');
        $username = I('post.username','','string,trim');
        $phone = I('post.phone','','string,trim');
        $email = I('post.email','','string,trim');
        $department = I('post.department','','string,trim');
        $position = I('post.position','','string,trim');
        $jurisdiction = I('post.jurisdiction',0,'intval');
        $company_id = I('post.company_id',0,'intval');
        $image = I('post.image');

        if(!$user_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户ID不存在',
            ));
        }

        if(!$username) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的用户名',
            ));
        }

        if(!$phone) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的手机号码',
            ));
        }

        if(!$email) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的电子邮箱',
            ));
        }

        if(!$department) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的所在部门',
            ));
        }

        if(!$position) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的职位名称',
            ));
        }

        if(!$jurisdiction) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择用户权限',
            ));
        }

        if(!$company_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择所属公司',
            ));
        }

        try {
            // 查看是否已经有此邮箱
            $emailAdmin = D('Admin')->getAdminByPhone($email);
            if($emailAdmin && $emailAdmin['user_id'] != $user_id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该电子邮箱已被注册',
                ));
            }

            // 查看是否存在手机号码
            $phoneAdmin = D('Admin')->getAdminByPhone($phone);
            if($phoneAdmin && $phoneAdmin['user_id'] != $user_id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该手机号码已被注册',
                ));
            }

            if($image) {
                $adminData = array(
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'department' => $department,
                    'position' => $position,
                    'jurisdiction' => $jurisdiction,
                    'company_id' => $company_id,
                    'gmt_modifiy' => date('Y-m-d H:i:s',time()),
                    'avatarUrl' => $image[0],
                );
            }else {
                $adminData = array(
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'department' => $department,
                    'position' => $position,
                    'jurisdiction' => $jurisdiction,
                    'company_id' => $company_id,
                    'gmt_modifiy' => date('Y-m-d H:i:s',time()),
                );
            }

            // 将注册信息写入数据库
            $update = D('Admin')->updateAdmin($user_id,$adminData);
            // 返回结果
            if(!$update) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '修改失败',
                ));
            }
            $newUser = D('Admin')->getAdminByID($user_id);
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '修改成功',
            ));

        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function changePasswordList() {
        $user_id = I('post.user_id',0,'intval');
        $password = I('post.password','','string,trim');

        if(!$user_id) {
           $this->ajaxReturn(array(
               'status' => 0,
               'message' => '用户ID为空'
           ));
        }

        if(!$password) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户为空'
            ));
        }

        try {
            $data = array(
                'password' => md5($password),
            );

            $update = D('Admin')->updateAdmin($user_id,$data);

            if(!$update) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '修改失败，请重试',
                ));
            }

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '修改成功',
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function checkAdminListADD() {
        $username = I('post.username','','string,trim');
        $password = I('post.password','','string,trim');
        $phone = I('post.phone','','string,trim');
        $email = I('post.email','','string,trim');
        $department = I('post.department','','string,trim');
        $position = I('post.position','','string,trim');
        $jurisdiction = I('post.jurisdiction',0,'intval');
        $company_id = I('post.company_id',0,'intval');
        $image = I('post.image');

        if(!$username) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的用户名',
            ));
        }

        if(!$password) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的密码',
            ));
        }

        if(!$phone) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的手机号码',
            ));
        }

        if(!$email) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的电子邮箱',
            ));
        }

        if(!$department) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的所在部门',
            ));
        }

        if(!$position) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入您的职位名称',
            ));
        }

        if(!$jurisdiction) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择用户权限',
            ));
        }

        if(!$company_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择所属公司',
            ));
        }

        try {
            // 查看是否已经有此邮箱
            $emailAdmin = D('Admin')->getAdminByPhone($email);
            if($emailAdmin) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该电子邮箱已被注册',
                ));
            }

            // 查看是否存在手机号码
            $phoneAdmin = D('Admin')->getAdminByPhone($phone);
            if($phoneAdmin) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该手机号码已被注册',
                ));
            }

            if($image) {
                $adminData = array(
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'department' => $department,
                    'position' => $position,
                    'jurisdiction' => $jurisdiction,
                    'company_id' => $company_id,
                    'gmt_create' => date('Y-m-d H:i:s',time()),
                    'password' => md5($password),
                    'avatarUrl' => $image[0],
                );
            }else {
                $adminData = array(
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'department' => $department,
                    'position' => $position,
                    'jurisdiction' => $jurisdiction,
                    'company_id' => $company_id,
                    'password' => md5($password),
                    'gmt_modifiy' => date('Y-m-d H:i:s',time()),
                    'avatarUrl' => C('DEFAULT_AVATAR_URL'),
                );
            }

            // 将注册信息写入数据库
            $add = D('Admin')->createAdmin($adminData);
            // 返回结果
            if(!$add) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '新增失败',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '新增成功',
            ));

        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function deleteUser() {
        $user_id = I('post.id',0,'intval');
        if($user_id) {
            try {
                $res = D('Admin')->deleteUserByID($user_id);
                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '删除成功',
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '删除失败，请稍后重试',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户ID不存在！',
            ));
        }
    }

    public function wxuserList() {

        $condition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        $quanxian = array(
            '0' => array(
                'id' => 0,
                'name' => '无权限',
            ),
            '1' => array(
                'id' => 1,
                'name' => '有权限',
            ),
        );
        $departments = D('Department')->selectAllDepartment();

        foreach ($departments as $key => $item) {
            if($item['department_id'] == 1 || $item['department_id'] == 3 || $item['department_id'] == 7) {
                continue;
            }
            unset($departments[$key]);
        }
        if(I('get.company_id','','string') && I('get.company_id','','string') != 'undefined') {
            $companySearchArray = explode(',',I('get.company_id','','string'));
            foreach ($companys as $key => $item) {
                $companys[$key]['selected'] = 0;
                foreach ($companySearchArray as $i => $j) {
                    if($item['company_id'] == $j) {
                        $companys[$key]['selected'] = 1;
                    }
                }
            }
            $condition['company_id'] = array('IN',$companySearchArray);
        }else {
            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
            $companyIDs = array_column($companys,'company_id');
            $condition['company_id'] = array('IN',$companyIDs);
            foreach ($companys as $key => $item) {
                $companys[$key]['selected'] = 0;
                foreach ($companyIDs as $i => $j) {
                    if($item['company_id'] == $j) {
                        $companys[$key]['selected'] = 1;
                    }
                }
            }
        }

        //1.2 获取当前页码
        $now_page = I('request.page',1,'intval');
        $page_size = I('request.pageSize',10,'intval');
        $page = $now_page ? $now_page : 1;
        //1.3 设置默认分页条数
        $pageSize = $page_size ? $page_size : 10;
        //1.4 数据库查询

        $userList = D('User')->listWxUser($condition,$page,$page_size);
        $countList = D('User')->getCountWxuser($condition);

        //1.5 实例化一个分页对象
        $res = new Page2($countList,$pageSize);
        //1.6 调用show方法前台显示页码
        $pageRes = $res->show();
        //1.7 处理数据

        foreach ($userList as $key => $item) {
            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $userList[$key]['company_name'] = $company['smallname'];
            $userList[$key]['gmt_create'] = date('Y-m-d',strtotime($item['gmt_create']));

            if($item['status'] == 0) {
                $userList[$key]['status_name'] = '无权限';
            }else if($item['status'] == 1) {
                $userList[$key]['status_name'] = '有权限';
            }

            //部门id
            if($item['depart_id']) {
                $depart = D('Department')->getDepartMentByID($item['depart_id']);
                $userList[$key]['depart_name'] = $depart['department_name'];
            }


            $userList[$key]['gender'] = ($item['gender'] == 1) ? '男' : ($item['gender'] == 2 ? '女' : '不男不女');
        }

        $this->assign(array(
            'wxuserList' => $userList,
            'pageRes' => $pageRes,
            'userInfo' => $userInfo,
            'companys' => $companys,
            'quanxian' => $quanxian,
            'departments' => $departments,
        ));

        $this->display();
    }

    public function editWxuser() {
        $user_id = I('post.id',0,'intval');
        if(!$user_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户ID为空'
            ));
        }

        try{
            $user = D('User')->getUserInfoByUserID($user_id);
            if(!$user) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '用户信息不存在'
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '用户信息查找成功',
                'user' => $user,
            ));

        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function checkWxAdminListValue() {
        $real_name = I('post.real_name','','string,trim');
        $status = I('post.status',0,'intval');
        $depart_id = I('post.depart_id',0,'intval');
        $company_id = I('post.company_id',0,'intval');
        $user_id = I('post.user_id',0,'intval');

        if(!$user_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户ID不存在',
            ));
        }

        if(!$real_name) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入真实姓名',
            ));
        }

        try {
            $data = array(
                'status' => $status,
                'depart_id' => $depart_id,
                'company_id' => $company_id,
                'real_name' => $real_name,
                'gmt_modify' => date('Y-m-d H:i:s',time()),
            );
            $res = D('User')->updateWxuserByID($user_id,$data);
            if(!$res) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '修改失败，稍后再试',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '修改成功',
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function deleteWxUser() {
        $user_id = I('post.id',0,'intval');
        if($user_id) {
            try {
                $res = D('Admin')->deleteUserByID($user_id);
                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '删除成功',
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '删除失败，请稍后重试',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户ID不存在！',
            ));
        }
    }

    public function orderIN() {

        // 万盈二部
        $company_id = 4;
        /*$data_array = '2016.11.1	张超	13801533660	8000	267	224	零用贷	周还	30	1700	800	800	5500	6300	邵静	陆春松	结清	宜兴	0	0
2016.10.31	黄君	13812182409	10000	500	280	零用贷	周还	20	1600	1000	1000	7400	8400	刘军	陆春松车	结清		0	0
2016.10.31	陶莉	13861720707	6000	300	168	零用贷	周还	20	1200	300	600	4500	5100			结清		0	0
2016.11.3	李江	13861832716	15000	750	420	零用贷	周还	20	2500	1500	1800	11000	12800	邵静	成乃柏车	结清		0	0
2016.11.3	姚尧	15161529899	8000	400	224	零用贷	周还	20	1800	800	960	5400	6360	崔日成	陆春松	结清		0	0
2016.11.3	黎志军	18811995408	10000	334	280	零用贷	周还	30	2000	1000	1200	7000	8200	徐辉	陆春松	结清		0	0
2016.11.4	唐纪春	18861797876	6000	300	168	零用贷	周还	20	1400	600	720	4000	4720	崔日成		结清		0	0
2016.11.4	毛卉	15961732269	15000	750	420	零用贷	周还	20	2500	1500	1800	11000	12800	崔日成	成乃柏	结清		0	0
2016.11.4	鲍琳	15152220163	6000	400	168	零用贷	周还	15	1400	600	720	4000	4720	崔日成		结清		0	0
2016.11.4	葛磊	13815104179	10000	334	280	零用贷	周还	30	2000	1000	1200	7000	8200	邵静	陆春松车	结清		0	0
2016.11.7	徐俊卫	15961791871	6000	300	168	零用贷	周还	20	1100	300	720	4600	5320	崔日成		结清		0	0
2016.11.7	杨阳	18352555359	8000	267	224	零用贷	周还	30	1600	800	960	5600	6560	邵静		结清		0	0
2016.11.7	邹珏	13033510870	8000	534	224	零用贷	周还	15	1800	800	960	5400	6360	陈玮伟	成乃柏	结清		0	0
2016.11.8	冯连成	13621511366	10000	500	280	零用贷	周还	20	1700	1000	1200	7300	8500	邵静	结清	结清		0	0
2016.11.8	周昱	15306196231	15000	750	420	零用贷	周还	20	2200	1500	1800	11300	13100	崔日成	陆春松	结清		0	0
2016.11.9	荣健	13395198532	15000	750	315	零用贷	周还	20	2200	1500	1800	11300	13100	朱莎莎	陆春松	结清		0	0
2016.11.9	章亚良	15052135314	8000	400	224	零用贷	周还	20	1600	800	960	5600	6560	邵静		结清		0	0
2016.11.9	杨六荣	13584231900	6000	300	168	零用贷	周还	20	1400	600	720	4000	4720	崔日成		结清		0	0
2016.11.9	魏冠华	13400021990	6000	600	200	零用贷	周还	10	1200	600	720	4200	4920	陈玮伟		结清		0	0
2016.11.9	张志坚	13665127552	10000	1000	350	零用贷	周还	10	2000	1000	1200	7000	8200	顾智睿	成乃柏车	结清		0	0
2016.11.10	邹晟	13912396565	6000	300	168	零用贷	周还	20	1300	600	720	4100	4820	陶超		结清		0	0
2016.11.10	杨洋	13771505778	8000	400	224	零用贷	周还	20	1600	800	960	5600	6560	崔日成	陆春松	结清		0	0
2016.11.10	朱峰	15961875081	10000	1000	350	零用贷	周还	10	2000	1000	1200	7000	8200	邵静	陆春松车	结清		0	0
2016.11.10	韩忠	15298404344	6000	300	168	零用贷	周还	20	1400	600	720	4000	4720	崔日成		结清		0	0
2016.11.10	华峥屹	18914110779	8000	400	224	零用贷	周还	20	1800	800	960	5400	6360	崔日成	陆春松车	结清		0	0
2016.11.10	龚新志	13812287183	6000	400	168	零用贷	周还	15	2400	600	720	3000	3720	陈玮伟	成乃柏车	结清		0	0
2016.11.11	周文斌	18762682167	8000	400	224	零用贷	周还	20	1800	800	960	5400	6360	崔日成	陆春松车	结清		0	0
2016.11.12	惠燕佳	13347906799	10000	334	280	零用贷	周还	30	2000	500	1200	7500	8700			结清		0	0
2016.11.12	李亚飞	13616170575	10000	500	280	零用贷	周还	20	2000	1000	1200	7000	8200	小相	陆春松车	结清		0	0
2016.11.14	华艳	15190258955	15000	500	400	零用贷	周还	30	2800	1500	2100	10700	12800	小相		结清		0	0
2016.11.15	岳伟	13961506151	6000	600	168	零用贷	周还	10	1400	600	720	4000	4720	崔日成		结清		0	0
2016.11.16	王旭东	15190251160	15000	1000	420	零用贷	周还	15	2550	1500	1800	10950	12750	崔日成		结清		0	0
2016.11.16	薛晓娟	15861585191	6000	300	168	零用贷	周还	20	1200	600	720	4200	4920	邵静		结清		0	0
2016.11.16	沈建华	15806173777	10000	500	280	零用贷	周还	20	2000	500	1200	7500	8700	崔日成	陆春松	结清		0	0
2016.11.16	刘韫	15951563038	6000	300	168	零用贷	周还	20	1268	600	720	4132	4852	邵静		结清		0	0
2016.11.17	沈杰	13771118164	6000	200	126	零用贷	周还	30	1000	300	720	4700	5420	张卿		结清		0	0
2016.11.17	李强	13921227727	10000	334	250	零用贷	周还	30	1800	500	0	7700	7700			结清		0	0
2016.11.17	王国才	15312229370	8000	400	224	零用贷	周还	20	1500	800	960	5700	6660	崔日成	陆春松	结清		0	0
2016.11.18	张剑	13915224236	6000	600	168	零用贷	周还	10	1368	600	720	4032	4752	王倩		结清		0	0
2016.11.18	李晓明	18800566587	6000	600	168	零用贷	周还	10	1400	600	720	4000	4720		陆春松车	结清		0	0
2016.11.18	赵玲犀	13951510107	10000	500	280	零用贷	周还	20	2280	500	1200	7220	8420	邵静		结清		0	0
2016.11.18	陈瑶	18795626947	8000	400	224	零用贷	周还	20	1924	800	960	5276	6236	邵静	成乃柏车	结清	宜兴	0	0
2016.11.22	陆琴珍	15261657255	15000	750	250	打卡	天还	20	3000	1500	750	10500	11250	王倩		结清		0	0
2016.11.22	李宁峰	13400022910	6000	300	210	零用贷	周还	20	1400	300	300	4300	4600	崔日成	陆春松	结清		0	0
2016.11.23	奚学东	18961756188	8000	400	224	零用贷	周还	20	1500	400	400	6100	6500	崔日成		结清		0	0
2016.11.23	张耀明	18915328535	7000	350	196	零用贷	周还	20	1550	700	350	4750	5100	张敏	成乃柏车	结清		0	0
2016.11.23	张鑫	15951502526	6000	600	210	零用贷	周还	10	1200	600	300	4200	4500	张敏		结清		0	0
2016.11.24	钱丽亚	15961809907	7000	350	196	零用贷	周还	20	1350	350	350	5300	5650			结清		0	0
2016.11.24	夏顺洪	13921318566	8000	534	224	零用贷	周还	15	1500	400	400	6100	6500	张敏		结清	宜兴	0	0
2016.11.25	高升	18651516787	6000	300	168	零用贷	周还	20	1100	600	300	4300	4600	王倩		结清		0	0
2016.11.25	胡书斌	18552093587	7000	350	245	零用贷	周还	20	1400	700	350	4900	5250	朱莎莎		结清		0	0
2016.11.25	白知会	18036877759	10000	500	280	零用贷	周还	20	1800	500	500	7700	8200			结清		0	0
2016.11.25	苏正山	13382227830	10000	500	280	零用贷	周还	20	1500	500	500	8000	8500	崔日成	成乃柏车	结清		0	0
2016.11.25	张军寒	18951589342	8000	400	224	零用贷	周还	20	1800	800	400	5400	5800	崔日成	陆春松车	结清		0	0
2016.11.25	孔敏键	18360813139	5000	500	140	零用贷	周还	10	1050	500	250	3450	3700	汇东		结清		0	0
2016.11.28	吴海	15852810707	6000	600	168	零用贷	周还	10	1200	300	300	4500	4800	王倩		结清		0	0
2016.11.28	赵琳	13812011088	8000	400	224	零用贷	周还	20	1700	400	400	5900	6300	张敏	成乃柏	结清		0	0
2016.11.28	程鑫	18861500549	6000	600	210	零用贷	周还	10	1100	600	300	4300	4600	张敏		结清		0	0
2016.11.28	倪叙兴	13921170108	15000	500	315	零用贷	周还	30	2550	750	1500	11700	13200	刘军		结清		0	0
2016.11.28	刘东	18951578523	6000	300	168	零用贷	周还	20	1400	600	900	4000	4900	陶超	成乃柏车	结清		0	0
2016.11.28	何福仙	18650721558	10000	500	280	零用贷	周还	20	1900	1000	1000	7100	8100	吕晓慧	陶超	结清		0	0
2016.11.28	沈峰	15358930906	8000	400	224	零用贷	周还	20	2100	800	1200	5100	6300	张敏	陆春松车	结清	江阴	0	0
2016.11.28	顾嘉洋	13616190461	10000	334	280	零用贷	周还	30	1500	1000	1000	7500	8500	王倩		结清		0	0
2016.11.28	顾嘉洋	13616190461	10000	334	280	零用贷	周还	30	1500	1000	1000	7500	8500			结清		0	0
2016.11.29	周建权	18101528956	6000	300	210	零用贷	周还	20	1200	600	600	4200	4800	徐氏		结清		0	0
2016.11.29	赵余彩	13771200354	6000	300	200	零用贷	周还	20	1600	300	900	4100	5000	崔日成		结清		0	0
2016.11.29	严国庆	18262285878	8000	400	300	零用贷	周还	20	2500	800	1200	4700	5900	吕晓慧	陆春松	结清		0	0
2016.11.29	钱霞萍	13621522583	8000	400	280	零用贷	周还	20	1200	800	800	6000	6800	零置		结清		0	0
2016.11.29	胡洁	13585013458	7000	470	250	零用贷	周还	15	1700	350	1050	4950	6000	崔日成		结清		0	0
2016.11.29	陈绪美	15852820080	15000	750	420	零用贷	周还	20	2700	800	1500	11500	13000	张敏	成乃柏	结清		0	0
2016.11.29	范嘉平	15052118531	6000	300	210	零用贷	周还	20	1700	300	600	4000	4600	张敏	陶超	结清		0	0
2016.11.30	朱彬	17306107076	13000	650	300	零用贷	周还	20	2450	1300	1300	9250	10550	吕晓慧	陆春松车	结清		0	0
2016.11.30	方明	18351574345	6000	400	210	零用贷	周还	15	1500	300	900	4200	5100	崔日成		结清		0	0
2016.11.30	王臻	13771551521	10000	350	350	零用贷	周还	30	3000	500	2000	6500	8500	张敏	陆春松	结清		0	0
2016.12.1	韩敏	13921125456	6000	300	210	零用贷	周还	20	1200	600	600	4200	4800	零置		结清		0	0
2016.12.1	谢鸣	15261572645	8000	400	250	零用贷	周还	20	2250	400	800	5350	6150	陈玮伟	陆春松	结清		0	0
2016.12.1	陈伟	18601555786	12000	600	400	零用贷	周还	20	2700	1200	1800	8100	9900	张敏		结清		0	0
2016.12.1	刘蓉	15961518822	6000	200	168	零用贷	周还	30	1100	300	600	4600	5200	徐氏		结清		0	0
2016.12.1	郑曦磊	15861675573	8000	400	250	零用贷	周还	20	2100	400	1200	5500	6700	张敏	陆春松	结清		0	0
2016.12.1	丁丽梅	13921151851	10000	500	300	零用贷	周还	20	2000	500	1000	7500	8500	崔日成	成乃柏车	结清		0	0
2016.12.2	彭杰	15261587235	10000	670	350	零用贷	周还	15	1800	1000	1000	7200	8200	汇东		结清		0	0
2016.12.2	陶国清	13812180924	8000	400	300	零用贷	周还	20	2000	800	1200	5200	6400	张敏	成乃柏车	结清		0	0
2016.12.2	刘希	13771180579	10000	1000	300	零用贷	周还	10	2000	500	1000	7500	8500	邵静		结清		0	0
2016.12.2	王代琳	13912352252	6000	300	170	零用贷	周还	20	900	600	300	4500	4800	邵静		结清		0	0
2016.12.3	袁俊杰	13485050562	6000	300	210	零用贷	周还	20	1500	500	900	4000	4900	张敏	陆春松车	结清		0	0
2016.12.3	王震	13816780514	6000	400	210	零用贷	周还	15	1200	300	300	4500	4800	邵静		结清		0	0
2016.12.3	陈文蕾	15152251600	6000	300	210	零用贷	周还	20	1400	600	900	4000	4900	张敏		结清		0	0
2016.12.3	李诚	18626319817	6000	300	210	零用贷	周还	20	1700	300	600	4000	4600	崔日成		结清		0	0
2016.12.4	金啸	18762810100	15000	750	525	零用贷	周还	20	3500	1500	2250	10000	12250	王倩	成乃柏车	结清		0	0
2016.12.5	吴春龙	13145204828	7000	240	250	零用贷	周还	30	1500	300	700	5200	5900	张敏	成乃柏车	结清		0	0
2016.12.5	华冠杰	18115390791	8000	400	280	零用贷	周还	15	1900	800	1200	5300	6500	张敏		结清		0	0
2016.12.6	周建中	18961768034	6000	300	210	零用贷	周还	20	1200	600	600	4200	4800	王倩	结清	结清		0	0
2016.12.6	刘进祥	18068377222	12000	400	300	零用贷	周还	30	1800	1200	600	9000	9600	崔日成	成乃柏车	结清		0	0
2016.12.6	吴晖	18661092919	6000	400	210	零用贷	周还	15	1400	600	900	4000	4900	张敏	结清	结清		0	0
2016.12.7	郑娟萍	13961882787	10000	500	300	零用贷	周还	20	2000	1000	1000	7000	8000	邵静	陆春松车	结清		0	0
2016.12.7	吴平	15861494360	7000	350	250	零用贷	周还	20	1600	400	700	5000	5700	张敏	成乃柏车	结清		0	0
2016.12.7	尤智杰	13861897636	8000	400	280	零用贷	周还	20	1700	800	800	5500	6300	邵静		结清		0	0
2016.12.8	乔广明	15949200408	6000	300	168	零用贷	周还	20	1200	600	0	4200	4200		结清	结清		0	0
2016.12.8	陈晓怡	15951514247	6000	300	250	零用贷	周还	20	1400	600	900	4000	4900	张敏		结清		0	0
2016.12.8	牛宝华	15852833977	10000	500	280	零用贷	周还	20	2000	1000	1000	7000	8000	杨云寒	成乃柏车	结清		0	0
2016.12.9	徐国平	13961673424	6000	300	210	零用贷	周还	20	1200	600	600	4200	4800	崔日成		结清	江阴	0	0
2016.12.9	邵凌	15852804328	10000	1000	400	零用贷	周还	10	3000	500	2000	6500	8500	邵静	陆春松车	结清		0	0
2016.12.9	庞海军	18206165722	8000	540	280	零用贷	周还	15	1800	800	1200	5400	6600	崔日成		结清		0	0
2016.12.9	陈晓春	13812582787	8000	400	320	零用贷	周还	20	2100	400	800	5500	6300	崔日成	陆春松车	结清	江阴	0	0
2016.12.9	秦伟	18921183889	8000	400	280	零用贷	周还	20	1500	800	800	5700	6500	张敏		结清		0	0
2016.12.9	朱丽芬	13665188750	15000	1500	600	零用贷	周还	10	3500	500	2250	11000	13250	张敏	成乃柏车	结清		0	0
2016.12.10	邵培斐	15895369537	8000	270	280	零用贷	周还	30	1700	800	800	5500	6300	汇东	陆春松车	结清		0	0
2016.12.10	王亚冬	13961751208	10000	1000	350	零用贷	周还	10	2500	500	1500	7000	8500	张敏		结清		0	0
2016.12.12	蒋佳妤	15852707706	10000	500	300	零用贷	周还	20	2000	500	1000	7500	8500	胡利	邵静上门	结清		0	0
2016.12.12	陈斌	13771426835	6000	400	210	零用贷	周还	15	1400	600	600	4000	4600	邵静	陆春松车	结清		0	0
2016.12.12	崔巍	18951112296	7000	350	300	零用贷	周还	20	1650	350	700	5000	5700	投资部	成乃柏	结清	宜兴	0	0
2016.12.13	王凯	13771426003	6000	300	210	零用贷	周还	20	1400	600	600	4000	4600	邵静	成乃柏车	结清		0	0
2016.12.13	符琳	13961496847	7000	700	250	零用贷	周还	10	2000	400	1050	4600	5650	张敏	成乃柏车	结清	江阴	0	0
2016.12.14	沈革群	15806198760	6000	300	200	零用贷	周还	20	1600	400	600	4000	4600	张敏	陆春松	结清		0	0
2016.12.14	朱卫江	13771198060	7000	700	250	零用贷	周还	10	1500	350	1050	5150	6200	张敏		结清		0	0
2016.12.14	钱军宇	18915332162	10000	500	350	零用贷	周还	20	2000	1000	1000	7000	8000	邵静		结清		0	0
2016.12.14	殷海洪	18351585557	10000	500	350	零用贷	周还	20	2500	500	1500	7000	8500	张敏	成乃柏车	结清		0	0
2016.12.15	许彬	13771155277	7000	350	250	零用贷	周还	20	1700	700	1050	4600	5650	张敏		结清		0	0
2016.12.15	王琳	18262263072	10000	335	350	零用贷	周还	30	1800	1000	1000	7200	8200	徐氏		结清		0	0
2016.12.16	王金	18795698919	6000	400	210	零用贷	周还	15	1400	600	600	4000	4600	张敏	成乃柏车	结清	宜兴	0	0
2016.12.17	唐剑星	13306171387	10000	500	350	零用贷	周还	20	1500	1000	1000	7500	8500	邵静		结清		0	0
2016.12.17	蔡亦斌	13584214410	8000	400	280	零用贷	周还	20	1500	800	800	5700	6500			结清		0	0
2016.12.17	孙龙姣	15852676578	8000	400	280	零用贷	周还	20	1700	800	800	5500	6300	吕晓慧		结清	宜兴	0	0
2016.12.17	邓咏君	18651031431	6000	300	210	零用贷	周还	20	1200	600	600	4200	4800	徐氏		结清		0	0
2016.12.18	邱晨晖	13771156493	20000	2000	420	零用贷	周还	10	2500	1000	1000	16500	17500	陆磊	陆春松车	结清		0	0
2016.12.19	倪召华	13921149872	8000	270	230	零用贷	周还	30	1500	800	800	5700	6500	邵静		结清		0	0
2016.12.19	倪玉立	15995251392	8000	270	230	零用贷	周还	30	1500	800	800	5700	6500	邵静		结清		0	0
2016.12.19	缪良钰	13961799149	10000	250	280	零用贷	周还	40	2500	500	1500	7000	8500	张敏	陆春松车	结清		0	0
2016.12.19	夏丽萍	18262293233	6000	300	250	零用贷	周还	20	1400	600	600	4000	4600	崔日成		结清		0	0
2016.12.19	盛晓鹏	15906193454	6000	200	170	零用贷	周还	30	900	600	300	4500	4800	崔日成		结清		0	0
2016.12.20	周俊伟	13961892998	10000	500	350	零用贷	周还	20	1500	1000	1000	7500	8500	邵静		结清		0	0
2016.12.20	周健	13771061919	15000	500	450	零用贷	周还	30	2750	1500	1500	10750	12250	胡利	陆春松车	结清		0	0
2016.12.21	田佳飞	13812078127	8000	400	280	零用贷	周还	20	1500	500	800	6000	6800	张敏		结清		0	0
2016.12.21	张国清	13861895858	6000	300	210	零用贷	周还	20	1200	300	600	4500	5100	崔日成		结清		0	0
2016.12.21	许俊伟	13914130707	8000	400	280	零用贷	周还	20	1900	800	1200	5300	6500	崔日成		结清		0	0
2016.12.22	匡敏	13915276467	6000	300	250	零用贷	周还	20	1500	600	600	3900	4500	张敏		结清		0	0
2016.12.22	唐耀	15295424141	15000	750	500	零用贷	周还	20	2800	1000	1500	11200	12700	邵静	陆春松车	结清		0	0
2016.12.23	王建荣	13861810853	10000	500	350	零用贷	周还	20	3000	500	2000	6500	8500	邵静	陆春松车	结清		0	0
2016.12.26	张瑞法	18061522078	7000	350	300	零用贷	周还	20	1500	500	700	5000	5700	崔日成		结清		0	0
2016.12.26	鲍海	13812272802	8000	400	300	零用贷	周还	20	1200	800	800	6000	6800	崔日成		结清		0	0
2016.12.27	季韦韦	13585094992	10000	500	350	零用贷	周还	20	2000	1000	1000	7000	8000		陆春松	结清		0	0
2016.12.27	都卫平	18795681850	6000	300	200	零用贷	周还	20	1500	500	600	4000	4600	张敏		结清		0	0
2016.12.27	沈峰	15358930906	6000	200	210	零用贷	周还	30	1400	300	300	4300	4600	张敏		结清		0	0
2016.12.28	徐爱峰	13921339157	8000	400	300	零用贷	周还	20	1800	400	800	5800	6600	邵静		结清		0	0
2016.12.29	蒋宇盛	15006159008	8000	270	280	零用贷	周还	30	2000	800	800	5200	6000	崔日成	陆春松车	结清	宜兴	0	0
2016.12.30	刘林	18921191990	6000	300	250	零用贷	周还	20	1200	600	600	4200	4800	崔日成		结清		0	0
2016.12.30	李峰	18800544049	10000	1000	350	零用贷	周还	10	2300	500	1000	7200	8200	邵静	陈玮伟	结清		0	0
2016.12.30	薛忠	18951576601	6000	300	250	零用贷	周还	20	1200	600	600	4200	4800	邵静		结清		0	0
2016.12.31	丁峰	13921510070	8000	400	300	零用贷	周还	20	1700	800	800	5500	6300	张敏	陆春松车	结清		0	0
2016.12.31	孟新华	13921133070	20000	1000	600	零用贷	周还	20	3500	2000	2000	14500	16500	崔日成	陆春松车	结清		0	0
2016.12.31	王星杰	15061795827	6000	200	170	零用贷	周还	30	1200	600	600	4200	4800	吕晓慧		结清		0	0
2016.12.31	秦伟	18921183889	6000	600	170	零用贷	周还	10	1200	600	600	4200	4800		陆春松车	结清		0	0
2016.12.31	刘晓兵	15949200055	30000	1000	900	零用贷	周还	30	4800	3000	4000	22200	26200	崔日成		结清		0	0
2017.1.2	魏冠华	13400021990	8000	400	280	零用贷	周还	20	1500	800	500	5700	6200	陈玮伟		结清		0	0
2017.1.2	钱敏	15365275285	6000	400	250	零用贷	周还	15	1200	600	600	4200	4800			结清	江阴	0	0
2017.1.3	钱加良	15106179423	6000	400	250	零用贷	周还	15	1400	600	900	4000	4900	崔日成		结清		0	0
2017.1.3	吴军	15345105259	6000	300	250	零用贷	周还	20	1200	600	600	4200	4800	吕晓慧		结清		0	0
2017.1.4	顾雪枫	13013619808	6000	300	250	零用贷	周还	20	1300	600	600	4100	4700	崔日成	夏飞车	结清		0	0
2017.1.4	陈昊	13861756523	10000	334	296	零用贷	周还	30	1900	1000	1000	7100	8100	张敏	陆春松车	结清		0	0
2017.1.5	吴海	15852810707	10000	500	280	零用贷	周还	20	2000	500	0	7500	7500			结清		0	0
2017.1.5	过武	13701518787	5000	250	180	零用贷	周还	20	1150	500	500	3350	3850	邵静	夏飞车	结清		0	0
2017.1.5	袁远	18351506511	7000	350	250	零用贷	周还	20	1500	700	700	4800	5500	张敏		结清		0	0
2017.1.6	沈建军	13338771156	8000	400	280	零用贷	周还	20	1500	800	800	5700	6500			结清		0	0
2017.1.6	范敏玉	13616169543	10000	334	300	零用贷	周还	30	2100	500	1200	7400	8600		陆春松车	结清		0	0
2017.1.9	刘斌	18601594398	8000	267	233	零用贷	周还	30	1500	800	800	5700	6500			结清		0	0
2017.1.9	华明秋	13921363733	20000	1000	600	零用贷	周还	20	3000	2000	0	15000	15000		陆春松车	结清	江阴	0	0
2017.1.11	顾芸	13921196878	7000	234	300	零用贷	周还	30	1400	700	700	4900	5600	邵静	夏飞	结清		0	0
2017.1.12	董晔歆	13706178785	7000	240	250	零用贷	周还	30	1900	700	1050	4400	5450	崔日成	陆春松	结清		0	0
2017.1.13	陈建伟	13861793730	7000	234	266	零用贷	周还	30	1350	650	700	5000	5700			结清		0	0
2017.1.13	秦淋	13771459703	8000	267	300	零用贷	周还	30	1600	800	800	5600	6400	张敏	陆春松车	结清		0	0
2017.1.15	孔文平	13404288970	10000	335	365	零用贷	周还	30	2000	1000	1000	7000	8000	张敏	张卿	结清		0	0
2017.1.16	吕春法	13914150569	7000	700	250	零用贷	周还	10	1600	400	700	5000	5700	吕晓慧	陆春松车	结清		0	0
2017.1.16	周建平	13961823699	8000	400	280	零用贷	周还	20	1600	800	800	5600	6400	俸小倩	夏飞车	结清		0	0
2017.1.17	鲍琳	15152220163	10000	500	350	零用贷	周还	20	1900	1000	500	7100	7600	崔日成	陆春松车	结清		0	0
2017.1.18	邵华	13921502780	6000	200	250	零用贷	周还	30	1400	600	600	4000	4600	张敏	陆春松车	结清		0	0
2017.1.18	季涛	13812061005	8000	400	280	零用贷	周还	20	1600	800	800	5600	6400	俸小倩	陆春松车	结清		0	0
2017.1.19	浦建林	13961776383	7000	350	338	零用贷	周还	20	1400	700	700	4900	5600	邵静		结清		0	0
2017.1.19	李鑫	18761512606	10000	334	300	零用贷	周还	30	2500	500	1500	7000	8500	邵静	夏飞车	结清		0	0
2017.1.20	毛卉	15961732269	15000	750	420	零用贷	周还	20	2900	1100	750	11000	11750	邵静		结清		0	0
2017.1.21	王骏	15006199734	20000	1340	660	零用贷	周还	15	3500	2000	2000	14500	16500	张敏	夏飞	结清		0	0
2017.1.24	庄登雲	13961711998	15000	750	550	零用贷	周还	20	2900	1100	1500	11000	12500	吴丹	夏飞车	结清		0	0
2017.2.4	管一蔚	13771139663	7000	350	250	零用贷	周还	20	1700	700	700	4600	5300	张敏	夏飞车	结清		0	0
2017.2.4	张柯	13861888150	15000	1500	550	零用贷	周还	10	2900	1000	1500	11100	12600	邵静	陆春松车	结清		0	0
2017.2.4	龚军	18921270588	10000	666	350	零用贷	周还	15	2000	1000	1000	7000	8000	邵静	夏飞	结清		0	0
2017.2.6	吴超天	15949202667	6000	150	250	零用贷	周还	40	1500	600	600	3900	4500	张敏	夏飞车	结清		0	0
2017.2.6	李江	13861832716	10000	500	280	零用贷	周还	20	1500	1000	500	7500	8000	邵静		结清		0	0
2017.2.7	张金桃	13961688149	8000	400	280	零用贷	周还	20	1600	800	800	5600	6400	张敏		结清		0	0
2017.2.7	李强	13921227727	7000	467	233	零用贷	周还	15	1350	450	0	5200	5200			结清		0	0
2017.2.8	刘希	13771180579	10000	500	300	零用贷	周还	20	2000	500	500	7500	8000	邵静		结清		0	0
2017.2.8	陈绪美	15852820080	20000	667	560	零用贷	周还	30	3000	1000	800	16000	16800	张敏		结清		0	0
2017.2.8	张正	13815499710	10000	500	350	零用贷	周还	20	2000	1500	1000	6500	7500	俸小倩	陆春松车	结清		0	0
2017.2.9	杨寅娜	18921118606	10000	667	353	零用贷	周还	15	1500	1000	1000	7500	8500	陈大大	夏飞车	结清		0	0
2017.2.10	唐琛华	18015391395	8000	400	268	零用贷	周还	20	1700	400	800	5900	6700	崔日成	夏飞车	结清	江阴	0	0
2017.2.11	杨斌杰	13921134538	20000	667	560	零用贷	周还	30	3500	2000	2000	14500	16500	邵静	陆春松车	结清		0	0
2017.2.13	刘东	18951578523	8000	400	168	零用贷	周还	20	1500	800	800	5700	6500	俸小倩		结清		0	0
2017.2.13	钱瑞洪	15961526726	6000	600	280	零用贷	周还	10	1400	600	600	4000	4600	崔日成		结清		0	0
2017.2.14	项沙	13400035433	4000	200	115	零用贷	周还	20	1100	400	400	2500	2900			结清		0	0
2017.2.14	袁方	18552006660	45000	900	630	零用贷	周还	50	7250	2500	4500	35250	39750	邵静	夏飞车	结清		0	0
2017.2.14	王雨晨	18961851388	10000	500	280	零用贷	周还	20	2000	500	1000	7500	8500	俸小倩	夏飞车	结清		0	0
2017.2.15	胡晓俊	18901517678	8000	400	224	零用贷	周还	20	1700	800	800	5500	6300	吕晓慧	陆春松车	结清		0	0
2017.2.15	唐纪春	18861797876	8000	400	280	零用贷	周还	20	1600	800	0	5600	5600		夏飞车	结清		0	0
2017.2.15	陈晓峰	15951500849	6000	600	280	零用贷	周还	10	1400	600	600	4000	4600	崔日成	夏飞车	结清		0	0
2017.2.16	朱悠晨	18651026358	6000	300	210	零用贷	周还	20	1200	600	600	4200	4800	崔日成		结清		0	0
2017.2.16	曹双喜	15152286464	8000	267	300	零用贷	周还	30	1700	800	800	5500	6300	崔日成	陆春松车	结清	江阴	0	0
2017.2.18	邹许峰	15261659721	10000	1000	280	零用贷	周还	10	2000	2000	1000	6000	7000	邵静	夏飞车	结清		0	0
2017.2.20	肖东	13861768903	15000	1500	550	零用贷	周还	10	2900	1500	1500	10600	12100	崔日成	夏飞	结清		0	0
2017.2.20	陶长彬	15961779901	10000	334	350	零用贷	周还	30	2000	1000	1000	7000	8000	邵静	夏飞车	结清		0	0
2017.2.20	徐以成	13961713309	6000	600	300	零用贷	周还	10	1200	600	600	4200	4800	邵静	陆春松车	结清		0	0
2017.2.21	任晓强	13601525697	15000	750	420	零用贷	周还	20	3000	1500	1500	10500	12000	崔日成	夏飞车	结清	江阴	0	0
2017.2.22	陆文勇	13515193718	6000	300	210	零用贷	周还	20	1400	600	600	4000	4600	邵静		结清	江阴	0	0
2017.2.23	潘娅红	15190228029	6000	300	250	零用贷	周还	20	1200	600	600	4200	4800	邵静		结清		0	0
2017.2.23	许彬	13771155277	10000	500	350	零用贷	周还	20	1500	1000	0	7500	7500	吴丹	夏飞车	结清		0	0
2017.2.24	王明路	13812053915	6000	300	300	零用贷	周还	20	1200	600	600	4200	4800	邵静		结清		0	0
2017.2.24	卞浩	15852509750	10000	500	350	零用贷	周还	20	2000	500	1000	7500	8500	邵静	夏飞车	结清		0	0
2017.2.25	李江	13861832716	13000	650	500	零用贷	周还	20	1900	1300	1300	9800	11100	邵静		结清		0	0
2017.2.25	杜为敏	15716199842	7000	350	250	零用贷	周还	20	1400	700	700	4900	5600	吕晓慧	夏飞车	结清		0	0
2017.2.27	陈胜	15950117515	7000	350	300	零用贷	周还	20	1500	0	700	5000	5700	吕晓慧	夏飞车	结清	江阴	0	0
2017.2.27	孙桂锁	15251670045	6000	600	210	零用贷	周还	10	1200	0	600	4800	5400	崔日成		结清		0	0
2017.2.28	蒋晓飞	13912362708	10000	334	350	零用贷	周还	30	2500	0	1000	7500	8500	邵静	陆春松车	结清		0	0
2017.2.28	吴佳燕	13511640183	6000	300	250	零用贷	周还	20	1800	0	600	4200	4800	崔日成	邵静	结清		0	0
2017.3.3	邵凌	15852804328	7000	117	70	零用贷	周还	60	2000	500	700	4500	5200	邵静		结清		0	0
2017.3.3	顾嘉洋	13616190461	10000	334	280	零用贷	周还	30	2300	0	0	7700	7700	吴丹		结清		0	0
2017.3.3	徐建成	13405751050	6000	300	168	零用贷	周还	20	1500	0	600	4500	5100			结清		0	0
2017.3.4	张潼敏	18552121088	6000	300	250	零用贷	周还	20	2000	0	600	4000	4600			结清		0	0
2017.3.4	季园	15852700821	7000	350	250	零用贷	周还	20	1800	0	700	5200	5900	崔日成	夏飞车	结清		0	0
2017.3.5	黄君	13812182409	6000	200	300	零用贷	周还	30	2000	0	0	4000	4000			结清		0	0
2017.3.6	周文斌	18762682167	8000	400	224	零用贷	周还	20	1800	0	800	6200	7000	崔日成		结清		0	0
2017.3.6	李海巍	15875378769	10000	500	320	零用贷	周还	20	2500	0	1000	7500	8500	吕晓慧		结清	江阴	0	0
2017.3.7	黄斌	13771204006	6000	200	210	零用贷	周还	30	2000	0	600	4000	4600	吕晓慧	夏飞车	结清	江阴	0	0
2017.3.7	张鑫	15951502526	6000	600	210	零用贷	周还	10	1800	0	600	4200	4800	崔日成	夏飞车	结清		0	0
2017.3.7	黄海琴	13921296154	6000	300	240	零用贷	周还	20	2000	0	600	4000	4600	邵静	夏飞车	结清		0	0
2017.3.8	谢寿清	13961781663	10000	1000	400	零用贷	周还	10	2500	0	1000	7500	8500	邵静		结清		0	0
2017.3.8	杨阳	18352555359	8000	267	293	零用贷	周还	30	2600	0	800	5400	6200	邵静	陆春松车	结清		0	0
2017.3.8	钱俊	13812578470	10000	500	350	零用贷	周还	20	2500	0	1000	7500	8500	吕晓慧		结清	江阴	0	0
2017.3.10	邹晟	13912396565	8000	400	280	零用贷	周还	20	2200	0	800	5800	6600	邵静		结清		0	0
2017.3.10	徐丽	13921113555	8000	267	300	零用贷	周还	30	2200	0	800	5800	6600	吕晓慧	夏飞车	结清		0	0
2017.3.11	张衡	18100657556	10000	500	300	零用贷	周还	20	2000	0	1000	8000	9000	崔日成		结清		0	0
2017.3.13	钟克喜	15906178577	6000	300	250	零用贷	周还	20	1800	0	600	4200	4800	邵静		结清		0	0
2017.3.14	陶莉	13861720707	6000	300	200	零用贷	周还	20	1800	0	0	4200	4200	吴丹		结清		0	0
2017.3.14	朱震球	18936072081	8000	400	350	零用贷	周还	20	2500	0	800	5500	6300	吕晓慧		结清		0	0
2017.3.14	许锬	13093081783	20000	2000	600	零用贷	周还	10	4000	0	1000	16000	17000	邵静		结清		0	0
2017.3.14	邱晨晖	13771156493	20000	1000	420	零用贷	周还	20	3500	0	0	16500	16500			结清		0	0
2017.3.16	王生华	13914251473	8000	400	300	零用贷	周还	20	2200	0	800	5800	6600	邵静	夏飞车	结清		0	0
2017.3.16	范海舟	13151008798	8000	400	280	零用贷	周还	20	2000	0	800	6000	6800	邵静	夏飞车	结清		0	0
2017.3.17	包德新	15061587083	8000	400	250	零用贷	周还	20	2000	0	800	6000	6800	邵静		结清	宜兴	0	0
2017.3.17	王春	15261576915	8000	400	230	零用贷	周还	20	2200	0	800	5800	6600	邵静		结清		0	0
2017.3.17	黄金荣	13812587330	10000	334	280	零用贷	周还	30	2200	0	1000	7800	8800	崔日成	夏飞车	结清	江阴	0	0
2017.3.21	吴亮	18861577909	10000	500	280	零用贷	周还	20	2000	0	1000	8000	9000	邵静		结清	宜兴	0	0
2017.3.21	李淼	13961795799	20000	2000	560	零用贷	周还	10	6000	0	3000	14000	17000	邵静		结清		0	0
2017.3.24	任红	15961835791	10000	1000	300	零用贷	周还	10	2500	0	1000	7500	8500	邵静		结清		0	0
2017.3.24	朱伟伟	15261598885	10000	1000	350	零用贷	周还	10	2500	0	1000	7500	8500	邵静		结清		0	0
2017.3.24	赵玲犀	13951510107	8000	400	280	零用贷	周还	20	2400	0	800	5600	6400	邵静		结清		0	0
2017.3.25	顾惠芳	15261530281	8000	400	300	零用贷	周还	20	2000	0	800	6000	6800	崔日成		结清		0	0
2017.3.27	陈伟强	18552128264	5000	250	175	零用贷	周还	20	1500	0	500	3500	4000	崔日成		结清		0	0
2017.3.27	牛宝华	15852833977	10000	500	300	零用贷	周还	20	2500	0	0	7500	7500		夏飞车	结清		0	0
2017.3.28	徐爱峰	13921339157	10000	500	380	零用贷	周还	20	2000	800	1000	7200	8200	邵静		结清		0	0
2017.3.29	陈晓峰	15951500849	8000	800	340	零用贷	周还	10	2000	0	800	6000	6800	崔日成		结清		0	0
2017.3.29	张巍	18052483790	5000	167	193	零用贷	周还	30	1500	0	500	3500	4000	崔日成	夏飞车	结清		0	0
2017.3.29	宋成	15951512616	6000	300	200	零用贷	周还	20	1400	0	600	4600	5200	崔日成	陆春松车	结清		0	0
2017.3.29	张迁	13665188610	10000	500	350	零用贷	周还	20	2000	0	1000	8000	9000	邵静	夏飞车	结清		0	0
2017.3.29	邹许峰	15261659721	10000	500	300	零用贷	周还	20	2500	0	1000	7500	8500	邵静		结清		0	0
2017.3.30	胡建新	13915348602	7000	350	370	零用贷	周还	20	1800	0	700	5200	5900	崔日成		结清		0	0
2017.3.30	李晓靓	13861774238	6000	600	250	零用贷	周还	10	1500	0	600	4500	5100	邵静	夏飞	结清		0	0
2017.3.31	汪先安	13812544959	10000	500	280	零用贷	周还	20	2500	0	1000	7500	8500	邵静	夏飞	结清		0	0
2017.4.1	顾雪枫	13013619808	8000	533	317	零用贷	周还	15	1900	0	0	6100	6100			结清		0	0
2017.4.1	杜峰	13915337542	6000	300	300	零用贷	周还	20	2000	0	600	4000	4600	李雪	夏飞	结清		0	0
2017.4.5	王剑	13912979000	15000	500	420	零用贷	周还	30	2500	0	1500	12000	13500		夏飞车	结清	江阴	0	0
2017.4.6	戴志成	15261652276	8000	270	250	零用贷	周还	30	2000	0	800	6000	6800	邵静		结清		0	0
2017.4.7	徐伟	13921101123	8000	400	300	零用贷	周还	20	2000	0	800	6000	6800	吕晓慧	夏飞车	结清		0	0
2017.4.7	邵栋良	13961715078	10000	1000	280	零用贷	周还	10	2000	0	1000	8000	9000	邵静	夏飞	结清		0	0
2017.4.7	缪文军	13861710945	6000	150	70	打卡	每天	40	1700	0	400	4300	4700	崔日成	夏飞车	结清		0	0
2017.4.8	朱兴	15052178338	10000	500	350	零用贷	周还	20	2500	0	1000	7500	8500	邵静		结清	江阴	0	0
2017.4.8	刘涛	18018304692	6000	600	210	零用贷	周还	10	1700	0	600	4300	4900	邵静		结清		0	0
2017.4.10	顾芸	13921196878	10000	1000	450	零用贷	周还	10	2500	0	900	7500	8400			结清		0	0
2017.4.10	过武	13701518787	10000	500	320	零用贷	周还	20	2300	0	1000	7700	8700	邵静		结清		0	0
2017.4.10	惠佳	13961744522	10000	334	352	零用贷	周还	30	2500	0	500	7500	8000	邵静		结清		0	0
2017.4.10	吴俊	13915396556	15000	1500	500	零用贷	周还	10	3000	0	1500	12000	13500	邵静		结清	宜兴	0	0
2017.4.10	刘金	15261655491	8000	400	250	零用贷	周还	20	2000	0	800	6000	6800	邵静		结清		0	0
2017.4.10	肖东	13861768903	20000	2000	700	零用贷	周还	10	5000	0	2000	15000	17000	崔日成		结清		0	0
2017.4.10	李秋佳	17766376811	10000	334	350	零用贷	周还	30	2500	0	1000	7500	8500	邵静		结清	江阴	0	0
2017.4.11	邵培斐	15895369537	8000	267	283	零用贷	周还	30	2000	0	0	6000	6000			结清		0	0
2017.4.11	沈建华	15806173777	10000	500	280	零用贷	周还	20	2000	500	0	7500	7500			结清		0	0
2017.4.11	储开成	15152224086	6000	300	250	零用贷	周还	20	1700	0	600	4300	4900			结清		0	0
2017.4.11	吴晓龙	13771443133	10000	500	350	零用贷	周还	20	2500	0	1000	7500	8500	邵静	陆春松车	结清		0	0
2017.4.13	柏文龙	13585047511	6000	200	210	零用贷	周还	30	1500	0	600	4500	5100	崔日成		结清		0	0
2017.4.13	万青青	15251613756	8000	267	280	零用贷	周还	30	2500	0	800	5500	6300	胡芸	夏飞车	结清		0	0
2017.4.14	韩敏	13921125456	8000	400	280	零用贷	周还	20	2000	0	800	6000	6800	邵静		结清		0	0
2017.4.14	王莉（薛皇宏）	15949282818	8000	400	250	零用贷	周还	20	2500	0	0	0	5500			结清		0	0
2017.4.14	伏优	13861545699	7000	350	0	打卡	每天	20	3400	0	400	3600	4000	吕晓慧	夏飞车	结清	宜兴	0	0
2017.4.15	陶国清	13812180924	6000	300	400	零用贷	周还	20	1700	0	600	4300	4900	邵静		结清		0	0
2017.4.15	李宁峰	13400022910	6000	300	200	零用贷	周还	20	1800	0	0	4200	4200			结清		0	0
2017.4.18	陈虎	18362384881	8000	400	260	零用贷	周还	20	2500	0	800	5500	6300	崔日成		结清		0	0
2017.4.18	王新洋	13914158748	20000	1000	600	零用贷	周还	20	4500	0	2000	15500	17500			结清		0	0
2017.4.18	王勇	13951510082	10000	1000	350	零用贷	周还	10	2000	1000	1000	7000	8000	邵静		结清		0	0
2017.4.19	任立楷	15951567929	8000	400	600	零用贷	周还	20	2000	0	800	6000	6800	邵静		结清	贷后结清	0	0
2017.4.20	周国南	13906187438	10000	500	350	零用贷	周还	20	2000	1000	1000	7000	8000	苏瑶		结清		0	0
2017.4.20	沈杰	13771118164	6000	200	126	零用贷	周还	30	1200	0	0	4800	4800			结清		0	0
2017.4.20	吴孝荣	13812005103	6000	300	200	零用贷	周还	20	1700	0	600	4300	4900	邵静		结清		0	0
2017.4.21	苏智斌	15161610175	10000	500	350	零用贷	周还	20	2500	0	1000	7500	8500	邵静	夏飞车	结清	江阴	0	0
2017.4.21	季韦韦	13585094992	10000	500	350	零用贷	周还	20	2500	0	0	7500	7500			结清		0	0
2017.4.22	邹和林	18915280585	6000	600	250	零用贷	周还	10	1700	0	600	4300	4900	邵静		结清		0	0
2017.4.22	沈田庆	15052287601	6000	40	40	零用贷	周还	150	1700	0	600	4300	4900	吕晓慧		结清		0	0
2017.4.23	金荣华	13961666221	10000	1000	350	零用贷	周还	10	2500	0	1000	7500	8500	邵静		结清	江阴	0	0
2017.4.23	朱乔凤	18261585409	6000	300	300	零用贷	周还	20	1500	0	600	4500	5100	季宏		结清		0	0
2017.4.24	莫家培	13921118277	30000	600	630	零用贷	周还	50	6000	0	2000	24000	26000	吕晓慧	夏飞车	还款中		38	47110
2017.4.24	堵圣杰	15961749119	6000	240	210	零用贷	周还	25	1500	0	600	4500	5100	崔日成		结清		0	0
2017.4.24	钱敏	15365275285	8000	400	280	零用贷	周还	20	1200	800	800	6000	6800	邵静		结清	江阴	0	0
2017.4.24	王飞	15161621237	10000	1000	350	零用贷	周还	10	2500	0	1000	7500	8500	崔日成		结清	江阴	0	0
2017.4.25	许俊伟	13914130707	10000	350	350	零用贷	周还	30	2000	1000	1000	7000	8000	邵静		结清		0	0
2017.4.25	陈瑶	18795626947	8000	267	283	零用贷	周还	30	2000	0	800	6000	6800	邵静		结清	宜兴	0	0
2017.4.25	江红妹	18800593771	8000	400	300	零用贷	周还	20	2000	0	800	6000	6800	邵静		结清		0	0
2017.4.26	许锬	13093081783	25000	2500	750	零用贷	周还	10	5000	0	2500	20000	22500	邵静		结清		0	0
2017.4.26	王臻	13771551521	15000	500	550	零用贷	周还	30	3750	0	0	11250	11250			结清		0	0
2017.4.27	沈君兰	18651513954	6000	300	220	零用贷	周还	20	900	600	600	4500	5100	崔日成		结清		0	0
2017.4.27	吴亚缓	18694930528	6000	300	220	零用贷	周还	20	900	600	600	4500	5100	吕晓慧	夏飞车	结清	江阴	0	0
2017.4.27	陆立群	13951578075	30000	750	630	零用贷	周还	40	4500	1500	3000	24000	27000	崔日成	夏飞	还款中		37	54160
2017.4.28	曹孟玉	13861814756	8000	400	300	零用贷	周还	20	1400	800	800	5800	6600	吕晓慧		结清		0	0
2017.4.28	李江	13861832716	10000	500	280	零用贷	周还	20	2000	0	1000	8000	9000	邵静		结清		0	0
2017.4.28	胡俊强	18921318791	8000	800	250	零用贷	周还	10	1200	800	800	6000	6800	邵静		结清	宜兴	0	0
2017.5.2	宋爱芳	15061846607	10000	1000	300	零用贷	周还	10	1000	1000	0	8000	8000	信息部	夏飞车	结清		0	0
2017.5.2	潘文祥	15852543197	6000	300	220	零用贷	周还	20	900	600	600	4500	5100	邵静		结清		0	0
2017.5.3	王洋	15261659111	6000	300	220	零用贷	周还	20	900	600	600	4500	5100	邵静		结清		0	0
2017.5.4	荣健	13395198532	10000	334	350	零用贷	周还	30	2000	1000	1000	7000	8000	邵静		结清		0	0
2017.5.5	项建英	13915275882	6000	300	200	零用贷	周还	20	900	600	600	4500	5100	邵静		结清		0	0
2017.5.8	陈伟	18601555786	10000	500	380	零用贷	周还	20	2000	1000	0	7000	7000			结清		0	0
2017.5.8	刘东	18951578523	10000	500	210	零用贷	周还	20	1300	1000	1000	7700	8700	邵静		结清		0	0
2017.5.6	唐胤	13812011969	30000	1000	840	零用贷	周还	30	4500	1500	3000	24000	27000		陆春松车	结清		0	0
2017.5.9	吴幻	15161508710	10000	500	280	零用贷	周还	20	2000	1000	1000	7000	8000	邵静	陆春松车	结清		0	0
2017.5.9	杨晓虎	18168892903	10000	1000	300	零用贷	周还	10	2000	1000	1000	7000	8000	颜臻卿		结清		0	0
2017.5.9	丁辰	13862590910	6000	240	60	打卡	每天	25	1400	600	400	4000	4400	崔日成		结清	宜兴	0	0
2017.5.9	金涛	13771193459	8000	267	280	零用贷	周还	30	1200	800	800	6000	6800	颜臻卿	夏飞车	结清		0	0
2017.5.10	王建荣	13861810853	10000	500	300	零用贷	周还	20	1500	500	0	8000	8000	邵静		结清		0	0
2017.5.10	吴定荣	13115074999	8000	534	448	零用贷	周还	15	1200	800	500	6000	6500	信息部		结清		0	0
2017.5.10	潘茹	15061808872	6000	300	200	零用贷	周还	20	900	600	600	4500	5100	颜臻卿		结清		0	0
2017.5.11	张祯	15206193536	5000	250	180	零用贷	周还	20	700	500	500	3800	4300	邵静		结清		0	0
2017.5.11	张超	13801533660	8000	267	224	零用贷	周还	30	1400	800	800	5800	6600	邵静	夏飞车	结清		0	0
2017.5.11	李淼	13961795799	20000	2000	560	零用贷	周还	10	6000	0	3000	14000	17000	邵静		结清		0	0
2017.5.12	易永生	13961965763	20000	2000	700	零用贷	周还	10	3000	2000	2000	15000	17000			结清		0	0
2017.5.13	陆文娟	15161510359	6000	300	250	零用贷	周还	20	1400	600	600	4000	4600	崔日成		结清		0	0
2017.5.15	苏正山	13382227830	10000	334	280	零用贷	周还	30	1500	500	0	8000	8000	崔日成		结清		0	0
2017.5.15	朱庆民	18651576389	7000	0	280	打卡	每天	25	1300	0	500	5000	5500	崔日成		结清		0	0
2017.5.15	周文斌	18762682167	8000	400	224	零用贷	周还	20	1800	0	800	6200	7000	崔日成		结清		0	0
2017.5.15	孙凌	13812298959	6000	60	40	打卡	每天	100	1200	600	400	4200	4600	邵静		结清		0	0
2017.5.17	张南峰	15106177681	10000	500	400	零用贷	周还	20	2000	1000	1000	7000	8000	邵静		结清		0	0
2017.5.17	胡晓俊	18901517678	10000	334	286	零用贷	周还	30	1500	1000	1000	7500	8500	吕晓慧		结清		0	0
2017.5.17	王志强	15906186021	8000	400	280	零用贷	周还	20	1200	800	800	6000	6800	邵静		结清		0	0
2017.5.18	潘宗锡	13861765976	6000	0	200	打卡	每天	30	2000	0	400	4000	4400	吕晓慧		结清		0	0
2017.5.18	赵陈	15312205937	6000	0	200	打卡	周还	30	2000	0	400	4000	4400	张卿		结清	宜兴	0	0
2017.5.18	顾建伟	13616199125	6000	300	220	零用贷	周还	20	900	600	600	4500	5100	张卿		结清		0	0
2017.5.18	陆正伟	13771092513	8000	400	230	零用贷	周还	20	1200	800	800	6000	6800	邵静	夏飞车	结清		0	0
2017.5.19	华寿锋	13634105361	7000	350	250	零用贷	周还	20	1300	700	700	5000	5700	张卿		结清		0	0
2017.5.19	殷小虎	13906185887	8000	400	300	零用贷	周还	20	1400	800	800	5800	6600	张卿		结清		0	0
2017.5.19	陈晓峰	15951500849	10000	1000	400	零用贷	周还	10	1500	1000	1000	7500	8500	崔日成		结清		0	0
2017.5.19	周幸芬	13961590350	6000	0	200	打卡	每天	35	1500	0	430	4500	4930	张卿		结清	宜兴	0	0
2017.5.19	杨倩	15852554275	6000	0	200	打卡	每天	35	2000	0	400	4000	4400	崔日成		结清		0	0
2017.5.19	刘希	13771180579	15000	750	500	零用贷	周还	20	2300	1500	1500	11200	12700	邵静		结清		0	0
2017.5.19	任吉媛	13861477218	20000	667	560	零用贷	周还	30	3000	1000	2000	16000	18000	邵静	夏飞车	结清		0	0
2017.5.20	李江	13861832716	10000	500	350	零用贷	周还	20	1500	1000	1000	7500	8500	邵静		结清		0	0
2017.5.20	朱琰鸿	18351586150	10000	500	350	零用贷	周还	20	1500	1000	1000	7500	8500	张卿		结清		0	0
2017.5.23	许龙飞	13771513211	8000	800	300	零用贷	周还	10	1200	800	800	6000	6800	崔日成		结清		0	0
2017.5.24	庄登雲	13961711998	20000	1000	560	零用贷	周还	20	3000	1000	2000	16000	18000	邵静		结清		0	0
2017.5.25	张鑫	15951502526	6000	600	210	零用贷	周还	10	1500	0	600	4500	5100	崔日成		结清		0	0
2017.5.25	胡江峰	15895367684	6000	300	220	零用贷	周还	20	900	600	600	4500	5100	崔日成		结清		0	0
2017.5.25	沈利峰	13665199991	7000	467	313	零用贷	周还	15	1100	700	700	5200	5900	张卿		结清		0	0
2017.5.25	吴建琳	13400013398	15000	750	525	零用贷	周还	20	2300	1500	1500	11200	12700	邵静	陆春松车	结清		0	0
2017.5.26	张莹	13814256377	7000	350	270	零用贷	周还	20	1300	700	700	5000	5700	崔日成		结清		0	0
2017.5.27	季顺江	18261589379	6000	300	200	零用贷	周还	20	1100	600	600	4300	4900			结清		0	0
2017.5.27	王明路	13812053915	6000	300	250	零用贷	周还	20	1100	600	600	4300	4900	邵静		结清		0	0
2017.5.27	王益新	13601486065	7000	350	250	零用贷	周还	20	1300	700	700	5000	5700	崔日成	夏飞车	结清		0	0
2017.5.27	孔晶晶	15061569805	7000	350	250	零用贷	周还	20	1300	700	700	5000	5700	邵静		结清	江阴	0	0
2017.5.28	蒋峰	13093095112	25000	834	566	零用贷	周还	30	5000	0	2500	20000	22500	邵静	陆春松车	结清		0	0
2017.5.28	李晓靓	13861774238	7000	350	300	零用贷	周还	20	1300	700	700	5000	5700	邵静		结清		0	0
2017.5.29	汪珺	18168833318	8000	400	280	零用贷	周还	20	1200	800	800	6000	6800			结清		0	0
2017.5.29	徐霞	13961892091	7000	0	300	打卡	每天	30	2000	0	500	5000	5500	邵静	陆春松车	结清		0	0
2017.5.31	沈建军	13338771156	8000	400	250	零用贷	周还	20	1200	800	800	6000	5200	徐辉		结清		0	0
2017.5.31	季涛	13812061005	8000	400	280	零用贷	周还	20	1200	800	800	6000	5200	邵静		结清		0	0
2017.5.31	徐真	13861474311	6000	300	220	零用贷	周还	20	900	600	600	4500	5100	邵静		结清		0	0
2017.6.1	谢宇	18018326868	8000	400	280	零用贷	周还	20	1400	800	800	5800	6600	吕晓慧		结清	宜兴	0	0
2017.6.1	华明秋	13921363733	10000	500	280	零用贷	周还	20	1000	0	0	9000	9000			结清	江阴	0	0
2017.6.1	邹云	15895397497	6000	300	280	零用贷	周还	20	1200	600	600	4200	4800	江韦		结清	宜兴	0	0
2017.6.2	唐晨浩	18751545382	15000	500	420	零用贷	周还	30	2000	1500	1500	11500	13000	王琳	夏飞车	结清		0	0
2017.6.3	杨建	15250818400	6000	0	280	打卡	每天	30	1200	600	420	4200	4620	江韦		结清	宜兴	0	0
2017.6.3	段汪裕	18006192810	10000	500	350	打卡	每天	20	1500	1000	1000	7500	8500	邵静		结清		0	0
2017.6.3	张勇敢	17715679930	6000	300	210	零用贷	周五	20	1400	600	600	4000	4600	江韦		结清		0	0
2017.6.3	王亚东	18352511741	7000	350	300	零用贷	周五	20	1300	700	700	5000	5700	邵静		结清		0	0
2017.6.5	汪珊珊	15152212323	10000	334	286	零用贷	周一	30	2500	0	1500	7500	9000	邵静	邵静	结清		0	0
2017.6.5	高璟	18706188815	10000	500	280	零用贷	周一	20	2500	0	1000	7500	8500	邵静		结清		0	0
2017.6.5	孙喆鸣	13961739348	10000	500	300	零用贷	周日	20	1500	1000	1000	7500	8500	邵静		结清		0	0
2017.6.6	邵培斐	15895369537	10000	334	316	零用贷	周五	30	1300	1000	0	7700	7700			结清		0	0
2017.6.6	丁雷	13506198054	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700	吕晓慧	夏飞车	结清		0	0
2017.6.7	杨巧	15371080028	5000	250	180	零用贷	周二	20	1200	500	500	3300	3800	邵静		结清		0	0
2017.6.8	陈建伟	13861793730	7000	350	240	零用贷	周三	20	1300	700	700	5000	5700	吕晓慧		结清		0	0
2017.6.8	吴俊	13915396556	15000	1500	500	零用贷	周三	10	3000	0	0	12000	12000	邵静		结清	宜兴	0	0
2017.6.9	朱丽芬	13665188750	10000	334	286	零用贷	周四	30	1500	1000	1000	7500	8500	吕晓慧	陆春松车	结清		0	0
2017.6.11	朱圣贤	18861844999	10000	500	280	零用贷	周五	20	1500	500	1000	8000	9000	邵静		结清		0	0
2017.6.12	王俊晓	13812033833	7000	350	210	零用贷	周一	20	1400	600	700	5000	5700	邵静		结清		0	0
2017.6.12	赵陈	15312205937	6000	300	0	打卡	每天	20	2000	0	400	4000	4400	张玲玲		结清	宜兴	0	0
2017.6.12	钱海峰	13921347799	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	崔日成		结清	宜兴	0	0
2017.6.13	陶莉	13861720707	6000	250	0	打卡	每天	30	1900	0	0	4100	4100			结清		0	0
2017.6.13	陆晓灵	13771597688	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	邵静		结清		0	0
2017.6.14	吴佩南	15161517220	6000	200	0	打卡	每天	40	1800	0	500	4200	4700	张玲玲		结清		0	0
2017.6.14	陈佳	18306197364	8000	267	233	零用贷	周二	30	1400	800	800	5800	6600	崔日成		结清		0	0
2017.6.14	周立新	15251638772	6000	250	0	打卡	每天	33	2000	0	400	4000	4400	江韦		结清		0	0
2017.6.15	华明秋	13921363733	10000	500	280	零用贷	周三	20	1000	0	0	9000	9000	信息部		结清	江阴	0	0
2017.6.15	蒋颖秀	13812224094	8000	267	313	零用贷	周三	30	1200	800	800	6000	6800	江韦		结清	宜兴	0	0
2017.6.15	朱炽钇	18626094087	15000	600	0	打卡	每天	30	2000	1000	1200	12000	13000	李文广	夏飞车	结清		0	0
2017.6.16	倪培康	18861508256	10000	500	350	零用贷	周四	20	1500	1000	800	7500	8500			结清		0	0
2016.6.16	赵振华	15995204929	10000	1000	280	零用贷	周四	10	1500	500	1000	8000	9000	吕晓慧	夏飞车	结清		0	0
2016.6.16	许彬	13771155277	10000	500	320	零用贷	周四	20	1500	500	0	8000	8000	吕单凤	夏飞车	结清		0	0
2016.6.16	张衡	18100657556	15000	750	520	零用贷	周四	20	2500	1500	1500	11000	12500	崔日成		结清		0	0
2016.6.16	杨卫东	13861632804	8000	350	0	打卡	每天	30	1200	800	600	6000	6600	江韦		结清		0	0
2016.6.18	刘文彬	13771002178	10000	500	300	零用贷	周五	20	1500	1000	1000	7500	8500	颜臻卿		结清		0	0
2017.6.19	徐霞	13961892091	8000	350	0	打卡	每天	30	1500	0	0	6500	6500	吕单凤		结清		0	0
2017.6.19	张丽	13771373108	6000	300	0	打卡	每天	25	1900	0	410	4100	4510	江韦		结清	宜兴	0	0
2017.6.19	邹维年	13585042728	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	张玲玲		结清		0	0
2017.6.20	周建平	13961823699	8000	400	250	零用贷	周一	20	1200	800	800	6000	6800	崔日成	夏飞车	结清		0	0
2017.6.20	顾红珍	15852574184	5000	500	260	零用贷	周一	10	1500	500	500	3000	3500	张玲玲		结清	宜兴	0	0
2017.6.20	陈晓峰	15951500849	12000	1200	500	零用贷	周一	10	1800	1200	1200	9000	10200	崔日成		结清		0	0
2017.6.21	周幸芬	13961590350	6000	200	0	打卡	每天	35	1500	0	450	4500	4950	张玲玲		结清	宜兴	0	0
2017.6.21	徐建成	13405751050	10000	500	280	零用贷	周二	20	1500	1000	1000	7500	8500	信息部		结清		0	0
2017.6.22	谢寿清	13961781663	20000	1000	700	零用贷	周四	20	4000	200	2000	14000	16000	信息部		结清		0	0
2017.6.23	陆文娟	15161510359	6000	300	250	零用贷	周五	20	1400	600	300	4000	4300	崔日成	夏飞车	结清		0	0
2017.6.23	刘银芳	15722995650	7000	300	0	打卡	每天	30	2000	0	500	5000	5500	信息部		结清	江阴	0	0
2017.6.26	刘金	18762641510	6000	250	0	打卡	每天	30	1700	0	400	4300	4700	崔日成		结清		0	0
2017.6.26	吕娜	13861831043	20000	800	0	打卡	每天	30	2000	2000	1600	16000	17600	崔日成		结清		0	0
2017.6.27	王刚毅	13616186556	10000	500	0	打卡	每天	20	2000	0	500	8000	8500	张玲玲	夏飞车	结清		0	0
2017.6.28	王斌	13921533306	6000	300	250	零用贷	周二	20	1400	600	600	4000	4600	张玲玲	夏飞车	结清		0	0
2017.6.29	凌兴	18261558828	6000	300	210	零用贷	周三	20	1400	600	600	4000	4600	崔日成		结清		0	0
2017.6.29	蒋冠华	15312483932	10000	334	316	零用贷	周三	30	1500	1000	1000	7500	8500	吕晓慧	夏飞车	结清		0	0
2017.6.29	许毅	13921514380	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	张玲玲	夏飞车	结清		0	0
2017.6.29	周圆圆	15895335514	15000	500	420	零用贷	周三	30	2300	1500	1500	11200	12700	李文广		结清		0	0
2017.6.29	王圣佳	15861544190	7000	350	250	零用贷	周三	20	1300	700	700	5000	5700	江韦		结清		0	0
2017.6.29	黄洁	15950145161	20000	667	563	零用贷	周三	30	3000	2000	2000	15000	17000	李文广		结清	江阴	0	0
2017.6.30	包德新	15061587083	8000	400	250	零用贷	周四	20	1200	800	800	6000	6800	崔日成		结清	宜兴	0	0
2017.6.30	曹云	15370233802	8000	400	250	零用贷	周四	20	1200	800	800	6000	6800	张玲玲	夏飞车	结清	江阴	0	0
2017.6.30	杨晓芙	18021190752	8000	400	300	零用贷	周四	20	1200	800	800	6000	6800	张玲玲		结清		0	0
2017.7.2	王健	15106195852	10000	500	350	零用贷	周五	20	1500	1000	1000	7500	8500	吕晓慧		结清		0	0
2017.7.2	蔡敏华	13606182738	10000	500	0	打卡	每天	20	2500	0	750	7500	8250	李文广	周诗华车	结清		0	0
2017.7.3	冯建林	18915322801	5000	300	0	打卡	每天	20	1700	0	330	3300	3630	江韦		结清		0	0
2017.7.3	赵陈	15312205937	7000	30	0	打卡	每天	30	2000	0	500	5000	5500	张玲玲		结清	宜兴	0	0
2017.7.4	吴幻	15161508710	8000	400	250	零用贷	周一	20	1000	800	0	6200	6200	吕单凤	夏飞车	结清		0	0
2017.7.4	周何强	15852547051	8000	267	233	零用贷	周二	30	1000	800	800	6200	7000	吕晓慧		还款中		28	14000
2017.7.4	杨云清	13912398710	6000	300	250	零用贷	周一	20	1400	600	600	4000	4600	张玲玲		结清		0	0
2017.7.7	王宇	18118885757	8000	800	300	零用贷	周四	10	1200	800	800	6000	6800	张玲玲	夏飞车	结清		0	0
2017.7.7	陶莉	13861720707	7000	300	0	打卡	每天	30	2000	0	0	5000	5000	信息部	吕单凤	结清		0	0
2017.7.7	李三喜	18021196579	8000	800	250	零用贷	周四	10	1200	800	800	6000	6800	李文广	夏飞车	结清		0	0
2017.7.8	柏文龙	13585047511	6000	300	250	零用贷	周五	20	1400	600	600	4000	4600	崔日成	夏飞车	结清		0	0
2017.7.9	周勰	13806157086	6000	300	210	零用贷	周五	20	1000	600	600	4400	5000	吕晓慧		结清		0	0
2017.7.10	邢连满	17081658782	6000	300	210	零用贷	周一	20	1000	600	600	4400	5000	李文广		结清		0	0
2017.7.10	徐爱峰	13921339157	10000	500	380	零用贷	周一	20	1500	500	0	8000	8000	吕单凤		结清	宜兴	0	0
2017.7.11	倪叙兴	13921170108	20000	667	633	零用贷	周一	30	3000	2000	0	15000	15000			结清		0	0
2017.7.12	陈云超	13921173170	6000	250	0	打卡	每天	30	1700	0	600	4300	4900	吕晓慧		结清		0	0
2017.7.13	朱雪波	13921232353	20000	667	453	零用贷	周三	30	4200	1000	300	14800	16100	吕单凤		结清	江阴	0	0
2017.7.13	蒋宇盛	18251572259	10000	500	400	零用贷	周三	20	2500	0	1000	7500	8500	崔日成	夏飞车	结清	宜兴	0	0
2017.7.13	张学贵	13405782798	7000	700	300	零用贷	周三	10	1300	700	950	5000	5950	李文广	周诗华车	结清		0	0
2017.7.14	陈洪伟	13584161296	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	张玲玲		结清	江阴	0	0
2017.7.14	钱乃龙	15949259710	10000	334	286	零用贷	周四	30	1500	1000	1400	7500	8900	张玲玲		结清		0	0
2017.7.14	司马贤	15806192167	10000	500	400	零用贷	周四	20	1500	1000	0	7500	7500			结清		0	0
2017.7.16	过武	13701518787	10000	500	300	零用贷	周五	20	1500	500	0	8000	8000	吕单凤		结清		0	0
2017.7.16	沈君兰	18651313954	6000	300	220	零用贷	周五	20	1200	600	0	4200	4200	吕单凤		结清		0	0
2017.7.18	吕文杰	13961836586	25000	1000	880	零用贷	周一	25	4500	2500	2900	18000	20900	张玲玲	周诗华车	结清		0	0
2017.7.18	刘银芳	15722995650	8000	500	0	打卡	每天	20	2000	0	800	6000	6800	张玲玲		结清	江阴	0	0
2017.7.18	张小彬	13915388773	6000	400	288	零用贷	周一	15	1200	600	800	4200	5000	张玲玲		结清	宜兴	0	0
2017.7.18	朱俊杰	13771113533	10000	500	350	零用贷	周一	20	1500	1000	1400	7500	8900	信息部	周诗华车	结清		0	0
2017.7.19	王宗海	18651022311	10000	500	400	零用贷	周二	20	1500	1000	1400	7500	8900	李文广	周诗华车	结清		0	0
2017.7.19	杨立新	15052218778	7000	350	280	零用贷	周二	20	1300	700	900	5000	5900	吕晓慧	成乃柏车	结清		0	0
2017.7.20	沈力	13606193007	10000	1000	400	零用贷	周三	10	1500	1000	1400	7500	8900	吕晓慧		结清		0	0
2017.7.21	华岳松	13771154411	7000	350	270	零用贷	周四	20	1300	700	900	5000	5900	吕晓慧	周诗华车	结清		0	0
2017.7.21	沈泽旺	15295421918	5000	250	250	零用贷	周四	20	1200	500	500	3300	3800	李文广		结清		0	0
2017.7.22	孙建芬	15061538360	8000	400	300	零用贷	周五	20	1200	800	800	6000	6800	信息部	周诗华车	结清		0	0
2017.7.22	王晓叶	15152239940	10000	500	0	打卡	每天	20	2500	500	500	7000	7500	吕晓慧		结清		0	0
2017.7.23	徐益波	13775132949	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	信息部	周诗华车	结清	江阴	0	0
2017.7.24	周洪妹	13771471285	10000	250	350	零用贷	周一	40	1500	1000	1400	7500	8900	吕晓慧		结清		0	0
2017.7.24	陈耀铮	13961731971	10000	1000	350	零用贷	周一	10	1500	1000	1400	7500	8900	吕晓慧	周诗华车	结清		0	0
2017.7.24	左林林	13861819056	7000	350	250	零用贷	周一	20	1300	700	900	5000	5900	张玲玲	周诗华车	结清		0	0
2017.7.25	吴竞	13812263517	20000	668	592	零用贷	周一	30	3000	2000	2400	15000	17400		陆春松车	结清		0	0
2017.7.25	王立仁	18018398956	6000	400	250	零用贷	周一	15	1400	600	800	4000	4800	吕晓慧		结清		0	0
2017.7.25	王敏敏	13921316060	10000	500	350	零用贷	周一	20	1500	1000	1400	7500	8900	李文广	周诗华车	结清	宜兴	0	0
2017.7.25	张鑫	15951502526	7000	700	250	零用贷	周一	10	1300	700	700	5000	5700	崔日成		结清		0	0
2017.7.26	陆建凤	18261592953	6000	300	0	打卡	每天	25	1800	0	600	4200	4800	吕晓慧	周诗华车	结清		0	0
2017.7.28	沈利峰	13665199991	7000	467	293	零用贷	周四	15	1300	700	700	5000	5700	张玲玲		结清		0	0
2017.7.30	陆建新	13861879917	6000	300	250	零用贷	周一	20	1200	600	800	4200	5000	李文广	周诗华车	结清		0	0
2017.7.31	邵寅伟	13806157525	10000	400	0	打卡	每天	30	2500	0	0	7500	7500	信息部		结清		0	0
2017.7.31	范婷婷	13921367725	8000	400	300	零用贷	周一	20	1200	800	1100	6000	7100	张玲玲	周诗华车	结清	江阴	0	0
2017.7.31	陶莉	13861720707	7000	300	0	打卡	每天	30	2000	0	0	5000	5000	信息部		结清		0	0
2017.7.31	杨学祥	18762814235	8000	800	300	零用贷	周一	10	1200	800	1100	6000	7100	杨忠桥	陆春松车	结清		0	0
2017.8.2	张本云	13961809319	7000	350	250	零用贷	周一	20	1000	700	900	5300	6200	张玲玲		结清		0	0
2017.8.2	姚敏晓	15261539610	8000	400	300	零用贷	周二	20	1200	800	1100	6000	7100	吕晓慧		结清		0	0
2017.8.2	张达	18626086021	6000	600	300	零用贷	周二	10	1400	600	800	4000	4800	李文广		结清	宜兴	0	0
2017.8.3	黄斌	13584125583	7000	350	300	零用贷	周三	20	1300	700	700	5000	5700	吕晓慧		结清	江阴	0	0
2017.8.3	黄艳华	18915330155	7000	350	350	零用贷	周三	20	1300	700	900	5000	5900	信息部	周诗华车	结清		0	0
2017.8.4	李方芳	15052236311	20000	1000	0	打卡	每天	21	4000	1000	1500	15000	16500	吕晓慧		结清		0	0
2017.8.4	吴俊	13915396556	15000	1500	470	零用贷	周四	10	3000	0	0	12000	12000	吕单凤		结清	宜兴	0	0
2017.8.5	曹敏	18036032629	7000	350	250	零用贷	周五	20	1300	700	700	5000	5700		周诗华车	结清		0	0
2017.8.5	潘文祥	15852543197	6000	400	250	零用贷	周五	15	1200	600	0	4200	4200	吕单凤		结清		0	0
2017.8.7	梁建兵	15052440008	10000	500	380	零用贷	周一	20	1200	1000	1000	7800	8800	信息部		结清		0	0
2017.8.7	贺泰来	15896480872	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700			结清		0	0
2017.8.7	黄仁龙	18767917293	15000	750	550	零用贷	周一	20	3000	1000	1900	11000	12900	李文广		结清	江阴	0	0
2017.8.8	许文彬	15190285970	10000	1000	350	零用贷	周一	10	1500	500	1000	8000	9000	信息部		结清		0	0
2017.8.9	孙凌	13812298959	6000	250	0	零用贷	周一	30	2000	0	0	4000	4000	吕单凤		结清		0	0
2017.8.9	王建荣	13861810853	10000	500	300	零用贷	周二	20	1500	1000	0	7500	7500	吕单凤		结清		0	0
2017.8.9	周鹏飞	18626053632	7000	350	250	零用贷	周二	20	1400	300	700	5300	6000		周诗华车	结清		0	0
2017.8.9	李欢	13812220228	20000	667	713	零用贷	周二	30	4600	400	2000	15000	17000	杨忠桥	周诗华车	结清	宜兴	0	0
2017.8.10	殷献	18915333324	6000	300	250	零用贷	周三	20	1200	600	600	4200	4800	信息部		结清		0	0
2017.8.10	李焕合	13771245191	8000	400	300	零用贷	周三	20	1200	800	1100	6000	7100	李文广	周诗华车	结清	江阴	0	0
2017.8.10	张勤	15061472427	10000	500	280	零用贷	周三	20	1500	1000	1400	7500	8900	张玲玲	周诗华车	结清		0	0
2017.8.10	王新洋	13914158748	20000	1000	600	零用贷	周三	20	2500	2000	2000	15500	17500			结清		0	0
2017.8.11	施密	13951567997	6000	350	0	打卡	每天	22	2000	0	600	4000	4600	吕晓慧		结清		0	0
2017.8.11	陶长彬	15961779901	10000	334	366	零用贷	周四	30	1500	1000	1400	7500	8900	张玲玲	周诗华车	结清		0	0
2017.8.13	李伟东	13771071580	57000	8500	0	打卡	每天	7	7000	0	2000	50000	50000	李文广		结清		0	0
2017.8.14	孔文平	13404288970	6000	300	0	打卡	每天	22	2000	0	600	4000	4600	吕单凤		结清	江阴	0	0
2017.8.14	王建荣	13861603770	7000	500	0	打卡	每天	18	2000	0	700	5000	5700	张玲玲		结清	江阴	0	0
2017.8.15	潘治江	17701571888	15000	500	550	零用贷	周一	30	2500	1500	0	11000	11000			结清		0	0
2017.8.15	翟寅	13701439659	10000	335	350	零用贷	周一	30	1500	1000	1400	7500	8900	信息部	成乃柏车	结清		0	0
2017.8.16	刘文彬	13771002178	10000	500	300	零用贷	周三	20	1200	1000	0	7800	7800	信息部		结清		0	0
2017.8.16	鲁涛	13961520082	10000	500	350	零用贷	周三	20	1500	1000	1400	7500	8900	李文广	周诗华车	结清	宜兴	0	0
2017.8.16	华锦燕	13961859995	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500		周诗华车	结清		0	0
2017.8.17	杜为敏	15366508233	7000	350	280	零用贷	周三	20	1300	700	900	5000	5900	吕晓慧	周诗华车	结清		0	0
2017.8.17	吴苗	18861539623	6000	300	210	零用贷	周三	20	1400	600	1100	4000	4800	张玲玲		结清		0	0
2017.8.17	王明路	13812053915	6000	300	250	零用贷	周三	20	1800	0	800	4200	5000	蒋义品		结清		0	0
2017.8.21	章伟文	13861819730	6000	300	0	打卡	每天	25	2000	0	600	4000	4600	杨忠桥	周诗华车	结清		0	0
2017.8.21	张柯	13861888150	20000	1000	0	打卡	每天	22	4000	0	2000	16000	18000	谢达丹	周诗华车	结清		0	0
2017.8.22	柳冠华	13806191482	8000	400	310	零用贷	周一	20	1200	800	800	6000	6800	周玉	周诗华车	结清		0	0
2017.8.22	俞洋	17701510242	6000	500	0	打卡	每天	15	2000	0	800	4000	4800	吕晓慧	成乃柏车	结清		0	0
2017.8.22	李君	15995225107	15000	1500	520	零用贷	周一	10	2500	1500	1900	11000	12900	吕晓慧	周诗华车	结清		0	0
2017.8.23	陆峰	13861718275	9000	450	270	零用贷	周二	20	2000	900	900	7000	8200	张娟		结清		0	0
2017.8.23	蔡雯雯	15061843685	7000	350	210	零用贷	周二	20	1300	700	900	5000	5900	张玲玲	周诗华车	结清		0	0
2017.8.24	张月舫	15301512053	15000	500	530	零用贷	周三	30	2500	1500	1900	11000	12900	吕晓慧		结清		0	0
2017.8.24	尹建余	13961705567	8000	400	300	零用贷	周三	20	1200	800	1100	6000	7100	李文广	周诗华车	结清		0	0
2017.8.24	钱彤	13915355327	6000	260	0	打卡	每天	30	1700	0	600	4300	4900	吕晓慧		结清		0	0
2017.8.25	蒋秋园	13961556888	10000	500	300	零用贷	周四	20	1500	1000	1400	7500	8900	李文广		结清	宜兴	0	0
2017.8.26	陈岚	18762684442	7000	234	266	零用贷	周五	30	1300	700	900	5000	5900	江韦		结清		0	0
2017.8.26	朱燕	13915268292	 8000	535	325	零用贷	周五	15	1200	800	1100	6000	7100	张玲玲	成乃柏车	结清		0	0
2017.8.28	王宇	18118885757	8000	800	300	零用贷	周一	10	2000	0	800	6000	6800			结清		0	0
2017.8.29	蒋旦	18361965601	6000	350	0	打卡	每天	20	2000	0	600	4000	4600	李文广		结清		0	0
2017.8.30	杭洪明	15961500330	8000	350	0	打卡	每天	30	2000	0	600	6000	6600	吕晓慧	周诗华车	结清	宜兴	0	0
2017.8.30	顾敏俊	18015351213	6000	250	0	打卡	每天	30	1800	0	600	4200	4800	吕晓慧		结清		0	0
2017.8.30	于芳	13921199381	6000	300	250	零用贷	周二	20	1400	600	800	4000	4800	吕晓慧		还款中		19	11050
2017.8.31	徐涌	13338768367	6000	600	280	零用贷	周三	10	1200	600	800	4200	5000	李文广	周诗华车	结清		0	0
2017.9.1	蒋鑫	13003395698	5000	230	0	打卡	每天	30	1800	0	500	3200	3700	信息部		结清 		0	0
2017.9.1	王雨晨	18961851388	6000	220	300	零用贷	周四	20	1200	600	600	4200	4800	信息部	周诗华车	结清		0	0
2017.9.1	刘欣怡	13646187064	6000	150	0	打卡	每天	60	2000	0	600	4000	4600	张玲玲	周诗华车	结清 		0	0
2017.9.3	刘东	18951578523	10000	500	250	零用贷	周五	20	1500	1000	1400	7500	8900	吕晓慧		结清		0	0
2017.9.3	邵佳	15298412276	10000	335	350	零用贷	周五	30	1500	1000	1400	7500	8900	吕晓慧	周诗华车	结清 		0	0
2017.9.4	吴钢	13861719944	10000	334	266	零用贷	周一	30	1500	1000	1400	7500	8900	吕晓慧	周诗华车	结清		0	0
2017.9.5	李启元	17766391291	8000	400	320	零用贷	周一	20	1200	800	800	6000	6800	信息部	成乃柏车	结清 		0	0
2017.9.5	方伟	13914190564	10000	500	350	零用贷	周一	20	1500	1000	1400	7500	8900	张玲玲	周诗华车	还款中	江阴	19	17950
2017.9.6	汤优峰	13961887379	10000	1000	350	零用贷	周二	10	1500	1000	1000	7500	8500	信息部	周诗华车	结清		0	0
2017.9.7	黄金荣	13812587330	10000	334	280	零用贷	周三	30	1500	1000	0	7500	7500	吕单凤		还款中	江阴	18	16350
2017.9.7	盛曦	13921209369	10000	500	350	零用贷	周三	20	1500	1000	1400	7500	8900	李文广		还款中	江阴	19	16700
2017.9.7	徐伟	13921101123	8000	400	300	零用贷	周三	20	1200	800	1100	6000	7100	吕晓慧	周诗华车	还款中		18	12600
2017.9.8	李三喜	18021196579	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	李文广		结清		0	0
2017.9.8	胡伟	13814274748	10000	500	0	打卡	每天	20	2000	0	0	8000	8000	谢达丹		结清		0	0
2017.9.9	邵晟	15006154371	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	李文广		结清	宜兴	0	0
2017.9.11	王俊晓	13812033833	8000	534	296	零用贷	周一	15	1200	800	800	6000	6800	信息部		结清		0	0
2017.9.11	周留芳	15961827111	6000	300	0	打卡	每天	25	2000	0	600	4000	4600	李文广	周诗华车	结清		0	0
2017.9.12	唐国中	15320202315	8000	400	280	零用贷	周一	20	1200	800	1100	6000	7100	张玲玲	周诗华车	结清		0	0
2017.9.13	张柯	13861888150	20000	1000	0	打卡	每天	22	4000	0	2000	16000	18000	谢达丹		结清		0	0
2017.9.13	高俊	18351571823	6000	400	250	零用贷	周二	15	1400	600	600	4000	4600	周玉		结清		0	0
2017.9.13	相建诚	13961839878	8000	800	300	零用贷	周二	10	1200	800	1100	6000	7100		周诗华车	结清		0	0
2017.9.13	邵懿晨	13771169167	7000	300	0	打卡	每天	30	1900	0	700	5100	5800	张玲玲		结清		0	0
2017.9.14	吴清	15052237695	6000	600	250	零用贷	周三	10	1400	600	800	4000	4800	何筱慧		结清		0	0
2017.9.14	石全军	13485065022	7000	350	290	零用贷	周三	20	1300	700	700	5000	5700	信息部	周诗华车	结清		0	0
2017.9.15	钱彤	13915355327	6000	260	0	打卡	每天	30	2000	0	300	4000	4300	吕晓慧		结清		0	0
2017.9.15	沈超	13861498840	12000	300	420	零用贷	周四	40	1800	1200	1200	9000	10200	李文广		结清	宜兴	0	0
2017.9.18	章伟文	13861819730	6000	300	0	打卡	每天	25	2000	0	0	4000	4000	信息部		结清		0	0
2017.9.18	蒋旦	18361965601	6000	300	0	打卡	每天	26	1700	0	300	4300	4600	李文广		结清		0	0
2017.9.18	秦文晓	13771138820	6000	600	280	零用贷	周一	10	1400	600	600	4000	4600	江韦		结清		0	0
2017.9.18	秦佳	13771511116	7000	350	340	零用贷	周一	20	1300	700	700	5000	5700	李文广		结清	江阴	0	0
2017.9.18	陆原	15370851483	10000	1000	350	零用贷	周一	10	1500	1000	1000	7500	8500	李文广		结清		0	0
2017.9.19	金伟民	13812091971	6000	600	220	零用贷	周一	10	1200	600	600	4200	4800	江韦		结清		0	0
2017.9.19	李川	15312285961	7000	700	300	零用贷	周一	10	1300	700	700	5000	5700	李文广		结清	江阴	0	0
2017.9.19	杨巧	15371080028	5000	250	200	零用贷	周一	20	1200	500	500	3300	3800	信息部	周诗华车	结清		0	0
2017.9.20	蒋冬	18961677663	6000	300	250	零用贷	周二	20	800	600	600	4200	4800	何筱慧		还款中	江阴	16	9400
2017.9.22	荣健	13395198532	8000	400	300	零用贷	周四	20	1200	800	0	6000	6000	吕单凤	周诗华车	结清		0	0
2017.9.22	张勇	13951511790	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	信息部	成乃柏车	结清		0	0
2017.9.22	潘国强	18606345912	8000	800	300	零用贷	周四	10	1400	800	800	5800	6600	吕晓慧		结清	宜兴	0	0
2017.9.22	沈勇	18652464120	6000	300	0	打卡	每天	25	2000	0	600	4000	4600	吴经理		结清		0	0
2017.9.22	沈泽旺	15295421918	6000	300	280	零用贷	周四	20	1400	600	300	4000	4300	李文广		结清		0	0
2017.9.25	华寿锋	13634105361	8000	800	300	零用贷	周一	10	1200	800	0	6000	6000	吕单凤		结清		0	0
2017.9.25	杨承宗	13861718655	8000	400	300	零用贷	周一	20	800	1200	800	6000	6800	李文广		还款中		16	11200
2017.9.26	杨学祥	18762814235	8000	800	300	零用贷	周一	10	2000	0	0	6000	6000	信息部	成乃柏车	结清		0	0
2017.9.26	潘震	18626383228	10000	400	0	打卡	每天	30	2500	0	1000	7500	8500	何筱慧	成乃柏车	结清		0	0
2017.9.26	汪玲	18362349941	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	谢达丹	周诗华车	结清	宜兴	0	0
2017.9.27	杨丽	15052183793	6000	350	0	打卡	每天	20	2000	0	600	4000	4600	吕晓慧		结清	江阴	0	0
2017.9.27	蒋晓飞	13912362708	6000	400	250	零用贷	周二	15	1400	600	600	4000	4600	周玉		结清		0	0
2017.9.27	过武	13701518787	12000	480	340	零用贷	周二	25	1800	700	0	9500	9500	吕单凤		结清		0	0
2017.9.27	华明秋	15051573217	20000	1000	600	零用贷	周二	20	3000	0	0	17000	17000			结清	江阴	0	0
2017.9.27	季韦韦	13585094992	10000	500	350	零用贷	周二	20	2500	0	0	7500	7500			结清		0	0
2017.9.28	吴平群	13961736885	6000	350	0	打卡	每天	21	2000	0	600	4000	4600	张玲玲		结清		0	0
2017.9.28	顾军	13511651594	6000	400	400	零用贷	周三	15	1200	600	600	4200	4800	吕晓慧	周诗华车	结清		0	0
2017.9.29	沈建军	13338771156	8000	400	250	零用贷	周四	20	1200	800	0	6000	6000	吕单凤	周诗华车	还款中		15	9750
2017.9.29	吴俊	13915396556	15000	1500	470	零用贷	周四	10	3000	1500	0	10500	10500	吕单凤		结清	宜兴	0	0
2017.9.29	沈利峰	13665199991	8000	340	0	打卡	每天	30	1600	0	400	6400	6800	张玲玲		结清		0	0
2017.9.29	卢航	15961666430	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	信息部	成乃柏车	结清	江阴	0	0
2017.9.29	唐光明	13914244484	8000	800	300	零用贷	周四	10	1200	800	800	6000	6800	张玲玲	周诗华车	结清		0	0
2017.9.29	孙龙姣	15161667852	6000	400	280	零用贷	周四	15	1200	600	600	4200	4800	吕晓慧		结清	宜兴	0	0
2017.9.29	杨铖	13814282703	10000	1000	350	零用贷	周四	10	2000	0	1000	8000	9000	谢达丹		结清		0	0
2017.9.29	吴莹	13951583587	8000	450	0	打卡	每天	20	2000	0	800	6000	6800	吕晓慧		结清		0	0
2017.10.6	沈建华	15806173777	10000	500	280	零用贷	周四	20	1500	1000	0	7500	7500	信息部		还款中	上海	14	10980
2017.10.6	王旭晨	13013619744	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	李文广		结清	无锡	0	0
2017.10.7	周文瀛	18762466080	6000	250	0	打卡	每天	30	2000	0	600	4000	4600	李文广		结清	无锡	0	0
2017.10.7	吴萍	15061566031	6000	300	255	零用贷	周五	20	1400	600	600	4000	4600	吕晓慧		还款中	江阴	14	8315
2017.10.7	李三喜	18021196579	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	李文广		结清	无锡	0	0
2017.10.8	徐建成	13425751050	10000	500	280	零用贷	周五	20	1000	1000	1000	8000	9000			结清	灌云	0	0
2017.10.9	陈浩东	15312247855	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	李文广		结清	盐城	0	0
2017.10.10	唐焕春	18795625697	6000	600	300	零用贷	周一	10	1400	600	600	4000	4600	吕晓慧		结清	宜兴	0	0
2017.10.10	范楼燕	18262274385	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李文广		结清	无锡	0	0
2017.10.10	张书南	15251676747	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	吕晓慧	刘强车	结清	兴化	0	0
2017.10.11	任吉媛	13861477218	15000	500	420	零用贷	周二	30	2500	800	1500	11700	13200	信息部		还款中	无锡	14	12880
2017.10.11	徐锦烩	13584195376	6000	300	260	零用贷	周二	20	1400	600	600	4000	4600	邹斌	周诗华车	结清	无锡	0	0
2017.10.11	邵懿晨	13771169167	7000	500	0	打卡	每天	18	2000	0	0	5000	5000	信息部		结清	无锡	0	0
2017.10.12	朱寅	18168863606	10000	389	436	零用贷	周三	20	1250	1000	1000	7750	8750	信息部		结清	无锡	0	0
2017.10.12	周国祥	18861506415	10000	500	320	零用贷	周三	20	1500	1000	1000	7500	8500	李文广	刘强车	还款中	建湖	14	11480
2017.10.12	陈耀铮	13961731971	6000	600	220	零用贷	周三	10	1400	600	600	4000	4600	吕晓慧		结清	无锡	0	0
2017.10.13	廉卢峰	15261570825	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	李文广	周诗华车	结清	无锡	0	0
2017.10.13	尤铖炜	13348109862	7000	350	250	零用贷	周四	20	1300	700	700	5000	5700	徐宽		还款中	无锡	13	8100
2017.10.13	蒋旦	18361965601	6000	300	0	打卡	每天	25	1800	0	300	4200	4500	信息部		结清	无锡	0	0
2017.10.13	杨丽	15052183793	6000	400	300	零用贷	周四	15	1300	600	600	4100	4700	吕晓慧		结清	江阴	0	0
2017.10.13	肖莹	18552423247	6000	600	300	零用贷	周四	10	1200	600	600	4200	4800	李文广		结清	无锡	0	0
2017.10.16	刘欣怡	13646187064	8000	400	0	打卡	每天	30	2000	0	800	6000	6800	李文广		结清	无锡	0	0
2017.10.17	陈芳平	15961622607	6000	300	0	打卡	每天	25	2000	0	600	4000	4600	徐宽		结清	江阴	0	0
2017.10.17	唐飚	15261584766	6000	300	300	零用贷	周一	20	1400	600	600	4000	4600	李文广		还款中	无锡	13	7800
2017.10.17	王剑	13912979000	15000	500	420	零用贷	周一	30	3000	0	1500	12000	13500	信息部		还款中	江阴	13	8400
2017.10.17	华丹燕	18362366267	6000	600	300	零用贷	周一	10	1400	600	600	4000	4600	徐宽		结清	无锡	0	0
2017.10.18	贺泰来	15896480872	8000	400	300	零用贷	周二	20	1200	800	0	6000	6000	信息部		结清	无锡	0	0
2017.10.18	曾娅妮	15995209869	8000	220	0	打卡	每天	60	2000	0	800	6000	6800	吕晓慧		结清	四川	0	0
2017.10.18	李吉玉	18262296367	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	徐宽		结清	句容	0	0
2017.10.19	周凯雷	18551055551	8000	534	296	零用贷	周三	15	1200	800	800	6000	6800	彭加双	周诗华车	还款中	启东	12	12430
2017.10.19	宋春磊	15961618017	6000	600	220	零用贷	周三	10	1200	600	600	4200	4800	李小小		结清	江阴	0	0
2017.10.19	朱宏良	13861700421	8000	400	300	零用贷	周三	20	1200	800	800	6000	6800	徐宽		结清	无锡	0	0
2017.10.20	陈嘉逸	15895302903	7000	350	250	零用贷	周四	20	1800	700	700	4500	5200	徐宽	刘强车	还款中	无锡	12	8000
2017.10.20	黄星	13812666246	8000	400	300	零用贷	周四	20	1200	800	800	6000	6800	李小小		还款中	无锡	12	8700
2017.10.21	孙茜	13812043402	10000	550	0	打卡	每天	20	2000	500	1000	7500	8500	吕晓慧	刘强车	结清	无锡	0	0
2017.10.21	朱玉兰	13961844805	7000	200	0	打卡	每天	50	2000	0	700	5000	5700	彭加双		结清	无锡	0	0
2017.10.23	郭丽萍	13921229096	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	吕晓慧	周诗华车	结清	江阴	0	0
2017.10.23	朱红清	13584113718	6000	300	0	打卡	每天	25	2000	0	700	4000	4700	徐宽		结清	江阴	0	0
2017.10.23	张奎	18352555377	10000	400	0	打卡	每天	30	1500	0	1000	7500	8500	李文广	周诗华车	结清	四川	0	0
2017.10.23	许雪	13814240427	10000	500	350	零用贷	周一	20	1500	1000	1100	7500	8600	徐宽		结清	无锡	0	0
2017.10.24	王峰	13912366447	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧	周诗华车	还款中	无锡	12	11200
2017.10.25	张柯	13861888150	10000	500	0	打卡	每天	22	2000	0	1100	8000	9100	徐宽		结清	无锡	0	0
2017.10.25	钱伟	13806179571	6000	600	350	零用贷	周二	10	1400	600	700	4000	4700	李文广		结清	无锡	0	0
2017.10.25	沈君兰	18651513954	6000	300	220	零用贷	周二	20	1400	600	700	4000	4700	李文广		还款中	无锡	12	6240
2017.10.25	杭洪明	15961500330	8000	350	0	打卡	每天	30	2000	0	900	6000	6900	徐宽		结清	宜兴	0	0
2017.10.25	张丽	13861603127	6000	600	280	零用贷	周二	10	1400	600	700	4000	4700	徐宽		结清	江阴（四川）	0	0
2017.10.26	徐秋	18915288365	6000	600	320	零用贷	周三	10	1400	600	600	4000	4600	徐宽		结清	无锡	0	0
2017.10.27	汤建良	13801523623	8000	800	300	零用贷	周四	10	1200	800	800	6000	6800	张玲玲		结清	江阴	0	0
2017.10.27	曹云	15370233802	8000	400	240	零用贷	周四	20	2000	0	0	6000	6000	信息部		还款中	江阴	11	7040
2017.10.27	卞蒙琪	15161619038	8000	400	280	零用贷	周四	20	1200	800	800	6000	6800	张娟	周诗华车	结清	江阴	0	0
2017.10.28	陈洪亮	13771023336	7000	467	283	零用贷	周五	15	1300	700	700	5000	5700	吕晓慧		还款中	无锡	10	7500
2017.10.30	张俊超	15161668490	10000	400	350	零用贷	周五	25	1500	1000	1100	7500	8600	徐宽	周诗华车	还款中	宜兴	11	8250
2017.10.31	杨炫宗	13915228028	10000	500	400	零用贷	周一	20	1500	1000	1100	7500	8600	吕晓慧	周诗华车	还款中	江阴	11	9900
2017.10.31	陈耀铮	13961731971	10000	500	350	零用贷	周一	20	1500	1000	1100	7500	8600	吕晓慧		还款中	无锡	12	18700
2017.11.1	顾晓莉	15995310689	8000	800	300	零用贷	周二	10	1200	800	800	6000	6800	李文广		结清	江阴	0	0
2017.11.1	蒋峰	13093095112	25000	834	566	零用贷	周二	30	5000	0	2500	20000	22500	李文广		还款中	无锡	11	15400
2017.11.2	张敏强	15251689762	8000	450	0	打卡	每天	20	2000	0	0	6000	6000	信息部		结清	无锡	0	0
2017.11.3	钟银营	15995344277	6000	300	0	打卡	每天	25	2000	0	600	4000	4600	吕晓慧	刘强车	结清	江阴	0	0
2017.11.4	朱玲	13771072871	10000	750	0	打卡	每天	20	0	0	1000	10000	11000	信息部		结清	无锡	0	0
2017.11.4	刘文彬	13771002178	10000	500	300	零用贷	周五	20	1500	1000	1000	7500	8500	徐宽		还款中	无锡	10	8000
2017.11.4	张江	15190314566	7000	700	300	零用贷	周五	10	1300	700	700	5000	5700	徐宽		结清	无锡	0	0
2017.11.4	葛高生	18018333398	10000	1000	500	零用贷	周五	10	1500	1000	1000	7500	8500	李文广		结清	无锡	0	0
2017.11.5	赵云龙	13814200651	10000	500	350	零用贷	周五	20	1500	1000	1000	7500	8500	徐宽	周诗华车	还款中	无锡	10	8000
2017.11.6	孙敏伟	13771119763	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李文广	周诗华车	还款中	无锡	9	7650
2017.11.6	刘丹	13338772066	7000	350	300	零用贷	周一	20	1300	700	700	5000	5700	彭加双	周诗华车	还款中	贵州	10	6500
2017.11.6	史卫明	13921261600	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	徐宽		还款中	江阴	10	8500
2017.11.8	周圆圆	15895335514	10000	770	380	零用贷	周二	13	1500	1000	1000	7500	8500	李文广		结清	无锡	0	0
2017.11.8	蒋旦	18361965601	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	彭加双		结清	无锡	0	0
2017.11.9	杨学祥	18762814235	10000	300	0	打卡	每天	40	2200	0	0	7800	7800	信息部		结清	射阳	0	0
2017.11.10	高逸涛	15961532983	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	徐宽	刘强车	结清	江阴	0	0
2017.11.13	董晔歆	13706178785	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	信息部	周诗华车	结清	无锡	0	0
2017.11.13	张金峰	13861619252	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	徐宽	周诗华车	还款中	江阴	9	7650
2017.11.15	吕峰	18915334640	10000	1000	350	零用贷	周二	10	1500	1000	1000	7500	8500	吕晓慧		结清	无锡	0	0
2017.11.16	徐晓晨	13013678733	10000	667	353	零用贷	周三	15	1500	1000	1000	7500	8500	徐宽		结清	无锡	0	0
2017.11.17	包国忠	13861480225	6000	600	300	零用贷	周四	10	1400	600	600	4000	4600	吕晓慧		还款中	江阴	8	7200
2017.11.20	周棵	15298400317	6000	600	280	零用贷	周六	10	1400	600	600	4000	4600	徐宽	周诗华车	结清	重庆	0	0
2017.11.20	郁胜杰	13806195123	15000	1500	550	零用贷	周一	10	2500	1000	1500	11000	12500	徐宽	周诗华车	结清	无锡	0	0
2017.11.20	周何强	15852547051	8000	400	550	零用贷	周一	20	2000	0	100	6000	6100	吕晓慧		还款中	无锡	8	7600
2017.11.21	李川	15312285961	8000	534	316	零用贷	周一	15	1200	800	800	6000	6800	李文广	周诗华车	结清	江阴	0	0
2017.11.21	沈奕伯	13861875378	15000	500	500	零用贷	周一	30	2250	1500	1500	11250	12750	徐宽		还款中	无锡	8	8000
2017.11.22	金忠德	15061775940	7000	350	300	零用贷	周二	20	1300	700	700	5000	5700	徐宽	周诗华车	结清	江阴	0	0
2017.11.22	夏亮	13861858387	10000	400	0	打卡	每天	30	2500	0	750	7500	8250	李文广		结清	无锡	0	0
2017.11.22	孙金伟	13921392527	6000	300	280	零用贷	周二	20	1400	600	600	4000	4600	吕晓慧	周诗华车	还款中	无锡	8	4640
2017.11.23	朱玲	13771072871	15000	750	530	零用贷	周三	20	2250	1500	1500	11250	12750	谢达丹		还款中	无锡	7	8960
2017.11.24	魏传坤	13306153272	8000	667	333	零用贷	周四	12	1200	800	800	6000	6800	吕晓慧	周诗华车	结清	兴化	0	0
2017.11.24	孙龙姣	15161667852	8000	800	300	零用贷	周四	10	1200	800	800	6000	6800	吕晓慧		还款中	宜兴	7	7700
2017.11.24	汪镐	13914152747	8000	400	300	零用贷	周四	20	1200	800	800	6000	6800	彭加双		结清	无锡	0	0
2017.11.25	俞仁辉	15906199926	10000	400	310	零用贷	周五	25	1500	1000	1000	7500	8500	吕晓慧	周诗华车	还款中	建湖	7	4970
2017.11.25	霍金龙	15961740933	8000	400	300	零用贷	周五	20	1200	800	800	6000	6800	吕晓慧	周诗华车	还款中	滨海	7	4900
2017.11.25	陆彦成	18861506105	15000	1500	450	零用贷	周五	10	2250	1500	1500	11250	12750	信息部（邹）		还款中	无锡	7	14250
2017.11.25	晁婷婷	15052118612	6000	300	0	打卡	每天	20	2000	0	400	4000	4400	钱晓		结清	无锡	0	0
2017.11.26	沈剑	13771097131	10000	1000	350	零用贷	周五	10	1500	1000	1000	7500	8500	李文广		结清	无锡	0	0
2017.11.27	黄晚秋	18168388975	6000	300	210	零用贷	周一	20	1400	600	600	4000	4600	徐宽		结清	四川	0	0
2017.11.27	秦文晓	13771138820	6000	600	280	零用贷	周一	10	1400	600	600	4000	4600	信息部		还款中	无锡	7	6160
2017.11.28	吴云峰	13585073362	10000	667	353	零用贷	周一	15	1500	1000	1000	7500	8500	徐宽		结清	无锡	0	0
2017.11.29	顾燕林	15152214015	10000	550	0	打卡	每天	20	2500	0	750	7500	8250	钱晓		结清	无锡	0	0
2017.12.1	崔玲艳	13771351630	10000	1000	400	零用贷	周四	10	1500	1000	1000	7500	8500	徐宽		结清	宜兴	0	0
2017.12.1	蓝韦龙	15312203240	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	李文广		还款中	无锡	6	5100
2017.12.1	王喜杰	15861410690	7000	467	283	零用贷	周四	15	1300	700	700	5000	5700	信息部		还款中	无锡	6	4500
2017.12.2	蒋旦	18361965601	6000	300	0	打卡	每天	25	1800	0	300	4200	4500	彭加双		结清	无锡	0	0
2017.12.2	过沂	13915333525	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	吕晓慧		结清	无锡	0	0
2017.12.2	严红兰	13812229198	10000	1000	350	零用贷	周五	10	1500	1000	1000	7500	8500	吕晓慧		还款中	宜兴	6	8700
2017.12.2	沈杰	15852711126	8000	800	280	零用贷	周五	10	1200	800	800	6000	6800	吕晓慧		还款中	无锡	6	6480
2017.12.4	钱亚新	15050682917	6000	600	300	零用贷	周一	10	1400	600	600	4000	4600	吕晓慧		还款中	无锡	6	5400
2017.12.4	仲宇龙	18661086168	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	杨洋	周诗华车	还款中	江阴	6	5100
2017.12.5	姚敏晓	15261539610	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧	周诗华车	还款中	无锡	6	5900
2017.12.5	黄龙	18921252930	10000	1000	350	零用贷	周一	10	1500	1000	1000	7500	8500	周玉	刘强车	还款中	江阴	6	8100
2017.12.6	赵晓蓉	15922523165	8000	800	300	零用贷	周二	10	1200	800	800	6000	6800	徐宽	周诗华车	还款中	四川	6	6900
2017.12.6	徐军	13771036125	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	徐宽		还款中	无锡	6	5100
2017.12.7	钟彪	15906159710	6000	600	300	零用贷	周三	10	1400	600	600	4000	4600	李文广		结清	宜兴	0	0
2017.12.7	华淇	13861718621	7000	350	300	零用贷	周三	20	1300	700	700	5000	5700	徐宽	周诗华车	还款中	无锡	5	3250
2017.12.8	过武	13701518787	7000	350	280	零用贷	周四	20	1500	0	0	5500	5500	信息部		还款中	无锡	5	3150
2017.12.8	沈建军	15052261038	10000	500	400	零用贷	周四	20	1500	1000	1000	7500	8500	李文广		还款中	东台	5	4500
2017.12.8	李万清	13771441818	10000	1000	300	零用贷	周四	10	1500	1000	1000	7500	8500	信息部		还款中	无锡	5	6500
2017.12.8	董晔歆	13706178785	6000	300	0	打卡	每天	25	2000	0	0	4000	4000	信息部		结清	无锡	0	0
2017.12.9	苏耀兴	13812587619	20000	1000	700	零用贷	周五	20	4000	2000	2000	14000	16000	周玉	周诗华车	还款中	江阴	5	8500
2017.12.9	王静强	13771220258	10000	500	350	零用贷	周五	20	1500	1000	1000	7500	8500			还款中	江阴	5	4250
2017.12.9	储伟	13771321827	10000	500	280	零用贷	周五	20	1500	1000	0	7500	7500			还款中	宜兴	5	3900
2017.12.9	沈鑫	13962099568	7000	700	300	零用贷	周五	10	1300	700	700	5000	5700	彭加双	刘强车	还款中	沈鑫	5	5000
2017.12.9	王廷举	13584185748	6000	260	0	打卡	每天	30	1800	0	420	4200	4620	徐宽	周诗华车	结清	兴化	0	0
2017.12.10	胡张玲	13584115100	7000	350	250	零用贷	周五	20	1300	700	700	5000	5700	吕晓慧		结清	江阴	0	0
2017.12.10	宋浩	13771137052	8000	534	316	零用贷	周五	15	1200	800	800	6000	6800	吕晓慧	周诗华车	还款中	无锡	4	3700
2017.12.11	施艳萍	18915262839	4000	200	0	打卡	每天	30	1500	0	250	2500	2750	吕晓慧		结清	无锡	0	0
2017.12.11	堵本锋	15061749753	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700	李文广	刘强车	结清	江阴	0	0
2017.12.11	郑长启	13485039788	6000	600	300	零用贷	周一	10	1400	600	600	4000	4600	吕晓慧		还款中	安徽	5	5000
2017.12.11	陆坤	18260039773	10000	500	300	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧	周诗华车	还款中	无锡	5	4000
2017.12.11	邵培斐	15895369537	10000	334	350	零用贷	周一	30	1500	1000	0	7500	7500	信息部	刘强车	还款中	无锡	5	3420
2017.12.11	方锦	15861414956	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	徐宽		结清	无锡	0	0
2017.12.11	吴侃如	13584208575	6000	300	0	打卡	每天	30	2000	0	400	4000	4400	吕晓慧	刘强车	结清	宜兴	0	0
2017.12.12	孙茜	1381203402	20000	1000	600	零用贷	周一	20	4000	0	2000	16000	18000			还款中	无锡	5	8000
2017.12.14	张健	13861641114	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	徐宽		还款中	江阴	4	2080
2017.12.14	王海	13921361881	10000	500	300	零用贷	周三	20	1500	1000	1000	7500	8500		周诗华车	还款中	江阴	5	4000
2017.12.15	马金男	15961837582	30000	1500	900	零用贷	周四	20	4500	3000	3000	22500	25500	吕晓慧	周诗华车	还款中	无锡	4	9600
2017.12.15	刘东	18951578523	10000	500	300	零用贷	周四	20	1500	1000	1000	7500	8500	李文广		还款中	无锡	4	3200
2017.12.15	华寿锋	15852808802	6000	600	250	零用贷	周四	10	1200	600	600	4200	4800	信息部		还款中	无锡	4	3400
2017.12.15	徐文雅	13511658322	14000	1400	500	零用贷	周四	10	2600	1400	1400	10000	11400	周玉	刘强车	还款中	无锡	4	7600
2017.12.16	张建华	13814281047	10000	500	380	零用贷	周五	20	1500	1000	1000	7500	8500	吕晓慧		还款中	无锡	4	3760
2017.12.18	胡俊强 	18921318791	11000	440	310	零用贷	周一	25	1900	1100	1100	8000	9100	徐宽		还款中	宜兴	4	3000
2017.12.18	赵洪昌	13771283538	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李文广		还款中	江阴	4	3400
2017.12.18	陈建强	13813676104	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧		还款中	常州	4	3400
2017.12.18	黄晨	13382222250	10000	667	353	零用贷	周一	15	1500	1000	1000	7500	8500	李文广		还款中	无锡	4	4080
2017.12.19	顾燕林	15152214015	10000	550	0	打卡	每天	20	2500	0	0	7500	7500	信息		结清	无锡	0	0
2017.12.20	夏亮	13861858387	8000	400	0	打卡	每天	25	2000	0	0	6000	6000	信息部		结清	无锡	0	0
2017.12.21	丁鹏程	15852742322	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	李文广	周诗华车	还款中	无锡	3	2550
2017.12.22	杨晨	18021878219	8000	400	300	零用贷	周四	20	1200	800	800	6000	6800	李文广	周诗华车	还款中	东台	3	2100
2017.12.22	段一星	13093002066	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	李文广		还款中	无锡	3	2550
2017.12.22	华明秋	15251573217	20000	1000	600	零用贷	周四	20	4000	0	0	16000	16000	信息部		还款中	江阴	3	4800
2017.12.23	朱俊杰	13771113533	10000	500	350	零用贷	周五	20	1500	500	1000	8000	9000	信息部		还款中	无锡	3	2550
2017.12.25	曹芝云	18906196320	10000	1000	350	零用贷	周一	10	1500	1000	1000	7500	8500	谢达丹		还款中	无锡	3	4050
2017.12.25	李光武	15961739742	10000	334	351	零用贷	周一	30	1500	1000	1000	7500	8500	彭加双	周诗华车	还款中	无锡	3	2055
2017.12.25	顾燕芳	13806193034	8000	800	400	零用贷	周一	10	1200	800	800	6000	6800	徐宽	周诗华车	结清	无锡	0	0
2017.12.25	张金锋	13861619252	20000	1000	700	零用贷	周一	20	3000	2000	2000	15000	17000	徐宽		还款中	无锡	3	5100
2017.12.26	过梅如	18914122251	8000	450	0	打卡	每天	20	2000	0	600	6000	6600	吕晓慧		结清	无锡	0	0
2017.12.27	盖琳	13506154240	7000	350	350	零用贷	周二	20	1300	700	700	5000	5700	吕晓慧		还款中	宜兴	3	2400
2017.12.29	董斐	13912351685	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	徐宽		还款中	无锡	17	5100
2017.12.29	方锦	18961737126	10000	500	330	零用贷	周四	20	1500	1000	0	7500	7500	信息部		结清	无锡	0	0
2016.11.9	杨斌杰	13921134538	30000	0	200	红包贷	每天	30	6000	0	3600	24000	27600	邵静	成乃柏车	结清		0	0
2016.11.11	顾建中	15861586133	15000	0	150	红包贷	每天	30	2950	1500	1800	10550	12350		成乃柏车	结清		0	0
2016.11.11	张忠伟	13861816748	20000	0	150	红包贷	每天	30	4000	0	2000	16000	18000	崔日成	陆春松车	结清		0	0
2016.11.12	薛君	15716183001	10000	0	100	红包贷	每天	30	2000	500	1200	7500	8700	邵静	成乃柏车	结清		0	0
2016.11.18	陈英红	18751558717	10000	0	100	红包贷	每天	30	2000	1000	1000	7000	8000			结清		0	0
2016.11.19	李江	13861832716	6000	0	80	红包贷	每天	30	1180	300	720	4520	5240	邵静		结清		0	0
2016.11.22	钟明强	13812257390	5000	0	70	红包贷	每天	30	1050	250	250	3700	3950	朱莎莎		结清		0	0
2016.11.30	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	500	750	7500	8250	吕晓慧	成乃柏车	结清		0	0
2016.11.30	东明	13906190577	10000	0	120	红包贷	每天	30	2000	500	750	7500	8250	崔日成	陆春松	结清		0	0
2016.12.9	杨斌杰	13921134538	30000	0	200	红包贷	每天	30	3000	0	1600	27000	28600	邵静		结清		0	0
2016.12.10	顾建中	15861586133	15000	0	150	红包贷	每天	30	3000	0	0	12000	12000			结清		0	0
2016.12.21	丁思远	15050682680	6000	0	60	红包贷	每天	30	1260	600	450	4140	4590	邵静		结清		0	0
2016.12.23	陈英红	18751558717	10000	0	100	红包贷	每天	30	3000	0	0	7000	7000			结清		0	0
2016.12.29	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	300	8000	8300	吕晓慧		结清		0	0
2017.1.6	尤亮	13771494525	10000	0	120	红包贷	每天	30	2520	0	1000	7480	8480		陆春松车	结清		0	0
2017.1.10	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	500	8000	8500	吕晓慧	陆春松车	结清		0	0
2017.2.9	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	500	8000	8500	吕晓慧		结清		0	0
2017.2.20	张旭泓	13861894933	10000	0	120	红包贷	每天	30	1800	500	500	7700	8200	吕晓慧		结清		0	0
2017.3.1	祝培勇	13806184998	10000	0	100	红包贷	每天	30	2000	0	800	8000	8800	俸小倩		结清		0	0
2017.3.11	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.3.12	袁惠宇	15961843769	6000	0	100	红包贷	每天	30	2000	0	400	4000	4400	吕晓慧	陆春松车	结清		0	0
2017.3.21	张旭泓	13861894933	10000	0	120	红包贷	每天	30	10000	0	0	0	8000			结清		0	0
2017.3.25	徐秋稔	13771470921	6000	0	80	红包贷	每天	30	1800	0	412	4200	4612	崔日成		结清		0	0
2017.3.29	薛皇红	18362356013	8000	0	80	红包贷	每天	30	2500	0	550	5500	6050			结清		0	0
2017.3.31	祝培勇	13806184998	10000	0	100	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.4.11	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.4.19	田寿玉	13400030746	6000	0	80	红包贷	每天	30	1700	0	430	4300	4730	苏瑶		结清		0	0
2017.4.21	吕娜	13861831043	30000	0	300	红包贷	每天	30	1000	3000	0	26000	26000	崔日成		结清		0	0
2017.4.21	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.5.10	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.5.17	冯霄峰	13812055385	10000	0	100	红包贷	每天	30	2500	0	740	7400	8140	崔日成	夏飞车	结清		0	0
2017.5.18	恽志恒	18626366766	6000	0	60	红包贷	每天	30	2000	0	400	4000	4400	邵静		结清		0	0
2017.5.22	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.5.23	邓正卫	13861803876	8000	0	90	红包贷	每天	30	1200	800	600	6000	6600	崔日成		结清		0	0
2017.5.22	吕娜	13861831043	30000	0	300	红包贷	每天	60	4000	0	0	26000	26000	崔日成		结清		0	0
2017.6.12	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.6.19	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.6.29	冯霄峰	13812055385	10000	0	100	红包贷	每天	30	2000	0	0	8000	8000		夏飞车	结清		0	0
2017.7.11	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.7.12	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.7.20	吕娜	13861831043	30000	0	300	红包贷	每天	30	4000	0	0	26000	26000			结清		0	0
2017.7.20	邓正卫	13861803876	8000	0	90	红包贷	每天	30	2000	0	0	6000	6000			结清		0	0
2017.8.9	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.8.10	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.8.27	邓正卫	13861803876	8000	0	70	红包贷	每天	30	2000	0	0	6000	6000			结清		0	0
2017.9.6	吴志刚	13961733171	10000	0	100	红包贷	每天	30	2000	0	500	8000	8500			结清		0	0
2017.9.6	陈敏	13400032155	6000	0	80	红包贷	每天	30	2000	0	600	4000	4600	吕晓慧		结清		0	0
2017.9.9	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.9.10	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.9.16	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.10.5	陈敏	13400032155	6000	0	80	红包贷	每天	30	2000	0	200	4000	4200			结清		0	0
2017.10.5	吴志刚	13961733171	10000	0	100	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.10.8	张旭泓	13861894933	8000	10000	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.10.9	张旭泓	13861894933	8000	10000	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.10.15	张旭泓	13861894933	8000	10000	120	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2017.11.4	吴志刚	13961733171	10000	0	100	红包贷	每天	30	2000	0	0	8000	8000			结清		0	0
2016.11.7	潘旭东	13961802979	50000	0	4000	空放	240	1	4000	0	0	46000	46000			还款中		14	60000
2016.11.20	马玲	18961881988	50000	0	4000	空放	30	1	4000	0	0	46000	46000			结清		0	0
2016.11.28	张荣山	13771096890	11000	0	1000	空放	10	1	1000	0	0	10000	10000			结清		0	0
2016.12.17	沈宝龙	13815108858	12000	0	0	空放	15	1	5000	0	500	7000	7500	吕晓慧	陆春松车	结清		0	0
2017.1.3	杨子亮	15312218182	40000	0	2000	空放	24	1	7000	0	2000	33000	35000	邵静		结清		0	0
2017.1.23	郁旦	15251523696	15000	0	6000	空放	22	1	7000	0	750	8000	8750	邵静	夏飞车	结清		0	0
2017.1.21	张荣山	13771096890	28000	0	3000	空放	13	1	3000	0	0	25000	25000			结清		0	0
2017.2.5	张巍	15261507327	20000	0	6000	空放	22	1	7000	2000	1200	11000	12200	邵静		结清		0	0
2017.2.7	蒋佳妤	15852707706	19000	0	0	空放	14	1	6000	0	0	13000	13000			结清		0	0
2017.2.15	石猛	13701512160	20000	0	5000	空放	22	1	5500	0	1000	14500	15500	吴丹	陆春松车	结清		0	0
2017.2.20	朱建东	15995268055	20000	0	2500	空放	30	1	0	2500	0	17500	17500			结清		0	0
2017.3.3	沈德前	13584183737	18000	0	3000	空放	30	1	3000	0	0	15000	15000		夏飞车	结清		0	0
2017.3.17	徐文浩	13706188648	25000	0	5000	空放	22	1	5000	0	1000	20000	21000	邵静		结清		0	0
2017.3.26	朱建东	15995268055	30000	0	3800	空放	30	1	3800	0	0	26200	26200			结清		0	0
2017.3.30	刘小春	13665191960	10000	0	1000	空放	30	1	1000	0	0	9000	9000			结清		0	0
2017.4.5	沈德前	13584183737	6500	0	1500	空放	30	1	1500	0	0	5000	5000			结清		0	0
2017.4.17	徐文浩	13706188648	25000	0	5000	空放	22	1	5000	0	1000	20000	21000	邵静		结清		0	0
2017.4.20	徐虹	13861822137	10000	0	2500	空放	22	1	2500	1000	600	6500	7100			结清		0	0
2017.5.18	杨丽娅	13616191515	40000	0	2000	空放	30	1	2000	0	0	38000	38000			结清		0	0
2017.6.7	过建九	13255110563	6000	0	2000	空放	22	1	2000	0	500	4000	4500	信息部		结清		0	0
2017.6.7	张强	13912385123	30000	0	8000	空放	22	1	8000	2000	2000	20000	22000	邵静	夏飞车	结清		0	0
2017.6.30	张强	13912385123	20000	0	15000	空放	22	1	5000	0	0	15000	15000			结清		0	0
2017.7.8	殷献	18915333324	10000	0	3000	空放	22	1	2500	500	700	7000	7700	信息部		结清		0	0
2017.7.17	周俊伟	13961892998	14000	0	4000	空放	22	1	4000	0	1000	10000	11000	信息部		结清		0	0
2017.7.19	过建九	13255110563	6000	0	2000	空放	22	1	2000	0	0	4000	4000	信息部		结清		0	0
2017.7.27	陈士猛	15206183989	10000	0	3000	空放	22	1	3000	0	700	7000	7700	吕晓慧		结清		0	0
2017.8.9	王心雷	15052220188	100000	0	5000	空放	8	1	5000	0	0	95000	95000			结清		0	0
2017.8.14	周俊伟	13961892998	14000	0	4000	空放	22	1	4000	0	1000	10000	11000	信息部		结清		0	0
2017.8.19	殷献	18915333324	10000	0	2500	空放	22	1	2500	500	700	7000	7700			结清		0	0
2017.8.21	李伟东	13771071580	30000	0	8000	空放	22	1	8000	0	2000	22000	24000	李文广		结清		0	0
2017.8.22	王心雷	15052220188	100000	0	7000	空放	10	1	7000	0	0	93000	93000			结清		0	0
2017.8.23	丁锡涛	13961715894	20000	0	5000	空放	30	1	5000	0	1300	15000	16300	黄旭		还款中		7	17750
2017.8.25	周俊伟	13961892998	14000	0	4000	空放	30	1	4000	0	0	10000	10000			结清		0	0
2017.8.28	杨明	13656153782	35000	0	11000	空放	22	1	11000	0	2500	24000	26500	李文广	周诗华车	结清		0	0
2017.9.1	王心雷	15052220188	110000	0	15000	空放	10	1	15000	0	0	95000	95000			结清		0	0
2017.9.7	单来发	13003351209	20000	0	6000	空放	22	1	6000	0	1400	14000	15400	信息部	周诗华车	结清		0	0
2017.9.13	周俊伟	13961892998	12000	0	3000	空放	30	1	3000	0	0	9000	9000	信息部		结清		0	0
2017.9.19	陈寒冰	15961871313	10000	0	2500	空放	22	1	2500	0	750	7500	8250	杨忠桥		结清		0	0
2017.10.11	吴玉娥	15861576427	8000	0	2500	空放	22	1	2500	0	550	5500	6050	吕晓慧		还款中		4	10600
2017.10.12	邹王坚	17520601512	20000	0	6000	空放	22	1	6000	0	1400	14000	15400	吕晓慧	周诗华车	逾期		0	0
2017.10.13	许伟东	15852502258	5000	0	1600	空放	22	1	2000	0	300	3000	3300	吕晓慧		还款中		4	6800
2017.10.21	周建国	13921194218	10000	0	3000	空放	22	1	3000	0	700	7000	7700	李小小	周诗华车	还款中		4	12200
2017.10.31	过建九	13255110563	6000	0	2000	空放	22	1	2000	0	0	4000	4000	信息部	信息部	还款中		3	6100
2017.11.15	张洪祥	15189254736	8000	0	2700	空放	22	1	2700	0	530	5300	5830	李文广		逾期		0	6000
2017.11.24	吴俊	13915396556	15000	0	4000	空放	22	1	4000	0	0	11000	11000			还款中		2	8500
2017.11.28	王共军	14751569795	15000	0	5000	空放	22	1	5000	0	1000	10000	11000	吕晓慧	周诗华车	还款中		2	10000
2017.11.30	周俊伟	13961892998	14000	0	4000	空放	25	1	4000	0	0	10000	10000	信息部		还款中		0	0
2017.12.3	王林	15161564198	10000	0	3000	空放	22	1	3000	0	700	7000	7700	徐宽	周诗华车	结清		0	0
2017.12.8	周钢	15951512334	15000	0	3750	空放	25	1	3750	0	800	11250	12050	信息部	刘强车	还款中		1	3750
2017.12.9	蒋军伟	13771416500	20000	0	6000	空放	22	1	6000	0	1400	14000	15400	徐宽	周诗华车	还款中		3	9000
2017.12.16	赵晓静	13861888430	20000	0	6500	空放	22	1	6500	0	650	13500	14150	徐宽	周诗华车	还款中		1	6500
2017.12.27	许营飞	13961856131	50000	0	7000	空放	30	1	7000	0	2500	43000	45500	信息部		还款中		0	0
2018.1.5	王雪	15861504781	10000	0	3000	空放	22	1	3000	0	700	7000	7700	吕晓慧	周诗华车	还款中		0	0
2016.11.12	龚新志	13812287183	22000	667	210	车贷	周五	30	3200	2000	1000	16800	17800	陈玮伟		结清		0	0
2016.11.25	奚学东	18961756188	50000	1000	450	车贷	周四	50	5700	2500	2000	41800	43800	崔日成	陆春松车	结清		0	0
2016.11.30	朱雪波	13921232353	20000	670	450	车贷	周二	30	4800	1000	2000	14200	16200	张敏	成乃柏车	结清		0	0
2016.12.5	唐玲娜	13861817029	20000	500	420	车贷	周一	40	5500	1000	3000	13500	16500	张敏	成乃柏	结清		0	0
2017.1.10	钱彬彬	13861471397	23000	1000	700	车贷	周一	30	5400	2000	2300	15600	17900	张敏	夏飞车	结清		0	0
2017.1.18	张志坚	13665127552	25000	830	520	车贷	周二	30	3300	2500	0	19200	19200			结清		0	0
2017.3.24	杨子亮	15312218182	35000	1750	700	车贷	周四	20	5050	3500	1750	26450	28200	邵静	夏飞车	结清		0	0
2017.8.7	刘刚	15061564880	20000	833	197	车贷	周一	24	4500	1000	2400	14500	16900	李文广	周诗华车	结清	江阴	0	0
2017.9.12	曹正天	18706186985	20000	800	700	车贷	周一	25	5000	2000	4500	13000	17500	信息部	成乃柏车	结清		0	0
2017.11.9	孙林霞	13961713310	30000	1500	1050	车贷	周三	20	5000	3000	2850	22000	24850	徐宽	周诗华车	还款中	无锡	10	26000
2017.11.30	许辉	18762684839	15000	1000	400	车贷	周三	15	4000	1500	1850	9500	11350	吕晓慧		结清		0	0
2017.12.11	耿清	13771231539	30000	1500	1100	车贷	周一	20	4500	3000	3000	22500	25500	徐宽	周诗华车	还款中		5	13300
2017.12.25	薛红	13912382192	50000	2500	1800	车贷	周一	20	10000	5000	6150	35000	41150	信息部	刘强车	还款中		3	12900
2017.12.25	陈贤超	13771162512	20000	1000	600	车贷	周一	20	3000	2000	3000	15000	18000	信息部	刘强车	结清		0	0
2016.11.3	强立雨	18888045224	10000	500	210	零用贷	周还	20	2000	1000	1200	7000	8200	邵静	成乃柏车	逾期		8	5680
2016.11.8	单冬梅	13013632717	10000	334	280	零用贷	周还	30	2000	1000	1200	7000	8200	陶超	成乃柏车	逾期		10	6140
2016.11.10	王朝晖	15052274501	8000	534	224	零用贷	周还	15	1800	800	960	5400	6360	王倩	成乃柏	逾期		2	1600
2016.11.10	潘美珍	18852475857	8000	400	224	零用贷	周还	20	1800	800	960	5400	6360	邵静	成乃柏车	逾期		6	3744
2016.11.13	李敏	13338115597	10000	334	280	零用贷	周还	30	2000	1000	1200	7000	8200	崔日成	陆春松车	逾期		10	6140
2016.11.16	王强	13906170574	10000	500	210	零用贷	周还	20	2000	500	1200	7500	8700	崔日成	成乃柏车	逾期		4	3140
2016.11.18	朱磊	15961757417	8000	534	224	零用贷	周还	15	1500	800	960	5700	6660	崔日成		逾期		7	5306
2016.11.19	李涛	13023367029	10000	667	350	零用贷	周还	15	2300	1000	1200	6700	7900	邵静	陆春松车	逾期	宜兴	5	3750
2016.11.19	刘其霞	15061502182	8000	400	224	零用贷	周还	20	1200	800	960	6000	6960			逾期		3	1872
2016.11.23	傅炎嘉	15852777710	8000	400	168	零用贷	周还	20	1200	800	400	6000	6400	陈玮伟		逾期		9	5112
2016.11.23	徐惠峰	13382880116	6000	300	168	零用贷	周还	20	1200	600	300	4200	4500			逾期		7	3276
2016.12.1	胡寒	15995370213	8000	400	300	零用贷	周还	20	2500	800	1200	4700	5900	邵静	成乃柏车	逾期	宜兴	5	3500
2016.12.7	苏震	15961703999	10000	500	350	零用贷	周还	20	3000	500	2000	6500	8500	黄旭		逾期		6	5100
2016.12.9	周鹏飞	13961777172	10000	334	366	零用贷	周还	30	2500	500	1500	7000	8500	张敏	成乃柏车	逾期		4	2800
2016.12.9	曹建波	13585075369	6000	300	210	零用贷	周还	20	1400	600	600	4000	4600	信息部	成乃柏车	逾期		6	3060
2016.12.14	孙彪	18018358103	8000	400	350	零用贷	周还	20	1800	800	1200	5400	6600	邵静		逾期		3	2250
2016.12.15	周龙	13585053876	7000	350	250	零用贷	周还	20	1700	700	1050	4600	5650	邵静		逾期		2	2800
2016.12.15	薛雄	15861600046	12000	400	400	零用贷	周还	30	2300	700	1200	9000	10200	张敏	陆春松车	逾期	江阴	1	800
2016.12.21	赵煜辉	15301515870	6000	300	168	零用贷	周还	20	1200	300	600	4500	5100	吕晓慧		逾期		8	3744
2016.12.21	陈洪良	15862451648	8000	200	80	打卡	每天	40	400	800	800	5520	6320	邵静		逾期		2	560
2016.12.23	陈霞	15995229472	10000	500	350	零用贷	周还	20	1800	1000	1000	7200	8200	吴丹		逾期		0	0
2016.12.26	李钮根	18115336552	6000	300	400	零用贷	周还	15	1200	600	600	4200	4800	崔日成		逾期		3	2100
2016.12.26	李涛	13915393392	8000	400	350	零用贷	周还	20	1900	400	800	5700	6500	邵静		逾期		5	3750
2016.12.26	王钢	13771614474	10000	334	350	零用贷	周还	30	2300	500	1000	7200	8200	邵静		逾期		1	684
2016.12.27	顾丽莉	18051576288	8000	400	300	零用贷	周还	20	2100	800	1500	5100	6600	崔日成	陆春松	逾期		6	4200
2016.12.29	张伟	13912481761	6000	300	300	零用贷	周还	20	1300	300	600	4400	5000	吕晓慧		逾期		8	5100
2016.12.30	蒋高安	18626366498	10000	1000	350	零用贷	周还	10	2500	500	1000	7000	8000	张敏	陆春松车	逾期	宜兴	2	2700
2017.1.3	欧黎峰	13861823537	8000	400	300	零用贷	周还	20	1600	800	800	5600	6400	张敏	夏飞车	逾期		1	700
2017.1.6	杨庆	18795690590	8000	400	300	零用贷	周还	20	1200	800	800	6000	6800	崔日成		逾期	宜兴	4	2800
2017.1.9	曹荣	18801519152	10000	500	350	零用贷	周还	20	2000	500	1000	7500	8500			逾期		1	850
2017.1.13	俞辉	13861884799	7000	200	250	零用贷	周还	35	1300	700	700	5000	5700		陆春松车	逾期		10	6100
2017.1.17	黎家鸣	15716181556	6000	200	250	零用贷	周还	30	1300	600	600	4100	4700	俸小倩	陆春松车	逾期		4	1800
2017.2.9	管阳	13912374436	6000	200	250	零用贷	周还	30	1400	600	600	4000	4600	朱新约	陆春松车	逾期		1	500
2017.2.11	陈强	13656189631	8000	400	280	零用贷	周还	20	1700	800	800	5500	6300	陈大大		逾期		7	4760
2017.2.14	封磊	15902140791	8000	200	224	零用贷	周还	40	1700	800	800	5500	6300	陈大大	陆春松车	逾期		4	4696
2017.2.16	刘朋飞	15861498003	10000	500	300	零用贷	周还	20	2000	500	1000	7500	8500	邵静	夏飞车	逾期		6	4800
2017.2.21	修竹	13914135500	6000	200	200	零用贷	周还	30	1200	600	600	4200	4800	邵静		逾期		5	4500
2017.2.28	杨晓良	18206186541	10000	250	210	零用贷	周还	40	2000	0	1000	8000	9000	邵静	夏飞车	逾期		2	920
2017.3.6	郁旦	15251523696	10000	500	330	零用贷	周还	20	2500	0	1000	7500	8500	邵静		逾期		2	1660
2017.3.8	马广富	13616170726	10000	1000	280	零用贷	周还	10	2000	0	1000	8000	9000	邵静		逾期		3	3840
2017.3.8	沈建新	18015102816	8000	800	280	零用贷	周还	10	1800	0	800	6200	7000	崔日成		逾期		1	1080
2017.3.9	宋翩	15251695041	6000	200	200	零用贷	周还	30	1800	0	600	4200	4800	吕晓慧		逾期		4	1600
2017.3.9	张建南	13196521235	8000	267	283	零用贷	周还	30	2300	0	800	5700	6500	崔日成	陆春松车	逾期	江阴	27	15440
2017.3.12	高红岗	17315507645	10000	500	300	零用贷	周还	20	2000	1000	1000	7000	8000	邵静		逾期		6	4800
2017.3.13	袁建平	18205030299	7000	350	300	零用贷	周还	20	2000	0	700	5000	5700	邵静		逾期		8	5200
2017.3.13	陈波	15298401771	10000	500	350	零用贷	周还	20	2000	0	1000	8000	9000	吴丹	夏飞车	逾期		2	1700
2017.3.15	朱念坤	13771059770	7000	350	250	零用贷	周还	20	2000	0	700	5000	5700	邵静	夏飞车	逾期		15	9200
2017.3.15	黄建海	13915209474	15000	750	470	零用贷	周还	20	4000	0	1500	11000	12500	邵静		逾期	江阴	6	7320
2017.3.16	张君	13771350876	10000	334	246	零用贷	周还	30	2000	0	500	8000	8500			逾期	宜兴	7	14060
2017.3.17	陈静	15312298388	10000	334	316	零用贷	周还	30	2000	0	1000	8000	9000	吕晓慧		逾期	宜兴	8	5200
2017.3.21	王莉	15949282818	10000	500	300	零用贷	周还	20	2000	1000	1000	7000	8000	邵静	成乃柏车	逾期	江阴	11	9100
2017.3.21	王奇	15061590086	6000	400	250	零用贷	周还	15	1800	0	600	4200	4800	邵静		逾期	宜兴	4	2600
2017.3.23	胡东方	18351589993	8000	400	280	零用贷	周还	20	2000	0	800	6000	6800	崔日成	夏飞	逾期		16	10880
2017.3.24	陆丽静	13771056560	6000	200	210	零用贷	周还	30	1800	0	600	4200	4800	邵静	夏飞车	逾期		3	1730
2017.3.25	黎应龙	15052108094	20000	1000	600	零用贷	周还	20	4500	0	2000	15500	17500	崔日成	成乃柏车	逾期		1	1600
2017.3.30	胡幼军	13961566795	8000	400	280	零用贷	周还	20	2000	0	800	6000	6800	吕晓慧		逾期	宜兴	2	1360
2017.3.31	李洋	15651515695	10000	1000	280	零用贷	周还	10	2200	0	1000	7800	8800	邵静		逾期		1	1280
2017.4.6	万文宁	15251669822	8000	800	320	零用贷	周还	10	2200	0	800	5800	6600	邵静	夏飞车	逾期		2	2240
2017.4.7	钱彩凤	15851633107	10000	334	366	零用贷	周还	30	2500	0	1000	7500	8500		陆春松车	逾期		3	2100
2017.4.7	周银凤	13961679738	14000	467	500	零用贷	周还	30	4000	0	1400	10000	11400	邵静		逾期	江阴	7	6769
2017.4.11	吴喜喜	13915721379	10000	500	350	零用贷	周还	20	2500	0	1000	7500	8500	邵静	夏飞车	逾期	江阴	2	1700
2017.4.11	吴平	15861494360	8000	400	280	零用贷	周还	20	2000	0	0	6000	6000			逾期		13	8940
2017.4.14	王平	13701512326	6000	300	210	零用贷	周还	20	1700	0	600	4300	4900	崔日成		逾期		6	3060
2017.4.15	钱霞萍	13621522583	6000	300	200	零用贷	周还	20	1500	0	0	4500	4500			逾期	江阴	3	1500
2017.4.17	蔡亦斌	13584214410	10000	500	350	零用贷	周还	20	2200	0	1000	7800	8800	邵静		逾期	宜兴	9	8350
2017.4.17	陈建丰	13812532591	8000	267	283	零用贷	周还	30	2000	0	800	6000	6800	邵静		逾期		15	8250
2017.4.19	毛卉	15961732269	20000	1000	560	零用贷	周还	20	4000	1500	2000	14500	16500			逾期		9	14040
2017.4.19	吴寅	13921506908	7000	350	245	零用贷	周还	20	1700	0	700	5300	6000	吕晓慧	夏飞车	逾期		6	4075
2017.4.20	吴海	15251439039	10000	500	300	零用贷	周还	20	2000	1000	0	7000	7000			逾期		7	5600
2017.4.20	汤卫燕	18151553981	8000	400	230	零用贷	周还	20	2200	0	800	5800	6600	邵静		逾期		6	3780
2017.4.22	陈赟	13812259601	7000	700	250	零用贷	周还	10	2000	0	700	5000	5700	崔日成		逾期	宜兴	3	2850
2017.4.24	屈君飞	13584126912	6000	300	210	零用贷	周还	20	1500	0	600	4500	5100	季宏		逾期	江阴	10	5100
2017.4.24	杨振宇	15961568243	6000	300	300	零用贷	周还	20	1500	0	600	4500	5100	季宏	夏飞车	逾期	宜兴	6	3700
2017.4.25	张正	13815499710	25000	1250	800	零用贷	周还	20	5000	0	2500	20000	22500	邵静		逾期		4	8200
2017.4.25	张家东	15951515889	8000	400	280	零用贷	周还	20	1500	500	800	6000	6800	邵静		逾期		6	4080
2017.4.27	李志龙	13382277345	8000	400	300	零用贷	周还	20	1400	800	800	5800	6600	邵静		逾期	江阴	4	4800
2017.4.29	马晓东	15961802891	6000	600	300	零用贷	周还	10	1200	300	600	4500	5100	邵静		逾期		1	900
2017.4.29	杨琴	18248811349	5000	500	200	零用贷	周还	10	1300	0	500	3700	4200	崔日成		逾期	宜兴	3	2800
2017.5.2	李强	13584166787	24000	0	1000	打卡	每天	26	6000	0	0	18000	18000			逾期		5	5000
2017.5.3	钱斌	18068263483	8000	400	280	零用贷	周还	20	1200	800	800	6000	6800	邵静		逾期	江阴	15	10200
2017.5.3	杨雯琦	13400006504	8000	55	45	打卡	每天	140	1500	800	800	5700	6500	崔日成		逾期		4	400
2017.5.4	李银春	18861863470	6000	200	80	打卡	每天	30	1800	0	600	4200	4800	吕晓慧		逾期		15	4200
2017.5.6	伏优	13861545699	10000	142	78	打卡	每天	70	3000	0	1000	7000	8000	吕晓慧		逾期		5	6700
2017.5.8	胡书斌	18552093587	7000	350	245	零用贷	周还	20	1300	700	700	5000	5700	邵静		逾期		10	5950
2017.5.8	史国兵	18800538103	7000	350	245	零用贷	周还	20	1100	700	700	5200	5900	崔日成		逾期	宜兴	0	0
2017.5.10	阙建庆	13706184200	6000	400	300	零用贷	周还	15	900	600	600	4500	5100	邵静	夏飞车	逾期		5	3500
2017.5.11	水振	13405777778	6000	300	250	零用贷	周还	20	900	600	600	4500	5100	邵静		逾期		2	1100
2017.5.14	黄意章	13771152686	10000	1000	350	零用贷	周还	10	2000	1000	1000	7000	8000			逾期		14	7000
2017.5.17	吴亚琴	18651001069	6000	0	200	打卡	每天	30	2000	0	400	4000	4400	吕晓慧		逾期	宜兴	8	1600
2017.5.17	范建平	13812038098	6000	300	250	零用贷	周还	20	1500	500	600	4000	4600	吕晓慧		逾期		1	550
2017.5.17	王志强	13961752124	6000	0	200	打卡	每天	40	900	600	450	4500	4950	崔日成		逾期		23	4600
2017.5.18	徐洪芬	13961844902	7000	700	300	零用贷	周还	10	1300	700	700	5000	5700	崔日成		逾期		3	3000
2017.5.18	相海	13861721874	10000	500	350	零用贷	周还	20	1500	1000	1000	7500	8500			逾期		5	4250
2017.5.18	汤凌	18861570091	6000	300	210	零用贷	周还	20	900	600	600	4500	5100	张卿		逾期	宜兴	4	2740
2017.5.19	华啸	15861638013	6000	300	220	零用贷	周还	20	900	600	600	4500	5100	张卿	夏飞车	逾期	江阴	3	1560
2017.5.20	黄健	13921289931	8000	400	250	零用贷	周还	20	1200	800	800	6000	6800	崔日成		逾期		6	4200
2017.5.23	王丹萍	15161683870	8000	400	300	零用贷	周还	20	1200	800	800	6000	6800	邵静	夏飞车	逾期	宜兴	17	11900
2017.5.23	周金华	13861778249	10000	1000	350	零用贷	周还	10	2000	1000	1000	7000	8000	颜臻卿		逾期		4	5400
2017.5.23	朱东兵	18362337274	6000	0	300	零用贷	周还	30	3200	0	800	2800	3600	崔日成		逾期	宜兴	4	1200
2017.5.25	丁丽梅	13921151851	10000	500	300	零用贷	周还	20	1500	500	1000	8000	9000	邵静	夏飞车	逾期		12	9600
2017.5.26	张仁伯	18762691258	6000	300	210	零用贷	周还	20	1100	600	600	4300	4900	邵静		逾期		18	9680
2017.5.26	杨耀红	13701518710	6000	0	200	打卡	每天	40	2200	0	400	3800	4200	颜臻卿	夏飞车	逾期		6	1500
2017.5.27	张国南	15950136622	7000	350	250	打卡	每天	20	1300	700	700	5000	5700	张卿		逾期	江阴	4	2400
2017.5.28	杨浩飞	18861631845	8000	400	230	零用贷	周还	20	1400	800	800	5800	6600	邵静		逾期	江阴	1	630
2017.5.31	叶遥	18706157806	6000	200	250	零用贷	周还	30	1200	600	600	4200	4800	江韦		逾期	宜兴	12	6000
2017.6.2	邵寅伟	13806157525	10000	334	346	零用贷	周还	30	1500	1000	1000	7500	8500	颜臻卿	夏飞车	逾期	宜兴	15	11380
2017.6.3	张鑫	13961806144	10000	0	400	打卡	每天	30	2000	500	750	7500	8250	吕晓慧		逾期		9	3600
2017.6.5	顾嘉洋	13616190461	10000	334	280	零用贷	周一	30	1300	1000	1000	7700	8700		夏飞	逾期		19	11666
2017.6.5	沈卫忠	18952476515	6000	300	250	零用贷	周一	20	1200	600	600	4200	4800	张玲玲		逾期		7	4950
2017.6.6	杭春牛	13584199589	14000	1400	500	零用贷	周一	10	2600	1400	1400	10000	11400	信息部	夏飞车	逾期		7	13500
2017.6.6	张海栋	13585001316	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700	邵静		逾期		7	4200
2017.6.6	曹建国	13328110252	10000	500	300	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧	陆春松车	逾期		8	6430
2017.6.7	刘鹏雄	13291230025	8000	400	300	零用贷	周二	20	1400	600	800	6000	6800	江韦	夏飞车	逾期	江阴	5	4100
2017.6.7	顾烨	17768338569	10000	334	286	零用贷	周二	30	1500	1000	1000	7500	8500	吕晓慧		逾期		15	9300
2017.6.8	李淼	13961795799	40000	2000	1250	零用贷	周一	20	12000	0	6000	28000	34000	邵静		逾期		2	6500
2017.6.9	徐玲	18118118755	14000	466	434	零用贷	周四	30	2600	1400	1400	10000	11400	吕晓慧		逾期	江阴	21	18900
2017.6.12	卢铭君	13665195095	6000	300	220	零用贷	周一	20	1400	600	600	4000	4600	吕晓慧	夏飞车	逾期		4	2080
2017.6.12	蒋爱芳	13376230188	7000	467	283	零用贷	周一	15	1300	700	700	5000	5700	崔日成		逾期	宜兴	7	5550
2017.6.12	张志强	15152225859	6000	300	330	零用贷	周一	20	1200	600	600	4200	4800	吕晓慧		逾期		3	1890
2017.6.13	蒋国栋	18051567666	6000	300	240	零用贷	周一	20	1800	0	600	4200	4800	信息部		逾期		6	3240
2017.6.14	缪晓朋	15950426302	6000	250	0	打卡	每天	30	1100	600	500	4300	4800	江韦		逾期	江阴	22	5500
2017.6.14	苏贞媛	13771276283	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	邵静		逾期	江阴	7	6250
2017.6.15	龚洪明	13801522853	10000	500	280	零用贷	周三	20	1500	500	1000	8000	9000	信息部	夏飞车	逾期	江阴	11	9080
2017.6.15	俞敏杰	13771163366	10000	500	300	零用贷	周三	20	1000	1000	1000	8000	9000	张玲玲	成乃柏车	逾期		13	10400
2017.6.15	陶善超	13915340666	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	崔日成		逾期		1	850
2016.6.19	许锬	13093081783	20000	2000	700	零用贷	周一	10	2000	2000	2000	16000	18000	吕晓慧		逾期		1	2700
2017.6.19	刘艳杰	15949250055	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700	张玲玲	夏飞车	逾期		6	3600
2017.6.20	王军	13912453533	6000	300	250	零用贷	周一	20	1200	600	600	4200	4800	吕晓慧		逾期	江阴	9	4950
2017.6.20	周敏慧	15852530452	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李文广	成乃柏车	逾期		6	6050
2017.6.21	宋爱芳	13485045800	20000	800	0	打卡	每天	30	2000	0	0	18000	18000			逾期		2	1600
2017.6.21	潘晓东	15950141705	6000	300	250	零用贷	周二	20	1200	600	600	4200	4800	张玲玲		逾期	江阴	0	0
2017.6.22	蔡明东	13861736736	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	李文广		逾期		2	1700
2017.6.22	陈星伟	15995244931	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	张玲玲	夏飞车	逾期		2	1700
2017.6.22	田金华	18106249939	6000	250	0	打卡	每天	30	1800	0	420	4200	4620	张玲玲		逾期	江阴	22	5500
2017.6.22	荣成贤	18168897357	6000	600	210	零用贷	周三	10	1200	600	600	4200	4800	张玲玲	夏飞车	逾期		2	1620
2017.6.22	高麒深	15261686699	10000	667	353	零用贷	周三	15	1500	1000	1000	7500	8500	张玲玲		逾期	宜兴	6	6120
2017.6.23	王周琴	13961581605	8000	800	280	零用贷	周四	10	1200	800	800	6000	6800	吕晓慧		逾期	宜兴	1	1080
2017.6.23	陈永兴	18360806871	10000	500	280	零用贷	周五	20	1500	1000	1000	7500	8500	张玲玲	夏飞车	逾期	江阴	16	14920
2017.6.24	肖东	13861768903	15000	1500	550	零用贷	周五	10	2500	1500	1500	11000	12500	崔日成		逾期		1	2350
2017.6.25	朱震球	18936072081	15000	750	550	零用贷	周五	20	4000	0	1500	11000	12500			逾期		5	6500
2017.6.25	吴亚新	13815136812	6000	300	250	零用贷	周五	20	1300	600	600	4100	4700			逾期	江阴	13	7150
2017.6.27	黄云云	18552059383	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	江韦		逾期	江阴	8	6200
2017.6.28	王生华	13914251473	8000	400	300	零用贷	周二	20	1200	800	800	6000	6800	李文广		逾期		10	7000
2017.6.28	陶良超	18261518245	6000	350	0	打卡	每天	20	1700	0	430	4300	4730			逾期	江阴	8	2800
2017.6.30	李桂忠	13921208878	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	张玲玲	成乃柏车	逾期	江阴	1	850
2017.6.30	秦红鸣	18921245787	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	张玲玲	周诗华车	逾期	江阴	12	11200
2017.7.1	李兴冬	15961762219	8000	400	300	零用贷	周五	20	1200	800	800	6000	6800	李文广		逾期		13	9400
2017.7.2	计锡君	15061794927	10000	500	300	零用贷	周一	20	1500	1000	1000	7500	8500	张玲玲	周诗华车	逾期		2	1600
2017.7.4	倪同群	15861577698	6000	250	0	打卡	每天	30	1750	0	600	4250	4850	李文广		逾期		5	1250
2017.7.4	吴凯	15861652628	10000	500	350	零用贷	周一	20	1500	500	1000	8000	9000		成乃柏车	逾期	江阴	6	5100
2017.7.4	费鹏	15251566296	8000	400	280	零用贷	周一	20	1200	800	800	6000	6800	张玲玲	夏飞车	逾期	江阴	5	3400
2017.7.6	陆铖	18051902907	5000	334	206	零用贷	周三	15	2000	0	500	3000	3500	张玲玲	夏飞车	逾期	江阴	1	540
2017.7.7	张艳	18012351561	8000	400	300	零用贷	周四	20	1200	800	800	6000	6800	李文广	夏飞车	逾期		9	6300
2017.7.12	顾浩峰	15190332829	6000	350	0	打卡	每天	20	1700	0	600	4300	4900	崔日成		逾期	江阴	2	700
2017.7.12	顾秀英	15950123293	8000	450	0	打卡	每天	20	2000	0	800	6000	6800	张玲玲		逾期	江阴	18	8100
2017.7.12	苏飞龙	15251565544	10000	1000	350	零用贷	周二	10	1500	1000	1000+400	7500	8900	张玲玲		逾期	江阴	3	4550
2017.7.13	陈建新	13771600067	6000	300	280	零用贷	周三	20	1200	600	600+200	4200	5000	崔日成		逾期	江阴	10	5800
2017.7.14	张玉花	15861510837	6000	300	250	零用贷	周四	20	1400	600	600+200	4000	4800	李文广		逾期	宜兴	8	4400
2017.7.14	王晓春	13616180022	10000	500	280	零用贷	周四	20	1500	500	1000+400	8000	9400	张玲玲	周诗华车	逾期		9	7020
2017.7.15	黄东	13771410930	8000	400	300	零用贷	周四	20	1400	800	800+300	5800	6900	李文广		逾期		1	700
2017.7.15	尤伟民	13337903347	20000	1000	800	零用贷	周五	20	3500	1500	2000+400	15000	17400			逾期		5	9000
2017.7.17	王春兰	13656150328	6000	400	288	零用贷	周一	15	1400	600	600+200	4000	4800	张玲玲		逾期	江阴	2	1376
2017.7.17	徐浩	18861687987	8000	400	280	零用贷	周一	20	1200	800	800+300	6000	7100	张玲玲	周诗华车	逾期	江阴	3	2040
2017.7.17	赵怀朋	15806177346	8000	400	430	零用贷	周一	20	1200	800	800+300	6000	7100	李文广		逾期		8	7470
2017.7.18	秦春军	15052196917	6000	300	288	零用贷	周一	20	1200	300	600+200	4500	5300	张玲玲		逾期	江阴	4	2352
2017.7.18	陆亚萍	13951582019	6000	250	0	打卡	每天	30	1800	0	600	4200	4800			逾期		21	5250
2017.7.19	翟正阳	13301525924	5000	335	215	零用贷	周二	15	1700	500	500+100	3300	3900	张玲玲		逾期	江阴	1	550
2017.7.19	鲁鹏飞	18961515758	10000	500	350	零用贷	周二	20	1500	500	1000+400	8000	9400	张玲玲	陆春松车	逾期		7	5950
2017.7.20	陈锡峰	13771577922	7000	468	232	零用贷	周三	15	1300	700	700+200	5000	5900	张玲玲		逾期		9	10000
2017.7.21	储俊锋	18352592863	8000	534	316	零用贷	周四	15	1200	800	800+300	6000	7100	李文广		逾期	宜兴	12	10200
2017.7.22	黄益杰	15961560550	7000	700	300	零用贷	周五	10	1300	700	700+200	5000	5900	吕晓慧		逾期	宜兴	5	5000
2017.7.24	祝文军	18552407340	6000	300	280	零用贷	周一	20	1400	600	600+200	4000	4800	张玲玲		逾期		1	580
2017.7.24	钱山江	13961822282	10000	500	320	零用贷	周一	20	6500	1000	1000+400	7500	8900	信息部	成乃柏车	逾期		5	4100
2017.7.24	朱晨	15298438204	20000	1000	600	零用贷	周一	20	3000	2000	2000+400	15000	17400	吕晓慧		逾期		3	4800
2017.7.24	曹志刚	13812021070	6000	300	210	零用贷	周一	20	1200	300	600+200	4500	5300			逾期		4	2540
2017.7.24	张雪兰	13952097369	10000	500	400	零用贷	周一	20	1500	1000	1000+400	7500	8900	张玲玲		逾期	宜兴	4	3600
2017.7.25	王彩艳	18306161676	10000	400	0	打卡	每天	30	2000	0	1000	8000	9000	张玲玲		逾期	江阴	8	3200
2017.7.25	林晓奇	13861704023	6000	300	280	零用贷	周二	20	1200	600	600+200	4200	5000	吕晓慧		逾期		2	1160
2017.7.25	许薇	13162614805	7000	234	246	零用贷	周一	20	1300	700	700+200	5000	5700			逾期		5	2400
2017.7.25	王立冬	15161539043	6000	300	250	零用贷	周一	20	1400	600	600+200	4000	4800	吕晓慧		逾期		2	1100
2017.7.27	朱清	13063610186	6000	400	250	零用贷	周三	15	1200	600	600	4200	4800	信息部		逾期	宜兴	2	1300
2017.7.27	怀金晓	13706169395	10000	500	350	零用贷	周三	20	1500	1000	1000+400	7500	8900			逾期	江阴	15	15900
2017.7.27	王仁华	18861614550	10000	500	350	零用贷	周三	20	1500	1000	1000+400	7500	8900	吕晓慧	周诗华车	逾期	江阴	10	8500
2017.7.28	钱寅	15061973758	7000	350	350	零用贷	周四	20	1300	700	700+200	5000	5900	张玲玲		逾期		1	700
2017.7.29	李宁峰	13400022910	6000	300	200	零用贷	周五	20	1200	600	0	4200	4200	吕单凤		逾期		5	9000
2017.7.31	宋颖超	15995344333	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	吕晓慧		逾期	江阴	2	1376
2017.7.31	王磊	15950445161	8000	300	0	打卡	每天	30	2000	0	800	6000	6800	张玲玲		逾期	江阴	3	2040
2017.7.31	陶金岳	13912452122	8000	400	300	零用贷	周一	20	1200	800	800+300	6000	7100	李文广		逾期	江阴	8	7470
2017.7.31	张敏民	13921237575	6000	300	280	零用贷	周一	20	1200	600	600+200	4200	5000	吕晓慧		逾期	江阴	4	2352
2017.7.31	陆栋梁	15365261637	15000	500	530	零用贷	周一	30	3000	2000	1500+400	10000	11900	张玲玲	周诗华车	逾期	江阴	21	5250
2017.8.1	蔡幼鸣	13861473843	6000	600	250	零用贷	周一	10	1400	600	600+200	4000	4800	吕晓慧		逾期	宜兴	1	550
2017.8.2	吴孝荣	13812005103	6000	300	220	零用贷	周二	20	1200	600	0	4200	4200	吕单凤		逾期		7	5950
2017.8.3	祝平	15261517300	5000	500	250	零用贷	周三	10	1500	500	500	3200	3700	张玲玲		逾期		9	10000
2017.8.4	周晓波	15052119170	7000	350	350	零用贷	周四	20	1300	700	700+200	5000	5900	张玲玲	周诗华车	逾期		12	10200
2017.8.4	姜正波	18861878540	5000	200	0	打卡	每天	30	1700	0	500	3300	3800	李文广		逾期		5	5000
2017.8.6	秦淋	13771459703	6000	250	0	打卡	每天	30	1800	0	0	4200	4200	吕单凤		逾期		1	580
2017.8.7	倪晓莉	13400029835	8000	400	300	零用贷	周一	20	1200	800	800+300	6000	7100	李文广	李贺车	逾期		5	4100
2017.8.9	吕凯	13921371652	8000	400	300	零用贷	周二	20	2000	800	800+300	6000	7100	吕晓慧		逾期	宜兴	3	4800
2017.8.10	贾学宣	13665163629	6000	200	220	零用贷	周三	30	1100	600	600+200	4300	5100	张玲玲		逾期		4	2540
2017.8.11	赵静晓	13921259202	6000	300	210	零用贷	周四	20	1100	600	600	4300	4900	信息部		逾期	江阴	4	3600
2017.8.11	高强	13812155373	 8000	400	300	零用贷	周四	20	2000	800	800	6000	6800	信息部		逾期	江阴	8	3200
2017.8.12	王云锋	13921247419	6000	280	0	打卡	每天	30	1800	0	600	4200	4800	吕晓慧		逾期	江阴	2	1160
2017.8.12	耿春晓	15995215686	10000	500	300	零用贷	周五	20	1200	1000	1000+400	7800	9200	张玲玲		逾期		5	2400
2017.8.14	杨卫东	13861632804	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	江韦		逾期		2	1100
2017.8.15	李平	13376222781	10000	500	300	零用贷	周一	20	1200	800	1000+400	8000	9400	张玲玲	周诗华车	逾期	江阴	2	1300
2017.8.16	梅伟杰	15061726522	6000	300	220	零用贷	周二	20	1200	600	600+200	4200	5000	李文广		逾期	宜兴	15	15900
2017.8.16	高东彪	13952472958	8000	400	280	零用贷	周二	20	1400	600	800+300	6000	7100	吕晓慧		逾期		10	8500
2017.8.17	张吉	15061505882	7000	467	273	零用贷	周三	15	1300	700	700+200	5000	5900	张玲玲		逾期		1	740
2017.8.17	张翔	15805173697	8000	400	280	零用贷	周三	20	1200	800	800+300	6000	7100	张玲玲		逾期	宜兴	5	3400
2017.8.17	张婷	18262251240	8000	400	320	零用贷	周三	20	1200	800	800+300	6000	7100	吕晓慧	成乃柏车	逾期		1	920
2017.8.21	陶莉	13861720707	7000	300	0	打卡	每天	30	2000	0	700	5000	5700			逾期		7	2100
2017.8.21	张祯	15206193536	5000	250	180	零用贷	周一	20	1300	200	0	3500	3500			逾期		5	2150
2017.8.21	丁雷	13506198054	 8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	谢达丹		逾期		12	8400
2017.8.25	龚益波	13915249158	10000	400	350	零用贷	周四	25	1500	1000	1000	7500	8500	信息部	周诗华车	逾期	江阴	2	1800
2017.8.26	王美红	15206196777	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	信息部	周诗华车	逾期		12	3600
2017.8.26	戴宗明	13921379393	6000	400	280	零用贷	周五	15	1400	600	600+200	4000	4600	江韦		逾期	宜兴	2	1360
2017.8.28	徐志文	17712388621	6000	300	250	零用贷	周一	20	1200	600	600+200	4200	5000	江韦		逾期		1	550
2017.8.28	李海	18262294947	6000	300	260	零用贷	周一	20	1200	600	600+200	4200	5000	江韦	周诗华车	逾期		6	3360
2017.8.30	高荣林	13151007535	7000	300	0	打卡	每天	28	2000	0	700	5000	5700	周玉		逾期		11	3300
2017.8.31	顾嘉洋	13616190461	8000	400	300	零用贷	周三	20	1200	800	800+300	6000	7100	李文广		逾期		7	4900
2017.8.31	许益勇	15061745393	6000	400	300	零用贷	周三	15	1800	600	600+200	4200	5000	张玲玲	周诗华车	逾期	江阴	4	2800
2017.8.31	薛裕	13706183173	6000	600	250	零用贷	周三	10	1200	600	600+200	4200	5000	张玲玲	成乃柏车	逾期		2	1700
2017.8.31	苏晓刚	15006160432	6000	400	270	零用贷	周三	15	1200	600	600+200	4200	5000	李文广		逾期	江阴	4	2680
2017.8.31	苏智斌	15161610175	10000	600	0	打卡	每天	20	2000	0	1000	8000	9000	李文广		逾期	江阴	6	3600
2017.9.5	季伟星	13621532259	7000	350	300	零用贷	周一	20	1300	700	700+200	5000	5900	吕晓慧		逾期		0	0
2017.9.5	曾本江	15070380771	6000	400	250	零用贷	周一	15	1400	600	600+200	4000	4800	李文广	周诗华车	逾期		12	7800
2017.9.5	姜恕荣	18861509809	20000	1000	0	打卡	每天	22	5000	0	2000	15000	17000	邹斌		逾期		4	4000
2017.9.6	吕娜	13861831043	30000	1000	0	打卡	每天	32	6000	0	0	24000	24000			逾期		27	27000
2017.9.6	朱辰	13646178202	7000	350	300	零用贷	周二	20	1300	700	700+200	5000	5900	张玲玲		逾期		3	1950
2017.9.6	高必勇	13616149396	6000	250	0	打卡	每天	30	2000	0	600	4000	4600	张玲玲	周诗华车	逾期		18	4500
2017.9.6	胡晓俊	18901517678	10000	500	300	零用贷	周二	20	1500	1000	1000+400	7500	8900	吕晓慧		逾期		16	12800
2017.9.7	顾锋	15312200969	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	张玲玲		逾期		6	1800
2017.9.8	郭洪军	13485016959	7000	350	300	零用贷	周四	20	1300	700	700+200	5000	5900	江韦		逾期	江阴	16	10400
2017.9.11	王敏敏	13921316060	25000	1000	875	零用贷	周一	25	4500	2500	2500+400	18000	20900	李文广		逾期	宜兴	13	28000
2017.9.11	周树辉	18352511820	15000	500	550	零用贷	周一	30	3000	1500	1500+400	10500	12400	周玉		逾期		11	12250
2017.9.12	顾刚强	13861619867	14000	700	0	打卡	每天	20	4000	0	0	10000	10000	信息部		逾期		1	700
2017.9.14	邱丹	18761595184	10000	500	350	零用贷	周三	20	1500	1000	1000+400	7500	8900	张玲玲	周诗华车	逾期	江阴	13	11050
2017.9.15	杨金平	13348100926	6000	600	250	零用贷	周四	10	1400	600	600	4000	4600	何筱慧		逾期		1	850
2017.9.15	尤磊	13961600249	10000	400	0	打卡	每天	30	2500	0	1000	7500	8500	吕晓慧	成乃柏车	逾期	江阴	6	2450
2017.9.18	张莹	13013684690	6000	300	0	打卡	每天	25	2000	0	600	4000	4600	张玲玲		逾期		15	8350
2017.9.21	杨洪	18118911479	6000	400	250	零用贷	周三	15	1400	600	600	4000	4600	吕晓慧	成乃柏车	逾期		10	8300
2017.9.22	沈有安	13921128848	10000	500	350	零用贷	周四	20	2000	0	1000	8000	9000	张玲玲	周诗华车	逾期		13	11050
2017.9.23	钱国华	18851509377	6000	250	0	打卡	每天	30	2000	0	600	0	4600	李文广		逾期		3	750
2017.9.25	刘勇	18921115887	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧		逾期		7	5950
2017.9.27	耿涛	18118873171	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	吕晓慧	周诗华车	逾期	宜兴	1	850
2017.9.28	马祥山	15161575231	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	吕晓慧	周诗华车	逾期		5	5250
2017.10.6	许云磊	18021563190	6000	400	220	零用贷	周四	15	1200	600	600	4200	4800	李文广		逾期	江阴	11	6820
2017.10.9	刘超	13861637645	10000	600	0	打卡	每天	20	2500	0	1000	7500	8500	吕晓慧	周诗华车	逾期	淮安	8	4800
2017.10.10	刘洋	13961761637	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧	周诗华车	逾期	无锡	1	850
2017.10.14	周勰	13806157086	8000	400	280	零用贷	周五	20	1400	800	400	6200	6200	吕晓慧		逾期	无锡	2	1360
2017.10.18	卞捍忠	13771202177	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	徐宽		逾期	江阴	6	5100
2017.10.20	张健	13921398392	6000	300	220	零用贷	周四	20	1200	600	600	4200	4800	徐宽		逾期	无锡	1	850
2017.10.23	沈利峰	13665199991	8000	330	0	打卡	每天	30	2000	0	0	6000	6000			逾期	无锡	23	7590
2017.10.25	恽超	15251596139	8000	400	300	零用贷	周二	20	1200	800	900	6000	6900	李文广		逾期	江阴	4	2800
2017.10.26	蒋国强	13861706504	7000	450	0	打卡	每天	19	2000	0	700	5000	5700	吕晓慧		逾期	无锡	10	4500
2017.10.27	夏一帆	13812175190	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	徐宽	周诗华车	逾期	江阴	4	2300
2017.10.29	周海霞	15961621522	8000	400	300	零用贷	周五	20	1200	800	900	6000	6900	徐宽	成乃柏车	逾期	江阴	9	7900
2017.10.31	杨志	18901512178	6000	280	0	打卡	每天	30	2000	0	700	4000	4700	吕晓慧		逾期	无锡	6	2180
2017.10.31	王为桢	13013624164	6000	400	300	零用贷	周一	15	1400	600	600	4000	4600	杨洋		逾期	无锡	2	1900
2017.11.1	沈伟	13656191645	10000	500	0	打卡	每天	22	2000	0	800	8000	8800	徐宽	周诗华车	逾期	无锡	18	9500
2017.11.2	朱梦娟	18761515512	7000	467	393	零用贷	周三	15	1300	700	700	5000	5700	李文广	周诗华车	逾期	无锡	3	3580
2017.11.3	徐礼文	13812161365	8000	400	300	零用贷	周四	20	1200	800	800	6000	6800	李文广	周诗华车	逾期	江阴	1	700
2017.11.3	吴平群	13961736885	7000	700	300	零用贷	周四	10	1300	700	700	5000	5700	徐宽		逾期	无锡	8	8000
2017.11.5	吴小涓	18262262094	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	吕晓慧	周诗华车	逾期	无锡	18	9000
2017.11.7	司马贤	15806192167	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧		逾期	无锡	6	5100
2017.11.7	王翊伟	18800589902	10000	500	400	零用贷	周一	20	1500	1000	1000	7500	8500	徐宽	周诗华车	逾期	无锡	5	4500
2017.11.8	何健	13812112521	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	徐宽	周诗华车	逾期	江阴	1	850
2017.11.10	周桦	15961852134	7000	450	0	打卡	每天	19	2000	0	500	5000	5500	吕晓慧		逾期	无锡	14	6300
2017.11.10	吴盛	15852589122	10000	625	375	零用贷	周四	16	1500	1000	1000	7500	8500	吕晓慧		逾期	无锡	4	4000
2017.11.12	浦洪漫	13665189307	7000	700	300	零用贷	周五	10	1300	700	700	5000	5700	徐宽		逾期	无锡	7	7000
2017.11.12	孙延松	15061545829	8000	400	330	零用贷	周五	20	1200	800	800	6000	6800	李文广	刘强车	逾期	无锡	1	730
2017.11.13	苏维维	18758188337	15000	750	450	零用贷	周一	20	2250	1500	1500	11250	12750	徐宽	李贺车	逾期	上海（江阴）	4	4800
2017.11.13	徐腾飞	13771395583	7000	350	300	零用贷	周一	20	1300	700	700	5000	5700	彭加双		逾期	宜兴	1	650
2017.11.14	郑荣兰	15006167920	14000	700	500	零用贷	周一	20	2600	1400	1400	10000	11400	李文广		逾期	贵州	2	2400
2017.11.14	陈慧	13951578227	6000	300	250	零用贷	周一	20	1400	600	600	4000	4600	徐宽		逾期	淮安	5	3300
2017.11.15	孙洪良	15312266605	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	彭加双		逾期	江阴	2	1700
2017.11.15	张柯	13861888150	10000	1000	350	零用贷	周二	10	1500	1000	1000	7500	8500	谢达丹		逾期	无锡	3	4050
2017.11.15	梁小宝	15052440001	10000	100	350	零用贷	周二	10	1500	1000	1000	7500	8500	信息部		逾期	灌云	7	9450
2017.11.17	王以行	13861714944	6000	350	0	打卡	每天	20	1800	0	420	4200	4620	杨洋		逾期	无锡	6	2100
2017.11.18	陆文伟	13812031777	10000	500	350	零用贷	周五	20	1500	1000	1000	7500	8500	徐宽	周诗华车	逾期	无锡	4	3400
2017.11.23	王烽	15251577905	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	徐宽		逾期	江阴	1	850
2017.11.24	赵溪哲	18800588220	7000	350	310	零用贷	周四	20	1300	700	700	5000	5700	徐宽	周诗华车	逾期	无锡	1	660
2017.11.27	徐益波	13775132949	10000	500	320	零用贷	周一	20	1500	1000	0	7500	7500	信息部		逾期	江阴	4	3360
2017.11.27	顾晨炎	15061793160	6000	300	260	零用贷	周一	20	1400	600	600	4000	4600	钱晓		逾期	无锡	1	560
2017.11.28	马海峰	18061572073	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李文广	刘强车	逾期	响水	1	850
2017.11.28	顾方军	13914288272	10000	500	350	零用贷	周一	20	1500	2000	1000	6500	7500	徐宽		逾期	江阴	4	3400
2017.11.29	张宋良	13861642053	4000	400	250	零用贷	周二	10	1600	400	400	2000	2400	吕晓慧	周诗华车	逾期	江阴	2	1300
2017.11.29	华其蔚	18762818603	8000	534	316	零用贷	周二	15	1300	700	800	6000	6800	吕晓慧	周诗华车	逾期	无锡	3	2800
2017.12.1	冯江云	15052637914	10000	334	366	零用贷	周四	30	1500	1000	1000	7500	8500	刘军	周诗华车	逾期	涟水	2	1400
2017.12.2	朱玉兰	13961844805	8000	350	0	打卡	每天	30	2000	0	400	6000	6400	彭加双		逾期	无锡	29	10150
2017.12.4	张杰	13861790220	6000	600	350	零用贷	周一	10	1400	600	600	4000	4600	徐宽		逾期	高邮	0	0
2017.12.4	谢松存	18061984445	8000	400	0	打卡	每天	25	2000	0	600	6000	6600	徐宽	周诗华车	逾期	兴华	5	2000
2017.12.5	查明晓	13761324773	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	徐宽	周诗华车	逾期	江阴	4	3400
2017.12.6	陈秋	15895373547	6000	260	0	打卡	每天	30	2000	0	400	4000	4400	彭加双		逾期	江阴	5	1300
2017.12.6	蒋鸿方	13621517987	10000	625	350	零用贷	周二	16	1500	1000	1000	7500	8500	徐宽		逾期	无锡	4	3900
2017.12.6	刘锋	17312728777	10000	1000	350	零用贷	周二	10	1500	1000	1000	7500	8500	徐宽		逾期	江阴	1	1350
2017.12.6	缪丹	15961611448	8000	800	300	零用贷	周二	10	1200	800	800	6000	6800	徐宽		逾期	江阴	4	4400
2017.12.7	陆文忠	13901528667	8000	400	250	零用贷	周三	20	1200	800	800	6000	6800	徐宽		逾期	江阴	0	0
2017.12.9	王新洋	13914158748	20000	1000	600	零用贷	周五	20	3000	2000	1000	15000	16000			逾期	兴华	1	1900
2017.12.18	黄春华	15161554110	10000	1000	300	零用贷	周一	10	1500	1000	1000	7500	8500	杨薇	周诗华车	逾期	无锡	1	1300
2017.12.31	章亚良	15052135314	5000	500	310	零用贷	周五	10	1500	500	500	3000	3500	吕晓慧	周诗华车	逾期	无锡	0	0
2016.12.6	周晓聪	13812115120	8000	0	100	红包贷	每天	30	1600	800	560	5600	6160	张敏		逾期		15	1500
2017.3.7	王志东	18020512990	10000	0	120	红包贷	每天	30	2500	0	1000	7500	8500	邵静		逾期		10	1200
2017.3.10	韩忠	15298404344	6000	0	80	红包贷	每天	30	2400	1000	250	2600	2850	崔日成	夏飞车	逾期		135	10800
2017.3.20	陈维俊	13771498505	8000	0	100	红包贷	每天	30	2000	0	600	6000	6600	邵静	陆春松车	逾期		12	1200
2017.5.19	田寿玉	13400030746	6000	0	80	红包贷	每天	30	800	0	0	5200	5200			逾期		7	560
2017.6.19	恽志恒	18626366766	6000	0	60	红包贷	每天	30	1500	0	0	4500	4500			逾期		17	1020
2017.8.8	许薇	13162614805	7000	0	100	红包贷	每天	30	2000	0	700	5000	5700	吕晓慧		逾期		5	2400
2017.11.4	陈敏	13400032155	6000	0	80	红包贷	每天	30	2000	0	0	4000	4000			逾期		16	1280
2017.11.6	张旭泓	13861894933	10000	10000	120	红包贷	每天	30	2000	0	0	8000	8000			逾期		0	7000
2017.11.7	张旭泓	13861894933	10000	10000	120	红包贷	每天	30	2000	0	0	8000	8000			逾期		0	0
2017.11.13	张旭泓	13861894933	10000	10000	120	红包贷	每天	30	2000	0	0	8000	8000			逾期		0	0
2017.12.4	吴志刚	13961733171	10000	0	100	红包贷	每天	30	2000	0	0	8000	8000			逾期		19	1900
2017.3.20	周凌波	18068791911	20000	0	2500	空放	30	1	2500	0	0	17500	17500			逾期		1	2500
2017.3.26	叶寅	13915294442	10000	0	1500	空放	30	1	1500	0	0	8500	8500			逾期		2	2800
2017.5.12	黄涛	13665178715	100000	0	12500	空放	180	1	20000	0	2000	80000	82000			逾期		1	10000
2017.5.23	黄云	13921179079	210000	0	70000	空放	66	1	60000	0	0	150000	150000			逾期		0	0
2017.5.25	黄意章	13771152686	10000	0	1500	空放	10	1	9500	500	0	0	8000		成乃柏车	逾期		14	7000
2017.9.1	徐文浩	13706188648	25000	0	5000	空放	25	1	5000	0	1000	20000	21000			逾期		0	0
2017.9.5	胡国利	18921366800	30000	0	10000	空放	22	1	10000	0	2000	20000	22000	李文广	周诗华车	逾期		0	0
2017.9.6	陈士猛	15206183989	8000	0	2500	空放	22	1	2500	0	0	5500	5500			逾期		0	0
2017.10.6	郁浩	18262264684	10000	0	3000	空放	22	1	3000	0	1000	7000	8000	吕晓慧	周诗华车	逾期		0	0
2017.11.1	童爱军	15861462825	6000	0	2400	空放	22	1	2400	0	360	3600	3960	彭加双		逾期		0	0
2017.12.24	孙欣	18861533597	7000	0	3000	空放	22	1	3000	0	400	4000	4400	徐宽		逾期		0	0
2016.12.28	郑娟萍	13961882787	40000	1000	500	车贷	周二	40	7700	2000	2000	30300	32300	邵静		逾期		17	25500
2017.9.4	田建康	18006163676	10000	500	350	车贷	周一	20	2500	1000	2000	6500	7900	李文广	成乃柏车	逾期	江阴	7	6950
2017.11.15	杨虎	13585083514	6000	250	0	打卡	每天	30	1900	0	410	4100	4510	吕晓慧		逾期	无锡	4	1000
2017.9.6	陈士猛	15206183989	8000	0	2500	空放	22	1	2500	0	0	5500	5500			逾期		1	1500';*/

        /*$data_array = '2017.10.6	周忠英	15306188171	6000	300	240	零用贷	周四	20	1200	600	600	4200	4800	徐剑萍	周诗华车	结清	无锡
2017.10.6	周文瀛	18762466080	8000	400	288	零用贷	周四	20	1200	800	800	6000	6800	李肖肖	成乃柏车	结清	无锡		
2017.10.6	杨亦柳	15052273070	7000	700	300	零用贷	周四	10	1300	700	700	5000	5700	李肖肖	刘强车	结清	无锡		
2017.10.6	惠为成	15852559853	6000	300	220	零用贷	周四	20	1200	600	600	4200	4800	徐剑萍	周诗华车	结清	阜宁		
2017.10.6	贺泰来	15896480872	6000	300	250	零用贷	周四	20	1400	600	600	4000	4600	李肖肖		结清	无锡		
2017.10.6	钱季伟	15061562943	6000	600	280	零用贷	周四	10	1200	600	600	4200	4800	李肖肖		结清	江阴		
2017.10.6	曹晓君	18661287477	10000	500	380	零用贷	周四	20	1500	1000	1000	7500	8500	曾燕丹	成乃柏车	结清	无锡		
2017.10.7	李川	15312285961	6000	400	250	零用贷	周五	15	1200	600	600	4200	4800	李肖肖		结清	江阴		
2017.10.7	刘康	15358099808	10000	500	350	零用贷	周五	20	1500	1000	1000	7500	8500	曾燕丹	成乃柏车	结清	沭阳		
2017.10.9	邹天顺	15061850543	6000	300	250	零用贷	周一	20	1200	600	600	4200	4800	徐剑萍		结清	无锡		
2017.10.9	周棵	15298400317	6000	300	250	零用贷	周一	20	1400	600	600	4000	4600	李肖肖		结清	重庆		
2017.10.10	封兴佳	13961664302	6000	300	210	零用贷	周一	20	1200	600	600	4200	4800	徐剑萍		结清	江阴		
2017.10.10	蔡超	13806187122	10000	667	353	零用贷	周一	15	1500	1000	1000	7500	8500	徐剑萍		结清	无锡		
2017.10.10	邓玲	13861883264	6000	400	250	零用贷	周一	15	1200	600	600	4200	4800	曾燕丹	刘强车	结清	无锡		
2017.10.10	范楼燕	18262274385	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李肖肖	周诗华车	结清	无锡		
2017.10.11	邵佳	15298412276	6000	300	250	零用贷	周二	20	1200	600	600	4200	4800	徐剑萍		结清	无锡		
2017.10.12	朱寅	18168863606	10000	500	325	零用贷	周三	20	1250	1000	1000	7750	8750	吴军	刘强车	结清	无锡		
2017.10.12	徐敏	13861706991	8000	400	300	零用贷	周二	20	1200	800	800	6000	6800	徐剑萍		结清	无锡		
2017.10.12	马章桂	15370218812	5000	250	200	零用贷	周三	20	1200	500	500	3300	3800	李肖肖		结清	靖江		
2017.10.13	廉卢峰	15261570825	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	李肖肖		结清	无锡		
2017.10.13	夏晓明	13861706267	6000	600	260	零用贷	周四	10	1400	600	600	4000	4600	李肖肖		结清	无锡		
2017.10.18	金伟亮	15861592893	6000	600	288	零用贷	周二	10	1400	600	600	4000	4600	徐剑萍		结清	无锡		
2017.10.18	李刚	18795672522	6000	300	200	零用贷	周二	20	1400	600	600	4000	4600	吴军		结清	江阴		
2017.10.18	刘冠伟	18861851052	7000	700	300	零用贷	周二	10	1300	700	700	5000	5700	李镇	周诗华车	结清	安徽		
2017.10.19	杨晓芙	18021190752	7000	350	300	零用贷	周三	20	1300	700	700	5000	5700	曾燕丹		结清	无锡		
2017.10.19	刘峰	18762801776	7000	700	300	零用贷	周三	10	1300	700	700	5000	5700	吴军	刘强车	结清	兴化		
2017.10.24	施晓忠	13616178847	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	张慧	刘强车	结清	无锡		
2017.10.24	史卫明	13921261600	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	徐剑萍	周诗华车	结清	江阴		
2017.10.25	钱伟	13806179571	6000	600	350	零用贷	周二	10	1400	600	600	4000	4600	吴军		结清	无锡		
2017.10.25	周圆圆	15895335514	8000	800	300	零用贷	周二	10	1200	800	800	6000	6800	李肖肖		结清	无锡		
2017.10.26	张颖	13771016018	6000	300	250	零用贷	周三	20	1400	600	600	4000	4600	李镇	周诗华车	结清	无锡		
2017.10.27	卞蒙琪	15161619038	8000	400	280	零用贷	周四	20	1200	800	800	6000	6800	信息部		结清	北京		
2017.10.27	胡晶	17625826887	6000	200	250	零用贷	周四	30	1400	600	600	4000	4600	李镇		结清	无锡		
2017.10.27	陈云超	13921173170	6000	400	300	零用贷	周四	15	1400	600	600	4000	4600	徐剑萍		结清	无锡		
2017.10.28	许辉	18762684839	10000	1000	400	零用贷	周五	10	1500	1000	1000	7500	8500	曾燕丹	周诗华车	结清	东台		
2017.10.28	鲁春华	15052228863	8000	400	300	零用贷	周五	20	1200	800	800	6000	6800	吴军	周诗华车	结清	无锡		
2017.10.29	戴伟成	18661011155	8000	800	300	零用贷	周五	10	1200	800	800	6000	6800	徐剑萍	周诗华车	结清	无锡		
2017.10.30	张凯	17826102399	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	李镇	周诗华车	结清	安徽		
2017.11.7	吴月	13771477729	6000	600	210	零用贷	周一	10	2000	0	600	4000	4600	姜超		结清	无锡		
2017.11.9	高逸涛	15961532983	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	姜超		结清	江阴		
2017.11.10	沈建军	15052261038	10000	1000	400	零用贷	周四	10	1500	1000	1000	7500	8500	胡晶晶	刘强车	结清	东台		
2017.11.12	张安平	13179699322	10000	1000	400	零用贷	周五	10	1500	1000	1000	7500	8500	姜超	刘强车	结清	无锡		
2017.11.13	蒋鑫龙	13951511114	5000	500	250	零用贷	周五	10	1300	500	500	3200	3700	张慧		结清	无锡		
2017.11.15	徐佳佳	13601532919	6000	600	280	零用贷	周二	10	1400	600	600	4000	4600	张慧		结清	宜兴		
2017.11.15	吕峰	18915334640	10000	1000	350	零用贷	周二	10	1500	1000	1000	7500	8500	徐剑萍		结清	无锡		
2017.11.15	倪叙兴	13921170108	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	信息部		结清	无锡		
2017.11.20	顾昊	15161660303	6000	400	250	零用贷	周一	15	1400	600	600	4000	4600	徐剑萍		结清	宜兴		
2017.12.1	陈明	13812289759	8000	400	320	零用贷	周四	20	1200	800	800	6000	6800	徐剑萍		结清	宜兴		
2017.12.5	季韦韦	13585094992	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	信息部老板		结清	无锡		
2017.12.5	夏银萍	13921270770	7000	700	300	零用贷	周一	10	1300	700	700	5000	5700	胡晶晶	刘强车	结清	靖江		
2017.12.6	蔡超	13806187122	12000	800	400	零用贷	周二	15	1800	1200	1200	9000	10200	徐剑萍		结清	无锡		
2017.12.10	胡张玲	13584115100	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700	徐剑萍		结清	江阴		
2017.12.11	堵本锋	15061749753	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700	姜超		结清	江阴		
2017.12.25	刘佳平	15861491280	10000	500	380	零用贷	周一	20	1500	1000	1000	7500	8500	徐剑萍	刘强车	结清	无锡
2018.1.3	许璐	13921184044	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	胡晶晶	刘强车	结清	无锡
2017.10.6	丁雷	13506198054	6000	400	300	零用贷	周四	15	1400	600	600	4000	4600	李肖肖		逾期	无锡	5	3500
2017.10.6	姜亚芳	13813698362	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	李肖肖	刘强车	逾期	无锡	9	7650
2017.10.6	唐晓春	13861721982	8000	534	286	零用贷	周四	15	1200	800	800	6000	6800	徐剑萍		逾期	无锡	3	2460
2017.10.6	庄建栋	13914267571	6000	400	288	零用贷	周四	15	1200	600	600	4200	4800	徐剑萍		逾期	无锡	5	3440
2017.10.6	邱月平	13861816323	8000	400	280	零用贷	周四	20	1200	800	800	6000	6800	徐剑萍	周诗华车	逾期	无锡	2	1360
2017.10.6	薛晓薇	15961815286	8000	400	300	零用贷	周四	20	1200	800	800	6000	6800	徐剑萍		逾期	无锡	1	700
2017.10.6	孙延松	15061545829	10000	500	380	零用贷	周四	20	1500	1000	1000	7500	8500	李肖肖		逾期	盱眙	6	5280
2017.10.6	贡岑	18915229777	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	李肖肖		逾期	江阴	1	850
2017.10.6	谢军	18068335791	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	徐剑萍	成乃柏车	逾期	安徽	8	6800
2017.10.7	鲍龙	18168867569	7000	467	233	零用贷	周五	15	1300	700	700	5000	5700	李肖肖		逾期	无锡	7	4900
2017.10.7	童爱军	15861462825	8000	400	280	零用贷	周五	20	1200	800	800	6000	6800	李肖肖	周诗华车	逾期	阜宁	4	2720
2017.10.8	王新洋	13914158748	20000	1000	600	零用贷	周五	20	3000	2000	2000	15000	17000	信息部		逾期	兴化	10	16100
2017.10.9	李阮	13771779082	6000	300	280	零用贷	周一	20	1400	600	600	4000	4600	李肖肖		逾期	江阴	3	1740
2017.10.9	吴升伟	13915363313	8000	800	300	零用贷	周一	10	1200	800	800	6000	6800	徐剑萍	周诗华车	逾期	滨海	3	5500
2017.10.10	刘洋	13961761637	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	徐剑萍		逾期	无锡	1	850
2017.10.11	施会	13961800885	6000	300	250	零用贷	周二	20	1400	600	600	4000	4600	李肖肖	周诗华车	逾期	阜宁	3	1650
2017.10.13	曹永	15052108846	7000	700	300	零用贷	周四	10	1300	700	700	5000	5700	徐剑萍	刘强车	逾期	安徽	6	8600
2017.10.18	卞捍忠	13771202177	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	曾燕丹		逾期	江阴	6	5100
2017.10.23	张奎	18352555377	10000	500	320	零用贷	周一	20	1500	1000	1000	7500	8500	吴军		逾期	四川	4	3280
2017.10.23	汤斌	15252211895	7000	700	300	零用贷	周一	10	1300	700	700	5000	5700	李镇	刘强车	逾期	邳州	5	5000
2017.10.24	周江	13912352537	6000	600	280	零用贷	周一	10	1400	600	600	4000	4600	徐剑萍		逾期	无锡	4	3520
2017.10.25	郁浩	15251675485	10000	667	383	零用贷	周二	15	1500	1000	1000	7500	8500	曾燕丹		逾期	盐城	1	1200
2017.10.27	夏一帆	13812175190	10000	1000	350	零用贷	周四	10	1500	1000	1000	7500	8500	徐剑萍		逾期	江阴	0	0
2017.10.28	沈纲平	15961698350	8000	800	300	零用贷	周五	10	1200	800	800	6000	6800	李肖肖	周诗华车	逾期	江阴	1	1100
2017.10.28	陈洪亮	13771023336	7000	467	283	零用贷	周五	15	1300	700	700	5000	5700	张慧	刘强车	逾期	无锡	10	7500
2017.10.29	朱西杨	18112499366	10000	500	350	零用贷	周五	20	2000	0	500	7000	7500	曾燕丹	成乃柏车	逾期	邳州	3	2550
2017.10.30	刘勇	18921115887	7000	467	303	零用贷	周一	15	1300	700	700	5000	5700	李肖肖		逾期	南京	1	770
2017.11.1	金春贵	13115085828	7000	700	300	零用贷	周二	10	1300	700	350	5000	5350	徐剑萍		逾期	无锡	2	2000
2017.11.3	杨薇	18352555035	8000	800	300	零用贷	周四	10	1200	800	800	6000	6800	徐剑萍	刘强车	逾期	无锡	5	7500
2017.11.3	陈芳平	15961622607	7000	467	253	零用贷	周四	15	1300	700	700	5000	5700	徐剑萍		逾期	江阴	5	3600
2017.11.7	许伟	18014942582	8000	534	296	零用贷	周一	15	1200	800	800	6000	6800	姜超		逾期	江阴	5	4150
2017.11.7	王翊伟	18800589902	10000	500	400	零用贷	周一	20	1500	1000	1000	7500	8500	姜超		逾期	无锡	5	4500
2017.11.7	刘石义	13151928783	10000	667	353	零用贷	周一	15	1500	1000	1000	7500	8500	胡晶晶	刘强车	逾期	安徽	4	4080
2017.11.8	何健	13812112521	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	姜超		逾期	江阴	1	850
2017.11.10	杜广波	13861841469	7000	700	380	零用贷	周四	10	1300	700	700	5000	5700	徐剑萍		逾期	无锡	0	0
2017.11.13	张忠跃	17312728644	6000	300	230	零用贷	周一	20	1400	600	600	4000	4600	李肖肖	刘强车	逾期	浙江	5	2650
2017.11.13	苏维维	18758188337	15000	750	450	零用贷	周一	20	2250	1500	1500	11250	12750	姜超		逾期	上海	4	4800
2017.11.14	郑荣兰	15006167920	14000	700	500	零用贷	周一	20	2600	1400	1400	10000	11400	李肖肖	刘强车	逾期	贵州	3	3600
2017.11.15	贡建新	13585056720	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	徐剑萍	刘强车	逾期	江阴	0	0
2017.11.15	朱承良	15895375287	7000	350	250	零用贷	周二	20	1300	700	700	5000	5700	胡晶晶		逾期	江阴	5	3000
2017.11.15	孙洪良	15312266605	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	徐剑萍		逾期	江阴	2	1700
2017.11.15	缪勤丰	13914275621	8000	400	300	零用贷	周二	20	1200	800	800	6000	6800	徐剑萍	刘强车	逾期	江阴	3	2100
2017.11.18	陆文伟	13812031777	10000	500	350	零用贷	周五	20	1500	1000	1000	7500	8500	姜超		逾期	无锡	4	3400
2017.11.23	王烽	15251577905	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	胡晶晶	刘强车	逾期	江阴	2	1700
2017.11.24	钱敏	13921217466	7000	350	350	零用贷	周四	20	1300	700	700	5000	5700	徐剑萍	刘强车	逾期	江阴	1	700
2017.12.1	周锡峰	18861803737	6000	300	220	零用贷	周四	20	1400	600	600	4000	4600	李肖肖		逾期	无锡	3	1560
2017.12.5	查明晓	13761324773	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	姜超		逾期	江阴	4	3100
2017.12.6	蒋鸿方	13621517987	10000	625	350	零用贷	周二	16	1500	1000	1000	7500	8500	信息部		逾期	无锡	4	3900
2017.12.9	刘娟	15061579616	20000	1000	650	零用贷	周五	20	3000	2000	2000	15000	17000	姜超		逾期	河北	3	4950
2017.12.14	张健	13861641114	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	姜超	周诗华车	逾期	江阴	1	850
2017.12.18	黄春华	15161554110	10000	1000	300	零用贷	周一	10	1500	1000	1000	7500	8500	信息部老板		逾期	无锡	1	1300
2017.10.6	高慧敏	13812270466	8000	450	0	打卡	每天	20	2000	0	800	6000	6800	李肖肖		逾期	无锡	10	4650
2017.10.6	朱小波	13921538241	6000	350	0	打卡	每天	20	1800	0	600	4200	4800	徐剑萍		逾期	无锡	5	3350
2017.10.9	刘超	13861637645	10000	600	0	打卡	每天	20	2500	0	1000	7500	8500	徐剑萍		逾期	淮安	8	4800
2017.10.24	吕斌	17826100036	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	李镇	刘强车	逾期	安徽	15	8920
2017.10.26	杨志	18901512178	7000	320	0	打卡	每天	30	2000	0	700	5000	5700	曾燕丹		逾期	无锡	11	4020
2017.11.2	郑建山	13771413051	6000	250	0	打卡	每天	26	2000	0	400	4000	4400	张慧		逾期	涟水	9	2250
2017.11.3	谢波	13912491432	8000	350	0	打卡	每天	30	2000	0	400	6000	6400			逾期	无锡	17	5950
2017.11.7	汤守萍	15371088909	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	徐剑萍	周诗华车	逾期	无锡	16	4800
2017.11.23	袁兆海	13921261145	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	信息部		逾期	滨海	6	1800
2017.11.28	胡晓俊	18901517678	10000	300	0	打卡	每天	40	2500	0	750	7500	8250	李肖肖		逾期	无锡	30	9000
2017.12.2	汪先安	13812544959	10000	500	0	打卡	每天	22	2500	0	750	7500	8250	胡晶晶		逾期	安徽	6	3000
2017.12.10	张柯	13861888150	10000	500	0	打卡	每天	22	2500	0	750	7500	8250	信息部老板		逾期	无锡	1	500
2017.12.14	贡晓华	15061856901	8000	350	0	打卡	每天	30	2000	0	600	6000	6600	姜超		逾期	无锡	18	6350
2017.12.25	汤耀芳	13771339288	7000	300	0	打卡	每天	30	2000	0	500	5000	5500	胡晶晶	周诗华车	逾期	宜兴	9	2700
2017.10.23	林海新	13328109115	6000	0	2000	空放	22	1	0	0	400	4000	4400	张慧		逾期		0	0
2017.10.6	过梅如	18914122251	8000	450	0	打卡	每天	22	2000	0	800	6000	6800	徐剑萍		结清	无锡		
2017.10.6	谢波	13912491432	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	徐剑萍		结清	无锡		
2017.10.6	朱寿贵	15861577138	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	李肖肖	刘强车	结清	浙江		
2017.10.6	黄玉生	18851572046	6000	260	0	打卡	每天	30	1800	0	600	4200	4800	李肖肖		结清	安徽		
2017.10.6	华士明	13814283578	8000	450	0	打卡	每天	20	2000	0	800	6000	6800	徐剑萍		结清	无锡		
2017.10.6	华佳	13771020747	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	徐剑萍	刘强车	结清	无锡		
2017.10.6	吴晓明	13601519077	10000	500	0	打卡	每天	22	2000	0	1000	7500	8500	李肖肖	周诗华车	结清	无锡		
2017.10.6	营秀英	15006156306	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	李肖肖		结清	宜兴		
2017.10.7	吕春一	18921270163	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	李肖肖	刘强车	结清	无锡		
2017.10.9	张雪影	18861745249	6000	200	0	打卡	每天	40	2000	0	600	4000	4600	李肖肖		结清	安徽		
2017.10.9	陈彪	15152213456	8000	300	0	打卡	每天	40	2000	0	800	6000	6800	曾燕丹		结清	无锡		
2017.10.9	金春贵	13115085828	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	徐剑萍		结清	无锡		
2017.10.11	张月舫	15301512053	10000	400	0	打卡	每天	30	2500	0	1000	7500	8500	李肖肖		结清	无锡		
2017.10.11	张伟刚	13606195995	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	徐剑萍		结清	无锡		
2017.10.12	杨薇	18352555035	8000	480	0	打卡	每天	20	1800	0	800	6200	7000	徐剑萍	周诗华车	结清	无锡		
2017.10.16	林丹丹	18761556117	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	吴军		结清	徐州		
2017.10.17	胡晓俊	18901517678	10000	300	0	打卡	每天	40	2500	0	1000	7500	8500	李肖肖		结清	无锡		
2017.10.26	华士明	13814283578	8000	450	0	打卡	每天	20	2000	0	800	6000	6800	徐剑萍		结清	无锡		
2017.10.27	黄霞	15261636511	10000	400	0	打卡	每天	30	2500	0	700	7500	8200	李镇	刘强车	结清	太仓		
2017.10.28	袁兆海	13921261145	6000	300	0	打卡	每天	25	2000	0	600	4000	4600	徐剑萍		结清	滨海		
2017.10.30	吴晓明	13601519077	10000	500	0	打卡	每天	22	2000	500	1000	7500	8500	李肖肖		结清	无锡		
2017.10.31	营秀英	1500656306	8000	500	0	打卡	每天	20	2000	0	800	6000	6800	李肖肖		结清	宜兴		
2017.11.3	黄玉生	18851572046	6000	260	0	打卡	每天	30	2000	0	600	4000	4600	李肖肖		结清	安徽		
2017.11.5	徐茜	15152661572	10000	500	0	打卡	每天	22	2000	0	800	8000	8800	李肖肖		结清	泰兴		
2017.11.6	林丹丹	18761556117	10000	500	0	打卡	每天	22	2500	0	370	7500	7870	吴军		结清	徐州		
2017.11.7	朱燕	15052292559	8000	400	0	打卡	每天	25	1800	0	620	6200	6820	李肖肖		结清	无锡		
2017.11.11	董斐	13912351685	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	徐剑萍	成乃柏车	结清	无锡		
2017.11.13	朱戴君	15061500166	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	徐剑萍	周诗华车	结清	无锡		
2017.11.20	周持勋	13961779610	6000	400	0	打卡	每天	20	2000	0	400	4000	4400	李肖肖		结清	无锡		
2017.11.20	张柯	13861888150	10000	550	0	打卡	每天	20	2500	0	750	7500	8250	谢达丹		结清	无锡		
2017.12.1	朱孙军	13815132255	8000	350	0	打卡	每天	30	2000	0	600	6000	6600	姜超		结清	江阴		
2017.12.2	董斐	13912351685	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	徐剑萍		结清	无锡		
2017.12.5	陈文虎	13814201030	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	徐剑萍		结清	无锡		
2017.12.5	陆建新	13861879917	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	胡晶晶		结清	无锡		
2017.12.6	温崇华	15251589242	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	姜超		结清	射阳		
2017.12.6	凌丽燕	15161652118	6000	250	0	打卡	每天	30	1800	0	420	4200	4620	姜超		结清	江阴		
2017.12.6	丁宜	13861466393	6000	300	0	打卡	每天	25	2000	0	400	4000	4400	李肖肖		结清	无锡		
2017.12.14	营秀英	15006156306	8000	450	0	打卡	每天	20	2000	0	600	6000	6600	李肖肖		结清	宜兴		
2017.12.23	黄玉生	18851572046	5000	300	0	打卡	每天	20	1800	0	320	3200	3520	李肖肖		结清	安徽		
2017.10.7	高鑫安	15852718181	10000	500	366	零用贷	周五	20	1500	1000	1000	7500	8500	李肖肖	周诗华车	还款中	河南	14	12124
2017.10.9	周文斌	18762682167	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	徐剑萍	周诗华车	还款中	无锡	14	11900
2017.10.9	华佳	13861603792	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	徐剑萍	刘强车	还款中	江阴	14	12200
2017.10.9	朱林震	13771457852	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	李肖肖	刘强车	还款中	沭阳	14	10300
2017.10.11	任吉媛	13861477218	15000	500	420	零用贷	周二	30	2500	800	1500	11700	13200	信息部		还款中	无锡	14	12880
2017.10.11	张振磊	13771283586	6000	300	250	零用贷	周二	20	1200	600	600	4200	4800	李肖肖	刘强车	还款中	江阴	14	7700
2017.10.11	韩蕾	13771114523	7000	233	207	零用贷	周二	30	1300	700	700	5000	5700	吴军	刘强车	还款中	无锡	14	6160
2017.10.12	周国祥	18861506415	10000	500	320	零用贷	周三	20	1500	1000	1000	7500	8500	李肖肖		还款中	建湖	14	11510
2017.10.16	鲁涛	13961520082	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	徐剑萍	刘强车	还款中	宜兴	13	11050
2017.10.16	华明秋	15251573217	20000	1000	600	零用贷	周一	20	4000		2000	16000	18000	信息部		还款中	江阴	13	20800
2017.10.17	马光耀	13093088733	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	李肖肖		还款中	无锡	13	9100
2017.10.18	陈昊	13861756523	10000	334	296	零用贷	周二	30	1500	1000	1000	7500	8500	吴军		还款中	泰兴	13	8460
2017.10.19	刘希	13771180579	7000	350	300	零用贷	周三	20	1300	700	700	5000	5700	吴军		还款中	无锡	13	8450
2017.10.20	陈嘉逸	15895302903	7000	350	250	零用贷	周四	20	1800	700	700	4500	5200	曾燕丹		还款中	无锡	12	8100
2017.10.24	刘寒冬	13057379897	7000	234	216	零用贷	周一	30	1300	700	700	5000	5700	徐剑萍	刘强车	还款中	江阴	12	5400
2017.10.28	苏重光	18861830111	5000	250	200	零用贷	周五	20	1200	500	500	3300	3800	徐剑萍		还款中	无锡	11	4950
2017.10.28	姜逸丹	15861637418	10000	500	400	零用贷	周五	20	1500	1000	1000	7500	8500	徐剑萍	成乃柏车	还款中	江阴	11	9900
2017.10.30	华岳松	13771154411	10000	667	353	零用贷	周一	15	1500	1000	1000	7500	8500	徐剑萍	周诗华车	还款中	无锡	11	11220
2017.10.30	刘东	18951578523	10000	500	300	零用贷	周一	20	1500	1000	1000	7500	8500	李肖肖	周诗华车	还款中	无锡	11	8800
2017.11.6	孙敏伟	13771119763	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	胡晶晶		还款中	无锡	10	8658
2017.11.7	刘进祥	18068377222	10000	500	300	零用贷	周一	20	1200	1000	1000	7800	8800	胡晶晶		还款中	南京	10	8000
2017.11.9	孙林霞	13961713310	20000	1000	700	零用贷	周三	20	2000	2000	1500	16000	17500	姜超		还款中	无锡	9	15800
2017.11.10	钱敏	18852491616	10000	500	280	零用贷	周四	20	1000	1000	500	8000	8500	徐剑萍		还款中	宜兴	9	7020
2017.11.13	朱玲	13771072871	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	谢达丹		还款中	无锡	9	7700
2017.11.13	孔苏湘	18051932266	6000	600	300	零用贷	周一	10	1400	600	600	4000	4600	徐剑萍		还款中	江阴	9	8100
2017.11.13	张金锋	13861619252	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	姜超		还款中	江阴	9	7650
2017.11.14	柳冠华	13806191482	6000	600	250	零用贷	周一	10	1200	300	600	4500	5100	周玉		还款中	无锡	9	7650
2017.11.15	盛曦	13921209369	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	信息部		还款中	如皋	9	7650
2017.11.16	杜为敏	15366508233	7000	350	280	零用贷	周三	20	1300	700	700	5000	5700	徐剑萍		还款中	滨海	9	5670
2017.11.20	何金锡	13861759720	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	姜超	刘强车	还款中	无锡	8	6800
2017.11.21	沈奕伯	13861875378	15000	500	500	零用贷	周一	30	2250	1500	1500	11250	12750	姜超		还款中	无锡	8	8000
2017.11.22	王剑	13912979000	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	胡晶晶		还款中	江阴	7	7150
2017.11.23	唐光明	13961907556	7000	700	280	零用贷	周三	10	1300	700	700	5000	5700	胡晶晶		还款中	无锡	7	6860
2017.11.24	华佳伟	15206158765	7000	467	293	零用贷	周四	15	1300	700	700	5000	5700	徐剑萍	刘强车	还款中	无锡	7	5320
2017.11.25	林丹丹	18761556117	10000	1000	320	零用贷	周五	10	1500	1000	1000	7500	8500	信息部		还款中	徐州	7	9240
2017.11.25	陆彦成	18861506105	15000	1500	450	零用贷	周五	10	2250	1500	1500	11250	12750	信息部邹	刘强车	还款中	无锡	7	14250
2017.11.28	谢广跃	15370857609	10000	1000	500	零用贷	周一	10	1500	1000	1000	7500	8500	胡晶晶	刘强车	还款中	无锡	7	10500
2017.11.28	刘宇新	13584132226	8000	534	316	零用贷	周一	15	1200	800	800	6000	6800	徐剑萍	成乃柏车	还款中	江阴	7	5950
2017.11.29	徐莉萍	13358105482	7000	350	250	零用贷	周二	20	1300	700	700	5000	5700	姜超		还款中	无锡	7	4200
2017.11.29	黄晴	15852532828	8000	400	300	零用贷	周二	20	1200	800	800	6000	6800	徐剑萍		还款中	无锡	7	4900
2017.11.29	严红兰	13812229198	10000	1000	350	零用贷	周二	10	1500	1000	1000	7500	8500	胡晶晶	刘强车	还款中	宜兴	7	9950
2017.11.29	陈金龙	18861642685	8000	400	300	零用贷	周二	20	1200	800	800	6000	6800	胡晶晶	周诗华车	还款中	江阴	7	4900
2017.12.1	徐丽	13382298312	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	胡晶晶		还款中	江阴	6	5100
2017.12.1	李璐	15852559564	7000	280	250	零用贷	周四	25	1300	700	700	5000	5700	姜超		还款中	宝应	6	3180
2017.12.1	杨承宗	13861718655	8000	400	320	零用贷	周四	20	1000	800	800	6200	7000	李肖肖	刘强车	还款中	安徽	6	4320
2017.12.1	蓝韦龙	15312203240	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	李肖肖	刘强车	还款中	广西	6	5100
2017.12.4	莫丽君	13771587309	8000	534	316	零用贷	周一	15	1200	800	800	6000	6800	姜超		还款中	江阴	6	5100
2017.12.5	韩彤	15961884790	6000	300	280	零用贷	周一	20	1400	600	600	4000	4600	徐剑萍		还款中	兴化	6	3480
2017.12.5	姚敏晓	15261539610	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	徐剑萍		还款中	无锡	6	6100
2017.12.5	黄龙	18921252930	10000	1000	350	零用贷	周一	10	1500	1000	1000	7500	8500	胡晶晶		还款中	江阴	6	8100
2017.12.6	许关荣	13585069885	8000	534	286	零用贷	周二	15	1200	800	800	6000	6800	姜超		还款中	射阳	6	4920
2017.12.6	徐军	13771036125	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	姜超		还款中	无锡	6	5100
2017.12.6	王鑫康	13771080854	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	胡晶晶	刘强车	还款中	无锡	6	5100
2017.12.8	沈建军	15052261038	10000	500	400	零用贷	周四	20	1500	1000	1000	7500	8500	胡晶晶	刘强车	还款中	东台	5	4500
2017.12.8	汪凤林	13771281671	6000	400	250	零用贷	周四	15	1400	600	600	4000	4600	徐剑萍		还款中	江阴	5	3250
2017.12.8	刘峰	18861815964	10000	1000	350	零用贷	周四	10	1500	1000	1000	7500	8500	信息部老板		还款中	兴化	3	4200
2017.12.8	盖琳	13506154240	6000	600	300	零用贷	周四	10	1400	600	600	4000	4600	姜超		还款中	宜兴	5	5400
2017.12.11	陆坤	18260039773	10000	500	300	零用贷	周一	20	1500	1000	1000	7500	8500	徐剑萍		还款中	无锡	5	4000
2017.12.11	宋更秀	15852725884	8000	800	300	零用贷	周一	10	1200	800	800	6000	6800	胡晶晶		逾期	兴化	4	4500
2017.12.11	葛高生	18018333398	10000	1000	400	零用贷	周一	10	1500	1000	1000	7500	8500	李肖肖	刘强车	还款中	无锡	5	7000
2017.12.14	陆英芝	15852622787	8000	800	300	零用贷	周三	10	1200	800	800	6000	6800	徐剑萍		还款中	江阴	5	5500
2017.12.14	王海	13921361881	10000	500	300	零用贷	周三	20	1500	1000	1000	7500	8500	信息部老板		还款中	江阴	5	4000
2017.12.15	金伟亮	15861592893	7000	700	250	零用贷	周四	10	1300	700	700	5000	5700	徐剑萍		还款中	无锡	4	3800
2017.12.16	张建华	13814281047	10000	500	380	零用贷	周五	20	1500	1000	1000	7500	8500	徐剑萍	刘强车	还款中	无锡	4	3760
2017.12.18	方建	18800516096	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	姜超	周诗华车	还款中	江阴	4	3400
2017.12.18	陈建强	13813676104	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	张慧	刘强车	还款中	常州	4	3400
2017.12.18	周洲	15251542603	10000	1000	400	零用贷	周一	10	1500	1000	1000	7500	8500	徐剑萍	周诗华车	还款中	江阴	4	5600
2017.12.18	赵洪昌	13771283538	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李肖肖		还款中	江阴	4	3400
2017.12.20	吴丽娟	13771252923	7000	700	300	零用贷	周二	10	1300	700	700	5000	5700	胡晶晶	刘强车	逾期	江阴	3	3900
2017.12.22	段一星	13093002066	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	李肖肖	刘强车	还款中	无锡	3	2550
2017.12.24	孙金伟	13921392527	6000	300	280	零用贷	周五	20	1400	600	600	4000	4600	信息部		还款中	无锡	3	1740
2017.12.25	曹晓君	18661287477	10000	500	380	零用贷	周一	20	1500	1000	1000	7500	8500	李肖肖		还款中	无锡	3	2640
2017.12.26	刘玉龙	15061751709	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	姜超	刘强车	还款中	江阴	3	2550
2017.12.26	王晓蕾	13771443441	8000	400	350	零用贷	周一	20	1200	800	800	6000	6800	胡晶晶	成乃柏车	还款中	无锡	3	2250
2017.12.26	王钦	13771252887	7000	467	303	零用贷	周一	15	1300	700	700	5000	5700	李肖肖		还款中	江阴	3	2310
2017.12.29	沈杰栋	13305165950	8000	800	300	零用贷	周四	10	1200	800	800	6000	6800	胡晶晶		还款中	无锡	2	2200
2017.12.29	尤铖炜	13348109862	7000	350	250	零用贷	周四	20	1300	700	700	5000	5700	李肖肖	刘强车	还款中	无锡	2	1200
2017.12.29	王琳	15152283449	10000	500	350	零用贷	周四	20	1500	100	1000	7500	8500	姜超	周诗华车	还款中	江阴	2	1700
2017.12.29	倪志军	18020308990	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	胡晶晶	刘强车	还款中	无锡	2	1700
2017.12.29	吴静	13701522759	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	姜超	刘强车	还款中	江阴	2	1700
2017.12.30	张建军	14761539684	8000	800	300	零用贷	周五	10	1200	800	800	6000	6800	李肖肖	刘强车	还款中	无锡	2	2200
2017.12.31	张安平	13179699322	10000	1000	400	零用贷	周五	10	1500	1000	1000	7500	8500	胡晶晶		还款中	无锡	2	2800
2018.1.2	张政	13962790401	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	姜超	刘强（未开车）	还款中	靖江	2	1700
2018.1.2	钟凯	13063693282	7000	350	300	零用贷	周一	20	1300	700	700	5000	5700	徐剑萍	刘强车	还款中	江阴	2	1300
2018.1.3	方流娜	13815129380	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	胡晶晶	周诗华车	还款中	江阴	2	1700
2018.1.4	史爱艳	18362392810	7000	350	250	零用贷	周三	20	1300	700	700	5000	5700	胡晶晶		还款中	无锡	2	1200
2018.1.5	蒋晓飞	13912362708	6000	600	300	零用贷	周四	10	1400	600	600	4000	4600	胡晶晶		还款中	无锡	1	900
2018.1.5	颜峰	13861733335	15000	750	550	零用贷	周四	20	2250	1500	1500	11250	12750	胡晶晶	刘强车	还款中	无锡	2	2600
2018.1.5	李伟东	13771071580	20000	1000	700	零用贷	周四	20	3000	2000	2000	15000	17000	李肖肖		还款中	无锡	1	1700
2018.1.7	王炯	15052183927	8000	534	306	零用贷	周五	15	1200	800	800	6000	6800	胡晶晶		还款中	江阴	1	840
2018.1.9	赵烽	15312212607	8000	400	300	零用贷	周一	20	1400	800	800	5800	6600	胡晶晶		还款中	无锡	1	700
2018.1.10	薛松	13961722603	8000	800	300	零用贷	周二	10	1400	800	800	5800	6600	李肖肖	周诗华车	还款中	无锡	1	1100
2018.1.11	徐佳佳	13601532919	6000	600	280	零用贷	周二	10	1400	600	600	4000	4600	信息部		还款中	宜兴	1	880
2018.1.12	秦国建	13179659688	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	姜超	刘强车	还款中	江阴	0	0
2018.1.13	朱俊杰	13771113533	10000	500	350	零用贷	周五	20	1500	500	1000	8000	9000	信息部陈霞		还款中	无锡	0	0
2018.1.13	戴赵伟	17712387441	6000	600	300	零用贷	周五	10	1400	600	600	4000	4600	胡晶晶		还款中	无锡	0	0
2017.12.26	唐剑星	13306171387	8000	350	0	打卡	每天	30	2000	0	600	6000	6600	胡晶晶		还款中	无锡	23	8050
2017.12.29	孙春雨	13701511093	10000	500	0	打卡	每天	22	2500	0	750	7500	8250	胡晶晶		还款中	响水	20	10000
2018.1.9	凌丽燕	15161652118	6000	250	0	打卡	每天	30	1800	0	420	4200	4620	姜超		还款中	江阴	9	2250
2018.1.9	过沂	13915333525	7000	450	0	打卡	每天	21	2000	0	500	5000	5500	徐剑萍		还款中	无锡	9	4050
2018.1.10	刘娜	13506188357	8000	400	0	打卡	每天	24	2000	0	600	6000	6600	胡晶晶	周诗华车	还款中	无锡	8	3200
2018.1.11	闵俊嵘	15061859259	6000	250	0	打卡	每天	30	2000	0	400	4000	4400	李肖肖		还款中	无锡	7	1750
2017.10.7	吴雪红	13961816448	10000	0	3000	空放	22	1	0	0	700	7000	7700	吴军	成乃柏车	还款中		4	12000
2017.11.11	时民生	13812522582	20000	0	6000	空放	22	1	0	0	800	14000	14800	李肖肖	刘强车	结清			
2017.12.18	徐益波	13775132949	8000	0	2500	空放	22	1	0	0	550	5500	6050	信息部		还款中		1	2800
2018.1.2	吴如琛	13357903793	10000	0	3500	空放	22	1	0	0	650	6500	7150	徐剑萍		还款中		1	1500';*/

        // 0.借款日期	1.客户姓名	2.客户电话	3.借款本金	4.一期本金	5.一期利息	6.借款类型	7.还款时间
        // 8.借款周期	9.手续费	10.保证金	11.同行反点	12.实际到账	13.实际支出	14.客户经理	15.上门经理
        //16.客户状态	17.客户地址	18.总已还款期数	19.总还款金额

        /*$data_array = '2017.5.27	莫家培222	13921118277	20000	400	560	零用贷	周五	50	3000	2000	2000	15000	17000	顾峰		还款中	无锡	32	30720
2017.7.17	邵传坚0903	15251677808	10000	334	276	零用贷	周一	30	2400	500	1950	7100	9050	朱新约	成乃柏	还款中	盐城	26	15860
2017.8.2	姚敏晓2514	15261539610	10000	334	346	零用贷	周二	30	1500	1000	1400	7500	8900	朱新约	成乃柏	还款中	无锡新区	23	15640
2017.8.15	顾敏俊1216	18015361213	6000	300	220	零用贷	周一	20	1200	600	600	4200	4800	顾峰		还款中	无锡南长	18	9360
2017.8.24	张月舫2464	15301512053	15000	500	530	零用贷	周三	30	2500	1500	1900	11000	12900	顾峰	成乃柏	还款中	无锡新区	20	20600
2017.9.2	曹云3554	15370233802	6000	600	280	零用贷	周五	10	1200	600	800	4200	5000	江韦		还款中	江阴	19	16720
2017.9.5	方伟6276	13914190564	10000	500	350	零用贷	周一	20	1500	1000	1400	7500	8900	王建杰		还款中	无锡	19	16150
2017.9.7	盛曦0413	13921209369	10000	500	350	零用贷	周三	20	1500	1000	1400	7500	8900	范利丽		还款中	无息	18	15300
2017.9.12	刘文彬3254	13771002178	12000	600	450	零用贷	周一	20	1800	1200	700	9000	9700	范利丽		还款中	无锡堰桥	18	18900
2017.9.22	蒋智兴5713	13771377766	6000	300	250	零用贷	周四	20	1400	600	600	4000	4600	顾峰		还款中	宜兴	16	8800
2017.10.6	刘猛1519	15052129626	8000	534	276	零用贷	周四	15	1200	800	800	6000	6800	江韦		还款中	无锡惠山	14	11340
2017.10.7	沈君兰3522	18651513954	7000	234	296	零用贷	周五	30	1300	700	700	5000	5700	范利丽		还款中	无锡北塘	14	7420
2017.10.8	徐建成3913	13405751050	10000	500	280	零用贷	周五	20	1000	1000	1000	8000	9000	信息部		还款中	无锡	14	10920
2017.10.9	王剑723x	13912979000	10000	334	316	零用贷	周一	30	1500	500	0	8000	8000	信息部		还款中	江阴	14	9100
2017.10.10	沈田庆2510	15052287601	6000	200	220	零用贷	周一	30	1200	600	300	4200	4500	顾峰		还款中	无锡	14	5880
2017.10.10	吴晟3595	13961509184	8000	400	280	零用贷	周一	20	1400	800	800	5800	6600	朱新约		还款中	宜兴	14	9520
2017.10.10	庄登雲0023	13961711998	15000	750	420	零用贷	周一	20	2000	1500	1500	11500	13000	范利丽		还款中	无锡	14	16380
2017.10.11	黄婷3049	15995352005	6000	300	220	零用贷	周二	20	1200	600	600	4200	4800	江韦		还款中	江阴	14	7280
2017.10.12	陈波7256	18752683375	7000	350	250	零用贷	周三	20	1300	1300	700	5000	5700	刘强	成乃伯	还款中	四川	13	7800
2017.10.13	蔡柯桢2016	15152224194	6000	300	210	零用贷	周四	20	1200	600	600	4200	4800	王建杰	周诗华	还款中	无锡南长	13	6630
2017.10.14	过武1819	13701518787	12000	480	340	零用贷	周五	25	1000	500	600	9600	10200	信息部	成乃伯	还款中	无锡东亭	13	10660
2017.10.17	张海峰7837	15961796830	10000	500	300	零用贷	周一	20	1500	1000	1000	7500	8500	朱新约	成乃伯	还款中	滨海	12	9600
2017.10.20	孙建涛2575	13921321830	6000	300	200	零用贷	周四	20	1200	600	200	4200	4400	朱新约		还款中	宜兴	12	6000
2017.10.25	何能国0619	13771042896	8000	260	300	零用贷	周二	30	1200	800	800	6000	6800	朱新约	成乃柏	还款中	安徽	12	6720
2017.10.25	杨舒涵4726	13812221113	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	信息部		还款中	无锡崇安	12	10200
2017.10.27	徐建科1038	13914209812	5000	250	210	零用贷	周四	20	1000	500	500	3500	4000	朱新约		还款中	江阴	11	5060
2017.10.30	李焕合6012	13771245191	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	信息部		还款中	辽宁	11	7700
2017.10.30	杨志豪4135	15061884830	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	刘强		还款中	无锡滨湖	11	7700
2017.10.31	杨炫宗	13915228028	10000	500	400	零用贷	周一	20	1500	1000	1000	7500	8500	信息部		还款中	江阴	11	9000
2017.11.1	蒋峰2351	13093095112	25000	834	566	零用贷	周二	30	2500	0	2500	20000	22500	范利丽	成乃伯	还款中	无锡钱桥	11	15400
2017.11.2	祝培勇613x	13806184998	10000	500	210	零用贷	周三	20	1700	300	0	8000	8000	王建杰		还款中	无锡滨湖	10	7100
2017.11.4	华建征2010	13771112778	8000	400	280	零用贷	周五	20	1200	800	800	6000	6800	李聪	成乃伯	还款中	无锡北塘	10	6800
2017.11.6	张彦0017	18251556279	10000	500	350	零用贷	周五	20	1500	1000	1000	7500	8500	顾峰		还款中	宜兴	10	8500
2017.11.7	王靖昌1215	18658828725	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	朱新约		还款中	无锡南长	10	7000
2017.11.7	钱慰6517	13003383709	15000	750	550	零用贷	周一	20	1500	1500	1500	12000	13500	范利丽		还款中	无锡滨湖	10	13000
2017.11.9	周海燕2078	13906172415	14000	934	501	零用贷	周三	15	2600	1400	1400	10000	11400	顾峰	成乃伯	还款中	无锡锡山	9	12915
2017.11.13	严明逸2279	13771525539	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	顾峰	成乃伯	还款中	无锡锡山	9	7650
2017.11.13	吴斌1017	13771170960	15000	1000	500	零用贷	周一	15	1500	1500	1500	11000	12500	李聪		还款中	无锡南长	9	13500
2017.11.13	陈波3414	17712364969	7000	350	300	零用贷	周一	20	1300	700	700	5000	5700	范利丽		还款中	淮安	9	5850
2017.11.14	孙井志2078	18861633858	6000	400	300	零用贷	周一	15	1400	600	600	4000	4600	朱新约		还款中	江阴	9	6300
2017.11.15	马金男	15961837582	40000	2000	1200	零用贷	周二	20	1400	5500	4000	32000	36000	顾峰	成乃柏	还款中	无锡锡山	9	28800
2017.11.15	陆书兵8233	18168391826	7000	700	290	零用贷	周二	10	1300	700	700	5000	5700	王建杰	周诗华	还款中	安徽	8	7920
2017.11.15	张轶峰	15895301285	10000	1000	280	零用贷	周二	10	1500	1000	1000	7500	8500	信息部		还款中	无锡	8	10240
2017.11.17	张瑜0918	13196598990	8000	800	300	零用贷	周四	10	1200	800	400	6000	6400	王建杰		还款中	黑龙江	8	8800
2017.11.17	赵沁阳2013	18552091997	8000	400	280	零用贷	周四	20	1200	800	800	6000	6800	顾峰		还款中	无锡北塘	8	5440
2017.11.21	储伟0979	13771321827	10000	500	280	零用贷	周二	20	1500	1000	300	7500	7800	朱新约		还款中	宜兴	8	6240
2017.11.21	刘东0015	18951578523	15000	750	420	零用贷	周一	20	2200	2500	1500	10300	11800	罗		还款中	无锡南长	8	9360
2017.11.21	陈昊2417	13861756523	10000	334	316	零用贷	周一	30	1000	1000	1000	8000	9000	信息部		还款中	泰兴	8	5200
2017.11.27	俞仁辉2890	15906199926	6000	400	250	零用贷	周一	15	1200	600	600	4200	4800	信息部		还款中	建湖	7	4550
2017.11.28	毛翔2838	13151005602	20000	2000	700	零用贷	周一	10	3000	2000	2000	15000	17000	范利丽		还款中	无锡梁溪	7	18900
2017.11.28	顾方军6016	13914288272	10000	500	350	零用贷	周一	20	1500	2000	1000	6500	7500	李聪		还款中	江阴	7	5950
2017.11.28	贺健1838	18661087256	6000	500	350	零用贷	周一	10	1200	600	600	4200	4800	信息部		还款中	无锡南长	7	5950
2017.11.29	李昌品6738	13511654292	6000	300	350	零用贷	周二	20	1200	600	600	4200	4800	顾峰		还款中	重庆	7	4550
2017.11.30	李刚8016	18795672522	6000	400	280	零用贷	周三	15	1200	600	600	4200	4800	范利丽		还款中	江阴	6	4080
2017.12.1	华岳松0714	13771154411	8000	800	330	零用贷	周四	10	1200	800	800	6000	6800	朱新约		还款中	无锡滨湖	6	6780
2017.12.1	吴莹0043	13951583557	10000	667	303	零用贷	周四	15	1000	1000	100	8000	8100	信息部		还款中	无锡滨湖	6	5820
2017.12.4	张明鹏2615	17715828652	10000	500	300	零用贷	周一	20	1500	1000	1000	7500	8500	李聪	成乃伯	还款中	安徽	6	4800
2017.12.4	徐语晨1329	13812287222	15000	750	520	零用贷	周一	20	2500	1500	1500	12000	13500	信息部		还款中	无锡复兴	6	7620
2017.12.5	樊浩1579	13771353089	8000	534	307	零用贷	周一	15	1400	800	800	5800	6600	顾峰		还款中	宜兴	6	5046
2017.12.5	尤铖炜1919	13348109862	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700	李聪		还款中	无锡北塘	6	3600
2017.12.6	张正军4719	13812563987	6000	400	250	零用贷	周二	15	1400	600	600	4000	4600	李聪		还款中	宜兴	6	3900
2017.12.6	张镇西0371	13771355877	15000	750	530	零用贷	周二	20	2500	1500	1500	11000	12500	罗		还款中	宜兴	6	7680
2017.12.7	张秦凯0011	15861569080	8000	800	300	零用贷	周三	10	1400	800	800	5800	6600	范利丽		还款中	无锡滨湖	5	5500
2017.12.7	张杰421x	15852842242	6000	400	250	零用贷	周三	15	1400	600	600	4000	4600	李聪		还款中	无锡惠山	5	3250
2017.12.7	王钦2271	13771252887	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	范利丽	成乃伯	还款中	江阴	5	4250
2017.12.7	华明秋3774	18121513820	20000	1000	600	零用贷	周四	20	1000	2000	1000	17000	18000	信息部		还款中	江阴	5	8000
2017.12.7	顾国华597x	13771579995	20000	1000	700	零用贷	周三	20	3000	2000	2000	15000	17000	范利丽	成乃伯	还款中	无锡滨湖	5	8500
2017.12.8	王宇4436	13616171777	6000	400	250	零用贷	周四	15	1200	600	600	4200	4800	信息部	成乃伯	还款中	无锡崇安	5	3250
2017.12.9	储伟0929	13771321827	10000	1000	280	零用贷	周五	10	1500	1000	300	7500	7800	朱新约		还款中	宜兴	5	6400
2017.12.11	王伟0812	18951588193	6000	200	250	零用贷	周一	30	1200	600	600	4200	4800	朱新约	成乃伯	还款中	无锡锡山	5	2250
2017.12.11	过梅如0021	18914122251	10000	667	353	零用贷	周一	15	1500	1000	1000	7500	8500	顾峰	成乃伯	还款中	无锡滨湖	5	5100
2017.12.12	邹丹6513	13585007783	6000	300	210	零用贷	周一	20	1200	600	600	4200	4800	信息部	成乃伯	还款中	无锡滨湖	5	2550
2017.12.12	邵燕滨4417	13861715189	6000	300	250	零用贷	周一	20	1200	600	600	4200	4800	王建杰		还款中	无锡惠山	5	2750
2017.12.15	张扬8524	13961670075	6000	600	250	零用贷	周四	10	1200	600	600	4200	4800	李聪		还款中	江阴	4	3400
2017.12.16	杨六闯6631	15861664747	10000	1000	350	零用贷	周五	10	1500	1000	1000	7500	8500	朱新约	周诗华	还款中	无锡锡山	4	5400
2017.12.16	王俊磊	13861690054	25000	834	666	零用贷	周五	30	4000	1000	2500	20000	22500	李聪	周诗华	还款中	无锡新区	4	6000
2017.12.18	胡俊强4873	18921318791	7000	280	240	零用贷	周一	25	1300	700	700	5000	5700	范利丽		还款中	宜兴	4	2080
2017.12.18	夏银萍0443	13921270770	6000	600	300	零用贷	周一	10	1400	600	600	4000	4600	范利丽		还款中	靖江	4	3600
2017.12.18	黄晨3529	13382222250	10000	667	353	零用贷	周一	15	1500	1000	1000	7500	8500	范利丽	周诗华	还款中	无锡北塘	4	4080
2017.12.19	孙力军229x	18051950693	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	顾峰		还款中	无锡锡山	4	3400
2017.12.19	张勇5998	13951511790	10000	670	350	零用贷	周一	15	1500	1000	1000	7500	8500	朱新约		还款中	无锡滨湖	4	2800
2017.12.20	杜为敏399x	15368508233	8000	400	300	零用贷	周二	20	1200	800	800	6000	6800	顾峰		还款中	滨海	4	5400
2017.12.20	顾芸0621	13921196878	10000	1000	350	零用贷	周二	10	1500	1000	1000	7500	8500	罗		还款中	无锡北塘	4	4520
2017.12.20	曹晓君2515	18661287477	15000	600	530	零用贷	周二	25	3000	1000	750	11000	11750	范利丽		还款中	无锡新区	3	2100
2017.12.21	冯一青4839	13771408510	8000	400	300	零用贷	周三	20	1200	800	800	6000	6800	顾峰		还款中	无锡惠山	3	4800
2017.12.21	吕文革7295	18061900698	20000	1000	600	零用贷	周三	20	2000	2000	700	16000	16700	罗		还款中	无锡新区	3	1950
2017.12.21	戴桂凤2548	13861772217	7000	350	300	零用贷	周三	20	1300	700	500	5000	5500	顾峰		还款中	无锡南长	3	2100
2017.12.22	蒋逸丹1546	15861637418	8000	400	300	零用贷	周四	20	1200	1600	800	5000	5800	信息部		还款中	江阴	3	2040
2017.12.24	虞晓5529	15161597522	8000	400	280	零用贷	周五	20	1200	0	400	6400	6800	范利丽		还款中	无锡惠山	3	2100
2017.12.25	芦甘3518	13812261885	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	王建杰		还款中	南京	3	2550
2017.12.25	顾文标527x	13812164833	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	信息部		还款中	江阴	3	2700
2017.12.25	顾晓东2338	18651009857	6000	600	300	零用贷	周一	10	1800	600	300	4200	4500	李聪		还款中	无锡南长	3	2550
2017.12.26	沈剑7578	13771097131	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李聪		还款中	无锡新区	3	2100
2017.12.27	营秀英0049	15006156306	8000	400	300	零用贷	周二	20	1400	800	800	5800	6600	顾峰		还款中	宜兴	2	1800
2017.12.28	耿清5775	13771231539	6000	600	300	零用贷	周三	10	1400	600	600	4000	4600	李聪		还款中	江阴	2	1800
2017.12.29	张鑫2633	15951502526	6000	600	300	零用贷	周四	10	1200	600	600	4200	4800	李聪		还款中	陕西	2	1700
2017.12.29	蓝伟龙1510	15312203240	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	范利丽		还款中	广西	2	1700
2017.2.27	石猛	13701512160	14000	0	140	红包贷	每天	30	3000	0	1050	11000	12050	范利丽		结清		0	0
2017.2.27	沈娟	15250838079	6000	0	60	红包贷	每天	30	1200	0	430	4800	5230	顾峰		结清		0	0
2017.2.27	许春	15949228776	8000	0	80	红包贷	每天	30	1700	0	580	6300	6880	周依含		结清		0	0
2017.2.28	刘希	13771180579	10000	0	100	红包贷	每天	30	2000	0	750	8000	8750	范利丽		结清		0	0
2017.2.28	梁机够	18301856288	8000	0	80	红包贷	每天	30	1700	0	580	6300	6880	朱新约		结清		0	0
2017.2.28	翁秋磊	13914285802	8000	0	80	红包贷	每天	30	1700	0	580	6300	6880	陈大大		结清		0	0
2017.3.1	祝培勇y	13806184998	10000	0	100	红包贷	每天	30	2000	0	750	8000	8750	月宝宝		结清		0	0
2017.3.4	恽志恒	18626366766	8000	0	80	红包贷	每天	30	1700	0	580	6300	6880	陈大大		结清		0	0
2017.3.10	徐丽	13921113555	8000	0	80	红包贷	每天	30	2200	0	600	5800	6400	朱新约		结清		0	0
2017.3.13	季振华	13646159041	8000	0	80	红包贷	每天	30	2000	0	600	6000	6600	顾峰		结清		0	0
2017.3.14	邵华	13921502780	6000	0	80	红包贷	每天	30	2000	0	400	4000	4400	朱新约		结清		0	0
2017.3.17	梁相君	13921117503	6000	0	80	红包贷	每天	30	1800	0	400	4200	4600	朱新约		结清		0	0
2017.3.22	顾珂珂	15961707602	30000	0	300	红包贷	每天	30	6000	0	4400	24000	28400	信息部		结清		0	0
2017.3.28	沈娟	15250838079	6000	0	60	红包贷	每天	30	1500	0	450	4500	4950	顾峰		结清		0	0
2017.3.29	石猛	13701512160	14000	0	140	红包贷	每天	30	2000	0	600	12000	12600	范利丽		结清		0	0
2017.3.29	许春	15949228776	8000	0	80	红包贷	每天	30	2000	0	0	6000	6000	周依含		结清		0	0
2017.3.30	翁秋磊	13914285802	8000	0	80	红包贷	每天	30	2000	0	300	6000	6300	陈大大		结清		0	0
2017.3.30	梁机够	18301856288	8000	0	80	红包贷	每天	30	2000	0	300	6000	6300	朱新约		结清		0	0
2017.3.31	苏坚明	13961771905	6000	0	70	红包贷	每天	30	1800	0	420	4200	4620	王建杰		结清		0	0
2017.4.3	恽志恒	18626366766	8000	0	80	红包贷	每天	30	2000	0	300	6000	6300	陈大大		结清		0	0
2017.4.5	吴智敏	13961597622	6000	0	70	红包贷	每天	30	1700	0	430	4300	4730	朱新约		结清		0	0
2017.4.10	李强	13921227727	11000	0	500	红包贷	每天	26	2000	0	900	9000	9900	信息部		结清		0	0
2017.4.12	邵华	13921502780	6000	0	80	红包贷	每天	30	2000	0	200	4000	4200	朱新约		结清		0	0
2017.4.13	邵晟	15006154371	15000	0	150	红包贷	每天	31	4000	0	1500	11000	12500	顾峰		结清		0	0
2017.4.16	谬文军	13861710945	6000	0	100	红包贷	每天	32	2000	0	400	4000	4400	王建杰		结清		0	0
2017.4.16	华凤	13915228958	6000	0	100	红包贷	每天	33	2000	0	400	4000	4400	王建杰		结清		0	0
2017.4.17	梅余峰	13915203237	10000	0	100	红包贷	每天	34	2500	0	750	7500	8250	王建杰		结清		0	0
2017.4.18	过梅如	18914122251	6000	0	80	红包贷	每天	35	1800	0	420	4200	4620	顾峰		结清		0	0
2017.4.19	顾洪强	18118893027	6000	0	60	红包贷	每天	36	1500	0	450	4500	4950	信息部		结清		0	0
2017.4.20	陆建凤	18261592953	8000	0	300	红包贷	每天	37	2400	0	560	5600	6160	顾峰		结清		0	0
2017.4.21	张旭泓	13861894933	10000	0	120	红包贷	每天	38	2500	0	750	7500	8250	信息部		结清		0	0
2017.4.21	张南峰	15106177681	8000	0	300	红包贷	每天	40	2000	0	600	6000	6600	范利丽		结清		0	0
2017.4.21	顾珂珂	15961707602	30000	0	300	红包贷	每天	30	2000	0	0	28000	28000	信息部		结清		0	0
2017.4.24	顾敏俊	18015351213	6000	0	210	红包贷	每天	42	1500	0	450	4500	4950	王建杰		结清		0	0
2017.4.27	李炜斌	13915357102	6000	0	210	红包贷	每天	40	2000	0	400	4000	4400	陈大大		结清		0	0
2017.4.28	张鑫	15951502526	6000	0	70	红包贷	每天	40	1700	0	430	4300	4730	李聪		结清		0	0
2017.4.29	翁秋磊	13914285802	8000	0	80	红包贷	每天	30	2000	0	300	6000	6300	陈大大		结清		0	0
2017.4.29	许春	15949228776	8000	0	80	红包贷	每天	30	2000	0	0	6000	6000	周依含		结清		0	0
2017.5.3	恽志恒	18626366766	8000	0	80	红包贷	每天	30	2000	0	300	6000	6300	陈大大		结清		0	0
2017.5.4	颜华	15716196677	10000	500	0	打卡	每天	30	2500	0	750	7500	8250	陈大大		结清		0	0
2017.5.4	吴智敏	13961597622	6000	0	70	红包贷	每天	30	2000	0	200	4000	4200	朱新约		结清		0	0
2017.5.5	杨建	15250818400	6000	270	0	打卡	每天	30	1800	0	420	4200	4620	顾峰		结清		0	0
2017.5.10	孔威	18626300885	6000	0	60	红包贷	每天	30	2000	0	400	4000	4400	顾峰		结清		0	0
2017.5.11	顾建伟	13616199125	8000	0	80	红包贷	每天	30	2200	0	580	5800	6380	王建杰		结清		0	0
2017.5.12	杨新伟	13921512177	5000	190	0	打卡	每天	30	1500	0	350	3500	3850	朱新约		结清		0	0
2017.5.12	六徐文	13914180018	15000	500	0	打卡	每天	40	4000	0	1100	11000	12100	李聪		结清		0	0
2017.5.12	邵晟	15006154371	15000	0	150	红包贷	每天	30	2000	0	650	13000	13650	顾峰		结清		0	0
2017.5.13	钱彤	13915355327	6000	0	70	红包贷	每天	30	1700	0	430	4300	4730	王建杰		结清		0	0
2017.5.13	施霞清	15861651481	6000	0	60	红包贷	每天	30	1700	0	430	4300	4730	朱新约		结清		0	0
2017.5.13	陈晓靓	13912376897	10000	0	100	红包贷	每天	30	2000	0	800	8000	8800	李聪		结清		0	0
2017.5.15	吴海	15251439039	8000	400	0	打卡	每天	25	2400	0	560	5600	6160	顾峰		结清		0	0
2017.5.15	陈胜	15950117515	6000	200	0	打卡	每天	30	1600	0	440	4400	4840	朱新约		结清		0	0
2017.5.16	梅余峰	13915203237	10000	0	100	红包贷	每天	30	2000	0	400	8000	8400	王建杰		结清		0	0
2017.5.16	吴斌峰	13063666798	6000	0	80	红包贷	每天	30	1800	0	420	4200	4620	顾峰		结清		0	0
2017.5.17	吴振瑞	13616168994	8000	300	0	打卡	每天	30	2400	0	560	5600	6160	王建杰		结清		0	0
2017.5.17	冯霄峰	13812055385	10000	100	0	打卡	每天	30	2600	0	740	7400	8140	李聪		结清		0	0
2017.5.18	董美新	13616192770	6000	200	0	打卡	每天	40	1600	0	440	4400	4840	顾峰		结清		0	0
2017.5.18	顾洪强	18118893027	6000	0	60	红包贷	每天	30	1000	0	250	5000	5250	信息部		结清		0	0
2017.5.19	颜华	15716196677	10000	500	0	打卡	每天	22	2000	0	400	8000	8400	陈大大		结清		0	0
2017.5.21	沈娟	15250838079	6000	0	60	红包贷	每天	30	1600	0	440	4400	4840	顾峰		结清		0	0
2017.5.21	张旭泓	13861894933	10000	120	0	打卡	每天	30	2000	0	400	8000	8400	信息部		结清		0	0
2017.5.22	王周琴	13961581605	8000	350	0	打卡	每天	30	2000	0	600	6000	6600	顾峰		结清		0	0
2017.5.24	邵鼎秋	18800599135	8000	280	0	打卡	每天	40	2000	0	600	6000	6600	朱新约		结清		0	0
2017.5.25	程海波	15052411869	6000	210	0	打卡	每天	40	1700	0	430	4300	4730	陈大大		结清		0	0
2017.5.25	李道明	18706192270	7000	250	0	打卡	每天	40	2200	0	480	4800	5280	陈大大		结清		0	0
2017.5.26	张南峰	15106177681	10000	440	0	打卡	每天	30	2500	0	750	7500	8250	范利丽		结清		0	0
2017.5.27	王佳梅	18352553905	8000	0	100	红包贷	每天	30	2000	0	600	6000	6600	范利丽		结清		0	0
2017.5.27	张鑫	15951502526	6000	0	70	红包贷	每天	30	1500	0	225	4500	4725	李聪		结清		0	0
2017.5.28	许春	15949228776	8000	0	80	红包贷	每天	30	2000	0	0	6000	6000	周依含		结清		0	0
2017.5.31	王丹萍	15161683870	8000	0	80	红包贷	每天	30	2000	0	600	6000	6600	范利丽		结清		0	0
2017.6.2	恽志恒	18626366766	8000	0	80	红包贷	每天	30	2000	0	300	6000	6300	陈大大		结清		0	0
2017.6.3	吴智敏	13961597622	6000	0	70	红包贷	每天	30	2000	0	200	4000	4200	朱新约		结清		0	0
2017.6.5	颜华	15716196677	15000	900	0	打卡	每天	30	2100	0	600	12900	13500	陈大大		结清		0	0
2017.6.5	张国南	15950136622	7000	0	80	红包贷	每天	30	1700	0	530	5300	5830	玲玲		结清		0	0
2017.6.8	赵陈	15312205937	6000	0	70	红包贷	每天	30	1700	0	430	4300	4730	范利丽		结清		0	0
2017.6.8	尤豪杰	13912355844	6000	0	70	红包贷	每天	30	1700	0	430	4300	4730	顾峰		结清		0	0
2017.6.10	王周琴	13961581605	10000	500	0	打卡	每天	30	2500	0	750	7500	8250	顾峰		结清		0	0
2017.6.11	钱彤	13915355327	6000	0	70	红包贷	每天	30	1500	0	225	4500	4725	王建杰		结清		0	0
2017.6.11	邵晟	15006154371	15000	150	0	打卡	每天	30	2000	0	650	13000	13650	顾峰		结清		0	0
2017.6.15	梅余峰	13915203237	10000	100	0	打卡	每天	30	2000	0	400	8000	8400	王建杰		结清		0	0
2017.6.16	张衡	18100657556	15000	600	0	打卡	每天	30	4000	0	1100	11000	12100	李聪		结清		0	0
2017.6.20	冯建林	18915322801	5000	200	0	打卡	每天	30	1500	0	350	3500	3850	江韦		结清		0	0
2017.6.20	邓咏君	18651031431	10000	400	0	打卡	每天	30	2500	0	750	7500	8250	陈大大		结清		0	0
2017.6.20	沈娟	15250838079	6000	0	60	红包贷	每天	30	1500	0	225	4500	4725	顾峰		结清		0	0
2017.6.22	陈瑶	18795626947	6000	0	70	红包贷	每天	30	1800	0	420	4200	4620	范利丽		结清		0	0
2017.6.22	张旭泓	13861894933	10000	0	120	红包贷	每天	30	2000	0	400	8000	8400	信息部		结清		0	0
2017.6.23	 陈勇	15301527815	6000	280	0	打卡	每天	30	1800	0	420	4200	4620	陈大大		结清		0	0
2017.6.27	张鑫	15951502526	6000	0	70	红包贷	每天	30	1500	0	225	4500	4725	李聪		结清		0	0
2017.6.28	许春	15949228776	8000	0	80	红包贷	每天	30	2000	0	0	6000	6000	周依含		结清		0	0
2017.6.29	崔森	15006153047	7000	0	100	红包贷	每天	30	2200	0	480	4800	5280	江韦		结清		0	0
2017.7.4	吴智敏	13961597622	6000	0	70	红包贷	每天	30	2000	0	200	4000	4200	朱新约		结清		0	0
2017.7.5	袁攀	15852650171	6000	0	80	红包贷	每天	30	2000	0	400	4000	4400	朱新约		结清		0	0
2017.7.7	尹勤仙	13584206185	6000	300	0	打卡	每天	25	1800	0	420	4200	4620	江韦		结清		0	0
2017.7.7	赵陈	15312205937	6000	0	70	红包贷	每天	30	1500	0	225	4500	4725	范利丽		结清		0	0
2017.7.8	王文波	15061506037	6000	400	0	打卡	每天	30	2000	0	400	4000	4400	江韦		结清		0	0
2017.7.8	尤豪杰	13912355844	6000	0	70	红包贷	每天	30	1500	0	225	4500	4725	顾峰		结清	续签	0	0
2017.7.11	钱彤	13915355327	6000	0	70	红包贷	每天	30	1500	0	225	4500	4725	王建杰		结清	续签	0	0
2017.7.12	王志强2316	1596186021	6000	260	0	打卡	每天	30	1800	0	600	4200	4800	信息部		结清	无锡	0	0
2017.7.12	潘海燕162x	13812054412	6000	360	0	打卡	每天	30	1800	0	600	4200	4800	朱新约		结清	无锡	0	0
2017.7.13	吴晓涓1529	15006187933	10000	500	0	打卡	每天	30	2500	0	1000	7500	8500	范利丽		结清	无锡	0	0
2017.7.14	陈洪伟1531	13584161296	6000	260	0	打卡	每天	30	2000	0	600	4000	4600	王建杰		结清	江阴	0	0
2017.7.15	蒋逸飞3014	13914280504	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	江韦		结清	江阴	0	0
2017.7.15	吴艳娟2527	15161562367	6000	260	0	打卡	每天	30	2000	0	600	4000	4600	朱新约		结清	无锡	0	0
2017.7.17	邹许峰7579	15261659721	8000	360	0	打卡	每天	30	2000	0	800	6000	6800	范利丽		结清	无锡	0	0
2017.7.20	王建2410	18888039689	6000	300	0	打卡	每天	30	1800	0	600	4200	4800	朱新约		结清	宜兴	0	0
2017.7.21	吴振瑞1278	13616168994	8000	340	0	打卡	每天	30	2000	0	800	6000	6800	李聪		结清	江阴	0	0
2017.7.21	陈锡峰2536	13771577922	8000	340	0	打卡	每天	30	2000	0	800	6000	6800	朱新约		结清	无锡	0	0
2017.7.21	张旭泓	13861894933	10000	120	0	打卡	每天	30	2000	0	400	8000	8400	信息部		结清	续签	0	0
2017.7.25	李文伟4931	13812098496	6000	250	0	打卡	每天	30	1900	0	600	4100	4700	朱新约		结清	安徽	0	0
2017.7.25	邵军勇7272	13814267552	8000	340	0	打卡	每天	30	2000	0	800	6000	6800	顾峰		结清	无锡	0	0
2017.7.28	张鑫	15951502526	6000	0	70	红包贷	每天	30	1500	0	225	4500	4725	李聪		结清	续签	0	0
2017.7.28	李朝清443x	18262297939	6000	260	0	打卡	每天	30	1800	0	600	4200	4800	朱新约		结清	无锡	0	0
2017.7.28	许春	15949228776	8000	0	80	红包贷	每天	30	2000	0	0	6000	6000	周依含		结清	续签	0	0
2017.7.31	沈娟7064	15250838079	6000	300	0	打卡	每天	25	1500	0	300	4500	4800	顾峰		结清	宜兴	0	0
2017.8.1	张艳6420	18012351561	6000	300	0	打卡	每天	25	1700	0	600	4300	4900	李聪		结清	安徽	0	0
2017.8.2	夏军2318	18961724557	5000	250	0	打卡	每天	25	1500	0	500	3500	4000	顾峰		结清	无锡北塘	0	0
2017.8.2	潘海燕162x	13812054412	6000	360	0	打卡	每天	20	1700	0	600	4300	4900	朱新约		结清	无锡南长	0	0
2017.8.4	周静444x	13921396555	20000	1000	0	打卡	每天	20	5500	0	1400	14500	15900	小五		结清		0	0
2017.8.5	王立仁001x	18018398956	6000	250	0	打卡	每天	30	1800	0	600	4200	4800	王建杰		结清	无锡梁溪	0	0
2017.8.6	过梅如0021	18020504995	8000	400	0	打卡	每天	25	2000	0	800	6000	6800	顾峰		结清	无锡滨湖	0	0
2017.8.8	杭洪明7995	15961500330	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	李聪		结清	宜兴周铁	0	0
2017.8.10	宋舜001x	18851576098	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	李聪		结清	无锡东亭	0	0
2017.8.11	唐建芬6922	13912590759	6000	260	0	打卡	每天	30	1200	0	600	4800	5400	顾峰		结清	无锡新区	0	0
2017.8.11	钱彤	13915355327	6000	0	70	红包贷	每天	30	1500	0	225	4500	4725	王建杰		结清	续签	0	0
2017.8.16	胡晓俊	18901517678	7000	300	0	打卡	每天	30	1800	0	400	5200	5600	顾峰		结清		0	0
2017.8.16	高东彪3412	13952472958	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	朱新约		结清	无锡长安	0	0
2017.8.16	华建征2010	13771112778	10000	300	0	打卡	每天	50	2500	0	1000	7500	8500	李聪		结清	无锡北塘	0	0
2017.8.16	卞燕斌5425	18861551811	10000	420	0	打卡	每天	30	2500	0	1000	7500	8500	李聪		结清	宜兴新建	0	0
2017.8.21	吴振瑞1278	13616168994	8000	300	0	打卡	每天	37	2000	0	400	6000	6400	信息部		结清	江阴夏港	0	0
2017.8.20	张旭泓	13861894933	10000	120	0	打卡	每天	30	2000	0	400	8000	8400	信息部		结清		0	0
2017.8.22	杨学祥1719	18762814235	8000	340	0	打卡	每天	30	2000	0	800	6000	6800	信息部		结清	射阳	0	0
2017.8.22	潘海燕162x	13812054412	6000	300	0	打卡	每天	25	1600	0	600	4400	5000	朱新约		结清	无锡南长	0	0
2017.8.24	陈建伟2217	13861793730	8000	300	0	打卡	每天	35	2000	0	800	6000	6800	顾峰		结清		0	0
2017.8.24	宫秀营2049	15006156306	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	顾峰		结清		0	0
2017.8.26	方明4216	18351574345	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	李聪		结清	无锡洛社	0	0
2017.8.28	张忠2477	13961853599	6000	260	0	打卡	每天	30	2000	0	600	4000	4600	江韦		结清	无锡东港	0	0
2017.8.29	陈键新0617	13961791743	8000	500	0	打卡	每天	20	2000	0	800	6000	6800	朱新约		结清	无锡方巷	0	0
2017.8.31	王照远3812	13382896555	20000	1000	0	打卡	每天	21	5000	0	1500	15000	16500	李聪		结清	无锡复地	0	0
2017.8.31	唐建中4873	13906158002	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	朱新约		结清	无锡恒大绿洲	0	0
2017.9.2	过梅如0021	18914122251	8000	400	0	打卡	每天	25	1800	0	800	6200	7000	顾峰		结清	无锡滨湖	0	0
2017.9.4	胡松喜3452	13951589443	6000	250	0	打卡	每天	30	1800	0	600	4200	4800	朱新约		结清	无锡长安	0	0
2017.9.5	袁群岚0029	15852518109	10000	500	0	打卡	每天	22	1500	0	1000	8500	9500	顾峰		结清	无锡黄巷	0	0
2017.9.13	沈娟7064	15250838079	8000	350	0	打卡	每天	30	2000	0	400	6000	6400	顾峰		结清	宜兴和桥	0	0
2017.9.14	王文波6561	15061506037	6000	400	0	打卡	每天	20	2000	0	0	4000	4000	信息部		结清	无锡胡埭	0	0
2017.9.15	钱彤0535	13915355327	8000	350	0	打卡	每天	30	2000	0	400	6000	6400	王建杰		结清	无锡风雷	0	0
2017.9.18	郑雅文2265	15961729621	10000	500	0	打卡	每天	22	1500	0	1000	8500	9500	信息部		结清	无锡锡山	0	0
2017.9.19	陈建新0617	13961791743	6000	300	0	打卡	每天	30	1200	0	600	4800	5400	朱新约		结清	无锡梁溪	0	0
2017.9.26	过梅如0021	18914122251	8000	400	0	打卡	每天	25	1600	0	800	6400	7200	顾峰		结清	无锡滨湖	0	0
2017.9.21	张旭泓	13861894933	10000	120	0	打卡	每天	30	2000	0	400	8000	8400	信息部		结清	续签	0	0
2017.9.27	王明路2512	13812053915	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	范利丽		结清	无锡北塘	0	0
2017.9.29	胡松喜3452	13951589443	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	朱新约		结清	无锡长安	0	0
2017.9.29	张敏强2518	15251689762	8000	100	0	打卡	每天	30	2000	0	800	6000	6800	范利丽		结清	无锡	0	0
2017.10.6	黄明霞4441	13921135645	7000	310	0	打卡	每天	30	2000	0	700	5000	5700	江韦		结清	无锡洛社	0	0
2017.10.10	朱寿贵2015	15861577138	10000	500	0	打卡	每天	22	1500	0	1000	8500	9500	李聪		结清	无锡惠山	0	0
2017.10.12	沈娟7064	15250838079	8000	350	0	打卡	每天	20	2000	0	400	6000	6400	顾峰		结清	宜兴	0	0
2017.10.20	岳晓春0015	15061820072	6000	260	0	打卡	每天	30	1800	0	600	4200	4800	朱新约		结清	无锡滨湖	0	0
2017.10.23	吕春一4446	13814267527	10000	500	0	打卡	每天	22	1500	0	1000	8500	9500	顾峰		结清	无锡惠山	0	0
2017.10.25	张珂2212	13861888150	10000	500	0	打卡	每天	22	1500	0	1000	8500	9500	范利丽		结清	无锡崇安	0	0
2017.10.26	高俊501x	18351571823	6000	260	0	打卡	每天	30	2000	0	600	4000	4600	范利丽		结清	无锡惠山	0	0
2017.10.27	沈杰3612	13812106475	10000	500	0	打卡	每天	22	1500	0	1000	8500	9500	朱新约		结清	江阴	0	0
2017.10.30	吴小涓1529	18262262094	10000	500	0	打卡	每天	22	1500	0	1000	8500	9500	范利丽		结清	无锡滨湖	0	0
2017.11.1	马佳5019	13901394668	10000	1000	0	打卡	每天	10	1500	0	500	8500	9000	朱新约		结清	无锡崇安	0	0
2017.11.2	过梅如0021	18914122251	8000	400	0	打卡	每天	25	2000	0	800	6000	6800	顾峰		结清	无锡滨湖	0	0
2017.11.2	钱乃龙1317	15949259710	6000	250	0	打卡	每天	30	2000	0	0	4000	4000	信息部		结清	高邮	0	0
2017.11.4	张雪影5525	18861745249	8000	350	0	打卡	每天	30	1200	0	0	6800	6800	信息部		结清	安徽	0	0
2017.11.6	杭洪明7995	15961500330	6000	300	0	打卡	每天	25	2000	0	300	4000	4300	李聪		结清	宜兴	0	0
2017.11.8	周圆圆2326	15895335514	8000	400	0	打卡	每天	25	2000	0	400	6000	6400	范利丽		结清	无锡	0	0
2017.11.10	马佳5019	13901394668	15000	15000	0	打卡	每天	10	3500	0	500	11500	12000	朱新约		结清	无锡崇安	0	0
2017.11.13	吕春一4446	13814267527	15000	800	0	打卡	每天	21	3500	0	1100	11500	12600	范利丽		结清	无锡惠山	0	0
2017.11.17	宋富强651x	13621517596	6000	400	0	打卡	每天	21	2000	0	400	4000	4400	范利丽		结清	无锡滨湖	0	0
2017.11.20	沈杰3612	13812106475	10000	500	0	打卡	每天	22	1500	0	750	8500	9250	朱新约		结清	江阴	0	0
2017.11.21	姚昱3039	13912365631	10000	1000	0	打卡	每天	11	2000	0	700	8000	8700	信息部		结清	无锡惠山	0	0
2017.11.23	张雪影5525	18861745249	7000	500	0	打卡	每天	20	1800	0	100	5200	5300	信息部		结清	安徽	0	0
2017.11.24	顾晓东2338	18651009857	6000	400	0	打卡	每天	20	1800	0	420	4200	4620	李聪		结清	无锡南长	0	0
2017.11.27	陆文娟0409	15161510359	6000	400	0	打卡	每天	20	1800	0	200	4200	4400	李聪		结清	无锡锡山	0	0
2017.11.27	周圆圆2326	15895335514	15000	900	0	打卡	每天	20	4000	0	1100	11000	12100	范利丽		结清	无锡梁溪	0	0
2017.11.30	高俊501x	18351571823	6000	200	0	打卡	每天	40	1800	0	420	4200	4620	范利丽		结清	无锡惠山	0	0
2017.12.2	吕春一4446	13814267527	20000	1000	0	打卡	每天	22	3500	0	1300	16500	17800	范利丽		结清	无锡惠山	0	0
2017.12.5	许雪0925	13814240427	10000	500	0	打卡	每天	21	1500	0	750	8500	9250	罗		结清	无锡南长	0	0
2017.12.8	陆炎冰2015	13771086777	20000	300	0	打卡	每天	30	5000	0	1500	15000	16500	王龙		结清	无锡新区	0	0
2017.12.8	张雪影5525	18861745249	7000	500	0	打卡	每天	20	1500	0	0	5500	5500	信息部		结清	安徽	0	0
2017.12.11	周圆圆2326	15895335514	6000	350	0	打卡	每天	20	1800	0	200	4200	4400	范利丽		结清	无锡梁溪	0	0
2017.12.12	顾晓东2338	18651009857	6000	400	0	打卡	每天	20	1500	0	300	4500	4800	李聪		结清	无锡南长	0	0
2017.2.16	杨斌杰	13921134538	20000	667	560	零用贷	周三	30	0	0	2000	15000	17000			结清		0	0
2017.2.16	朱悠晨	18651026358	6000	300	210	零用贷	周三	20	0	0	600	4200	4800			结清		0	0
2017.2.16	高合成	13906186560	10000	500	280	零用贷	周三	20	0	0	1000	7000	8000			结清		0	0
2017.2.16	史正和	13915353338	8000	400	300	零用贷	周三	20	0	0	800	5700	6500			结清		0	0
2017.2.17	徐爱峰	13921339157	8000	400	300	零用贷	周四	20	0	0	800	5500	6300		成乃柏	结清		0	0
2017.2.17	毛卉	15961732269	20000	1000	630	零用贷	周四	20	0	0	2000	15000	17000		陆春松	结清		0	0
2017.2.18	李江	13861832716	10000	500	280	零用贷	周五	20	0	0	1000	7500	8500		陆春松	结清		0	0
2017.2.18	陈瑶	18795626947	6000	200	240	零用贷	周五	30	0	0	600	4200	4800			结清		0	0
2017.2.20	陆松	15861698499	6000	300	240	零用贷	周一	20	0	0	600	4200	4800		成乃柏	结清		0	0
2017.2.20	肖东	13861768903	15000	1500	550	零用贷	周一	10	0	0	1500	10600	12100		夏飞	结清		0	0
2017.2.20	姚伟星	18706184881	20000	2000	560	零用贷	周一	10	0	0	2000	14000	16000		成乃柏	结清		0	0
2017.2.21	任红	15961835791	10000	1000	300	零用贷	周一	10	0	0	1000	7500	8500		陆春松	结清		0	0
2017.2.21	钱慰	13003383709	15000	1500	420	零用贷	周一	10	0	0	1500	10600	12100		陆春松	结清		0	0
2017.2.22	陆文勇	13515193718	6000	300	210	零用贷	周二	20	0	0	600	4000	4600		成乃柏	结清		0	0
2017.2.22	黄芳	13861711875	15000	750	450	零用贷	周一	20	0	0	1500	10500	12000			结清		0	0
2017.2.23	任晓强	13601525697	15000	750	420	零用贷	周三	20	0	0	1500	11100	12600			结清		0	0
2017.2.23	温玉好	13584112016	8000	534	346	零用贷	周四	15	0	0	800	5500	6300			结清		0	0
2017.2.23	杨晓芙	18021190752	6000	200	210	零用贷	周三	30	0	0	600	4200	4800		成乃柏	结清		0	0
2017.2.24	张凌燕	15050676648	10000	334	281	零用贷	周四	30	0	0	1000	7500	8500			结清		0	0
2017.2.24	陈建伟	13861793730	8000	400	280	零用贷	周四	20	0	0	800	5500	6300			结清		0	0
2017.2.24	杨巧	15371080028	5000	250	175	零用贷	周四	20	0	0	500	3150	3650		陆春松	结清		0	0
2017.2.25	杜为敏	15716199872	6000	300	210	零用贷	周五	20	0	0	600	4500	5100			结清		0	0
2017.2.25	黄浩东	15052225927	6000	200	210	零用贷	周五	30	0	0	600	4500	5100			结清		0	0
2017.2.27	顾芸	13921196878	6000	300	210	零用贷	周一	20	0	0	600	4200	4800			结清		0	0
2017.2.28	庄登雲	13961711998	15000	750	550	零用贷	周二	20	0	0	1500	11500	13000			结清		0	0
2017.2.28	刘东	18951578523	8000	400	196	零用贷	周一	20	0	0	800	5900	6700		成乃柏	结清		0	0
2017.2.28	蒋晓飞	13912362708	10000	334	350	零用贷	周一	30	0	0	1000	7500	8500			结清		0	0
2017.3.1	潘治江	17701571888	10000	334	281	零用贷	周三	30	0	0	1000	7500	8500		陆春松	结清		0	0
2017.3.1	胡晓俊	18901517678	6000	200	200	零用贷	周二	30	0	0	600	4400	5000			结清		0	0
2017.3.2	邹许峰	15261659721	10000	500	300	零用贷	周三	20	0	0	1000	7500	8500			结清		0	0
2017.3.3	邵凌	15852804328	6000	600	210	零用贷	周四	10	0	0	600	4200	4800		成乃柏	结清		0	0
2017.3.3	顾嘉洋	13616190461	10000	334	281	零用贷	周四	30	0	0	1000	7500	8500			结清		0	0
2017.3.3	徐建成	13405751050	10000	500	280	零用贷	周四	20	0	0	1000	7500	8500		陆春松	结清		0	0
2017.3.5	杨阳	15062328326	8000	800	280	零用贷	周五	10	0	0	800	5700	6500			结清		0	0
2017.3.5	黄君	13812182409	6000	200	300	零用贷	周五	30	0	0	600	4000	4600			结清		0	0
2017.3.6	周文斌	18762682167	8000	400	225	零用贷	周一	20	0	0	800	6200	7000			结清		0	0
2017.3.6	李海魏	15895378769	10000	500	320	零用贷	周一	20	0	0	1000	7500	8500		陆春松	结清		0	0
2017.3.6	韩忠	15298404344	8000	400	300	零用贷	周一	20	0	0	800	5700	6500			结清		0	0
2017.3.6	李长啸	13218465023	6000	200	210	零用贷	周一	30	0	0	600	4000	4600			结清		0	0
2017.3.7	陈卫	18861705997	6000	300	380	零用贷	周一	20	0	0	600	4000	4600			结清		0	0
2017.3.8	谢寿清	13961781663	8000	400	500	零用贷	周二	20	0	0	800	5800	6600			结清		0	0
2017.3.8	韦国盛	18961818108	8000	800	350	零用贷	周二	10	0	0	800	5800	6600		成乃柏	结清		0	0
2017.3.8	钱俊	13812578470	8000	400	270	零用贷	周二	20	0	0	800	6000	6800		夏飞	结清		0	0
2017.3.9	惠元峰	15961730333	6000	600	260	零用贷	周三	10	0	0	600	3400	4000			结清		0	0
2017.3.10	王春	15261576915	8000	400	230	零用贷	周四	20	0	0	800	5800	6600			结清		0	0
2017.3.11	朱震球	18936070873	14000	700	550	零用贷	周五	20	0	0	1400	10000	11400		陆春松	结清		0	0
2017.3.11	张衡	18100657556	15000	750	420	零用贷	周五	20	0	0	1500	11800	13300		成乃柏	结清		0	0
2017.3.13	钟克喜	15906178577	6000	300	250	零用贷	周一	20	0	0	600	4200	4800			结清		0	0
2017.3.13	吴定荣	13115074999	10000	667	563	零用贷	周一	15	0	0	1000	7500	8500		陆春松	结清		0	0
2017.3.14	陶莉	13861720707	6000	300	200	零用贷	周一	20	0	0	600	4200	4800			结清		0	0
2017.3.14	孙磊	13485033018	6000	300	250	零用贷	周一	20	0	0	600	4200	4800			结清		0	0
2017.3.14	王伟	13812064323	10000	500	300	零用贷	周一	20	0	0	1000	7500	8500		成乃柏	结清		0	0
2017.3.16	王生华	13914251473	7000	350	250	零用贷	周三	20	0	0	700	5100	5800			结清		0	0
2017.3.16	范海舟	13151008798	8000	400	280	零用贷	周三	20	0	0	800	6000	6800			结清		0	0
2017.3.16	杨灿英	15006153252	6000	200	190	零用贷	周三	30	0	0	600	4300	4900			结清		0	0
2017.3.17	包德新	15061587083	10000	500	280	零用贷	周四	20	0	0	1000	7500	8500		成乃柏	结清		0	0
2017.3.17	宋颖超	15995344333	10000	0	300	零用贷	周四	20	0	0	1000	7500	8500		陆春松	结清		0	0
2017.3.18	曾建平	13585085307	10000	334	316	零用贷	周五	30	0	0	1000	7000	8000		陆春松	结清		0	0
2017.3.18	蒋国栋	13961853984	6000	0	220	零用贷	周五	20	0	0	600	4200	4800			结清		0	0
2017.3.18	陈晓峰	15951500849	6000	600	300	零用贷	周五	10	0	0	600	4500	5100			结清		0	0
2017.3.19	蒋秋园	13961556888	15000	750	420	零用贷	周五	20	0	0	1500	11500	13000			结清		0	0
2017.3.20	张伯平	15895360479	6000	300	210	零用贷	周一	20	0	0	600	4200	4800			结清		0	0
2017.3.20	李淼	13961795799	20000	1334	626	零用贷	周一	15	0	0	2000	15000	17000			结清		0	0
2017.3.20	陆磊	15251604408	6000	300	180	零用贷	周二	20	0	0	600	4200	4800			结清		0	0
2017.3.21	吴亮	18861577909	10000	500	280	零用贷	周一	20	0	0	1000	8000	9000		陆春松	结清		0	0
2017.3.21	周鹏飞	18626053632	6000	300	210	零用贷	周一	20	0	0	600	4200	4800			结清		0	0
2017.3.23	吴洪芹	18352568650	8000	800	300	零用贷	周三	10	0	0	800	5800	6600		成乃柏	结清		0	0
2017.3.23	陈洪亮	13771023336	6000	300	250	零用贷	周三	20	0	0	600	3200	3800		成乃柏	结清		0	0
2017.3.23	张涛	13400002828	6000	300	170	零用贷	周三	20	0	0	600	3600	4200		陆春松	结清		0	0
20107.3.24	许锬	13093081783	15000	1500	470	零用贷	周四	10	0	0	1500	11500	13000			结清		0	0
2017.3.25	鲍海	13812272802	8000	400	300	零用贷	周五	20	0	0	800	4800	5600			结清		0	0
2017.3.25	臧海	15261554361	6000	300	210	零用贷	周五	20	0	0	600	4200	4800			结清		0	0
2017.3.25	徐秋稔	13771470921	5000	250	180	零用贷	周五	20	0	0	500	3100	3600		成乃柏	结清		0	0
2017.3.25	顾慧芳	15261530281	8000	400	300	零用贷	周五	20	0	0	800	6000	6800			结清		0	0
2017.3.27	陈伟强	18552128264	5000	250	180	零用贷	周一	20	0	0	500	3500	4000			结清		0	0
2017.3.27	王钢	15061789109	10000	500	300	零用贷	周一	20	0	0	1000	4500	5500		陆春松	结清		0	0
2017.3.27	牛宝华	15852833977	8000	400	230	零用贷	周一	20	0	0	800	6000	6800			结清		0	0
2017.3.28	张健	13861798759	8000	400	240	零用贷	周五	20	0	0	800	5900	6700		成乃柏	结清		0	0
2017.3.28	华明秋	13921363733	20000	1000	600	零用贷	周一	20	0	0	2000	15500	17500			结清		0	0
2017.3.29	杨阳	18352555359	8000	267	293	零用贷	周二	30	0	0	800	6000	6800			结清		0	0
2017.3.29	薛皇红177x	18362356013	8000	400	300	零用贷	周二	20	0	0	800	5500	6300		成乃柏	结清		0	0
2017.3.30	胡建新	13915348602	7000	350	370	零用贷	周三	20	0	0	700	5200	5900			结清		0	0
2017.3.30	李晓靓	13861774238	6000	600	250	零用贷	周三	10	0	0	600	4500	5100			结清		0	0
2017.3.31	王代琳	13912352252	10000	500	250	零用贷	周四	20	0	0	1000	7200	8200			结清		0	0
2017.4.1	张敏	15061875998	8000	534	316	零用贷	周五	15	0	0	800	6000	6800			结清		0	0
2017.4.5	顾洪强	18118893027	10000	500	170	零用贷	周三	20	0	0	1000	8000	9000			结清		0	0
2017.4.5	王剑	13912979000	15000	500	420	零用贷	周二	30	0	0	1500	12000	13500			结清		0	0
2017.4.5	刘希	13771180529	10000	500	300	零用贷	周二	20	0	0	1000	7500	8500			结清		0	0
2017.4.6	虞晓	15161597522	7000	350	200	零用贷	周三	20	0	0	700	5000	5700		陆春松	结清		0	0
2017.4.7	邵栋良	13961715078	10000	1000	280	零用贷	周四	10	0	0	1000	8000	9000			结清		0	0
2017.4.7	顾雪枫	13013619808	6000	400	250	零用贷	周四	15	0	0	600	4400	5000			结清		0	0
2017.4.8	孔文平	13404288970	10000	500	350	零用贷	周五	20	0	0	1000	7500	8500		陆春松	结清		0	0
2017.4.8	刘涛	18018304692	6000	600	210	零用贷	周五	10	0	0	600	4300	4900		成乃柏	结清		0	0
2017.4.10	肖东	13861768903	20000	2000	700	零用贷	周一	10	0	0	2000	15000	17000			结清		0	0
2017.4.11	储开成	15152224086	6000	300	250	零用贷	周一	20	0	0	600	4300	4900		陆春松	结清		0	0
2017.4.11	丁奔	18921186565	10000	334	286	零用贷	周一	30	0	0	1000	8000	9000			结清		0	0
2017.4.12	钱斌	18068263483	10000	500	300	零用贷	周二	20	0	0	1000	7500	8500		陆春松	结清		0	0
2017.4.13	柏文龙	13585047511	6000	200	210	零用贷	周三	30	0	0	600	4500	5100			结清		0	0
2017.4.13	万青青	15251613756	8000	267	283	零用贷	周三	30	0	0	800	5800	6600			结清		0	0
2017.4.14	王建英	15995330897	10000	500	350	零用贷	周四	20	0	0	1000	7000	8000			结清		0	0
2017.4.15	陶国清y	13812180924	6000	300	400	零用贷	周五	20	0	0	600	4300	4900		成乃柏	结清		0	0
2017.4.15	李宁峰y	13400022910	6000	300	200	零用贷	周五	20	0	0	600	4200	4800			结清		0	0
2017.4.17	钱慰	13003383709	20000	2000	600	零用贷	周一	10	0	0	2000	15000	17000			结清		0	0
2017.4.19	任立楷	15951567929	8000	400	600	零用贷	周二	20	0	0	800	6000	6800		成乃柏	结清		0	0
2017.4.20	管一蔚	13771139663	8000	400	280	零用贷	周三	20	0	0	800	5500	6300			结清		0	0
2017.4.20	吴孝荣	13812005103	6000	300	200	零用贷	周三	20	0	0	600	3300	3900		成乃柏	结清		0	0
2017.4.20	徐虹	13861822137	10000	334	366	零用贷	周三	30	0	0	1000	7000	8000		成乃柏	结清		0	0
2017.4.20	韩敏	13921125456	8000	400	280	零用贷	周三	20	0	0	800	6000	6800			结清		0	0
2017.4.21	韦国盛	18961818108	8000	400	350	零用贷	周四	20	0	0	800	6200	7000			结清		0	0
2017.4.22	赵斌	15961899346	8000	400	280	零用贷	周五	20	0	0	800	3000	3800			结清		0	0
2017.4.22	沈田庆	15052287601	4000	160	230	零用贷	周五	25	0	0	400	2700	3100			结清		0	0
2017.4.23	金荣华	13961666221	10000	1000	350	零用贷	周五	10	0	0	1000	7500	8500		成乃柏	结清		0	0
2017.4.24	吴晓龙	13771443133	6000	200	250	零用贷	周一	30	0	0	600	4200	4800			结清		0	0
2017.4.24	王飞	15161621237	10000	1000	350	零用贷	周一	10	0	0	1000	7500	8500			结清		0	0
2017.4.25	程鑫	13921503708	6000	600	250	零用贷	周一	10	0	0	600	4200	4800		成乃柏	结清		0	0
2017.4.27	刘军	18914163867	10000	334	296	零用贷	周三	30	0	0	1000	7500	8500		成乃柏	结清		0	0
2017.4.27	王海英	13861608899	10000	500	300	零用贷	周三	20	0	0	1000	7500	8500		陆春松	结清		0	0
2017.4.27	沈君兰	18651513954	6000	200	250	零用贷	周三	30	0	0	600	4200	4800		成乃柏	结清		0	0
2017.4.28	孙翼飞	18921399226	15000	750	450	零用贷	周四	20	0	0	1500	11000	12500			结清		0	0
2017.4.28	胡俊强	18921318791	8000	800	250	零用贷	周五	10	0	0	800	6000	6800			结清		0	0
2017.4.28	李江	13861832716	10000	1000	300	零用贷	周四	10	0	0	1000	7000	8000			结清		0	0
2017.4.28	杜秋花	13861624731	6000	200	210	零用贷	周四	30	0	0	600	4300	4900		陆春松	结清		0	0
2017.4.29	徐玲	18118118755	10000	334	350	零用贷	周五	30	0	0	1000	7500	8500		成乃柏	结清		0	0
2017.4.29	沈超	13861498840	12000	400	420	零用贷	周五	30	0	0	1200	9200	10400		成乃柏	结清		0	0
2017.5.2	季韦伟5430	15995265540	10000	500	350	零用贷	周一	20	0	0	1000	7500	8500			结清		0	0
2017.5.4	黄建武	13921143235	8000	400	280	零用贷	周三	20	0	0	800	5600	6400		成乃柏	结清		0	0
2017.5.4	荣健	13395198532	8000	267	283	零用贷	周三	30	0	0	800	5600	6400			结清		0	0
2017.5.6	唐胤	13812011969	20000	667	563	零用贷	周一	30	0	0	2000	16000	18000		陆春松	结清		0	0
2017.5.6	蔡雯雯	15061843685	6000	200	170	零用贷	周五	30	0	0	600	4300	4900			结清		0	0
2017.5.9	王俊晓	13812033833	8000	400	280	零用贷	周一	20	0	0	800	6000	6800			结清		0	0
2017.5.9	陶长彬	15961775195	6000	600	300	零用贷	周一	10	0	0	600	4500	5100			结清		0	0
2017.5.9	陈晓峰	15951500849	8000	800	300	零用贷	周一	10	0	0	800	6000	6800			结清		0	0
2017.5.10	刘东	18951578523	10000	500	280	零用贷	周二	20	0	0	1000	7500	8500			结清		0	0
2017.5.10	王建荣	13861810853	10000	500	300	零用贷	周二	20	0	0	1000	7500	8500			结清		0	0
2017.5.11	黄斌	13771204006	8000	400	280	零用贷	周三	20	0	0	800	5800	6600			结清		0	0
2017.5.11	李淼	13961795799	20000	2000	560	零用贷	周一	10	0	0	2000	15000	17000		成乃柏	结清		0	0
2017.5.14	祝培勇	13806184998	20000	1000	420	零用贷	周五	20	0	0	2000	14000	16000		陆春松	结清		0	0
2017.5.15	周文斌	18762682167	10000	500	300	零用贷	周一	20	0	0	1000	8000	9000			结清		0	0
2017.5.15	孙凌1011	13812298959	6000	200	300	零用贷	周一	30	0	0	600	4300	4900		陆春松	结清		0	0
2017.5.17	胡晓俊6015	18901517678	10000	334	286	零用贷	周二	30	0	0	1000	7800	8800			结清		0	0
2017.5.17	陶勇	15365253165	10000	500	300	零用贷	周二	20	0	0	1000	7000	8000		成乃柏	结清		0	0
2017.5.17	蒋靖	18921167987	12000	600	500	零用贷	周二	20	0	0	1200	8400	9600			结清		0	0
2017.5.18	王晓巍	15052257430	6000	300	380	零用贷	周三	20	0	0	600	4200	4800			结清		0	0
2017.5.18	郭勇0219	18801510477	8000	400	230	零用贷	周三	20	0	0	800	5800	6600		成乃柏	结清		0	0
2017.5.18	陆正伟	13771092513	8000	400	230	零用贷	周一	20	0	0	800	6000	6800			结清		0	0
2017.5.19	周健	13861804447	6000	300	200	零用贷	周五	20	0	0	600	4000	4600			结清		0	0
2017.5.19	徐爱峰	13921339157	14000	700	440	零用贷	周四	20	0	0	1400	10500	11900			结清		0	0
2017.5.20	张金龙	13915288214	6000	300	200	零用贷	周五	20	0	0	600	4200	4800		成乃柏	结清		0	0
2017.5.24	朱悠晨	18651026358	10000	500	350	零用贷	周二	20	0	0	1000	7500	8500			结清		0	0
2017.5.24	吴晓忠	15061893580	10000	334	416	零用贷	周二	30	0	0	1000	7000	8000		成乃柏	结清		0	0
2017.5.24	钱淼	13814296175	6000	200	200	零用贷	周二	30	0	0	600	4200	4800		陆春松	结清		0	0
2017.5.25	徐伟	13921101123	10000	500	300	零用贷	周三	20	0	0	1000	7500	8500		陆春松	结清		0	0
2017.5.27	邓正卫	13861803876	6000	300	220	零用贷	周五	20	0	0	600	4300	4900			结清		0	0
2017.5.27	庄登雲	13961711998	10000	500	280	零用贷	周五	20	0	0	1000	7500	8500			结清		0	0
2017.5.27	王益新	13601486065	7000	350	250	零用贷	周五	20	0	0	700	5000	5700			结清		0	0
2017.5.28	蒋峰	13093095112	25000	834	566	零用贷	周五	30	0	0	2500	20000	22500			结清		0	0
2017.5.31	徐真	13861474311	8000	400	300	零用贷	周二	20	0	0	800	5800	6600		成乃柏	结清		0	0
2017.5.31	殷海洪	18351585557	6000	300	210	零用贷	周二	20	0	0	600	4200	4800			结清		0	0
2017.5.31	徐浩	15306180069	6000	300	160	零用贷	周二	20	0	0	600	4500	5100			结清		0	0
2017.6.2	许云磊	18021563190	8000	534	276	零用贷	周四	15	0	0	800	6000	6800		陆春松	结清		0	0
2017.6.2	唐晨浩	18751545382	15000	500	420	零用贷	周四	30	0	0	1500	11500	13000			结清		0	0
2017.6.5	堵圣杰	15961749119	6000	240	210	零用贷	周一	25	0	0	600	4300	4900			结清		0	0
2017.6.5	汪姗姗	15152212323	10000	334	286	零用贷	周一	30	0	0	1000	7500	8500			结清		0	0
2017.6.5	高璟	18706188815	10000	500	280	零用贷	周一	20	0	0	1000	8000	9000			结清		0	0
2017.6.5	孙喆鸣	13961739348	10000	500	300	零用贷	周日	20	0	0	1000	7500	8500			结清		0	0
2017.6.6	章伟文	13861819730	6000	300	220	零用贷	周一	20	0	0	600	3160	3760			结清		0	0
2017.6.6	王健农	18115767075	5000	250	180	零用贷	周一	20	0	0	500	3700	4200			结清		0	0
2017.6.6	赵雪琪	18706150550	6000	200	250	零用贷	周一	30	0	0	600	4200	4800		                    	结清		0	0
2017.6.6	王芸倩	15961708187	6000	600	280	零用贷	周一	10	0	0	600	4500	5100			结清		0	0
2017.6.8	周金华	13861778249	10000	1000	350	零用贷	周三	10	0	0	1000	7000	8000			结清		0	0
2017.6.8	胡佳	15152277502	6000	300	210	零用贷	周三	20	0	0	600	4300	4900			结清		0	0
2017.6.9	朱丽芬	13665188750	10000	334	286	零用贷	周四	30	0	0	1000	7500	8500			结清		0	0
2017.6.12	陈晓峰	15951500849	10000	1000	400	零用贷	周一	10	0	0	1000	7500	8500		成乃柏	结清		0	0
2017.6.12	史爱艳	18362392810	7000	234	196	零用贷	周一	30	0	0	700	5500	6200		周诗华	结清		0	0
2017.6.13	陆晓灵	13771597688	10000	500	350	零用贷	周二	20	0	0	1000	7500	8500			结清		0	0
2017.6.13	朱未明	15152238787	8000	267	283	零用贷	周一	30	0	0	800	4800	5600		成乃柏	结清		0	0
2017.6.14	陈佳	18306197364	8000	267	233	零用贷	周二	30	0	0	800	5800	6600		周诗华	结清		0	0
2017.6.15	杭洪明	15961500330	8000	800	330	零用贷	周三	10	0	0	800	6000	6800			结清		0	0
2017.6.16	赵振华	15995204929	6000	600	200	零用贷	周四	10	0	0	600	4500	5100			结清		0	0
2017.6.18	刘文彬	13771002178	10000	500	300	零用贷	周五	20	0	0	1000	7500	8500		周诗华	结清		0	0
2017.6.19	钱慰	13003383709	20000	2000	600	零用贷	周一	10	0	0	2000	15000	17000			结清		0	0
2017.6.19	姜金东	15161605357	8000	200	280	零用贷	周一	40	0	0	800	6000	6800		成乃柏	结清		0	0
2017.6.19	钟克喜	15906178577	8000	400	280	零用贷	周一	20	0	0	800	6000	6800			结清		0	0
2017.6.21	潘文祥	15852543197	5000	250	180	零用贷	周二	20	0	0	500	3500	4000			结清		0	0
2017.6.21	徐建成	13405751050	10000	500	280	零用贷	周二	20	0	0	1000	7500	8500		结清续签	结清		0	0
2017.6.22	刘宏	15949259908	6000	300	200	零用贷	周三	20	0	0	600	4200	4800			结清		0	0
2017.6.22	李江	13861832716	15000	750	520	零用贷	周三	20	0	0	1500	11200	12700		成乃柏	结清		0	0
2017.6.22	李用国	15895369580	10000	500	280	零用贷	周三	20	0	0	1000	7500	8500		成乃柏	结清		0	0
2017.6.23	钱敏	15365275285	7000	350	250	零用贷	周四	20	0	0	700	5000	5700		成乃柏	结清		0	0
2017.6.23	隆烨洲	15251558353	10000	1000	350	零用贷	周四	10	0	0	1000	7000	8000			结清		0	0
2017.6.25	顾洪强	18118893027	8000	267	224	零用贷	周五	30	0	0	800	6000	6800			结清		0	0
2017.6.26	徐剑	18015374090	10000	500	350	零用贷	周一	20	0	0	1000	7500	8500			结清		0	0
2017.6.26	朱伟峰	15006195495	10000	500	500	零用贷	周一	20	0	0	1000	7500	8500			结清		0	0
2017.6.27	董翠波	13814241683	5000	334	216	零用贷	周一	15	0	0	500	3000	3500		成乃柏	结清		0	0
2017.6.27	吴定荣	13115074999	10000	667	503	零用贷	周一	15	0	0	1000	7500	8500			结清		0	0
2017.6.28	过红卫	13621518633	6000	400	250	零用贷	周二	15	0	0	600	4200	4800			结清		0	0
2017.6.28	潘治江	17701571888	20000	667	563	零用贷	周二	30	0	0	2000	15000	17000			结清		0	0
2017.6.29	蒋冠华	15312483932	10000	334	316	零用贷	周二	30	0	0	1000	7500	8500			结清		0	0
2017.6.29	周圆圆	15895335514	15000	500	420	零用贷	周三	30	0	0	1500	11200	12700		成乃柏	结清		0	0
2017.6.29	黄洁	15950145161	20000	667	563	零用贷	周三	30	0	0	2000	15000	17000			结清		0	0
2017.6.30	沈利峰	13665199991	6000	400	200	零用贷	周五	15	0	0	600	3900	4500			结清		0	0
2017.6.30	包德新	15061587083	10000	500	280	零用贷	周四	20	0	0	1000	7500	8500			结清		0	0
2017.7.1	王建267x	15106195852	10000	500	350	零用贷	周五	20	0	0	1000	7500	8500			结清		0	0
2017.7.1	许溢2529	15961895593	6000	400	210	零用贷	周五	15	0	0	600	4300	4900			结清		0	0
2017.7.3	王耀良2512	15961850045	5000	334	216	零用贷	周一	15	0	0	500	2500	3000			结清		0	0
2017.7.3	杨晓芙4825	18021190752	6000	200	210	零用贷	周一	30	0	0	600	4200	4800			结清		0	0
2017.7.3	张丽4248	13771373108	5000	334	216	零用贷	周一	15	0	0	500	3500	4000			结清		0	0
2017.7.4	郭洪军1018	13985016959	12000	600	420	零用贷	周一	20	0	0	1200	7800	9000		周诗华	结清		0	0
2017.7.5	叶伟锋6914	13616186372	10000	500	350	零用贷	周二	20	0	0	1000	7500	8500			结清		0	0
2017.7.5	邹秋明7310	18036008380	6000	300	250	零用贷	周二	20	0	0	600	4300	4900			结清		0	0
2017.7.7	王宇2315	18118885757	10000	1000	350	零用贷	周四	10	0	0	1000	7500	8500			结清		0	0
2017.7.9	周勰0619	13806157086	6000	300	210	零用贷	周五	20	0	0	600	4400	5000		周诗华	结清		0	0
2017.7.10	邢连满0636	17081658782	6000	300	210	零用贷	周一	20	0	0	600	4400	5000		成乃柏	结清		0	0
2017.7.10	沈建华6954	15806173777	8000	534	306	零用贷	周一	15	0	0	800	6000	6800		周诗华	结清		0	0
2017.7.10	徐凤娟1824	13861879393	6000	300	290	零用贷	周一	20	0	0	600	4400	5000		成乃柏	结清		0	0
2017.7.11	刘希4126	13771180529	10000	500	300	零用贷	周一	20	0	0	1000	7800	8800			结清		0	0
2017.7.12	王靖昌1215	18658828725	8000	400	330	零用贷	周二	20	0	0	800	6000	6800		成乃柏	结清		0	0
2017.7.12	吴颖2468	15961707083	6000	300	250	零用贷	周二	20	0	0	600	4400	5000			结清		0	0
2017.7.12	陈云超2575	13921173170	6000	300	250	零用贷	周二	20	0	0	600	4200	4800			结清		0	0
2017.7.13	蒋宇盛0513	18251572259	8000	400	340	零用贷	周三	20	0	0	800	5800	6600			结清		0	0
2017.7.14	钱乃龙1317	15949259710	10000	334	286	零用贷	周四	30	0	0	1000	7900	8900		成乃柏	结清		0	0
2017.7.14	司马贤1399	15806192167	10000	500	400	零用贷	周四	20	0	0	1000	7900	8900			结清		0	0
2017.7.15	华锦阳1397	13961733323	8000	800	300	零用贷	周五	10	0	0	800	6300	7100		成乃柏	结清		0	0
2017.7.16	李群051x	15951563443	6000	600	250	零用贷	周五	10	0	0	600	4400	5000			结清		0	0
2017.7.17	郑雅文2265	15961729621	10000	1000	300	零用贷	周一	10	0	0	1000	7900	8900			结清		0	0
2017.7.17	程志峰0012	13373632337	6000	300	210	零用贷	周一	20	0	0	600	4500	5100		成乃柏	结清		0	0
2017.7.18	吕文杰3010	13961836586	25000	1000	880	零用贷	周一	25	0	0	2500	19500	22000			结清		0	0
2017.7.18	朱俊杰4419	13771113533	10000	500	350	零用贷	周一	20	0	0	1000	7900	8900			结清		0	0
2017.7.19	王宗海3192	18651022311	7000	350	290	零用贷	周二	20	0	0	700	5400	6100			结清		0	0
2017.7.19	陆文娟0409	15161510359	8000	267	333	零用贷	周二	30	0	0	800	6300	7100			结清		0	0
2017.7.20	杨立新1219	15052218778	7000	350	280	零用贷	周二	20	0	0	700	5200	5900			结清		0	0
2017.7.21	刘一龙4813	15251628310	10000	500	320	零用贷	周四	20	0	0	1000	7900	8900			结清		0	0
2017.7.21	华岳松0714	13771154411	7000	350	270	零用贷	周四	20	0	0	700	5200	5900			结清		0	0
2017.7.24	左林林3523	13861819056	7000	350	250	零用贷	周一	20	0	0	700	5200	5900			结清		0	0
2017.7.25	李兴东3017	15961762219	6000	600	250	零用贷	周一	10	0	0	600	4400	5000			结清		0	0
2017.7.25	王敏敏0556	13921316060	10000	500	350	零用贷	周一	20	0	0	1000	7900	8900			结清		0	0
2017.7.28	陆文忠3771	13901528667	6000	400	210	零用贷	周四	15	0	0	600	4400	5000			结清		0	0
2017.7.29	顾俊杰3014	13861783019	8000	534	486	零用贷	周五	15	0	0	800	5900	6700			结清		0	0
2017.7.31	沈力0073	13606193007	15000	1500	600	零用贷	周一	10	0	0	1500	11400	12900		成乃柏	结清		0	0
2017.7.31	华明秋3774	18121513820	10000	500	300	零用贷	周一	20	0	0	1000	7500	8500			结清		0	0
2017.8.3	王亚东3677	13961751208	10000	1000	300	零用贷	周三	10	0	0	1000	7900	8900			结清		0	0
2017.8.7	梁建兵2810	15052440008	10000	500	380	零用贷	周一	20	0	0	1000	8200	9200		成乃柏	结清		0	0
2017.8.9	许关荣5734	13585069885	8000	400	360	零用贷	周三	20	0	0	800	6100	6900			结清		0	0
2017.8.9	李欢7825	13812220228	20000	667	713	零用贷	周二	30	0	0	2000	15400	17400			结清		0	0
2017.8.9	张其3113	13586153257	8000	400	310	零用贷	周二	20	0	0	800	5300	6100		成乃柏	结清		0	0
2017.8.10	张勤4491	15061472427	10000	500	280	零用贷	周三	20	0	0	1000	7900	8900			结清		0	0
2017.8.11	年夫云439x	18861603335	8000	800	300	零用贷	周四	10	0	0	800	6300	7100			结清		0	0
2017.8.11	过武1891	13701518787	10000	500	300	零用贷	周四	20	0	0	1000	7800	8800			结清		0	0
2017.8.11	马金男0613	15961837582	10000	500	350	零用贷	周四	20	0	0	1000	7400	8400		成乃柏	结清		0	0
2017.8.12	贺泰来4518	15896480872	8000	400	330	零用贷	周五	20	0	0	800	5900	6700			结清		0	0
2017.8.13	吴莹0043	13951583557	10000	334	316	零用贷	周六	30	0	0	1000	8400	9400			结清		0	0
2017.8.14	谈佳毅0032	13814252452	7000	350	270	零用贷	周一	20	0	0	700	5200	5900			结清		0	0
2017.8.14	钱慰6517	13003383709	15000	750	550	零用贷	周一	20	0	0	1500	11000	12500			结清		0	0
2017.8.16	顾成龙5770	13771582288	6000	300	220	零用贷	周二	20	0	0	600	4400	5000			结清		0	0
2017.8.16	鲁涛3412	13962520082	10000	500	350	零用贷	周二	20	0	0	1000	7900	8900			结清		0	0
2017.8.17	隆烨洲4776	15251558352	10000	667	383	零用贷	周三	15	0	0	1000	7500	8500			结清		0	0
2017.8.17	徐语晨1329	13812287222	15000	750	520	零用贷	周三	20	0	0	1500	12000	13500		成乃柏	结清		0	0
2017.8.21	华明秋3774	18121513820	10000	500	300	零用贷	周一	20	0	0	1000	7500	8500			结清		0	0
2017.8.21	张珂2212	13861888150	10000	1000	350	零用贷	周一	10	0	0	1000	7900	8900			结清		0	0
2017.8.22	柳冠华1516	13806191482	8000	400	310	零用贷	周一	20	0	0	800	6300	7100			结清		0	0
2017.8.22	李君3455	15995225107	15000	1500	520	零用贷	周一	10	0	0	1500	11400	12900			结清		0	0
2017.8.24	黄佐桂5197	13915272982	10000	500	300	零用贷	周三	20	0	0	1000	7900	8900		成乃柏	结清		0	0
2017.8.24	钱欣4416	15261513837	6000	300	350	零用贷	周三	20	0	0	600	4200	4800		成乃柏	结清		0	0
2017.8.26	沈华强0774	15251573577	10000	500	350	零用贷	周五	20	0	0	1000	7900	8900		成乃柏	结清		0	0
2017.8.28	黄斌1538	13584125583	8000	400	250	零用贷	周一	20	0	0	800	6000	6800			结清		0	0
2017.8.28	虞晓5529	15161597522	7000	350	200	零用贷	周一	20	0	0	700	5000	5700			结清		0	0
2017.8.28	王宇2315	18118885757	10000	1000	350	零用贷	周一	10	0	0	1000	7500	8500		成乃柏	结清		0	0
2017.8.28	马莉	0	8000	400	300	零用贷	周一	20	0	0	800	6300	7100			结清		0	0
2017.8.30	杭洪明7995	15961500330	10000	500	350	零用贷	周二	20	0	0	1000	7000	8000			结清		0	0
2017.8.30	张瑜0918	13196598990	6000	600	250	零用贷	周二	10	0	0	600	4200	4800			结清		0	0
2017.8.30	周凯亮1516	15050690413	10000	500	300	零用贷	周二	20	0	0	1000	8400	9400		成乃柏	结清		0	0
2017.8.31	周坚4517	15995281886	6000	400	250	零用贷	周三	15	0	0	600	4400	5000			结清		0	0
2017.9.1	惠为成2477	15852559853	8000	400	280	零用贷	周四	20	0	0	800	6300	7100		成乃伯	结清		0	0
2017.9.2	顾栋明5834	15052145812	6000	400	400	零用贷	周五	15	0	0	600	4400	5000			结清		0	0
2017.9.3	刘东0015	18951578523	10000	500	280	零用贷	周五	20	0	0	1000	7500	8500			结清		0	0
2017.9.4	杨丽1764	15052183793	7000	467	293	零用贷	周一	15	0	0	700	5200	5900			结清		0	0
2017.9.5	贺健1838	0	6000	600	250	零用贷	周一	10	0	0	600	4000	4600			结清		0	0
2017.9.5	许伟东3530	15852502258	6000	300	240	零用贷	周一	20	0	0	600	4400	5000			结清		0	0
2017.9.5	李启元5416	13915164179	8000	400	320	零用贷	周一	20	0	0	800	6000	6800			结清		0	0
2017.9.7	孙建涛2575	13921321830	14000	700	500	零用贷	周三	20	0	0	1400	10500	11900		成乃伯	结清		0	0
2017.9.9	邵晟0016	15006154371	10000	667	433	零用贷	周五	15	0	0	1000	7500	8500		成乃伯	结清		0	0
2017.9.12	姚琪4140	15706185577	8000	800	300	零用贷	周一	10	0	0	800	6300	7100			结清		0	0
2017.9.15	潘国强2419	18606345912	10000	1000	300	零用贷	周四	10	0	0	1000	7500	8500		周诗华	结清		0	0
2017.9.15	沈超5010	13861498840	12000	300	420	零用贷	周四	40	0	0	1200	9000	10200		成乃伯	结清		0	0
2017.9.19	金伟民7593	13812091971	10000	1000	350	零用贷	周一	10	0	0	1000	7500	8500		成乃伯	结清		0	0
2017.9.19	陈寒冰0014	15961871313	10000	1000	300	零用贷	周一	10	0	0	1000	7500	8500		成乃伯	结清		0	0
2017.9.20	任薇3942	18762683891	10000	667	433	零用贷	周二	15	0	0	1000	7500	8500		成乃伯	结清		0	0
2017.9.22	史爱艳0043	18362392180	7000	234	196	零用贷	周四	30	0	0	700	5500	6200			结清		0	0
2017.9.25	黄柯玮4414	18352561950	8000	400	300	零用贷	周一	20	0	0	800	6000	6800			结清		0	0
2017.9.26	张晓中3834	15052271182	10000	1000	300	零用贷	周一	10	0	0	1000	7500	8500		成乃伯	结清		0	0
2017.9.26	汪玲7668	18362349941	10000	500	350	零用贷	周一	20	0	0	1000	7500	8500			结清		0	0
2017.9.27	周旭旦6311	13812297912	15000	1000	600	零用贷	周二	15	0	0	1500	11000	12500			结清		0	0
2017.9.29	荣志鹏3558	13057231008	10000	500	350	零用贷	周四	20	0	0	1000	7500	8500		成乃伯	结清		0	0
2017.9.29	周建英3020	15852527991	6000	300	220	零用贷	周四	20	0	0	600	4200	4800			结清		0	0
2017.10.8	徐建成3913	13405751050	10000	500	280	零用贷	周五	20	0	0	1000	8000	9000			结清		0	0
2017.10.10	王健4446	13665191731	8000	400	280	零用贷	周一	20	0	0	800	5800	6600		成乃伯	结清		0	0
2017.10.14	陈云超2575	13921173170	6000	300	220	零用贷	周五	20	0	0	600	4000	4600			结清		0	0
2017.10.16	蔡雯雯6048	15061843685	6000	300	210	零用贷	周一	20	0	0	600	4200	4800			结清		0	0
2017.10.16	曹晓军2515	18661287477	10000	500	380	零用贷	周一	20	0	0	1000	7500	8500			结清		0	0
2017.10.16	夏斯海3634	15961883899	10000	1000	380	零用贷	周一	10	0	0	1000	7500	8500			结清		0	0
2017.10.16	曹鸿翔1819	13862692593	10000	500	320	零用贷	周一	20	0	0	1000	7500	8500		成乃伯	结清		0	0
2017.10.17	储伟0979	13771321827	10000	1000	300	零用贷	周一	10	0	0	1000	7500	8500			结清		0	0
2017.10.17	张王振2013	15061757007	6000	300	220	零用贷	周一	20	0	0	600	4200	4800		成乃伯	结清		0	0
2017.10.19	虞旭阳2474	15895380960	6000	600	250	零用贷	周三	10	0	0	600	4000	4600			结清		0	0
2017.10.20	黄晨3529	13392222250	15000	1500	525	零用贷	周四	10	0	0	1500	11000	12500		成乃伯	结清		0	0
2017.10.20	刘佳宁6529	18351585662	6000	400	300	零用贷	周四	15	0	0	600	4000	4600			结清		0	0
2017.10.25	姚琪4140	15706185577	10000	1000	350	零用贷	周二	10	0	0	1000	7500	8500			结清		0	0
2017.10.25	张鑫2633	15951502526	6000	600	250	零用贷	周二	10	0	0	600	4000	4600			结清		0	0
2017.10.25	唐一峰1618	18921146836	7000	350	290	零用贷	周二	20	0	0	700	5000	5700			结清		0	0
2017.10.28	李群051x	15951563443	6000	600	250	零用贷	周五	10	0	0	600	3900	4500			结清		0	0
2017.10.31	晁婷婷5480	15052118612	8000	400	250	零用贷	周一	20	0	0	800	6000	6800			结清		0	0
2017.11.1	程甲7118	18961726532	10000	500	350	零用贷	周二	20	0	0	1000	6100	7100		成乃伯	结清		0	0
2017.11.2	樊振中0313	13915210508	8000	800	300	零用贷	周三	10	0	0	800	6000	6800			结清		0	0
2017.11.2	顾芸0621	13921196878	10000	1000	350	零用贷	周三	10	0	0	1000	7500	8500		成乃伯	结清		0	0
2017.11.2	阮小莹0920	18014798851	8000	267	333	零用贷	周三	30	0	0	800	6000	6800		刘强	结清		0	0
2017.11.3	杨麟4116	13196597661	6000	600	210	零用贷	周四	10	0	0	600	4000	4600		成乃伯	结清		0	0
2017.11.6	高祥淦1921	13921108564	10000	334	346	零用贷	周一	30	0	0	1000	7500	8500			结清		0	0
2017.11.7	张颖5026	13771016018	5000	500	250	零用贷	周一	10	0	0	500	3500	4000			结清		0	0
2017.11.9	吴伟军0118	15995239187	6000	600	210	零用贷	周三	10	0	0	600	4000	4600			结清		0	0
2017.11.15	雷葶3521	13771521374	8000	400	300	零用贷	周二	20	0	0	800	6000	6800			结清		0	0
2017.11.21	惠为成2477	15852559853	8000	800	300	零用贷	周一	10	0	0	800	6000	6800			结清		0	0
2017.11.21	王春叶6677	13915281721	10000	500	350	零用贷	周一	20	0	0	1000	7500	8500			结清		0	0
2017.11.23	陈曙0017	13812035890	8000	400	280	零用贷	周二	20	0	0	800	6000	6800		成乃柏	结清		0	0
2017.11.27	钟彪521x	15906159710	6000	600	300	零用贷	周一	10	0	0	600	4200	4800			结清		0	0
2017.11.29	邵嘉明0219	18552434997	5000	500	250	零用贷	周二	10	0	0	500	3500	4000			结清		0	0
2017.12.1	刘善强1917	13812060439	10000	1000	350	零用贷	周四	10	0	0	1000	7500	8500			结清		0	0
2017.12.6	傅迪5744	15261580206	6000	400	250	零用贷	周二	15	0	0	600	4200	4800			结清		0	0
2017.12.8	胡刚5192	18800517700	10000	1000	350	零用贷	周四	10	0	0	1000	7500	8500			结清		0	0
2017.12.8	黄柯玮4414	18352561950	10000	500	350	零用贷	周四	20	0	0	1000	6600	7600			结清		0	0
2017.12.13	张晓中3834	15052271182	20000	2000	600	零用贷	周二	10	0	0	2000	15000	17000			结清		0	0
2017.12.15	虞旭阳2474	15868390960	7000	350	300	零用贷	周三	20	0	0	700	5000	5700			结清		0	0
2017.12.19	王林1231	15161564198	10000	1000	350	零用贷	周一	10	0	0	1000	7500	8500			结清		0	0
2017.2.20	赵程	15261610606	6000	300	290	零用贷	周一	20	1800	0	600	3600	4200	陈大大		逾期		0	0
2017.3.7	王志东	18020512990	10000	334	426	零用贷	周一	30	1500	0	1000	7500	8500	陈大大		逾期		1	760
2017.3.16	吴占锋	18761561700	5000	167	213	零用贷	周三	20	1200	0	500	3300	3800	朱新约		逾期		0	0
2017.3.6	韩义涛	13338782888	10000	0	500	红包贷		22	2300	0	1000	6700	7700	陈大大		逾期		14	7000
2017.3.8	沈建新	18015102816	6000	600	200	零用贷	周二	10	900	0	600	4500	5100	范利丽		逾期		1	800
2017.3.6	郁旦	15251523696	10000	500	330	零用贷	周一	20	1500	0	1000	7500	8500	范利丽		逾期		2	1660
2017.3.20	周晓健	15716177080	6000	600	250	零用贷	周一	10	1300	0	600	4100	4700	陈大大		逾期		0	0
2017.3.11	许雪芬	13771086736	6000	0	80	红包贷		30	2400	0	600	3000	3600	陈大大		逾期		18	1440
2017.3.8	马广富	13616170726	20000	2000	560	零用贷	周二	10	2500	0	2000	15500	17500	陈大大		逾期		3	7680
2017.3.7	孙利军	15061506022	8000	400	230	零用贷	周一	20	1400	0	800	5800	6600	陈大大		逾期		3	1890
2017.3.13	陈波	15298401771	10000	500	350	零用贷	周一	20	1500	0	1000	7500	8500	范利丽		逾期		2	1700
2017.2.16	刘朋飞	15861498003	10000	500	300	零用贷	周三	20	1500	0	1000	7500	8500	邓添文		逾期		6	4800
2017.3.20	陈维俊	13771498505	7000	0	100	红包贷		30	1170	0	700	5130	5830	范利丽		逾期		17	1700
2017.3.21	周敏	13093105338	10000	500	300	零用贷	周一	20	5000	0	1000	4000	5000	范利丽		逾期		2	1600
2017.4.11	魏响	13812083384	5000	250	330	零用贷	周一	20	2000	0	500	2500	3000	顾峰		逾期		0	0
2017.3.30	胡幼军	13961566795	8000	400	280	零用贷	周三	20	1200	0	800	6000	6800	李聪		逾期		2	1360
2017.3.9	宋翩	15251695041	6000	200	200	零用贷	周三	30	1200	0	600	4200	4800	顾峰		逾期		5	2000
2017.3.24	张士洋	15261563082	10000	500	350	零用贷	周四	20	2000	0	1000	7000	8000	陈大大		逾期		3	2550
2017.3.24	陆丽静	13771056560	6000	200	210	零用贷	周四	30	1200	0	600	4200	4800	范利丽		逾期		3	1230
2017.3.21	王奇	15061590086	7000	0	80	红包贷	每天	60	1500	0	700	4800	5500	顾峰		逾期		35	2800
2017.4.11	朱澄阳	18761850073	10000	1000	300	零用贷	周一	10	1500	0	1000	7500	8500	陈大大		逾期		1	1300
2017.3.15	吴小强	15995327323	8000	400	250	零用贷	周二	20	1000	0	800	6200	7000	朱新约		逾期		5	3250
2017.4.6	万文宁	15251669822	8000	800	320	零用贷	周三	10	1400	0	800	5800	6600	朱新约		逾期		2	2240
2017.3.15	高红来	13400033867	10000	0	100	红包贷	每天	30	1300	0	1000	7700	8700	陈大大		逾期		45	4500
2017.3.17	高红岗	17315507645	10000	500	300	零用贷	周四	20	1500	0	1000	7500	8500	范利丽		逾期		5	4000
2017.3.1	郑娟萍y	13961882787	8000	400	280	零用贷	周二	20	1400	0	800	5800	6600	邓添文		逾期		8	5440
2017.4.15	王宝中	17505106053	6000	300	300	零用贷	周五	10	1400	0	600	4000	4600	朱新约		逾期		1	600
2017.4.25	蒋云超	13861506131	6000	600	230	零用贷	周二	10	1200	0	600	4200	4800	王建杰		逾期		0	0
2017.4.26	赵强	15152227075	6000	0	200	红包贷	每天	40	1380	0	600	4020	4620	王建杰		逾期		6	1200
2017.4.7	钱彩凤	15851633107	10000	334	366	零用贷	周四	30	1500	0	1000	7500	8500	陈大大		逾期		3	2100
2017.3.10	黄建海	13915209474	10000	500	280	零用贷	周四	20	1500	0	1000	7500	8500	陈大大		逾期		7	5460
2017.4.22	魏小健	18800522736	6000	300	340	零用贷	周五	20	1900	0	600	3500	4100	陈大大		逾期		1	640
2017.4.24	吴喜喜	13915721379	10000	10000	0	空放	22	1	3850	0	1000	5150	6150	王建杰		逾期		0	0
2017.4.10	黄凯	15906162823	8000	400	280	零用贷	周一	20	2400	0	800	4800	5600	陈大大		逾期		1	680
2017.5.3	吴洪芹	18352568650	8000	800	300	零用贷	周二	10	1200	0	800	6000	6800	李聪		逾期		0	0
2017.3.1	张君	13771350876	10000	334	246	零用贷	周三	30	1500	0	1000	7500	8500	信息部		逾期		9	5220
2017.5.6	薛忠	18951576601	5000	334	166	零用贷	周五	15	1500	0	500	3000	3500	顾峰		逾期		0	0
2017.5.3	韩玲珍y	18362366163	8000	0	350	打卡	每天	30	1620	0	800	5580	6380	范利丽		逾期		12	4200
2017.4.27	张君	13771350876	10000	10000	0	空放	22	1	2650	0	1000	6350	7350	信息部		逾期		0	0
2017.4.7	姚勤裕	18112357892	6000	300	230	零用贷	周四	20	1400	0	600	4000	4600	陈大大		逾期		5	2650
2017.3.17	陈静	15312298388	10000	334	316	零用贷	周四	20	1000	0	1000	8000	9000	朱新约		逾期		8	5200
2017.4.23	吴科	13921218199	8000	534	226	零用贷	周五	15	1200	0	800	6000	6800	朱新约		逾期		2	1520
2017.5.4	李银春	18861863470	6000	0	280	打卡	每天	30	1380	0	600	4020	4620	顾峰		逾期		16	4480
2017.5.5	范湘湘	13812237363	8000	0	80	红包贷	每天	30	1800	0	800	5400	6200	顾峰		逾期		17	1360
2017.5.15	吴亚琴	18651001069	6000	0	60	红包贷	每天	30	1160	0	600	4240	4840	顾峰		逾期		10	600
2017.5.12	刘群	18306180618	6000	300	210	零用贷	周四	20	1200	0	600	4200	4800	范利丽		逾期		1	510
2017.5.5	徐峰	13906191882	20000	2000	700	零用贷	周四	10	3000	0	2000	15000	17000	顾峰		逾期		2	5400
2017.3.25	张杰	13174048888	10000	1000	350	零用贷	周五	10	2500	0	1000	6500	7500	陆仟玉		逾期		8	10800
2017.4.16	水振	13405777778	10000	500	300	零用贷	周一	20	1500	0	1000	7500	8500	王建杰		逾期		5	4000
2017.5.21	华金	15190289614	6000	200	250	零用贷	周一	30	1200	0	600	4200	4800	范利丽		逾期		0	0
2017.3.23	潘宁宁	15861515371	6000	0	80	红包贷	每天	30	3280	0	600	2120	2720	顾峰		逾期		66	5280
2017.4.19	田寿玉	13400030746	10000	1000	400	零用贷	周二	10	1500	0	1000	7500	8500	朱新约		逾期		5	7000
2017.5.19	吴丽江	13914200652	10000	10000	0	空放	22	1	2800	0	1000	6200	7200	顾峰		逾期		0	0
2017.3.31	周银凤	13961679738	10000	500	280	零用贷	周四	20	2000	0	1000	7000	8000	陈大大		逾期		8	6240
2017.4.7	黄意章	13771152686	10000	500	350	零用贷	周四	20	1500	0	1000	7500	8500	陈大大		逾期		7	5950
2017.3.7	朱东兵	18362337274	6000	300	500	零用贷	周一	20	1400	0	600	4000	4600	陈大大		逾期		12	9600
2017.5.2	郑峰	18761507013	7000	700	200	零用贷	周一	10	1100	0	700	5200	5900	陈大大		逾期		5	4500
2017.5.22	汤卫燕	18151553981	6000	600	210	零用贷	周一	10	1200	0	600	4200	4800	陈大大		逾期		1	810
2017.5.25	何亚军	15335241635	8000	400	300	零用贷	周三	20	1200	0	800	6000	6800	陈大大		逾期		1	700
2017.5.25	杨旦	13921179520	8000	400	300	零用贷	周三	20	1200	0	800	6000	6800	陈大大		逾期		1	700
2017.5.20	杨浩飞	18861631845	8000	400	230	零用贷	周五	20	1400	0	800	5800	6600	范利丽		逾期		2	1260
2017.5.9	王莉	15949282818	8000	400	300	零用贷	周一	20	1200	0	800	6000	6800	顾峰		逾期		4	2800
2017.6.9	吴海	15251439039	10000	0	500	打卡	每天	23	2300	0	1000	6700	7700	顾峰		逾期		3	1500
2017.5.18	汤凌	18861570091	5000	0	70	红包贷	每天	30	1040	0	500	3460	3960	李聪		逾期		28	1960
2017.5.24	王林芳	15006163884	6000	0	200	打卡	40	40	2270	0	600	3130	3730	顾峰		逾期		19	3800
2017.5.19	华啸	15861638013	5000	250	200	零用贷	周四	20	1000	0	500	3500	4000	张卿		逾期		3	1350
2017.5.13	金炜	18015317600	10000	500	350	零用贷	周五	20	2000	0	1000	7000	8000	信息部		逾期		4	3400
2017.6.3	张鑫	13961806144	10000	500	400	零用贷	周五	20	1500	0	1000	7500	8500	顾峰		逾期		1	900
2017.6.10	顾红	18205038361	6000	600	210	零用贷	周五	10	1200	0	600	4200	4800	李聪		逾期		0	0
2017.5.2	郑峰	18761507013	7000	700	200	零用贷	周一	10	1100	0	700	5200	5900	陈大大		逾期		6	5400
2017.6.7	王璞	18021431660	6000	600	280	零用贷	周二	10	1100	0	600	4300	4900	朱新约		逾期		1	880
2017.3.16	胡晓勤	1855212536	8000	267	253	零用贷	周三	30	1200	0	800	6000	6800	陈大大		逾期		13	6760
2017.4.27	陈天河	13771233456	8000	400	300	零用贷	周三	20	1400	0	800	5800	6600	顾峰		逾期		7	4900
2017.6.8	李淼	13961795799	40000	2000	1250	零用贷	周四	20	6000	0	4000	30000	34000	陈大大		逾期		1	3250
2017.6.9	董美新	13616192770	6000	300	210	零用贷	周四	20	1100	0	600	4300	4900	顾峰		逾期		1	510
2017.6.12	六徐文	13914180018	20000	0	500	打卡	每天	60	2400	0	2000	15600	17600	顾峰		逾期		11	5500
2017.6.13	颜华	15716196677	15000	0	900	打卡	每天	900	500	0	1500	13000	14500	顾峰		逾期		10	9000
2017.6.15	陶善超	13915340666	10000	500	350	零用贷	周三	20	1500	0	1000	7500	8500	李聪		逾期		1	850
2017.6.1	相海	13861721874	10000	500	350	零用贷	周三	20	1500	0	1000	7500	8500	信息部		逾期		3	2550
2017.5.16	许小勇	13373640323	6000	600	250	零用贷	周一	10	1200	0	600	4200	4800	范利丽		逾期		5	4250
2017.6.11	施霞清	15861651481	6000	0	70	红包贷	每天	30	1485	0	600	3915	4515	朱新约		逾期		17	1190
2017.6.2	许锬	13093081783	15000	1000	470	零用贷	周四	15	2000	0	1500	11500	13000	范利丽		逾期		3	4410
2017.5.12	黄涛	13665178715	100000	100000	0	空放	22	1	18000	0	10000	72000	82000	信息部		逾期		0	0
2017.6.7	黄云	13921179079	80000	80000	0	空放	22	1	75000	0	8000	-3000	5000	信息部		逾期		0	0
2017.6.7	孔威	18626300885	6000	1380	0	红包贷	每天	60	1275	0	600	4125	4725	顾峰		逾期		23	1380
2017.6.28	王周琴	13961581605	10000	500	0	打卡	每天	23	1750	0	1000	7250	8250	顾峰		逾期		12	2000
2017.6.13	陈晓靓	13912376897	10000	0	100	红包贷	每天	30	1600	0	1000	7400	8400	李聪		逾期		21	2100
2017.7.3	恽志恒	18626366766	8000	0	80	红包贷	每天	30	1700	0	800	5500	6300	陈大大		逾期		3	240
2017.6.25	肖东	13861768903	15000	1500	550	零用贷	周五	10	2500	0	1500	11000	12500	李聪		逾期		1	2050
2017.6.17	沈萍	13585071515	15000	15000	0	空放	22	1	2810	0	1500	10690	12190	信息部		逾期		0	0
2017.5.2	顾健	15995222535	10000	300	500	零用贷	周一	20	1500	0	1000	7500	8500	陈大大		逾期		9	7200
2017.6.12	张志强	15152225859	6000	300	330	零用贷	周一	20	1000	0	600	4400	5000	王建杰		逾期		3	1890
2017.6.21	屈君飞	13584126912	6000	300	210	零用贷	周二	20	1400	0	600	4000	4600	信息部		逾期		1	510
2017.4.13	徐霞	13961892091	6000	400	250	零用贷	周三	20	1800	0	600	3600	4200	王建杰		逾期		12	7800
2017.6.22	蔡明东	13861736736	10000	500	350	零用贷	周三	20	1500	0	1000	7500	8500	顾峰		逾期		2	1700
2017.6.16	吴亮	18861577909	8000	400	300	零用贷	周四	20	1200	0	800	6000	6800	范利丽		逾期		3	2100
2017.7.8	顾浩峰	15190332829	8000	50	450	打卡	20天	30	1900	0	800	5300	6100	朱新约		逾期		4	1800
2017.7.1	陈杰	13771796155	10000	1000	300	零用贷	周五	10	1500	0	1000	7500	8500	王建杰		逾期		1	1300
2017.7.12	李文强	15161666856	8000	400	300	零用贷	周二	20	900	0	800	6300	7100	江韦		逾期		0	0
2017.6.22	顾誉	15195104242	6000	300	350	零用贷	周三	20	1200	0	600	4200	4800	范利丽		逾期		3	2450
2017.6.22	陈星韦	15995244931	10000	500	350	零用贷	周三	20	1500	0	1000	7500	8500	玲玲		逾期		3	2550
2017.7.13	朱伟英1624	15371052656	6000	400	230	零用贷	周三	15	1000	0	600	4400	5000	朱新约		逾期		0	0
2017.6.22	杨建	15250818400	6000	150	0	打卡	每天	80	1270	0	600	4130	4730	顾峰		逾期		27	6210
2017.3.23	胡东方	18351589993	7000	350	250	零用贷	周三	20	1100	0	700	5200	5900	李聪		逾期		16	9600
2017.7.12	夏玉娟	18261565525	6000	400	0	打卡	每天	30	1400	0	600	4000	4600	顾峰		逾期		9	2700
2017.6.30	谢敏芳	15006154861	6000	0	300	红包贷	每天	25	1380	0	600	4020	4620	朱新约		逾期		8	2400
2017.7.20	朱广益7217	13771525779	8000	450	0	打卡	每天	20	1200	0	800	6000	6800	朱新约		逾期		0	0
2017.5.20	徐金良	18068334652	6000	400	300	零用贷	周五	15	1200	0	600	4200	4800	范利丽		逾期		8	5600
2017.7.8	蔡敏华	13606182738	8000	534	336	零用贷	周五	15	1200	0	800	6000	6800	信息部		逾期		1	870
2017.7.10	韩忠	15206176055	8000	800	280	零用贷	周一	10	1600	0	800	5600	6400	朱新约		逾期		1	1080
2017.7.11	吴伟	15006197066	6000	600	210	零用贷	周一	10	1200	0	600	4200	4800	朱新约		逾期		1	810
2017.4.26	王春	15261576915	6000	300	350	零用贷	周二	20	1200	0	600	4200	4800	陈大大		逾期		12	7800
2017.6.7	刘鹏雄	13291230025	8000	400	300	零用贷	周二	20	1400	0	800	5800	6600	江韦		逾期		6	4200
2017.6.2	过顺兴	13915333799	7000	350	250	零用贷	周四	20	1300	0	700	5000	5700	顾峰		逾期		7	4200
2017.6.4	朱震球	18936072081	14000	700	550	零用贷	周五	20	2600	0	1400	10000	11400	顾峰		逾期		7	8750
2017.7.17	朱震球2497	18936072081	10000	334	351	零用贷	周一	30	1500	0	1000	7500	8500	顾峰		逾期		2	1370
2017.7.22	张海栋2677	13585001316	6000	300	250	零用贷	周五	20	2000	0	600	3400	4000	范利丽		逾期		0	0
2017.7.21	顾珂珂	15961707602	35000	1500	900	零用贷	周六	20	5000	0	3500	26500	30000	信息部		逾期		0	0
2017.6.16	罗剑	15161655418	6000	400	200	零用贷	周五	15	1200	0	600	4200	4800	顾峰		逾期		6	5600
2017.6.12	缪小朋	15950426302	6000	300	280	零用贷	周日	20	1100	0	600	4300	4900	顾峰		逾期		3	1740
2017.7.11	杭春牛	13584199589	8000	800	300	零用贷	周一	10	1200	0	800	6000	6800	信息部		逾期		3	3300
2017.7.21	周美168x	15861560921	6000	260	0	打卡	每天	30	1100	0	600	4300	4900	李聪		逾期		9	2340
2017.6.13	蒋国栋	18051567666	8000	400	280	零用贷	周一	20	1200	0	800	6000	6800	信息部		逾期		6	4080
2017.6.7	周锡源	15961823438	6000	300	210	零用贷	周二	20	1300	0	600	4100	4700	朱新约		逾期		7	3570
2017.7.20	王彪227x	15861471252	6000	400	260	零用贷	周三	15	1000	0	600	4400	5000	李聪		逾期		1	1160
2017.7.27	徐虹2060	13861722137	20000	20000	0	空放	22	1	5900	0	2000	12100	14100	信息部		逾期		0	0
2017.6.12	蒋爱芳	13376230188	7000	467	283	零用贷	周一	15	1300	0	700	5000	5700	李聪		逾期		7	5250
2017.7.5	顾星	15951560585	6000	300	250	零用贷	周二	20	1400	0	600	4000	4600	顾峰		逾期		4	2200
2017.4.6	徐忠	13606188559	10000	500	300	零用贷	周三	20	1500	0	1000	7500	8500	陈大大		逾期		17	13600
2017.6.22	高麒深	15261686699	10000	667	353	零用贷	周三	15	1500	0	1000	7500	8500	范利丽		逾期		6	6120
2017.7.22	曹建国1355	13328110252	10000	500	320	零用贷	周五	20	600	0	1000	8400	9400	顾峰		逾期		2	1640
2017.7.24	陆欢0515	18861758959	6000	0	260	打卡	每天	30	1200	0	600	4200	4800	江韦		逾期		25	6500
2017.7.20	黄云云2555	18552059383	6000	200	250	零用贷	周三	30	1000	0	600	4400	5000	江韦		逾期		3	1350
2017.8.10	朱脐峰0036	18452419677	7000	0	300	打卡		30	1100	0	700	5200	5900	顾峰		逾期		6	1200
2017.8.11	储宣渊5899	13812218945	6000	300	500	零用贷	周四	20	1000	0	600	4400	5000	朱新约		逾期		0	0
2017.6.11	丁丽梅	13921151851	10000	500	300	零用贷	周五	20	1000	0	1000	8000	9000	范利丽		逾期		8	7100
2017.7.15	张敏民651x	13921237575	8000	400	280	零用贷	周五	20	900	0	800	6300	7100	朱新约		逾期		4	3040
2017.4.17	蔡亦斌	13584214410	10000	500	350	零用贷	周一	20	1200	0	1000	7800	8800	范利丽		逾期		17	14450
2017.6.5	林晓奇	13861704023	6000	300	280	零用贷	周一	20	1100	0	600	4300	4900	顾峰		逾期		10	5800
2017.7.24	张雪兰0822	13952097369	10000	500	400	零用贷	周一	20	1100	0	1000	7900	8900	李聪		逾期		3	2700
2017.7.25	朱晨1413	15298438204	10000	500	320	零用贷	周一	20	1100	0	1000	7900	8900	顾峰		逾期		3	2460
2017.7.24	吴春雷3279	15152278239	6000	300	290	零用贷	周一	20	1000	0	600	4400	5000	朱新约		逾期		3	1770
2017.8.7	朱晨凯4270	13861666433	15000	750	530	零用贷	周一	20	12000	0	1500	1500	3000	范利丽		逾期		1	1280
2017.7.20	徐敏5038	15961584554	8000	800	330	零用贷	周三	10	900	0	800	6300	7100	范利丽		逾期		3	3390
2017.8.3	钱斌6513	18068263483	8000	400	280	零用贷	周三	20	1200	0	800	6000	6800	范利丽		逾期		2	1360
2017.8.11	李文伟4931	13812098496	6000	400	220	零用贷	周四	15	1100	0	600	4300	4900	朱新约		逾期		1	620
2017.7.26	王磊5991	18352520718	10000	10000	0	空放	10	1	2300	0	1000	6700	7700	信息部		逾期		1	2000
2017.6.26	尤伟民	13337903347	30000	30000	0	空放	30	1	7500	0	3000	19500	22500	顾峰		逾期		1	9000
2017.8.14	臧一龙5513	18915274652	6000	260	0	打卡	每天	30	1200	0	600	4200	4800	王建杰		逾期		9	2340
2017.8.7	梅伟杰6017	15061726522	6000	300	210	零用贷	周一	20	1000	0	600	4400	5000	朱新约		逾期		2	1020
2017.8.5	钱山江503x	13961822282	8000	800	330	零用贷	周五	10	900	0	800	6300	7100	信息部		逾期		2	2260
2017.7.10	钱寅	15061973758	8000	400	400	零用贷	周一	20	1700	0	800	5500	6300	王建杰		逾期		6	4800
2017.8.9	吕凯1657	13921371652	10000	667	423	零用贷	周二	15	1600	0	1000	7400	8400	朱新约		逾期		2	2180
2017.7.18	谢松4438	15261558818	8000	800	340	零用贷	周一	10	1100	0	800	6100	6900	李聪		逾期		5	6200
2017.7.6	杨卫东	13861632804	5000	334	216	零用贷	周三	15	1000	0	500	3500	4000	江韦		逾期		7	3850
2017.6.17	杭杰	13771326019	5000	334	236	零用贷	周五	15	1000	0	500	3500	4000	江韦		逾期		10	6700
2017.7.23	许薇1866	13162614805	10000	334	356	零用贷	周五	30	600	0	1000	8400	9400	顾峰		逾期		5	3450
2017.8.3	苏智斌6511	15161610175	8000	400	300	零用贷	周三	20	900	0	800	6300	7100	范利丽		逾期		4	2800
2017.8.11	王芸倩3427	15961708187	7000	350	290	零用贷	周四 	20	1300	0	700	5000	5700	范利丽		逾期		3	1920
2017.8.25	龚益波1054	13915249158	10000	400	350	零用贷	周四	25	8600	0	1000	400	1400	信息部		逾期		1	1050
2017.8.12	耿春晓1314	15995215686	10000	500	300	零用贷	周五	20	800	0	1000	8200	9200	范利丽		逾期		3	2400
2017.6.27	叶明	18706185181	6000	300	210	零用贷	周一	20	1100	0	600	4300	4900	朱新约		逾期		10	5100
2017.7.31	范婷婷4587	13921367725	6000	300	250	零用贷	周一	20	1000	0	600	4400	5000	王建杰		逾期		5	2750
2017.5.29	唐丽艳	15961705122	10000	500	320	零用贷	周一	20	1500	0	1000	7500	8500	王建杰		逾期		14	11480
2017.6.28	王生华	13914251473	8000	400	300	零用贷	周二	20	1200	0	800	6000	6800	范利丽		逾期		10	7000
2017.8.12	黄涛0539	13861543772	10000	500	380	零用贷	周五	20	1100	0	1000	7900	8900	信息部		逾期		4	3520
2017.8.31	季勇0772	15861653344	6000	350	0	打卡	每天	20	1200	0	600	4200	4800	顾峰		逾期		15	5250
2017.9.11	宋舜001x	18851576098	6000	260	0	打卡	每天	30	1400	0	600	4000	4600	李聪		逾期		5	1300
2017.8.8	俞敏杰5374	13771163366	10000	500	320	零用贷	周一	20	600	0	1000	8400	9400	顾峰		逾期		5	4100
2017.6.2	邵寅伟	13806157525	10000	334	346	零用贷	周四	30	1500	0	1000	7500	8500	范利丽		逾期		15	10200
2017.7.14	王晓春5712	13616180022	10000	500	280	零用贷	周四	20	1100	0	1000	7900	8900	王建杰		逾期		9	7020
2017.9.5	姜恕荣4479	18861509809	20000	1000	0	打卡	每天	22	3000	0	2000	15000	17000	王建杰		逾期		4	4000
2017.8.28	王俊晓t	13812033833	10000	334	346	零用贷	周一	30	1000	0	1000	8000	9000	信息部		逾期		2	1360
2017.2.27	陈伟	18601555786	8000	267	273	零用贷	周一	30	1400	0	800	5800	6600	陈大大		逾期		28	15120
2017.3.9	张建南	13196521235	8000	267	283	零用贷	周三	30	1500	0	800	5700	6500	李聪		逾期		27	14850
2017.6.30	顾烨	17768338569	10000	334	286	零用贷	周四	30	1500	0	1000	7500	8500	李聪		逾期		12	7440
2017.6.25	吴亚新	13815136812	6000	300	250	零用贷	周五	20	1100	0	600	4300	4900	信息部		逾期		13	7150
2017.7.1	许溢	15961895593	6000	400	210	零用贷	周五	15	1100	0	600	4300	4900	江韦		逾期		12	7320
2017.8.26	高晓亮6518	13912362468	7000	350	270	零用贷	周五	20	1100	0	700	5200	5900	王建杰		逾期		4	2480
2017.6.13	陶莉	13861720707	6000	300	200	零用贷	周一	20	1300	0	600	4100	4700	信息部		逾期		11	5500
2017.8.14	张艳6420	18012351561	6000	400	250	零用贷	周一	15	1200	0	600	4200	4800	李聪		逾期		4	2600
2017.7.11	张南峰	15106177681	8000	400	300	零用贷	周一	20	1200	0	800	6000	6800	范利丽		逾期		11	7700
2017.9.6	高东彪3412	13952472958	10000	500	350	零用贷	周二	20	1500	0	1000	7500	8500	朱新约		逾期		3	2550
2017.5.26	张仁伯	18762691258	6000	300	210	零用贷	周四	20	1100	0	600	4300	4900	顾峰		逾期		18	9680
2017.6.30	秦红鸣	18921245787	10000	500	350	零用贷	周四	20	1500	0	1000	7500	8500	玲玲		逾期		13	12050
2017.9.7	陈小荣1511	13771040768	6000	260	0	打卡	每天	30	1400	0	600	4000	4600	李聪		逾期		28	7280
2017.9.25	钱素珍2029	15852615399	6000	300	250	零用贷	周一	20	1200	0	600	4200	4800	范利丽		逾期		1	550
2017.9.28	单明晓2276	13914100809	10000	500	350	零用贷	周三	20	1500	0	1000	7500	8500	顾峰		逾期		1	1200
2017.9.22	朱小波0010	13921538241	8000	350	0	打卡	每天	30	1200	0	800	6000	6800	顾峰		逾期		19	6650
2017.7.27	李宁峰0912	13400022910	6000	300	200	零用贷	周三	20	1100	0	600	4300	4900	信息部		逾期		9	4500
2017.9.20	任薇3942	18762683891	10000	667	433	零用贷	周二	15	1500	0	1000	7500	8500	江韦		逾期		3	8300
2017.6.22	吴建琳	13400013398	10000	500	350	零用贷	周三	20	1500	0	1000	7500	8500	王建杰		逾期		16	13600
2017.8.10	贾学宣1915	13665163629	6000	200	220	零用贷	周三	30	900	0	600	4500	5100	范利丽		逾期		9	3780
2017.9.23	贡岑8541	18915229777	10000	500	350	零用贷	周五	20	1500	0	1000	7500	8500	顾峰		逾期		3	2550
2017.9.29	钟斌龙1374	13665111621	6000	260	0	打卡	每天	30	1200	0	600	4200	4800	信息部		逾期		21	5460
2017.7.17	赵怀朋5937	15806177346	8000	400	430	零用贷	周一	20	900	0	800	6300	7100	范利丽		逾期		15	12450
2017.10.12	朱镇宇5299	13961611239	8000	400	0	打卡	每天	27	1200	0	800	6000	6800	信息部		逾期		13	5200
2017.7.28	顾嘉洋4435	13616190461	10000	334	281	零用贷	周四	30	1500	0	1000	7500	8500	信息部		逾期		12	7380
2017.9.25	吕娜3020	13861831043	10000	1000	300	零用贷	周一	10	1500	0	1000	7500	8500	信息部		逾期		4	5200
2017.10.9	邱泉0015	13235188638	6000	260	0	打卡	每天	30	1200	0	600	4200	4800	顾峰		逾期		18	4680
2017.6.27	殷飞黄	18601587777	8000	400	280	零用贷	周一	20	1600	0	800	5600	6400	信息部		逾期		17	11560
2017.10.25	华淼0570	18351589031	8000	533	297	零用贷	周二	15	1400	0	800	5800	6600	李聪		逾期		0	0
2017.8.9	周鹏飞5810	18626053632	7000	350	250	零用贷	周二	20	1300	0	700	5000	5700	顾峰		逾期		8	4800
2017.10.26	刘冬1313	15206187483	6000	200	300	零用贷	周三	30	2400	0	600	3000	3600	李聪		逾期		0	0
2017.10.12	田建康6490	18006163676	8000	400	300	零用贷	周三	20	1200	0	800	6000	6800	顾峰		逾期		2	1400
2017.8.10	陈永兴7050	18360806817	8000	400	280	零用贷	周三	20	1100	0	800	6100	6900	顾峰		逾期		11	7480
2017.10.14	周勰0619	13806157086	8000	400	280	零用贷	周五	20	1800	0	800	5400	6200	顾峰		逾期		2	1360
2017.7.31	陆栋梁0032	15365261637	15000	500	530	零用贷	周一	30	3100	0	1500	10400	11900	顾峰		逾期		13	13390
2017.10.31	唐铖僖3918	13915283265	6000	600	220	零用贷	周一	10	1200	0	600	4200	4800	王建杰		逾期		0	0
2017.10.9	徐玲5528	18118118755	10000	500	350	零用贷	周一	20	1500	0	1000	7500	8500	朱新约		逾期		3	2550
2017.10.18	高晟3210	18552188242	5000	250	200	零用贷	周三	20	1000	0	500	3500	4000	李聪		逾期		2	900
2017.11.3	陈银2333	18136388014	10000	500	400	零用贷	周四	20	1500	0	1000	7500	8500	范利丽		逾期		0	0
2017.10.10	刘勇1219	18921115887	10000	500	350	零用贷	周一	20	1500	0	1000	7500	8500	李聪		逾期		4	3400
2017.10.30	高强3519	13812155373	8000	400	300	零用贷	周一	20	1200	0	800	6000	6800	信息部		逾期		1	700
2017.11.1	吴晓忠1370	15061893580	10000	500	500	零用贷	周二	20	2200	0	1000	6800	7800	王建杰		逾期		1	1000
2017.7.27	怀金晓6278	13706169395	10000	500	350	零用贷	周三	20	1100	0	1000	7900	8900	顾峰		逾期		15	12750
2017.10.31	夏列鹰377x	13771597541	6000	300	220	零用贷	周一	20	1400	0	600	4000	4600	顾峰		逾期		1	520
2017.11.3	杨麟4116	13196597661	6000	600	210	零用贷	周四	10	1400	0	600	4000	4600	李聪		逾期		1	810
2017.10.6	周美芳1387	18115355619	6000	200	200	零用贷	周五	30	1200	0	600	4200	4800	王建杰		逾期		5	2000
2017.8.25	沈利峰0237	13665199991	8000	400	320	零用贷	周五	20	1800	0	800	5400	6200	信息部		逾期		11	7920
2017.11.2	李江195x	13861832716	15000	750	520	零用贷	周三	20	3800	0	1500	9700	11200	信息部		逾期		1	1270
2017.11.11	庄建栋4212	13914267571	5000	500	250	零用贷	周五	10	1000	0	500	3500	4000	朱新约		逾期		0	0
2017.8.15	陈敏3832	13400032155	7000	350	250	零用贷	周一	20	1100	0	700	5200	5900	顾峰		逾期		13	7800
2017.10.9	惠佳2514	13961744522	10000	500	500	零用贷	周一	20	2000	0	1000	7000	8000	信息部		逾期		2	2500
2017.10.23	刘凯0592	13665166774	10000	500	350	零用贷	周一	20	1500	0	1000	7500	8500	顾峰		逾期		3	2550
2017.8.30	王建荣	13861810853	10000	500	300	零用贷	周二	20	1500	0	1000	7500	8500	顾峰		逾期		11	8800
2017.9.22	李海燕0021	13901530361	20000	1000	600	零用贷	周四	20	3000	0	2000	15000	17000	范利丽		逾期		8	12800
2017.11.19	吴小涓1529	18262262094	10000	600	0	打卡	每天	21	1750	0	1000	7250	8250	朱新约		逾期		3	1800
2017.7.30	江海荣2073	15190231689	6000	300	220	零用贷	周五	20	1000	0	600	4400	5000	顾峰		逾期		16	8320
2017.10.9	蒋津晟0019	17505105748	8000	400	280	零用贷	周一	20	1200	0	800	6000	6800	刘强		逾期		6	4080
2017.10.23	李洪春0973	15161664822	6000	400	360	零用贷	周一	15	1400	0	600	4000	4600	顾峰		逾期		4	3040
2017.10.31	刘强6978	18861608231	7000	700	270	零用贷	周一	10	1300	0	700	5000	5700	李聪		逾期		3	2910
2017.8.21	丁雷6550	13506198054	6000	400	250	零用贷	周一	15	1200	0	600	4200	4800	信息部		逾期		12	7800
2017.11.9	张建国0212	13601533336	6000	300	220	零用贷	周三	20	1400	0	600	4000	4600	李聪		逾期		2	1040
2017.9.5	曾本江6534	15070380771	6000	400	250	零用贷	周一	15	1200	0	600	4200	4800	朱新约		逾期		12	7800
2017.10.11	冯小龙2696	13861842112	10000	334	346	零用贷	周二	30	1500	0	1000	7500	8500	江韦		逾期		8	5440
2017.12.5	徐向荣4873	15358989775	7000	700	300	零用贷	周一	10	1300	0	700	5000	5700	王龙		逾期		0	0
2017.10.17	顾晨炎4517	15061793160	6000	300	260	零用贷	周一	20	1400	0	600	4000	4600	朱新约		逾期		7	3920
2017.11.29	龚金2090	15861480901	20000	1000	700	零用贷	周二	20	3000	0	2000	15000	17000	王龙		逾期		1	1700
2017.11.9	陈栋0314	13861866590	6000	500	210	零用贷	周三	10	1400	0	600	4000	4600	顾峰		逾期		710	2840
2017.11.10	陆文忠3771	13901528667	8000	534	336	零用贷	周四	15	1400	0	800	5800	6600	李聪		逾期		4	3480
2017.10.20	许程梁1812	17751505775	8000	400	380	零用贷	周四	20	1200	0	800	6000	6800	刘强		逾期		7	5460
2017.11.17	韩九华0796	13815147295	20000	1000	700	零用贷	周四	20	3000	0	2000	15000	17000	信息部		逾期		3	5100
2017.11.26	王惠2560	13921338628	6000	300	250	零用贷	周五	20	1400	0	600	4000	4600	朱新约		逾期		2	1100
20179.19	李涎郡6116	13328105625	6000	200	220	零用贷	周一	30	1200	0	600	4200	4800	朱新约		逾期		12	5040
2017.10.10	徐雪峰7573	15850101527	6000	400	220	零用贷	周一	15	1200	0	600	4200	4800	江韦		逾期		9	5580
2017.12.5	马宁2413	15190251383	7000	467	303	零用贷	周一	15	1300	0	700	5000	5700	李聪		逾期		1	770
2017.10.9	徐剑1830	18015374090	10000	500	300	零用贷	周一	20	1500	0	1000	7500	8500	江韦		逾期		9	7200
2017.12.6	朱承良1516	15895375287	6000	400	250	零用贷	周二	15	1400	0	600	4000	4600	罗		逾期		1	650
2017.8.11	姚翔2074	13861710479	6000	300	350	零用贷	周四	20	1200	0	600	4200	4800	顾峰		逾期		16	10400
2017.9.11	王敏敏0556	13921316060	25000	1000	875	零用贷	周一	25	4100	0	2500	18400	20900	朱新约		逾期		14	26250
2017.11.13	王健267x	15358997688	10000	500	350	零用贷	周一	20	2150	0	1000	6850	7850	顾峰		逾期		5	4250
2017.11.7	司马贤1399	15806192167	10000	500	350	零用贷	周一	20	1500	0	1000	7500	8500	朱新约		逾期		5	4250
2017.8.25	蒋秋园5862	13961556888	10000	500	300	零用贷	周四	20	1500	0	1000	7500	8500	朱新约		逾期		17	13600
2017.9.1	陶萍2268	13771132163	5000	250	210	零用贷	周四	20	800	0	500	3700	4200	朱新约		逾期		17	7820
2017.11.29	姚昱3039	13912365631	20000	1000	0	打卡	每天	22	2400	0	2000	15600	17600	信息部		逾期		1	6000
2017.12.22	张华6318	13815104611	10000	500	350	零用贷	周四	20	2000	0	1000	7000	8000	范利丽		逾期		0	0
2017.9.15	胡晓俊6015	18901517678	10000	500	320	零用贷	周四	20	1200	0	1000	7800	8800	顾峰		逾期		14	11480
2017.3.21	唐琛华	18015391395	10000	4000	0	空放	22	1	4000	0	600	6000	6600	陈大大		结清		0	0
2017.4.17	黄涛	13665178715	20000	4000	0	空放	22	1	4000	0	1500	15000	16500	顾峰		结清		0	0
2017.4.20	徐虹	13861822137	10000	2500	0	空放	22	1	2500	0	650	6500	7150	信息部		结清		0	0
2017.4.24	章俊贤	13400043512	10000	3500	0	空放	22	1	3500	0	650	6500	7150	陈大大		结清		0	0
2017.4.27	许锬	13093081783	10000	2500	0	空放	22	1	2500	0	350	7000	7350	朱新约		结清		0	0
2017.4.28	盛彤	13771031226	10000	3000	0	空放	22	1	3000	0	650	6500	7150	信息部		结清		0	0
2017.5.16	尤雁斌	15852501168	20000	4000	0	空放	30	1	4000	0	1500	15000	16500	顾峰		结清		0	0
2017.5.27	庄登雲	18601555786	20000	4000	0	空放	30	1	4000	0	1500	15000	16500	范利丽		结清		0	0
2017.6.22	谢寿清	13961781663	20000	6000	0	空放	22	1	6000	0	1400	13000	14400	顾峰		结清		0	0
2017.7.17	张衡	18100657556	10000	3000	0	空放	22	1	3000	0	700	6500	7200	李聪		结清		0	0
2017.8.28	杨明1877	13656153782	35000	11000	0	空放	22	1	11000	0	3200	24000	27200	朱新约		结清		0	0
2017.10.24	胡晓俊	18901517678	16000	3000	0	空放	22	1	3000	0	900	12000	12900	顾峰		结清		0	0
2017.12.11	戴怡雯3069	13921137315	6000	1500	0	空放	25	1	1500	0	400	4500	4900	信息部		结清		0	0
2017.12.18	王俊磊759x	13861690054	35000	7500	0	空放	25	1	7500	0	2000	27500	29500	李聪		结清		0	0
2017.7.24	刘若秋3682	13961805000	50000	5000	0	空放	22	1	11500	5000	3900	33500	37400	顾峰		还款中		15	75000
2017.8.23	丁锡涛3011	13961715894	20000	5000	0	空放	15	1	5000	0	1300	15000	16300	信息部		还款中		11	27500
2017.9.5	尤佳俊3017	13338770705	50000	7500	0	空放	25	1	7500	2500	0	40000	40000	信息部		还款中		4	35000
2017.9.23	吴如琛4111	13357903793	10000	3000	0	空放	11	1	3000	0	700	7000	7700	朱新约		还款中		10	15000
2017.12.9	陆铸军6519	13382888508	63000	12000	0	空放	10	1	10000	0	3000	50000	53000	信息部		还款中		3	36000
2017.12.25	杨文凯3515	13093087928	10000	2500	0	空放	22	1	2500	0	750	7500	8250	罗		还款中		1	2500
2017.12.29	杨艳3529	15251638698	10000	2500	0	空放	22	1	2500	0	750	7500	8250	信息部		还款中		0	0
2017.12.31	江巍波	0	100000	20000	0	空放	22	1	20000	0	4000	80000	84000	信息部		还款中		0	0';*/

        exit();
        $data = explode("\n",$data_array);
        foreach ($data as $key => $value) {
            $item = explode("\t", $value);
            // 借款时间
            $loan_data = str_replace('.','-',$item['0']);
            // 客户经理，如果客户经理为空，则默认为信息部
            if(strtotime(date('Y-m',strtotime($loan_data))) == strtotime('2018-01')) {
                continue;
            }


            if(!$item['14']) {
                $staff_name = '信息部';
            }else {
                $staff_name = $item['14'];
            }


            // 查看是否存在客户经理信息
            $staffCondition = array(
                'staff_name' => $staff_name,
                'company_id' => $company_id,
            );
            $staff = D('Staff')->findOneStaffByCondition($staffCondition);
            if($staff) {
                // 存在客户经理
                $staff_id  = $staff['staff_id'];
            }else {
                // 不存在客户经理，写入
                $staffData = array(
                    'staff_name' => $staff_name,
                    'department_id' => 6, //业务员
                    'staff_status' => -1,
                    'induction_time' => strtotime($loan_data),
                    'create_time' => strtotime($loan_data),
                    'company_id' => $company_id,
                    'is_old' => 1 // 老数据
                );
                $staff_id = D('Staff')->addStaff($staffData);
            }

            // 查看是否存在外访经理信息
            if(!$item['15']) {
                $foreign_id = 0;
            }else {
                $foreign_name = $item['15'];

                $foreignCondition = array(
                    'staff_name' => $foreign_name,
                    'company_id' => $company_id,
                );
                $foreign = D('Staff')->findOneStaffByCondition($foreignCondition);
                if($foreign) {
                    // 存在外访经理
                    $foreign_id  = $foreign['staff_id'];
                }else {
                    // 不存在外访经理，写入
                    $foreignData = array(
                        'staff_name' => $foreign_name,
                        'department_id' => 7, // 外访
                        'staff_status' => -1,
                        'induction_time' => strtotime($loan_data),
                        'create_time' => strtotime($loan_data),
                        'company_id' => $company_id,
                        'is_old' => 1 // 老数据
                    );
                    $foreign_id = D('Staff')->addStaff($foreignData);
                }

            }

            // 客户姓名
            /*if(!$item['1']) {
                $customer_name = '未知';
            }else {
                $isMatched = preg_match_all('/[\u4e00-\u9fa5]+/', $customer_name, $matches);
                print_r($isMatched, $matches);
                exit();
                $customer_name = $item['1'];
            }*/

            if(!$item['1']) {
                $customer_name = '未知';
            }else {
                preg_match('/[\x80-\xff]{6,30}/', $item['1'], $matches);
                $customer_name = $matches[0];
            }

            // 手机号码
            if(!$item['2']) {
                $customer_phone = '00000000000';
            }else {
                $customer_phone = $item['2'];
            }

            // 客户地址
            if(!$item['17']) {
                $customer_address = '';
            }else {
                $customer_address = $item['17'];
            }

            // 借款金额
            if(!$item['3']) {
                $principal = 0;
            }else {
                $principal = $item['3'] . '.00';
            }

            // 查看客户是否存在
            $customerCondition = array(
                'name' => $customer_name,
                'phone' => $customer_phone,
                'is_delete' => 0,
            );

            $customer = D('Customer')->findOneCustomerByCondition($customerCondition);
            if($customer) {
                $customer_id = $customer['id'];
            }else {
                // 不存在客户信息，写入客户信息
                $customerData = array(
                    'name' => $customer_name,
                    'phone' => $customer_phone,
                    'address' => $customer_address,
                    'recommender' => $staff_id,
                    'company_id' => $company_id,
                    'is_old' => 1,
                    'create_time' => strtotime($loan_data),
                );
                $customer_id = D('Customer')->addCustomer($customerData);
            }

            //查看是否有重复的记录
            $isLoanData = array(
                'customer_id' => $customer_id,
                'create_time' => strtotime($loan_data),
                'principal' => $principal,
                'staff_id' => $staff_id,
                'is_old' => 1,
            );
            $is_loan = D('Loan')->findLoanByALLCondition($isLoanData);
            // 存在
            if($is_loan) {
                continue;
            }

            // 一期本金
            if(!$item['4']) {
                $cyc_principal = 0;
            }else {
                $cyc_principal = $item['4'];
            }

            // 一期利息
            if(!$item['5']) {
                $cyc_interest = 0;
            }else {
                $cyc_interest = $item['5'];
            }

            $cyc_principal = $cyc_principal + $cyc_interest;

            // 贷款类型
            if(!$item['6']) {
                continue;
            }
            switch ($item['6']) {
                case '零用贷' : $product_id = 1;break;
                case '打卡' : $product_id = 2;break;
                case '空放' : $product_id = 3;break;
                case '车贷' : $product_id = 4;break;
                case '红包贷' : $product_id = 5;break;
                default: $product_id = 1;break;
            }

            // 借款周期
            if(!$item['8']) {
                $cyclical = 0;
            }else {
                $cyclical = $item['8'];
            }

            // 共计利息
            $interest = $cyclical * $cyc_interest;

            // 管理费
            if(!$item['9']) {
                $poundage = 0;
            }else {
                $poundage = $item['9'];
            }

            // 保证金
            if(!$item['10']) {
                $bond = 0;
            }else {
                $bond = $item['10'];
            }

            // 同行返点
            if(!$item['11']) {
                $rebate = 0;
            }else {
                $rebate = $item['11'];
            }

            // 实际到账
            if(!$item['12']) {
                $arrival = 0;
            }else {
                $arrival = $item['12'];
            }

            // 实际到账
            if(!$item['13']) {
                $expenditure = 0;
            }else {
                $expenditure = $item['13'];
            }

            // 已还期数
            if(!$item['18']) {
                $repay_cyc = 0;
            }else {
                $repay_cyc = $item['18'];
            }

            // 已还金额
            if(!$item['19']) {
                $repay_money = 0;
            }else {
                $repay_money = $item['19'];
            }

            // 已还金额和
            $repay_rmoney = $cyc_principal * $repay_cyc;
            // 违约金和
            $repay_bmoney = $repay_money - $repay_rmoney;



            // 借款状态
            switch ($item['16']) {
                case '结清' : $loan_status = 1;break;
                case '还款中' : $loan_status = 0;break;
                case '逾期' : $loan_status = -1;break;
                default: $loan_status = 1;break;
            }

            // 具体还款时间
            $juti_data = 1;
            if($product_id == 2 || $product_id == 5) {
                // 打卡红包贷
                $juti_data = 1;
            }

            if($product_id == 1 || $product_id == 4) {
                // 零用贷 车贷
                switch ($item['7']) {
                    case '周一' : $juti_data = 1;break;
                    case '周二' : $juti_data = 2;break;
                    case '周三' : $juti_data = 3;break;
                    case '周四' : $juti_data = 4;break;
                    case '周五' : $juti_data = 5;break;
                    case '周六' : $juti_data = 6;break;
                    case '周日' : $juti_data = 7;break;
                    default: $loan_status = 1;break;
                }
            }

            if($product_id == 3) {
                // 空放
                $juti_data = $item['7'];
            }

            // 到期时间
            $create_time = strtotime($loan_data);
            $weekDay = date("w",$create_time) > 0 ? date("w",$create_time) : 7;
            if($product_id == 1 || $product_id == 4) {
                // 零用贷或车贷
                $add_data = 7 + ($juti_data - $weekDay) + ($cyclical - 1) * 7;
                $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.$add_data.' day');
            }else if($product_id == 2 || $product_id == 5) {
                // 打卡或红包贷
                $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.($cyclical-1).' day');

            }else if($product_id == 3) {
                // 空放
                $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.($juti_data-1)*$cyclical.' day');
            }

            // 写入借款数据
            $loanData = array(
                'customer_id' => $customer_id,
                'principal' => $principal,
                'interest' => $interest,
                'cyc_principal' => $cyc_principal,
                'cyc_interest' => $cyc_interest,
                'product_id' => $product_id,
                'cyclical' => $cyclical,
                'poundage' => $poundage,
                'bond' => $bond,
                'rebate' => $rebate,
                'arrival' => $arrival,
                'expenditure' => $expenditure,
                'staff_id' => $staff_id,
                'foreign_id' => $foreign_id,
                'loan_status' => $loan_status,
                'create_time' => strtotime($loan_data),
                'juti_data' => $juti_data,
                'exp_time' => $exp_time,
                'company_id' => $company_id,
                'is_old' => 1, //老数据
            );

            $loan_id = D('Loan')->addLoan($loanData);
            $setInc = D('Customer')->setIncLoanTimes($customer_id);
            // 写入还款信息
            if($loan_status == 1) {
                // 已经结清
                for($i = 1; $i <= $cyclical; $i++) {
                    /*if($product_id == 2 || $product_id == 5) {
                        // 打卡 每天还
                        $k = $i - 1;
                        $gmt_repay = date('Y-m-d',strtotime($loan_data . ' +' . $k .' day')) . ' 16:00:00';
                    }else if($product_id == 1 || $product_id == 4) {
                        // 零用贷 每周还款
                        $k = $i;
                        $gmt_repay = date('Y-m-d',strtotime($loan_data . ' -1 day +' . $k . ' week')) . ' 16:00:00';
                    }else if($product_id == 3) {
                        // 空放
                        $gmt_repay = date('Y-m-d',strtotime($loan_data . ' +' . $juti_data . ' day')) . ' 16:00:00';
                    }*/
                    $repayData = array(
                        'loan_id' => $loan_id,
                        'cycles' => $i,
                        's_money' => $cyc_principal,
                        'r_money' => $cyc_principal,
                        'b_money' => 0,
                        'gmt_create' => date('Y-m-d',strtotime($loan_data)) . ' 16:00:00',
                        'staff_id' => 1,
                        'pay_style' => 1,
                        'gmt_repay' => date('Y-m-d',strtotime($loan_data)) . ' 16:00:00',
                        'company_id' => $company_id,
                        'is_old' => 1,
                    );
                    $repay_id = D('Repayments')->addRepayments($repayData);
                }
            }else if($loan_status == -1 || $loan_status == 0) {
                // 逾期
                for ($i = 1; $i <= $repay_cyc; $i++) {
                    if($product_id == 2 || $product_id == 5) {
                        // 打卡 每天还
                        $k = $i - 1;
                        $gmt_repay = date('Y-m-d',strtotime($loan_data . ' +' . $k .' day')) . ' 16:00:00';
                    }else if($product_id == 1 || $product_id == 4) {
                        // 零用贷 每周还款
                        $k = $i;
                        $gmt_repay = date('Y-m-d',strtotime($loan_data . ' -1 day +' . $k . ' week')) . ' 16:00:00';
                    }else if($product_id == 3) {
                        // 空放
                        $gmt_repay = date('Y-m-d',strtotime($loan_data . ' +' . $juti_data . ' day')) . ' 16:00:00';
                    }

                    if($i == $repay_cyc && $repay_bmoney > 0) {
                        $r_money = $cyc_principal + $repay_bmoney;
                        $b_money = $repay_bmoney;
                    }else {
                        $r_money = $cyc_principal;
                        $b_money = 0;
                    }
                    $repayData = array(
                        'loan_id' => $loan_id,
                        'cycles' => $i,
                        's_money' => $cyc_principal,
                        'r_money' => $r_money,
                        'b_money' => $b_money,
                        'gmt_create' => $gmt_repay,
                        'staff_id' => 101,
                        'pay_style' => 1,
                        'gmt_repay' => $gmt_repay,
                        'company_id' => $company_id,
                        'is_old' => 1,
                    );
                    $repay_id = D('Repayments')->addRepayments($repayData);
                }
            }
        }
    }

    public function changeCustomer() {
        /*$allLoans = D('Loan')->selectAllBycondition();
        foreach ($allLoans as $key => $item) {
            $oldStaff = D('Staff')->getStaffByID($item['staff_id']);
            $staffCondition = array(
                'staff_name' => $oldStaff['staff_name'],
                'company_id' => $item['company_id'],
            );
            $staffCompany = D('Staff')->findOneStaffByCondition($staffCondition);
            if(!$staffCompany) {
                $staffData = array(
                    'staff_name' => $oldStaff['staff_name'],
                    'phone_number' => $oldStaff['phone_number'],
                    'idcard' => $oldStaff['idcard'],
                    'department_id' => $oldStaff['department_id'],
                    'address' => $oldStaff['address'],
                    'induction' => $item['create_time'],
                    'create_time' => $item['create_time'],
                    'company_id' => $item['company_id'],
                    'is_old' => 2,
                );
                $newStaff = D('Staff')->addStaff($staffData);
                $staff_id = $newStaff;
            }else {
                $staff_id = $staffCompany['staff_id'];
            }

            if($item['foreign_id']) {
                $oldForeign = D('Staff')->getStaffByID($item['foreign_id']);
                $foreignCondition = array(
                    'staff_name' => $oldForeign['staff_name'],
                    'company_id' => $item['company_id'],
                );
                $foreignCompany = D('Staff')->findOneStaffByCondition($staffCondition);
                if(!$staffCompany) {
                    $foreignData = array(
                        'staff_name' => $oldForeign['staff_name'],
                        'phone_number' => $oldForeign['phone_number'],
                        'idcard' => $oldForeign['idcard'],
                        'department_id' => $oldForeign['department_id'],
                        'address' => $oldForeign['address'],
                        'induction' => $item['create_time'],
                        'create_time' => $item['create_time'],
                        'company_id' => $item['company_id'],
                        'is_old' => 2,
                    );
                    $newForeign = D('Staff')->addStaff($foreignData);
                    $foreign_id = $newForeign;
                }else {
                    $foreign_id = $staffCompany['staff_id'];
                }
            }else {
                $foreign_id = '';
            }


            // 查看是否存在客户信息
            $oldCustomer = D('Customer')->getCustomerByID($item['customer_id']);
            $customerCondition = array(
                'name' => $oldCustomer['name'],
                'company_id' => $item['company_id'],
            );
            $customerCompany = D('Customer')->findOneCustomerByCondition($customerCondition);
            if(!$customerCompany) {
                $customerData = array(
                    'name' => $oldCustomer['name'],
                    'phone' => $oldCustomer['phone'],
                    'address' => $oldCustomer['address'],
                    'recommender' => $staff_id,
                    'create_time' => $item['create_time'],
                    'company_id' => $item['company_id'],
                    'is_old' => 2,
                );
                $newCustomer = D('Customer')->addCustomer($customerData);
                $customer_id = $newCustomer;
            }else {
                $customer_id = $customerCompany['id'];
            }

            if($staff_id != $item['staff_id'] || $customer_id != $item['staff_id'] || $foreign_id != $item['foreign_id']) {
                //更新信息
                $loanData = array(
                    'staff_id' => $staff_id,
                    'customer_id' => $customer_id,
                    'foreign_id' => $foreign_id,
                );
                $updateLoan = D('Loan')->updateLoanByID($item['loan_id'],$loanData);
            }
        }*/
    }

    public function setLoanINC() {
        /*$allLoans = D('Loan')->selectAllBycondition();
        foreach ($allLoans as $key => $item) {
            $customer_loantime = D('Customer')->setIncLoanTimes($item['customer_id']);
        }*/
    }

    public function changeRepay() {
        /*$condition['gmt_repay'] = array('GT','2018-01-01 00:00:00');
        $condition['is_old'] = 1;
        $repays = D('Repayments')->listRepaymentsByCondition($condition);

        foreach ($repays as $key => $item) {
            $loan = D('Loan')->getLoanByID($item['loan_id']);
            if($loan['loan_status'] == 1 && $loan['is_old'] == 1) {
                $repaysData['gmt_repay'] = '2017-12-31 16:00:00';
                $re = D('Repayments')->updateRepayments($item['repayments_id'],$repaysData);
            }
        }*/
    }

    public function loanForeign() {
        $data_array = '5487	112
5489	0
5490	0
5491	0
5492	0
5493	0
5494	123
5840	103
6385	122
6386	0
6387	0
6388	0
6389	0
6390	123
1350	87
1351	89
1352	0
1353	79
1354	0
1355	0
1356	0
1358	112
1360	0
1341	79
1342	79
1343	120
1344	0
1345	0
1346	0
1347	87
1348	112
1349	76
1303	0
1304	79
1305	0
1306	0
1308	0
1311	0
1319	79
1320	0
1323	0
1329	87
1333	0
1334	112
1335	0
1336	0
1337	0
1338	0
1339	0
1340	0
1284	79
1285	0
1290	0
1291	0
1292	0
1294	87
1297	0
1322	0
1324	0
1332	0
1265	0
1268	0
1321	0
1256	76
1296	0
1298	112
1316	103
1318	0
1251	0
1252	0
1253	95
1255	76
1258	0
1260	87
1295	112
1313	0
1314	103
1315	0
1328	0
1330	0
1244	95
1245	79
1247	0
1248	0
1250	0
1293	0
1302	0
1310	127
1312	0
1239	0
1241	0
1242	0
1243	95
1301	123
1307	103
1309	0
1327	0
1232	0
1233	0
1234	0
1235	89
1236	0
1237	79
1238	87
1275	123
1286	103
1287	0
1299	0
1300	0
1326	0
1225	0
1226	95
1227	0
1228	0
1229	0
1230	95
1231	0
1273	0
1282	0
1283	0
1325	0
1213	0
1214	0
1216	87
1217	0
1218	0
1219	79
1220	95
1221	76
1222	0
1223	0
1208	87
1212	88
1271	0
1203	0
1205	0
1206	89
1207	99
1210	72
1280	0
1281	127
1193	0
1194	0
1195	0
1196	0
1197	0
1198	87
1199	88
1200	87
1202	87
1204	0
1240	89
1266	0
1267	112
1269	0
1331	0
1183	0
1184	0
1185	0
1186	0
1187	0
1188	0
1189	0
1190	0
1191	94
1192	87
1264	0
1276	0
1277	0
1278	0
1171	0
1172	0
1174	76
1175	0
1176	0
1177	0
1178	0
1179	0
1180	0
1182	0
1262	112
1263	123
1274	103
1317	0
1153	0
1156	0
1159	79
1160	0
1161	0
1162	0
1163	79
1164	0
1165	0
1166	0
1167	0
1168	76
1169	0
1170	0
1257	112
1259	112
1261	0
1270	120
1272	0
5213	297
5480	0
6384	0
5479	316
5486	87
5474	0
5475	316
5476	314
5477	316
5478	316
5482	0
5488	0
4789	0
4790	0
5590	0
5591	0
6383	0
5589	0
4898	0
4788	0
5588	0
5471	316
5472	315
5473	0
5481	0
4787	0
5587	0
4911	303
4912	303
5470	0
5293	316
4783	0
4784	297
4785	297
4786	0
5584	0
5585	0
5586	0
5358	314
6382	0
5469	0
5236	0
5583	0
5398	0
4782	0
5468	316
4779	297
4780	0
4781	0
5582	0
6362	0
4778	297
5579	0
5580	0
5581	0
5467	316
4777	0
5576	0
5577	0
5578	0
4776	0
5574	0
5575	0
6106	0
5463	314
5464	316
5465	314
5466	0
5212	297
5485	0
4772	0
4773	0
4774	0
4775	0
5571	0
5572	0
5573	120
5344	0
6376	0
4897	0
5462	316
4771	0
5569	120
5570	120
5461	0
4767	297
4768	0
4769	0
4770	303
5568	0
6105	0
5397	0
5459	0
5460	0
4765	0
4766	297
5343	314
5357	0
6104	0
5774	0
4764	0
5566	322
5567	0
4910	297
5456	0
5457	0
5458	316
5773	0
4757	0
4758	303
4759	0
4760	297
4761	303
4762	0
4763	303
5292	0
5564	322
5565	322
6375	0
4755	0
4756	297
5291	0
5356	0
4896	0
5211	0
4750	297
4751	0
4752	0
4753	303
4754	297
5563	0
5342	0
6381	0
4895	0
5452	316
5453	0
5454	0
5455	0
4746	0
4747	0
5771	0
4748	0
5772	0
4749	0
5562	322
6102	0
6103	0
5210	0
4744	0
4745	297
5557	0
5558	0
5559	322
5560	0
5561	322
5394	0
5395	0
5396	0
5449	0
5450	0
5451	316
5206	0
5207	0
5208	0
5209	0
4742	297
4743	0
5290	0
5555	0
5556	0
6354	0
6101	0
5341	0
5392	0
5393	0
5446	0
5447	0
5448	0
5205	297
4740	297
4741	303
5770	0
5288	0
5289	316
5553	0
5554	0
6342	0
6352	0
5340	0
5445	0
5203	0
5204	297
5225	0
4738	0
4739	297
5551	322
5552	0
4894	297
5391	0
5202	0
4734	0
4735	0
4736	0
4737	0
5769	0
5355	0
5390	0
5441	0
5442	0
5443	316
5444	316
5201	297
4731	0
4732	0
4733	0
5287	0
5549	0
5550	0
6100	0
5339	0
4893	0
4909	0
5768	0
5548	0
5437	0
5438	0
5439	316
5440	314
5199	297
5200	297
4730	0
5547	0
6344	0
6099	0
6361	0
4892	0
5435	316
5436	315
5197	303
5198	0
4729	0
5544	0
5545	0
5546	0
5354	0
5195	0
5196	0
4727	0
4728	0
5766	0
5767	0
5543	0
6098	0
4726	0
6349	0
5433	0
5434	316
4722	297
4723	297
4724	0
4725	0
4891	0
5432	316
5194	297
4719	297
4720	0
4721	0
5765	0
5338	316
5431	0
5193	0
4718	0
5764	0
6097	103
5337	316
5353	0
5430	0
4715	297
4716	0
4717	297
5429	0
4713	297
4714	0
5763	0
5540	0
5541	0
5542	0
6095	0
6096	0
5388	0
5389	0
5428	316
4710	297
4711	297
4712	0
5762	0
5286	0
6333	0
5192	297
5336	0
5191	0
4709	0
5761	0
5538	0
5539	0
6348	0
5427	0
4708	0
4890	0
5426	0
5188	0
5189	0
5190	0
4707	0
5239	0
5535	103
5536	120
5537	0
5283	0
5284	0
5285	0
6094	0
5332	316
5333	0
5334	0
5335	316
5425	0
5186	0
5187	0
5534	0
5331	316
5387	314
5422	0
5423	0
5424	0
5184	312
5185	0
4705	297
4706	297
5224	0
5760	0
5531	322
5532	0
5533	0
5282	0
5329	316
5330	0
6357	0
5182	0
5183	303
5281	316
5386	315
5484	316
6327	0
5421	0
5180	0
5181	0
4704	303
5759	0
5280	316
6346	0
5328	0
4908	297
5420	0
4703	0
5530	322
5279	0
6339	0
6345	0
6093	0
5179	297
4701	0
4702	0
5758	0
5327	0
5385	0
5419	0
5177	0
5178	297
5223	0
5528	0
5529	0
5278	0
5324	0
6092	0
5325	0
5326	316
6358	0
5352	314
5384	0
5418	0
4698	297
4699	297
4700	0
5222	0
5757	0
5527	0
6091	0
5383	0
5176	297
4697	297
4693	0
4694	0
4695	0
4696	0
5221	0
5756	0
5526	322
4846	0
5382	0
5174	297
5175	0
4692	303
6317	0
6323	0
5322	316
6090	322
5323	0
5351	0
5173	297
4691	0
5754	0
5755	0
5525	0
6326	0
6087	0
6088	322
6089	122
5350	0
5172	297
4689	0
4690	0
5235	0
5753	0
5524	322
6320	0
6086	322
5321	0
5381	0
4889	94
5170	0
5171	0
4687	297
4688	0
5523	0
6314	0
6322	0
6337	0
6085	0
5380	0
5416	314
5417	314
4686	297
5752	0
5521	0
5522	0
5277	314
6319	0
5320	0
5169	264
5276	314
5319	315
5379	0
5414	0
5415	315
4685	0
5274	314
5275	314
6084	0
5317	314
5318	316
5378	316
5168	297
4682	0
4683	0
4684	297
5751	0
5520	0
5271	0
5272	0
5273	0
5316	0
5377	0
5167	0
4681	0
5750	0
5270	314
6309	0
5349	0
5166	0
4676	0
4677	0
4678	0
4679	0
4680	0
5749	0
5518	103
5519	0
5268	0
5269	0
6307	0
6081	0
6082	0
5315	0
6083	0
5413	316
4675	297
5266	316
5267	314
5314	0
5348	316
6374	0
5165	0
4671	297
4672	0
4673	297
4674	0
5748	0
6330	0
5312	0
6336	0
5313	316
5359	0
4888	0
4669	303
4670	0
5412	0
5164	0
4667	303
4668	0
5747	0
5517	0
6079	322
6080	0
6347	0
5411	0
4664	297
4665	0
4666	0
5264	0
5265	316
6078	0
5410	0
5163	0
4661	0
4662	0
4663	0
5261	0
5262	0
5263	314
6316	0
5311	0
5376	0
5409	0
4657	0
4658	0
4659	0
4660	0
5516	322
6076	0
6077	322
6343	0
5407	316
5408	0
4656	0
6072	0
6073	0
6074	0
6075	322
5375	0
4845	0
5162	0
5515	322
6312	0
6071	0
4887	0
4651	297
4652	0
4653	0
4654	0
4655	0
5514	120
5259	0
5260	0
5310	316
4886	297
5406	0
4648	0
4649	303
4650	0
5746	0
5256	316
5257	0
5513	322
5258	0
6302	0
6310	0
5374	314
4885	0
5403	0
5404	316
5405	316
4645	0
4646	297
4647	0
5255	0
5512	0
5309	314
6341	0
5372	0
5373	0
4642	0
4643	0
4644	303
5161	297
5745	0
5251	0
5252	0
5253	316
5509	0
5254	314
5510	0
5511	0
6318	0
6070	322
5308	0
6351	0
5400	314
5401	316
5402	316
4641	0
5160	297
5249	0
5250	0
5508	0
6305	0
6315	0
6329	0
5306	0
5307	314
6335	0
6353	0
5347	0
4844	0
5369	0
5370	0
5371	0
4640	0
5507	0
5305	0
4843	0
5399	314
4637	0
4638	0
4639	0
5483	315
5247	0
5248	315
5506	0
5303	0
5304	314
5368	316
4635	0
4636	0
5159	0
5744	0
5234	297
5240	314
5241	315
5242	316
5243	314
5244	0
5245	0
5246	315
5505	0
5294	0
5295	316
5296	0
5297	0
5298	314
5299	0
5300	0
6324	0
5301	0
5302	315
5345	0
5346	0
5360	0
5361	0
5362	316
5363	0
5364	0
5365	316
5366	314
5367	0
4841	0
4842	0
4627	297
4628	0
4629	0
4630	264
4631	297
4632	0
4633	0
4634	0
5742	0
5743	0
6300	0
6068	322
6069	0
4625	0
4626	297
5158	297
6293	0
4620	0
4621	0
4622	0
4623	0
4624	0
5157	297
5741	0
6067	0
4617	264
4618	264
4619	297
5739	0
6065	322
6066	0
4615	0
4616	0
5156	0
6292	0
6304	0
6064	0
5155	0
6299	0
6380	0
4610	297
4611	264
4612	0
4613	0
4614	0
5154	297
5504	0
6294	0
6063	0
6332	0
5153	264
5740	0
4609	0
6296	0
6062	322
4608	297
4884	0
5738	0
6060	322
6061	322
4606	0
4607	0
5152	0
5737	0
4601	0
4602	0
4603	0
4604	0
4605	0
4840	0
5150	0
5151	264
5736	0
6058	120
6059	322
6363	0
4599	0
4600	0
5149	297
5735	0
4597	0
4598	297
4883	0
5734	0
4593	0
4594	0
4595	297
4596	0
5148	0
4907	264
5503	0
6057	0
4592	297
5146	0
5147	0
6273	0
6356	0
4590	0
4591	297
4839	0
6056	322
4838	0
4589	0
5145	0
4587	0
4588	0
4882	297
5144	0
5502	0
6291	0
6055	322
4584	0
4585	0
4586	297
5140	0
5141	0
5142	297
5143	0
5233	0
6288	0
4836	0
4837	0
4583	297
5137	0
5138	297
5139	0
5733	0
5232	297
5501	0
6277	0
6052	0
6053	0
6054	0
6340	0
4581	264
4582	297
6379	0
5732	0
5238	264
6051	0
4580	297
6050	0
4578	0
4579	297
5731	0
5500	0
6049	0
4881	0
5231	0
6048	322
6360	0
4575	0
4576	297
4577	297
5132	0
5133	297
5134	264
5135	0
5136	0
5729	0
5730	0
6272	0
6047	0
4574	297
5131	0
6044	0
6045	0
6046	103
6331	0
4571	297
4572	0
4573	0
5728	0
4570	0
5129	0
5130	297
4880	297
5727	0
6278	0
6040	0
6041	0
6042	103
6043	0
4569	0
6373	0
4835	0
5127	297
5128	0
5726	0
6284	0
6039	103
4567	0
4568	264
5126	297
4879	0
6265	0
6325	0
4566	0
6359	0
5724	0
5725	0
5499	103
6037	103
6038	103
4563	0
4564	297
4565	0
4878	0
4561	0
4562	297
6378	0
4877	0
5722	0
5723	0
6035	0
6036	0
4558	297
4559	264
4560	297
5123	0
5124	0
5125	0
4876	0
5720	0
6033	0
6034	0
6338	0
4556	297
4557	297
5721	0
4875	0
5120	0
5121	0
5122	264
6031	0
6032	103
4553	297
4554	0
4555	0
5716	0
5717	0
5718	0
5719	0
6029	0
6030	0
4550	0
4551	297
4552	297
5118	0
5119	0
5498	0
6328	0
4548	0
4549	264
5117	297
4874	0
6254	0
6027	0
6028	0
6286	0
4546	0
4547	0
5116	0
6026	0
4545	0
6266	0
6271	0
6025	0
5114	0
5115	0
5714	0
5715	0
6240	0
6251	0
6264	0
6022	0
6023	0
6024	103
4543	0
4544	297
6355	0
5112	0
5113	0
5713	0
6239	0
6021	0
6298	0
6311	0
4539	0
4540	297
4541	297
4542	0
4834	0
5111	0
4873	0
6258	0
6018	0
6019	0
6020	103
6308	0
4535	0
4536	0
4537	297
4538	297
4833	0
5110	0
5712	0
5220	0
6274	0
4534	0
4906	297
6248	0
6255	0
6017	103
4531	0
4532	0
4533	0
5109	312
5711	0
5108	0
5710	0
6256	0
4529	297
4530	0
5709	0
4527	0
4528	0
5106	297
5107	0
6250	0
6263	0
6016	0
4525	0
4526	297
5105	0
5707	0
5708	0
5497	103
4522	0
4523	0
4524	0
5104	0
5706	0
5103	0
5705	0
6268	0
6014	103
6015	0
4518	0
4519	297
4520	0
4521	263
6313	0
5098	0
5099	0
5100	0
5101	0
5102	297
4517	297
6334	0
6013	0
5097	0
5702	0
5703	0
5704	0
6012	0
6303	0
4516	0
5096	0
4872	0
6231	0
6295	0
6321	0
5093	0
5094	0
5095	297
6252	0
4515	297
5700	0
5701	0
6246	0
6010	0
6011	0
4511	263
4512	0
4513	297
4514	0
5089	0
5090	0
5091	0
5092	0
6237	0
6245	0
6247	0
6009	0
4508	0
4509	297
4510	297
5084	0
5085	264
5086	0
5087	0
5088	0
6377	0
6262	0
4507	297
6222	0
6236	0
4505	297
4506	0
5083	0
5697	0
5698	0
5699	0
6223	0
6227	0
6007	0
6008	0
4503	297
4504	0
5082	0
5696	0
6212	0
6230	0
6238	0
6249	0
6006	0
4502	0
5081	0
4831	0
4832	0
4871	0
6004	0
6005	0
4500	297
4501	264
5079	0
5080	263
6002	0
6003	0
6259	0
4496	297
4497	0
4498	0
4499	297
5077	0
5078	0
4870	0
5695	0
6221	0
6000	0
6001	103
5496	103
6301	0
5074	0
5075	297
5076	0
6372	0
5999	0
4494	0
4495	0
5693	0
5694	0
6242	0
5998	103
5072	0
5073	0
5692	0
5996	103
5997	0
6276	0
4491	0
4492	0
4493	0
5070	0
5071	297
5691	0
6207	0
5995	0
4488	0
4489	284
4490	297
5069	0
5689	0
5690	0
6204	0
6210	0
5992	103
5993	0
5994	0
4487	0
5066	0
5067	0
5068	0
4830	0
5688	0
6216	0
6226	0
5991	0
4486	0
6287	0
4829	0
6215	0
5988	103
5989	120
5990	103
6257	0
4484	0
4485	0
5987	120
4483	0
4869	0
5686	0
5687	0
6202	0
6214	0
4482	284
5684	0
5685	0
5986	0
4479	284
4480	77
4481	284
5065	284
6260	0
5064	284
5683	0
6233	0
5984	0
5985	0
5682	0
5983	120
4476	284
4477	0
4478	0
5061	0
5062	264
5063	284
6193	0
5980	0
5981	0
5982	0
4474	0
4475	0
4472	0
4473	297
5060	297
6203	0
5978	0
5979	0
6283	0
5059	0
4868	0
6211	0
5976	0
5977	0
4469	0
4470	284
4471	0
6281	0
6290	0
5057	264
5058	297
5681	0
5973	0
5974	103
5975	0
4463	0
4464	284
4465	284
4466	0
4467	0
4468	0
4828	284
6191	0
5680	0
5971	0
5972	0
4462	284
6270	0
5055	0
5056	0
5679	0
5969	103
5970	0
4461	284
6267	0
6306	0
5054	0
5967	0
5968	0
4459	0
4460	0
6253	0
6194	0
5966	0
6282	0
5052	0
5053	0
5051	0
5678	0
5964	103
5965	0
4457	284
4458	0
5049	0
5050	284
5676	0
5677	0
6200	0
6205	0
6206	0
6208	0
5961	0
5962	103
5963	103
6235	0
4456	0
6297	0
5044	0
5045	284
5046	0
5047	284
5048	0
6371	0
6198	0
5959	0
5960	333
4454	0
4455	0
5042	0
5043	0
5673	0
5674	0
5675	0
4451	284
4452	0
4453	0
5040	0
5041	264
5956	0
5957	103
5958	0
4448	0
4449	0
4450	0
5219	0
5039	284
4827	0
5955	120
6195	0
6261	0
5672	0
6201	0
5954	0
6224	0
4442	0
5671	0
6183	0
5953	0
4439	0
4440	0
4441	284
5035	284
5036	264
5037	0
5952	120
4436	0
4437	0
4438	0
5033	0
5034	0
6182	0
6192	0
5950	0
5951	103
4434	0
4435	0
6228	0
6285	0
5032	0
6181	0
6197	0
5948	103
5949	120
4431	0
4432	0
4433	0
6225	0
6232	0
5029	284
5030	0
5031	0
4826	0
5669	0
5670	0
6186	0
4430	0
6241	0
6175	0
5668	0
6169	0
6180	0
5947	0
4429	263
5028	0
5666	0
5667	0
6179	0
5945	0
5946	0
4427	0
4428	0
5027	0
4866	0
4867	284
6176	0
6189	0
6190	0
4426	0
6218	0
6229	0
5025	284
5026	0
5941	0
5942	0
5943	332
5944	0
4424	0
4425	284
5022	284
5023	0
5024	263
5664	0
5665	0
5937	0
5938	0
5939	0
5940	0
4421	261
4422	0
4423	0
6244	0
5020	285
5021	0
6220	0
6174	0
5663	0
4417	0
4418	0
4419	0
4420	0
5019	0
5662	0
6187	0
5935	330
5936	0
4416	284
6219	0
6275	0
5018	284
6184	0
4413	0
4414	0
4415	0
5661	0
5932	103
5933	0
5934	0
4410	0
4411	0
4412	0
5017	0
4408	0
4409	263
6269	0
5660	0
5931	0
4406	263
4407	0
5016	0
5658	0
5659	0
5928	0
5929	0
5930	0
4402	0
4403	0
4404	284
4405	0
5495	0
5015	0
6370	0
5657	0
4401	0
6289	0
5013	0
5014	284
6165	0
6166	0
5655	0
5656	0
5927	330
4397	0
4398	0
4399	0
4400	263
5230	264
5012	284
5654	0
6171	0
5924	0
5925	103
5926	330
4396	0
4395	0
5229	0
5009	284
5010	0
5011	0
4824	0
6164	0
5653	0
4823	0
4825	0
6156	0
5651	0
5652	0
6167	0
5923	103
4393	0
4394	0
6213	0
5008	0
6159	0
5650	0
6172	0
5921	0
4386	0
5922	0
4387	0
4388	0
4389	0
4390	0
4391	0
4392	284
5218	0
5007	284
4865	0
5648	0
5649	0
6170	0
4382	0
5918	0
4383	0
5919	103
4384	0
5920	0
4385	284
5004	0
5005	0
5006	0
4822	0
5646	0
5647	0
4379	0
5915	0
4380	0
5916	103
4381	0
5917	0
5001	0
5002	0
5003	0
4821	284
5644	0
5645	0
6185	0
6369	0
6151	0
5642	0
5643	0
4375	0
4376	0
4377	0
5913	0
4378	0
5914	330
5912	330
5000	0
5639	0
5640	0
5641	0
4374	0
6173	0
5636	0
5637	0
5638	0
6152	0
4373	0
6188	0
5228	0
5635	0
4370	0
4371	284
4372	0
5910	0
5911	103
4999	0
5634	0
4367	0
4368	0
4369	0
5908	0
5909	0
4998	284
4820	0
4363	263
4364	0
4365	0
4366	284
5905	0
5906	0
5907	0
6168	0
4360	0
4361	0
4996	0
4997	0
4362	263
5903	330
5904	0
4995	0
6143	0
5633	0
6150	0
4359	0
6153	0
5632	0
6149	0
4358	0
5901	103
5902	0
4994	0
5631	0
6144	0
4357	0
4992	0
4993	0
6141	0
5630	0
4355	284
4356	0
5900	0
6163	0
6196	0
4991	0
5898	103
5899	103
4989	0
4990	0
5628	0
5629	0
4352	0
4353	0
4354	0
5894	0
5895	0
5896	0
5897	330
6368	0
5627	0
6145	0
5891	103
5892	330
5893	103
6178	0
4988	0
6367	0
5626	0
4349	0
4350	284
4351	0
6217	0
6135	0
4347	0
4348	0
5890	103
4986	0
4987	0
6134	0
4344	0
4345	0
4346	0
5888	0
5889	0
4984	0
4985	284
6366	0
4340	284
4341	0
4342	0
4343	0
5625	0
6139	0
6148	0
4338	0
4339	0
5887	103
4983	0
4336	0
4337	0
6138	0
5885	0
5886	0
4818	0
4819	0
4334	284
4335	0
5622	0
5623	0
5624	0
5884	0
4864	0
4981	0
4982	0
4331	0
4332	0
4333	0
5621	0
5880	0
5881	103
5882	103
5883	0
6158	0
4979	0
4980	284
4817	0
4330	0
5620	0
5879	103
4327	0
4328	0
4329	0
5619	0
6243	0
4977	0
4978	0
6365	0
5618	0
5878	0
4863	0
6155	0
5616	0
5617	0
4976	0
4325	0
4326	0
5876	103
5877	0
6133	0
4975	0
4322	0
4323	0
4324	284
5875	0
6199	0
4320	0
4321	284
5615	0
5873	0
5874	0
5614	0
5872	330
4973	284
4974	0
4816	0
4316	0
4317	0
4318	0
4319	263
6121	0
5870	330
5871	0
6127	0
4309	0
4310	0
4311	0
4312	0
4313	0
4314	0
4315	0
5613	0
5869	0
6140	0
4307	0
4308	0
5867	330
5868	103
6146	0
6161	0
4971	263
4972	0
4304	284
4305	285
4306	284
5865	0
5866	0
6136	0
6234	0
4970	284
4303	0
5864	330
6129	0
4302	284
5861	0
5862	0
5863	0
5612	0
4862	0
5611	0
4300	0
4301	285
5860	0
6160	0
4969	0
4299	285
4815	0
5859	0
5610	0
4968	0
4297	0
4298	285
5857	0
5858	0
5608	0
5609	0
6122	0
4861	0
4292	0
4293	284
4294	263
4295	284
4296	0
4814	0
5855	0
5856	103
5606	0
5607	0
4291	0
5853	103
5854	0
5605	0
4289	0
4290	284
5850	0
5851	330
5852	0
5227	0
4860	0
6154	0
4967	264
4288	0
4813	0
5846	0
5847	0
5848	103
5849	0
4905	284
4966	284
4285	0
4286	0
4287	0
6124	0
6125	0
6157	0
6209	0
4965	285
5842	103
5843	103
5844	330
5604	0
4963	264
4964	0
4283	0
4284	0
4812	0
5839	330
5841	0
6364	0
6120	0
6126	0
5217	263
5226	0
5836	0
5837	0
5838	0
6113	0
6119	0
5835	0
5832	330
5833	0
5834	0
6147	0
4962	0
4280	0
4281	0
4282	284
5830	103
5831	330
5603	0
6131	0
4859	0
6177	0
4961	0
4278	284
4279	284
5827	0
5828	0
5829	0
6109	0
4959	284
4960	0
6128	0
6130	0
4274	0
4275	0
4276	0
4277	0
5824	0
5825	0
5826	103
5602	0
4957	0
4958	284
4273	0
5822	0
5823	330
5601	0
6117	0
4956	0
4811	263
4272	0
5820	330
5821	103
4810	0
6114	0
5216	284
4270	0
4271	284
5819	0
5600	0
6137	0
4954	0
4955	263
6280	0
5818	0
6123	0
4952	0
4953	0
4267	0
4268	263
4269	0
5815	0
5816	103
5817	331
6111	0
6115	0
6162	0
5215	0
4264	284
4265	284
4266	284
5814	0
6108	0
6116	0
4951	0
4262	0
4263	0
5810	0
5811	330
5812	0
5813	0
6110	0
6112	0
4261	0
5808	0
5809	0
4259	0
4260	284
5599	0
4256	0
4257	0
4258	0
5805	103
5806	0
5807	330
4858	284
5804	0
5802	330
5803	0
4809	0
5598	0
6132	0
6142	0
4950	284
4254	263
4255	261
5799	0
5800	103
5801	0
5595	0
5596	0
5597	0
6279	0
4252	284
4253	0
5798	0
5592	0
5593	0
5594	0
4250	0
4251	284
5796	0
5797	0
4248	0
4249	284
5793	0
5794	0
5795	330
4246	0
4247	284
5790	0
5791	0
5792	103
4245	0
5788	103
5789	0
4949	0
4244	284
5786	330
5787	330
4241	285
4242	284
4243	263
5783	103
5784	331
5785	103
4808	0
6107	0
4857	0
4240	284
5781	330
5782	0
5779	103
5780	330
4948	284
4238	0
4239	263
5775	0
5776	0
5777	0
5778	0
6118	0
4235	263
4236	284
4237	284
4856	263
4947	263
4232	0
4233	284
4234	284
4230	0
4231	0
4946	0
4229	263
4228	284
4945	263
4227	284
4807	0
4224	0
4225	0
4226	263
4222	0
4223	0
4855	0
4220	284
4221	0
4854	0
4217	284
4218	263
4219	285
4216	284
4852	284
4215	285
4853	0
4214	0
4212	0
4213	284
4904	0
4210	263
4211	263
4944	263
4209	263
4207	263
4208	284
4206	273
4943	263
4204	0
4205	263
4203	262
4202	285
4903	284
4806	263
4942	0
4200	0
4201	263
4941	0
4198	0
4199	263
4805	263
4195	0
4196	284
4197	0
4193	284
4194	263
4940	284
4191	0
4192	0
4851	0
4189	0
4190	0
4184	263
4185	263
4186	0
4187	263
4188	0
4939	263
4181	0
4182	267
4183	0
4938	0
4180	263
4804	0
4179	0
5237	0
4937	262
4176	262
4177	0
4178	0
4934	0
4935	0
4936	0
4174	0
4175	0
4933	0
4173	263
4803	0
4171	0
4172	263
4931	0
4932	0
4168	0
4169	0
4170	0
4802	0
4166	0
4167	263
4161	0
4162	0
4163	263
4164	0
4165	0
4160	263
4156	0
4157	0
4158	0
4159	0
4850	263
4155	264
4153	0
4154	0
4929	0
4930	263
4149	262
4150	0
4151	0
4152	264
4928	0
4147	264
4148	264
4144	281
4145	263
4146	88
4142	263
4143	0
4801	0
4136	0
4137	263
4138	0
4139	263
4140	0
4141	264
4926	264
4927	264
4800	0
4133	268
4134	0
4135	264
4130	263
4131	264
4132	0
4925	0
4127	268
4128	264
4129	268
5214	0
4125	264
4126	0
4902	88
4124	264
4120	263
4121	0
4122	0
4123	0
4116	0
4117	264
4118	0
4119	0
4110	0
4111	262
4112	0
4113	0
4114	262
4115	264
4924	264
4107	263
4108	0
4109	262
4901	264
4798	264
4799	262
4100	0
4101	0
4102	262
4103	0
4104	0
4105	88
4106	271
4096	271
4097	263
4098	0
4099	0
4849	0
4091	0
4092	88
4093	0
4094	0
4095	264
4900	263
4085	0
4086	0
4087	0
4088	264
4089	263
4090	0
4083	0
4084	0
4922	0
4923	0
4080	0
4081	264
4082	0
4797	0
4078	0
4079	262
4848	0
4920	263
4921	0
4796	0
4919	0
4795	0
4074	0
4075	263
4076	0
4077	264
4071	0
4072	0
4073	262
4918	264
4067	0
4068	0
4069	262
4070	0
4066	0
4065	0
4917	263
4899	0
4794	264
4063	0
4064	263
4792	264
4793	263
4062	263
4915	88
4916	264
4056	0
4057	262
4058	263
4059	0
4060	263
4061	264
4791	264
4051	262
4052	0
4053	0
4054	0
4055	264
4914	264
4049	268
4050	262
4046	0
4047	0
4048	88
4847	0
4042	0
4043	88
4044	0
4045	263
4913	264
4039	264
4040	262
4041	262
4036	262
4037	263
4038	0
5038	0
4447	0
4443	284
4444	284
4445	0
4446	0
5845	0
6350	0';
        $data = explode("\n",$data_array);
        foreach ($data as $key => $value) {
            $item = explode("\t", $value);
            $loanData = array(
                'foreign_id' => $item['1'],
            );
            $updateLoan = D('Loan')->updateLoanByID($item['0'],$loanData);
        }
    }


    public function checkLoanStatus() {
        $data_array = '2016.11.3	强立雨	18888045224	10000	500	210	零用贷	周还	20	2000	1000	1200	7000	8200	邵静	成乃柏车	逾期		8	5680
2016.11.8	单冬梅	13013632717	10000	334	280	零用贷	周还	30	2000	1000	1200	7000	8200	陶超	成乃柏车	逾期		10	6140
2016.11.10	王朝晖	15052274501	8000	534	224	零用贷	周还	15	1800	800	960	5400	6360	王倩	成乃柏	逾期		2	1600
2016.11.10	潘美珍	18852475857	8000	400	224	零用贷	周还	20	1800	800	960	5400	6360	邵静	成乃柏车	逾期		6	3744
2016.11.13	李敏	13338115597	10000	334	280	零用贷	周还	30	2000	1000	1200	7000	8200	崔日成	陆春松车	逾期		10	6140
2016.11.16	王强	13906170574	10000	500	210	零用贷	周还	20	2000	500	1200	7500	8700	崔日成	成乃柏车	逾期		4	3140
2016.11.18	朱磊	15961757417	8000	534	224	零用贷	周还	15	1500	800	960	5700	6660	崔日成		逾期		7	5306
2016.11.19	李涛	13023367029	10000	667	350	零用贷	周还	15	2300	1000	1200	6700	7900	邵静	陆春松车	逾期	宜兴	5	3750
2016.11.19	刘其霞	15061502182	8000	400	224	零用贷	周还	20	1200	800	960	6000	6960			逾期		3	1872
2016.11.23	傅炎嘉	15852777710	8000	400	168	零用贷	周还	20	1200	800	400	6000	6400	陈玮伟		逾期		9	5112
2016.11.23	徐惠峰	13382880116	6000	300	168	零用贷	周还	20	1200	600	300	4200	4500			逾期		7	3276
2016.12.1	胡寒	15995370213	8000	400	300	零用贷	周还	20	2500	800	1200	4700	5900	邵静	成乃柏车	逾期	宜兴	5	3500
2016.12.7	苏震	15961703999	10000	500	350	零用贷	周还	20	3000	500	2000	6500	8500	黄旭		逾期		6	5100
2016.12.9	周鹏飞	13961777172	10000	334	366	零用贷	周还	30	2500	500	1500	7000	8500	张敏	成乃柏车	逾期		4	2800
2016.12.9	曹建波	13585075369	6000	300	210	零用贷	周还	20	1400	600	600	4000	4600	信息部	成乃柏车	逾期		6	3060
2016.12.14	孙彪	18018358103	8000	400	350	零用贷	周还	20	1800	800	1200	5400	6600	邵静		逾期		3	2250
2016.12.15	周龙	13585053876	7000	350	250	零用贷	周还	20	1700	700	1050	4600	5650	邵静		逾期		2	2800
2016.12.15	薛雄	15861600046	12000	400	400	零用贷	周还	30	2300	700	1200	9000	10200	张敏	陆春松车	逾期	江阴	1	800
2016.12.21	赵煜辉	15301515870	6000	300	168	零用贷	周还	20	1200	300	600	4500	5100	吕晓慧		逾期		8	3744
2016.12.21	陈洪良	15862451648	8000	200	80	打卡	每天	40	400	800	800	5520	6320	邵静		逾期		2	560
2016.12.23	陈霞	15995229472	10000	500	350	零用贷	周还	20	1800	1000	1000	7200	8200	吴丹		逾期		0	0
2016.12.26	李钮根	18115336552	6000	300	400	零用贷	周还	15	1200	600	600	4200	4800	崔日成		逾期		3	2100
2016.12.26	李涛	13915393392	8000	400	350	零用贷	周还	20	1900	400	800	5700	6500	邵静		逾期		5	3750
2016.12.26	王钢	13771614474	10000	334	350	零用贷	周还	30	2300	500	1000	7200	8200	邵静		逾期		1	684
2016.12.27	顾丽莉	18051576288	8000	400	300	零用贷	周还	20	2100	800	1500	5100	6600	崔日成	陆春松	逾期		6	4200
2016.12.29	张伟	13912481761	6000	300	300	零用贷	周还	20	1300	300	600	4400	5000	吕晓慧		逾期		8	5100
2016.12.30	蒋高安	18626366498	10000	1000	350	零用贷	周还	10	2500	500	1000	7000	8000	张敏	陆春松车	逾期	宜兴	2	2700
2017.1.3	欧黎峰	13861823537	8000	400	300	零用贷	周还	20	1600	800	800	5600	6400	张敏	夏飞车	逾期		1	700
2017.1.6	杨庆	18795690590	8000	400	300	零用贷	周还	20	1200	800	800	6000	6800	崔日成		逾期	宜兴	4	2800
2017.1.9	曹荣	18801519152	10000	500	350	零用贷	周还	20	2000	500	1000	7500	8500			逾期		1	850
2017.1.13	俞辉	13861884799	7000	200	250	零用贷	周还	35	1300	700	700	5000	5700		陆春松车	逾期		10	6100
2017.1.17	黎家鸣	15716181556	6000	200	250	零用贷	周还	30	1300	600	600	4100	4700	俸小倩	陆春松车	逾期		4	1800
2017.2.9	管阳	13912374436	6000	200	250	零用贷	周还	30	1400	600	600	4000	4600	朱新约	陆春松车	逾期		1	500
2017.2.11	陈强	13656189631	8000	400	280	零用贷	周还	20	1700	800	800	5500	6300	陈大大		逾期		7	4760
2017.2.14	封磊	15902140791	8000	200	224	零用贷	周还	40	1700	800	800	5500	6300	陈大大	陆春松车	逾期		4	4696
2017.2.16	刘朋飞	15861498003	10000	500	300	零用贷	周还	20	2000	500	1000	7500	8500	邵静	夏飞车	逾期		6	4800
2017.2.21	修竹	13914135500	6000	200	200	零用贷	周还	30	1200	600	600	4200	4800	邵静		逾期		5	4500
2017.2.28	杨晓良	18206186541	10000	250	210	零用贷	周还	40	2000	0	1000	8000	9000	邵静	夏飞车	逾期		2	920
2017.3.6	郁旦	15251523696	10000	500	330	零用贷	周还	20	2500	0	1000	7500	8500	邵静		逾期		2	1660
2017.3.8	马广富	13616170726	10000	1000	280	零用贷	周还	10	2000	0	1000	8000	9000	邵静		逾期		3	3840
2017.3.8	沈建新	18015102816	8000	800	280	零用贷	周还	10	1800	0	800	6200	7000	崔日成		逾期		1	1080
2017.3.9	宋翩	15251695041	6000	200	200	零用贷	周还	30	1800	0	600	4200	4800	吕晓慧		逾期		4	1600
2017.3.9	张建南	13196521235	8000	267	283	零用贷	周还	30	2300	0	800	5700	6500	崔日成	陆春松车	逾期	江阴	27	15440
2017.3.12	高红岗	17315507645	10000	500	300	零用贷	周还	20	2000	1000	1000	7000	8000	邵静		逾期		6	4800
2017.3.13	袁建平	18205030299	7000	350	300	零用贷	周还	20	2000	0	700	5000	5700	邵静		逾期		8	5200
2017.3.13	陈波	15298401771	10000	500	350	零用贷	周还	20	2000	0	1000	8000	9000	吴丹	夏飞车	逾期		2	1700
2017.3.15	朱念坤	13771059770	7000	350	250	零用贷	周还	20	2000	0	700	5000	5700	邵静	夏飞车	逾期		15	9200
2017.3.15	黄建海	13915209474	15000	750	470	零用贷	周还	20	4000	0	1500	11000	12500	邵静		逾期	江阴	6	7320
2017.3.16	张君	13771350876	10000	334	246	零用贷	周还	30	2000	0	500	8000	8500			逾期	宜兴	7	14060
2017.3.17	陈静	15312298388	10000	334	316	零用贷	周还	30	2000	0	1000	8000	9000	吕晓慧		逾期	宜兴	8	5200
2017.3.21	王莉	15949282818	10000	500	300	零用贷	周还	20	2000	1000	1000	7000	8000	邵静	成乃柏车	逾期	江阴	11	9100
2017.3.21	王奇	15061590086	6000	400	250	零用贷	周还	15	1800	0	600	4200	4800	邵静		逾期	宜兴	4	2600
2017.3.23	胡东方	18351589993	8000	400	280	零用贷	周还	20	2000	0	800	6000	6800	崔日成	夏飞	逾期		16	10880
2017.3.24	陆丽静	13771056560	6000	200	210	零用贷	周还	30	1800	0	600	4200	4800	邵静	夏飞车	逾期		3	1730
2017.3.25	黎应龙	15052108094	20000	1000	600	零用贷	周还	20	4500	0	2000	15500	17500	崔日成	成乃柏车	逾期		1	1600
2017.3.30	胡幼军	13961566795	8000	400	280	零用贷	周还	20	2000	0	800	6000	6800	吕晓慧		逾期	宜兴	2	1360
2017.3.31	李洋	15651515695	10000	1000	280	零用贷	周还	10	2200	0	1000	7800	8800	邵静		逾期		1	1280
2017.4.6	万文宁	15251669822	8000	800	320	零用贷	周还	10	2200	0	800	5800	6600	邵静	夏飞车	逾期		2	2240
2017.4.7	钱彩凤	15851633107	10000	334	366	零用贷	周还	30	2500	0	1000	7500	8500		陆春松车	逾期		3	2100
2017.4.7	周银凤	13961679738	14000	467	500	零用贷	周还	30	4000	0	1400	10000	11400	邵静		逾期	江阴	7	6769
2017.4.11	吴喜喜	13915721379	10000	500	350	零用贷	周还	20	2500	0	1000	7500	8500	邵静	夏飞车	逾期	江阴	2	1700
2017.4.11	吴平	15861494360	8000	400	280	零用贷	周还	20	2000	0	0	6000	6000			逾期		13	8940
2017.4.14	王平	13701512326	6000	300	210	零用贷	周还	20	1700	0	600	4300	4900	崔日成		逾期		6	3060
2017.4.15	钱霞萍	13621522583	6000	300	200	零用贷	周还	20	1500	0	0	4500	4500			逾期	江阴	3	1500
2017.4.17	蔡亦斌	13584214410	10000	500	350	零用贷	周还	20	2200	0	1000	7800	8800	邵静		逾期	宜兴	9	8350
2017.4.17	陈建丰	13812532591	8000	267	283	零用贷	周还	30	2000	0	800	6000	6800	邵静		逾期		15	8250
2017.4.19	毛卉	15961732269	20000	1000	560	零用贷	周还	20	4000	1500	2000	14500	16500			逾期		9	14040
2017.4.19	吴寅	13921506908	7000	350	245	零用贷	周还	20	1700	0	700	5300	6000	吕晓慧	夏飞车	逾期		6	4075
2017.4.20	吴海	15251439039	10000	500	300	零用贷	周还	20	2000	1000	0	7000	7000			逾期		7	5600
2017.4.20	汤卫燕	18151553981	8000	400	230	零用贷	周还	20	2200	0	800	5800	6600	邵静		逾期		6	3780
2017.4.22	陈赟	13812259601	7000	700	250	零用贷	周还	10	2000	0	700	5000	5700	崔日成		逾期	宜兴	3	2850
2017.4.24	屈君飞	13584126912	6000	300	210	零用贷	周还	20	1500	0	600	4500	5100	季宏		逾期	江阴	10	5100
2017.4.24	杨振宇	15961568243	6000	300	300	零用贷	周还	20	1500	0	600	4500	5100	季宏	夏飞车	逾期	宜兴	6	3700
2017.4.25	张正	13815499710	25000	1250	800	零用贷	周还	20	5000	0	2500	20000	22500	邵静		逾期		4	8200
2017.4.25	张家东	15951515889	8000	400	280	零用贷	周还	20	1500	500	800	6000	6800	邵静		逾期		6	4080
2017.4.27	李志龙	13382277345	8000	400	300	零用贷	周还	20	1400	800	800	5800	6600	邵静		逾期	江阴	4	4800
2017.4.29	马晓东	15961802891	6000	600	300	零用贷	周还	10	1200	300	600	4500	5100	邵静		逾期		1	900
2017.4.29	杨琴	18248811349	5000	500	200	零用贷	周还	10	1300	0	500	3700	4200	崔日成		逾期	宜兴	3	2800
2017.5.2	李强	13584166787	24000	0	1000	打卡	每天	26	6000	0	0	18000	18000			逾期		5	5000
2017.5.3	钱斌	18068263483	8000	400	280	零用贷	周还	20	1200	800	800	6000	6800	邵静		逾期	江阴	15	10200
2017.5.3	杨雯琦	13400006504	8000	55	45	打卡	每天	140	1500	800	800	5700	6500	崔日成		逾期		4	400
2017.5.4	李银春	18861863470	6000	200	80	打卡	每天	30	1800	0	600	4200	4800	吕晓慧		逾期		15	4200
2017.5.6	伏优	13861545699	10000	142	78	打卡	每天	70	3000	0	1000	7000	8000	吕晓慧		逾期		5	6700
2017.5.8	胡书斌	18552093587	7000	350	245	零用贷	周还	20	1300	700	700	5000	5700	邵静		逾期		10	5950
2017.5.8	史国兵	18800538103	7000	350	245	零用贷	周还	20	1100	700	700	5200	5900	崔日成		逾期	宜兴	0	0
2017.5.10	阙建庆	13706184200	6000	400	300	零用贷	周还	15	900	600	600	4500	5100	邵静	夏飞车	逾期		5	3500
2017.5.11	水振	13405777778	6000	300	250	零用贷	周还	20	900	600	600	4500	5100	邵静		逾期		2	1100
2017.5.14	黄意章	13771152686	10000	1000	350	零用贷	周还	10	2000	1000	1000	7000	8000			逾期		14	7000
2017.5.17	吴亚琴	18651001069	6000	0	200	打卡	每天	30	2000	0	400	4000	4400	吕晓慧		逾期	宜兴	8	1600
2017.5.17	范建平	13812038098	6000	300	250	零用贷	周还	20	1500	500	600	4000	4600	吕晓慧		逾期		1	550
2017.5.17	王志强	13961752124	6000	0	200	打卡	每天	40	900	600	450	4500	4950	崔日成		逾期		23	4600
2017.5.18	徐洪芬	13961844902	7000	700	300	零用贷	周还	10	1300	700	700	5000	5700	崔日成		逾期		3	3000
2017.5.18	相海	13861721874	10000	500	350	零用贷	周还	20	1500	1000	1000	7500	8500			逾期		5	4250
2017.5.18	汤凌	18861570091	6000	300	210	零用贷	周还	20	900	600	600	4500	5100	张卿		逾期	宜兴	4	2740
2017.5.19	华啸	15861638013	6000	300	220	零用贷	周还	20	900	600	600	4500	5100	张卿	夏飞车	逾期	江阴	3	1560
2017.5.20	黄健	13921289931	8000	400	250	零用贷	周还	20	1200	800	800	6000	6800	崔日成		逾期		6	4200
2017.5.23	王丹萍	15161683870	8000	400	300	零用贷	周还	20	1200	800	800	6000	6800	邵静	夏飞车	逾期	宜兴	17	11900
2017.5.23	周金华	13861778249	10000	1000	350	零用贷	周还	10	2000	1000	1000	7000	8000	颜臻卿		逾期		4	5400
2017.5.23	朱东兵	18362337274	6000	0	300	零用贷	周还	30	3200	0	800	2800	3600	崔日成		逾期	宜兴	4	1200
2017.5.25	丁丽梅	13921151851	10000	500	300	零用贷	周还	20	1500	500	1000	8000	9000	邵静	夏飞车	逾期		12	9600
2017.5.26	张仁伯	18762691258	6000	300	210	零用贷	周还	20	1100	600	600	4300	4900	邵静		逾期		18	9680
2017.5.26	杨耀红	13701518710	6000	0	200	打卡	每天	40	2200	0	400	3800	4200	颜臻卿	夏飞车	逾期		6	1500
2017.5.27	张国南	15950136622	7000	350	250	打卡	每天	20	1300	700	700	5000	5700	张卿		逾期	江阴	4	2400
2017.5.28	杨浩飞	18861631845	8000	400	230	零用贷	周还	20	1400	800	800	5800	6600	邵静		逾期	江阴	1	630
2017.5.31	叶遥	18706157806	6000	200	250	零用贷	周还	30	1200	600	600	4200	4800	江韦		逾期	宜兴	12	6000
2017.6.2	邵寅伟	13806157525	10000	334	346	零用贷	周还	30	1500	1000	1000	7500	8500	颜臻卿	夏飞车	逾期	宜兴	15	11380
2017.6.3	张鑫	13961806144	10000	0	400	打卡	每天	30	2000	500	750	7500	8250	吕晓慧		逾期		9	3600
2017.6.5	顾嘉洋	13616190461	10000	334	280	零用贷	周一	30	1300	1000	1000	7700	8700		夏飞	逾期		19	11666
2017.6.5	沈卫忠	18952476515	6000	300	250	零用贷	周一	20	1200	600	600	4200	4800	张玲玲		逾期		7	4950
2017.6.6	杭春牛	13584199589	14000	1400	500	零用贷	周一	10	2600	1400	1400	10000	11400	信息部	夏飞车	逾期		7	13500
2017.6.6	张海栋	13585001316	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700	邵静		逾期		7	4200
2017.6.6	曹建国	13328110252	10000	500	300	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧	陆春松车	逾期		8	6430
2017.6.7	刘鹏雄	13291230025	8000	400	300	零用贷	周二	20	1400	600	800	6000	6800	江韦	夏飞车	逾期	江阴	5	4100
2017.6.7	顾烨	17768338569	10000	334	286	零用贷	周二	30	1500	1000	1000	7500	8500	吕晓慧		逾期		15	9300
2017.6.8	李淼	13961795799	40000	2000	1250	零用贷	周一	20	12000	0	6000	28000	34000	邵静		逾期		2	6500
2017.6.9	徐玲	18118118755	14000	466	434	零用贷	周四	30	2600	1400	1400	10000	11400	吕晓慧		逾期	江阴	21	18900
2017.6.12	卢铭君	13665195095	6000	300	220	零用贷	周一	20	1400	600	600	4000	4600	吕晓慧	夏飞车	逾期		4	2080
2017.6.12	蒋爱芳	13376230188	7000	467	283	零用贷	周一	15	1300	700	700	5000	5700	崔日成		逾期	宜兴	7	5550
2017.6.12	张志强	15152225859	6000	300	330	零用贷	周一	20	1200	600	600	4200	4800	吕晓慧		逾期		3	1890
2017.6.13	蒋国栋	18051567666	6000	300	240	零用贷	周一	20	1800	0	600	4200	4800	信息部		逾期		6	3240
2017.6.14	缪晓朋	15950426302	6000	250	0	打卡	每天	30	1100	600	500	4300	4800	江韦		逾期	江阴	22	5500
2017.6.14	苏贞媛	13771276283	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	邵静		逾期	江阴	7	6250
2017.6.15	龚洪明	13801522853	10000	500	280	零用贷	周三	20	1500	500	1000	8000	9000	信息部	夏飞车	逾期	江阴	11	9080
2017.6.15	俞敏杰	13771163366	10000	500	300	零用贷	周三	20	1000	1000	1000	8000	9000	张玲玲	成乃柏车	逾期		13	10400
2017.6.15	陶善超	13915340666	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	崔日成		逾期		1	850
2016.6.19	许锬	13093081783	20000	2000	700	零用贷	周一	10	2000	2000	2000	16000	18000	吕晓慧		逾期		1	2700
2017.6.19	刘艳杰	15949250055	7000	350	250	零用贷	周一	20	1300	700	700	5000	5700	张玲玲	夏飞车	逾期		6	3600
2017.6.20	王军	13912453533	6000	300	250	零用贷	周一	20	1200	600	600	4200	4800	吕晓慧		逾期	江阴	9	4950
2017.6.20	周敏慧	15852530452	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李文广	成乃柏车	逾期		6	6050
2017.6.21	宋爱芳	13485045800/13376204905	20000	800	0	打卡	每天	30	2000	0	0	18000	18000			逾期		2	1600
2017.6.21	潘晓东	15950141705	6000	300	250	零用贷	周二	20	1200	600	600	4200	4800	张玲玲		逾期	江阴	0	0
2017.6.22	蔡明东	13861736736	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	李文广		逾期		2	1700
2017.6.22	陈星伟	15995244931	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	张玲玲	夏飞车	逾期		2	1700
2017.6.22	田金华	18106249939	6000	250	0	打卡	每天	30	1800	0	420	4200	4620	张玲玲		逾期	江阴	22	5500
2017.6.22	荣成贤	18168897357	6000	600	210	零用贷	周三	10	1200	600	600	4200	4800	张玲玲	夏飞车	逾期		2	1620
2017.6.22	高麒深	15261686699	10000	667	353	零用贷	周三	15	1500	1000	1000	7500	8500	张玲玲		逾期	宜兴	6	6120
2017.6.23	王周琴	13961581605	8000	800	280	零用贷	周四	10	1200	800	800	6000	6800	吕晓慧		逾期	宜兴	1	1080
2017.6.23	陈永兴	18360806871	10000	500	280	零用贷	周五	20	1500	1000	1000	7500	8500	张玲玲	夏飞车	逾期	江阴	16	14920
2017.6.24	肖东	13861768903	15000	1500	550	零用贷	周五	10	2500	1500	1500	11000	12500	崔日成		逾期		1	2350
2017.6.25	朱震球	18936072081	15000	750	550	零用贷	周五	20	4000	0	1500	11000	12500			逾期		5	6500
2017.6.25	吴亚新	13815136812	6000	300	250	零用贷	周五	20	1300	600	600	4100	4700			逾期	江阴	13	7150
2017.6.27	黄云云	18552059383	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	江韦		逾期	江阴	8	6200
2017.6.28	王生华	13914251473	8000	400	300	零用贷	周二	20	1200	800	800	6000	6800	李文广		逾期		10	7000
2017.6.28	陶良超	18261518245	6000	350	0	打卡	每天	20	1700	0	430	4300	4730			逾期	江阴	8	2800
2017.6.30	李桂忠	13921208878	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	张玲玲	成乃柏车	逾期	江阴	1	850
2017.6.30	秦红鸣	18921245787	10000	500	350	零用贷	周四	20	1500	1000	1000	7500	8500	张玲玲	周诗华车	逾期	江阴	12	11200
2017.7.1	李兴冬	15961762219	8000	400	300	零用贷	周五	20	1200	800	800	6000	6800	李文广		逾期		13	9400
2017.7.2	计锡君	15061794927	10000	500	300	零用贷	周一	20	1500	1000	1000	7500	8500	张玲玲	周诗华车	逾期		2	1600
2017.7.4	倪同群	15861577698	6000	250	0	打卡	每天	30	1750	0	600	4250	4850	李文广		逾期		5	1250
2017.7.4	吴凯	15861652628	10000	500	350	零用贷	周一	20	1500	500	1000	8000	9000		成乃柏车	逾期	江阴	6	5100
2017.7.4	费鹏	15251566296	8000	400	280	零用贷	周一	20	1200	800	800	6000	6800	张玲玲	夏飞车	逾期	江阴	5	3400
2017.7.6	陆铖	18051902907	5000	334	206	零用贷	周三	15	2000	0	500	3000	3500	张玲玲	夏飞车	逾期	江阴	1	540
2017.7.7	张艳	18012351561	8000	400	300	零用贷	周四	20	1200	800	800	6000	6800	李文广	夏飞车	逾期		9	6300
2017.7.12	顾浩峰	15190332829	6000	350	0	打卡	每天	20	1700	0	600	4300	4900	崔日成		逾期	江阴	2	700
2017.7.12	顾秀英	15950123293	8000	450	0	打卡	每天	20	2000	0	800	6000	6800	张玲玲		逾期	江阴	18	8100
2017.7.12	苏飞龙	15251565544	10000	1000	350	零用贷	周二	10	1500	1000	1000+400	7500	8900	张玲玲		逾期	江阴	3	4550
2017.7.13	陈建新	13771600067	6000	300	280	零用贷	周三	20	1200	600	600+200	4200	5000	崔日成		逾期	江阴	10	5800
2017.7.14	张玉花	15861510837	6000	300	250	零用贷	周四	20	1400	600	600+200	4000	4800	李文广		逾期	宜兴	8	4400
2017.7.14	王晓春	13616180022	10000	500	280	零用贷	周四	20	1500	500	1000+400	8000	9400	张玲玲	周诗华车	逾期		9	7020
2017.7.15	黄东	13771410930	8000	400	300	零用贷	周四	20	1400	800	800+300	5800	6900	李文广		逾期		1	700
2017.7.15	尤伟民	13337903347	20000	1000	800	零用贷	周五	20	3500	1500	2000+400	15000	17400			逾期		5	9000
2017.7.17	王春兰	13656150328	6000	400	288	零用贷	周一	15	1400	600	600+200	4000	4800	张玲玲		逾期	江阴	2	1376
2017.7.17	徐浩	18861687987	8000	400	280	零用贷	周一	20	1200	800	800+300	6000	7100	张玲玲	周诗华车	逾期	江阴	3	2040
2017.7.17	赵怀朋	15806177346	8000	400	430	零用贷	周一	20	1200	800	800+300	6000	7100	李文广		逾期		8	7470
2017.7.18	秦春军	15052196917	6000	300	288	零用贷	周一	20	1200	300	600+200	4500	5300	张玲玲		逾期	江阴	4	2352
2017.7.18	陆亚萍	13951582019	6000	250	0	打卡	每天	30	1800	0	600	4200	4800			逾期		21	5250
2017.7.19	翟正阳	13301525924	5000	335	215	零用贷	周二	15	1700	500	500+100	3300	3900	张玲玲		逾期	江阴	1	550
2017.7.19	鲁鹏飞	18961515758	10000	500	350	零用贷	周二	20	1500	500	1000+400	8000	9400	张玲玲	陆春松车	逾期		7	5950
2017.7.20	陈锡峰	13771577922	7000	468	232	零用贷	周三	15	1300	700	700+200	5000	5900	张玲玲		逾期		9	10000
2017.7.21	储俊锋	18352592863	8000	534	316	零用贷	周四	15	1200	800	800+300	6000	7100	李文广		逾期	宜兴	12	10200
2017.7.22	黄益杰	15961560550	7000	700	300	零用贷	周五	10	1300	700	700+200	5000	5900	吕晓慧		逾期	宜兴	5	5000
2017.7.24	祝文军	18552407340	6000	300	280	零用贷	周一	20	1400	600	600+200	4000	4800	张玲玲		逾期		1	580
2017.7.24	钱山江	13961822282	10000	500	320	零用贷	周一	20	6500	1000	1000+400	7500	8900	信息部	成乃柏车	逾期		5	4100
2017.7.24	朱晨	15298438204	20000	1000	600	零用贷	周一	20	3000	2000	2000+400	15000	17400	吕晓慧		逾期		3	4800
2017.7.24	曹志刚	13812021070	6000	300	210	零用贷	周一	20	1200	300	600+200	4500	5300			逾期		4	2540
2017.7.24	张雪兰	13952097369	10000	500	400	零用贷	周一	20	1500	1000	1000+400	7500	8900	张玲玲		逾期	宜兴	4	3600
2017.7.25	王彩艳	18306161676	10000	400	0	打卡	每天	30	2000	0	1000	8000	9000	张玲玲		逾期	江阴	8	3200
2017.7.25	林晓奇	13861704023	6000	300	280	零用贷	周二	20	1200	600	600+200	4200	5000	吕晓慧		逾期		2	1160
2017.7.25	许薇	13162614805	7000	234	246	零用贷	周一	20	1300	700	700+200	5000	5700			逾期		5	2400
2017.7.25	王立冬	15161539043	6000	300	250	零用贷	周一	20	1400	600	600+200	4000	4800	吕晓慧		逾期		2	1100
2017.7.27	朱清	13063610186	6000	400	250	零用贷	周三	15	1200	600	600	4200	4800	信息部		逾期	宜兴	2	1300
2017.7.27	怀金晓	13706169395	10000	500	350	零用贷	周三	20	1500	1000	1000+400	7500	8900			逾期	江阴	15	15900
2017.7.27	王仁华	18861614550	10000	500	350	零用贷	周三	20	1500	1000	1000+400	7500	8900	吕晓慧	周诗华车	逾期	江阴	10	8500
2017.7.28	钱寅	15061973758	7000	350	350	零用贷	周四	20	1300	700	700+200	5000	5900	张玲玲		逾期		1	700
2017.7.29	李宁峰	13400022910	6000	300	200	零用贷	周五	20	1200	600	0	4200	4200	吕单凤		逾期		5	9000
2017.7.31	宋颖超	15995344333	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	吕晓慧		逾期	江阴	2	1376
2017.7.31	王磊	15950445161	8000	300	0	打卡	每天	30	2000	0	800	6000	6800	张玲玲		逾期	江阴	3	2040
2017.7.31	陶金岳	13912452122	8000	400	300	零用贷	周一	20	1200	800	800+300	6000	7100	李文广		逾期	江阴	8	7470
2017.7.31	张敏民	13921237575	6000	300	280	零用贷	周一	20	1200	600	600+200	4200	5000	吕晓慧		逾期	江阴	4	2352
2017.7.31	陆栋梁	15365261637	15000	500	530	零用贷	周一	30	3000	2000	1500+400	10000	11900	张玲玲	周诗华车	逾期	江阴	21	5250
2017.8.1	蔡幼鸣	13861473843	6000	600	250	零用贷	周一	10	1400	600	600+200	4000	4800	吕晓慧		逾期	宜兴	1	550
2017.8.2	吴孝荣	13812005103	6000	300	220	零用贷	周二	20	1200	600	0	4200	4200	吕单凤		逾期		7	5950
2017.8.3	祝平	15261517300	5000	500	250	零用贷	周三	10	1500	500	500	3200	3700	张玲玲		逾期		9	10000
2017.8.4	周晓波	15052119170	7000	350	350	零用贷	周四	20	1300	700	700+200	5000	5900	张玲玲	周诗华车	逾期		12	10200
2017.8.4	姜正波	18861878540	5000	200	0	打卡	每天	30	1700	0	500	3300	3800	李文广		逾期		5	5000
2017.8.6	秦淋	13771459703	6000	250	0	打卡	每天	30	1800	0	0	4200	4200	吕单凤		逾期		1	580
2017.8.7	倪晓莉	13400029835/13376205288	8000	400	300	零用贷	周一	20	1200	800	800+300	6000	7100	李文广	李贺车	逾期		5	4100
2017.8.9	吕凯	13921371652	8000	400	300	零用贷	周二	20	2000	800	800+300	6000	7100	吕晓慧		逾期	宜兴	3	4800
2017.8.10	贾学宣	13665163629	6000	200	220	零用贷	周三	30	1100	600	600+200	4300	5100	张玲玲		逾期		4	2540
2017.8.11	赵静晓	13921259202	6000	300	210	零用贷	周四	20	1100	600	600	4300	4900	信息部		逾期	江阴	4	3600
2017.8.11	高强	13812155373	 8000	400	300	零用贷	周四	20	2000	800	800	6000	6800	信息部		逾期	江阴	8	3200
2017.8.12	王云锋	13921247419	6000	280	0	打卡	每天	30	1800	0	600	4200	4800	吕晓慧		逾期	江阴	2	1160
2017.8.12	耿春晓	15995215686	10000	500	300	零用贷	周五	20	1200	1000	1000+400	7800	9200	张玲玲		逾期		5	2400
2017.8.14	杨卫东	13861632804	8000	350	0	打卡	每天	30	2000	0	800	6000	6800	江韦		逾期		2	1100
2017.8.15	李平	13376222781	10000	500	300	零用贷	周一	20	1200	800	1000+400	8000	9400	张玲玲	周诗华车	逾期	江阴	2	1300
2017.8.16	梅伟杰	15061726522	6000	300	220	零用贷	周二	20	1200	600	600+200	4200	5000	李文广		逾期	宜兴	15	15900
2017.8.16	高东彪	13952472958	8000	400	280	零用贷	周二	20	1400	600	800+300	6000	7100	吕晓慧		逾期		10	8500
2017.8.17	张吉	15061505882	7000	467	273	零用贷	周三	15	1300	700	700+200	5000	5900	张玲玲		逾期		1	740
2017.8.17	张翔	15805173697	8000	400	280	零用贷	周三	20	1200	800	800+300	6000	7100	张玲玲		逾期	宜兴	5	3400
2017.8.17	张婷	18262251240	8000	400	320	零用贷	周三	20	1200	800	800+300	6000	7100	吕晓慧	成乃柏车	逾期		1	920
2017.8.21	陶莉	13861720707	7000	300	0	打卡	每天	30	2000	0	700	5000	5700			逾期		7	2100
2017.8.21	张祯	15206193536	5000	250	180	零用贷	周一	20	1300	200	0	3500	3500			逾期		5	2150
2017.8.21	丁雷	13506198054	 8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	谢达丹		逾期		12	8400
2017.8.25	龚益波	13915249158	10000	400	350	零用贷	周四	25	1500	1000	1000	7500	8500	信息部	周诗华车	逾期	江阴	2	1800
2017.8.26	王美红	15206196777	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	信息部	周诗华车	逾期		12	3600
2017.8.26	戴宗明	13921379393	6000	400	280	零用贷	周五	15	1400	600	600+200	4000	4600	江韦		逾期	宜兴	2	1360
2017.8.28	徐志文	17712388621	6000	300	250	零用贷	周一	20	1200	600	600+200	4200	5000	江韦		逾期		1	550
2017.8.28	李海	18262294947	6000	300	260	零用贷	周一	20	1200	600	600+200	4200	5000	江韦	周诗华车	逾期		6	3360
2017.8.30	高荣林	13151007535	7000	300	0	打卡	每天	28	2000	0	700	5000	5700	周玉		逾期		11	3300
2017.8.31	顾嘉洋	13616190461	8000	400	300	零用贷	周三	20	1200	800	800+300	6000	7100	李文广		逾期		7	4900
2017.8.31	许益勇	15061745393	6000	400	300	零用贷	周三	15	1800	600	600+200	4200	5000	张玲玲	周诗华车	逾期	江阴	4	2800
2017.8.31	薛裕	13706183173	6000	600	250	零用贷	周三	10	1200	600	600+200	4200	5000	张玲玲	成乃柏车	逾期		2	1700
2017.8.31	苏晓刚	15006160432	6000	400	270	零用贷	周三	15	1200	600	600+200	4200	5000	李文广		逾期	江阴	4	2680
2017.8.31	苏智斌	15161610175	10000	600	0	打卡	每天	20	2000	0	1000	8000	9000	李文广		逾期	江阴	6	3600
2017.9.5	季伟星	13621532259	7000	350	300	零用贷	周一	20	1300	700	700+200	5000	5900	吕晓慧		逾期		0	0
2017.9.5	曾本江	15070380771	6000	400	250	零用贷	周一	15	1400	600	600+200	4000	4800	李文广	周诗华车	逾期		12	7800
2017.9.5	姜恕荣	18861509809	20000	1000	0	打卡	每天	22	5000	0	2000	15000	17000	邹斌		逾期		4	4000
2017.9.6	吕娜	13861831043	30000	1000	0	打卡	每天	32	6000	0	0	24000	24000			逾期		27	27000
2017.9.6	朱辰	13646178202	7000	350	300	零用贷	周二	20	1300	700	700+200	5000	5900	张玲玲		逾期		3	1950
2017.9.6	高必勇	13616149396	6000	250	0	打卡	每天	30	2000	0	600	4000	4600	张玲玲	周诗华车	逾期		18	4500
2017.9.6	胡晓俊	18901517678	10000	500	300	零用贷	周二	20	1500	1000	1000+400	7500	8900	吕晓慧		逾期		16	12800
2017.9.7	顾锋	15312200969	7000	300	0	打卡	每天	30	2000	0	700	5000	5700	张玲玲		逾期		6	1800
2017.9.8	郭洪军	13485016959	7000	350	300	零用贷	周四	20	1300	700	700+200	5000	5900	江韦		逾期	江阴	16	10400
2017.9.11	王敏敏	13921316060	25000	1000	875	零用贷	周一	25	4500	2500	2500+400	18000	20900	李文广		逾期	宜兴	13	28000
2017.9.11	周树辉	18352511820	15000	500	550	零用贷	周一	30	3000	1500	1500+400	10500	12400	周玉		逾期		11	12250
2017.9.12	顾刚强	13861619867	14000	700	0	打卡	每天	20	4000	0	0	10000	10000	信息部		逾期		1	700
2017.9.14	邱丹	18761595184	10000	500	350	零用贷	周三	20	1500	1000	1000+400	7500	8900	张玲玲	周诗华车	逾期	江阴	13	11050
2017.9.15	杨金平	13348100926	6000	600	250	零用贷	周四	10	1400	600	600	4000	4600	何筱慧		逾期		1	850
2017.9.15	尤磊	13961600249	10000	400	0	打卡	每天	30	2500	0	1000	7500	8500	吕晓慧	成乃柏车	逾期	江阴	6	2450
2017.9.18	张莹	13013684690	6000	300	0	打卡	每天	25	2000	0	600	4000	4600	张玲玲		逾期		15	8350
2017.9.21	杨洪	18118911479	6000	400	250	零用贷	周三	15	1400	600	600	4000	4600	吕晓慧	成乃柏车	逾期		10	8300
2017.9.22	沈有安	13921128848	10000	500	350	零用贷	周四	20	2000	0	1000	8000	9000	张玲玲	周诗华车	逾期		13	11050
2017.9.23	钱国华	18851509377	6000	250	0	打卡	每天	30	2000	0	600	0	4600	李文广		逾期		3	750
2017.9.25	刘勇	18921115887	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧		逾期		7	5950
2017.9.27	耿涛	18118873171	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	吕晓慧	周诗华车	逾期	宜兴	1	850
2017.9.28	马祥山	15161575231	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	吕晓慧	周诗华车	逾期		5	5250
2017.10.6	许云磊	18021563190	6000	400	220	零用贷	周四	15	1200	600	600	4200	4800	李文广		逾期	江阴	11	6820
2017.10.9	刘超	13861637645	10000	600	0	打卡	每天	20	2500	0	1000	7500	8500	吕晓慧	周诗华车	逾期	淮安	8	4800
2017.10.10	刘洋	13961761637	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧	周诗华车	逾期	无锡	1	850
2017.10.14	周勰	13806157086	8000	400	280	零用贷	周五	20	1400	800	400	6200	6200	吕晓慧		逾期	无锡	2	1360
2017.10.18	卞捍忠	13771202177	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	徐宽		逾期	江阴	6	5100
2017.10.20	张健	13921398392	6000	300	220	零用贷	周四	20	1200	600	600	4200	4800	徐宽		逾期	无锡	1	850
2017.10.23	沈利峰	13665199991	8000	330	0	打卡	每天	30	2000	0	0	6000	6000			逾期	无锡	23	7590
2017.10.25	恽超	15251596139	8000	400	300	零用贷	周二	20	1200	800	900	6000	6900	李文广		逾期	江阴	4	2800
2017.10.26	蒋国强	13861706504	7000	450	0	打卡	每天	19	2000	0	700	5000	5700	吕晓慧		逾期	无锡	10	4500
2017.10.27	夏一帆	13812175190	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	徐宽	周诗华车	逾期	江阴	4	2300
2017.10.29	周海霞	15961621522	8000	400	300	零用贷	周五	20	1200	800	900	6000	6900	徐宽	成乃柏车	逾期	江阴	9	7900
2017.10.31	杨志	18901512178	6000	280	0	打卡	每天	30	2000	0	700	4000	4700	吕晓慧		逾期	无锡	6	2180
2017.10.31	王为桢	13013624164	6000	400	300	零用贷	周一	15	1400	600	600	4000	4600	杨洋		逾期	无锡	2	1900
2017.11.1	沈伟	13656191645	10000	500	0	打卡	每天	22	2000	0	800	8000	8800	徐宽	周诗华车	逾期	无锡	18	9500
2017.11.2	朱梦娟	18761515512	7000	467	393	零用贷	周三	15	1300	700	700	5000	5700	李文广	周诗华车	逾期	无锡	3	3580
2017.11.3	徐礼文	13812161365	8000	400	300	零用贷	周四	20	1200	800	800	6000	6800	李文广	周诗华车	逾期	江阴	1	700
2017.11.3	吴平群	13961736885	7000	700	300	零用贷	周四	10	1300	700	700	5000	5700	徐宽		逾期	无锡	8	8000
2017.11.5	吴小涓	18262262094	10000	500	0	打卡	每天	22	2500	0	1000	7500	8500	吕晓慧	周诗华车	逾期	无锡	18	9000
2017.11.7	司马贤	15806192167	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	吕晓慧		逾期	无锡	6	5100
2017.11.7	王翊伟	18800589902	10000	500	400	零用贷	周一	20	1500	1000	1000	7500	8500	徐宽	周诗华车	逾期	无锡	5	4500
2017.11.8	何健	13812112521	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	徐宽	周诗华车	逾期	江阴	1	850
2017.11.10	周桦	15961852134	7000	450	0	打卡	每天	19	2000	0	500	5000	5500	吕晓慧		逾期	无锡	14	6300
2017.11.10	吴盛	15852589122	10000	625	375	零用贷	周四	16	1500	1000	1000	7500	8500	吕晓慧		逾期	无锡	4	4000
2017.11.12	浦洪漫	13665189307	7000	700	300	零用贷	周五	10	1300	700	700	5000	5700	徐宽		逾期	无锡	7	7000
2017.11.12	孙延松	15061545829	8000	400	330	零用贷	周五	20	1200	800	800	6000	6800	李文广	刘强车	逾期	无锡	1	730
2017.11.13	苏维维	18758188337	15000	750	450	零用贷	周一	20	2250	1500	1500	11250	12750	徐宽	李贺车	逾期	上海（江阴）	4	4800
2017.11.13	徐腾飞	13771395583	7000	350	300	零用贷	周一	20	1300	700	700	5000	5700	彭加双		逾期	宜兴	1	650
2017.11.14	郑荣兰	15006167920	14000	700	500	零用贷	周一	20	2600	1400	1400	10000	11400	李文广		逾期	贵州	2	2400
2017.11.14	陈慧	13951578227	6000	300	250	零用贷	周一	20	1400	600	600	4000	4600	徐宽		逾期	淮安	5	3300
2017.11.15	孙洪良	15312266605	10000	500	350	零用贷	周二	20	1500	1000	1000	7500	8500	彭加双		逾期	江阴	2	1700
2017.11.15	张柯	13861888150	10000	1000	350	零用贷	周二	10	1500	1000	1000	7500	8500	谢达丹		逾期	无锡	3	4050
2017.11.15	梁小宝	15052440001	10000	100	350	零用贷	周二	10	1500	1000	1000	7500	8500	信息部		逾期	灌云	7	9450
2017.11.17	王以行	13861714944	6000	350	0	打卡	每天	20	1800	0	420	4200	4620	杨洋		逾期	无锡	6	2100
2017.11.18	陆文伟	13812031777	10000	500	350	零用贷	周五	20	1500	1000	1000	7500	8500	徐宽	周诗华车	逾期	无锡	4	3400
2017.11.23	王烽	15251577905	10000	500	350	零用贷	周三	20	1500	1000	1000	7500	8500	徐宽		逾期	江阴	1	850
2017.11.24	赵溪哲	18800588220	7000	350	310	零用贷	周四	20	1300	700	700	5000	5700	徐宽	周诗华车	逾期	无锡	1	660
2017.11.27	徐益波	13775132949	10000	500	320	零用贷	周一	20	1500	1000	0	7500	7500	信息部		逾期	江阴	4	3360
2017.11.27	顾晨炎	15061793160	6000	300	260	零用贷	周一	20	1400	600	600	4000	4600	钱晓		逾期	无锡	1	560
2017.11.28	马海峰	18061572073	10000	500	350	零用贷	周一	20	1500	1000	1000	7500	8500	李文广	刘强车	逾期	响水	1	850
2017.11.28	顾方军	13914288272	10000	500	350	零用贷	周一	20	1500	2000	1000	6500	7500	徐宽		逾期	江阴	4	3400
2017.11.29	张宋良	13861642053	4000	400	250	零用贷	周二	10	1600	400	400	2000	2400	吕晓慧	周诗华车	逾期	江阴	2	1300
2017.11.29	华其蔚	18762818603	8000	534	316	零用贷	周二	15	1300	700	800	6000	6800	吕晓慧	周诗华车	逾期	无锡	3	2800
2017.12.1	冯江云	15052637914	10000	334	366	零用贷	周四	30	1500	1000	1000	7500	8500	刘军	周诗华车	逾期	涟水	2	1400
2017.12.2	朱玉兰	13961844805	8000	350	0	打卡	每天	30	2000	0	400	6000	6400	彭加双		逾期	无锡	29	10150
2017.12.4	张杰	13861790220	6000	600	350	零用贷	周一	10	1400	600	600	4000	4600	徐宽		逾期	高邮	0	0
2017.12.4	谢松存	18061984445	8000	400	0	打卡	每天	25	2000	0	600	6000	6600	徐宽	周诗华车	逾期	兴华	5	2000
2017.12.5	查明晓	13761324773	8000	400	300	零用贷	周一	20	1200	800	800	6000	6800	徐宽	周诗华车	逾期	江阴	4	3400
2017.12.6	陈秋	15895373547	6000	260	0	打卡	每天	30	2000	0	400	4000	4400	彭加双		逾期	江阴	5	1300
2017.12.6	蒋鸿方	13621517987	10000	625	350	零用贷	周二	16	1500	1000	1000	7500	8500	徐宽		逾期	无锡	4	3900
2017.12.6	刘锋	17312728777	10000	1000	350	零用贷	周二	10	1500	1000	1000	7500	8500	徐宽		逾期	江阴	1	1350
2017.12.6	缪丹	15961611448	8000	800	300	零用贷	周二	10	1200	800	800	6000	6800	徐宽		逾期	江阴	4	4400
2017.12.7	陆文忠	13901528667	8000	400	250	零用贷	周三	20	1200	800	800	6000	6800	徐宽		逾期	江阴	0	0
2017.12.9	王新洋	13914158748	20000	1000	600	零用贷	周五	20	3000	2000	1000	15000	16000			逾期	兴华	1	1900
2017.12.18	黄春华（两家）	15161554110	10000	1000	300	零用贷	周一	10	1500	1000	1000	7500	8500	杨薇	周诗华车	逾期	无锡	1	1300
2017.12.31	章亚良	15052135314	5000	500	310	零用贷	周五	10	1500	500	500	3000	3500	吕晓慧	周诗华车	逾期	无锡	0	0
2016.12.6	周晓聪	13812115120	8000	0	100	红包贷	每天	30	1600	800	560	5600	6160	张敏		逾期		15	1500
2017.3.7	王志东	18020512990	10000	0	120	红包贷	每天	30	2500	0	1000	7500	8500	邵静		逾期		10	1200
2017.3.10	韩忠	15298404344	6000	0	80	红包贷	每天	30	2400	1000	250	2600	2850	崔日成	夏飞车	逾期		135	10800
2017.3.20	陈维俊	13771498505	8000	0	100	红包贷	每天	30	2000	0	600	6000	6600	邵静	陆春松车	逾期		12	1200
2017.5.19	田寿玉	13400030746	6000	0	80	红包贷	每天	30	800	0	0	5200	5200			逾期		7	560
2017.6.19	恽志恒	18626366766	6000	0	60	红包贷	每天	30	1500	0	0	4500	4500			逾期		17	1020
2017.8.8	许薇	13162614805	7000	0	100	红包贷	每天	30	2000	0	700	5000	5700	吕晓慧		逾期		5	2400
2017.11.4	陈敏	13400032155	6000	0	80	红包贷	每天	30	2000	0	0	4000	4000			逾期		16	1280
2017.11.6	张旭泓	13861894933	10000	10000	120	红包贷	每天	30	2000	0	0	8000	8000			逾期		0	7000
2017.11.7	张旭泓	13861894933	10000	10000	120	红包贷	每天	30	2000	0	0	8000	8000			逾期		0	0
2017.11.13	张旭泓	13861894933	10000	10000	120	红包贷	每天	30	2000	0	0	8000	8000			逾期		0	0
2017.12.4	吴志刚	13961733171	10000	0	100	红包贷	每天	30	2000	0	0	8000	8000			逾期		19	1900
2017.3.20	周凌波	18068791911	20000	0	2500	空放		30	2500	0	0	17500	17500			逾期		1	2500
2017.3.26	叶寅	13915294442	10000	0	1500	空放		30	1500	0	0	8500	8500			逾期		2	2800
2017.5.12	黄涛	13665178715	100000	0	12500	空放		180	20000	0	2000	80000	82000			逾期		1	10000
2017.5.23	黄云	13921179079	210000	0	70000	空放		66	60000	0	0	150000	150000			逾期		0	0
2017.5.25	黄意章	13771152686	10000	0	1500	空放		10	9500	500	0	0	8000		成乃柏车	逾期		14	7000
2017.9.1	徐文浩	13706188648	25000	0	5000	空放		25	5000	0	1000	20000	21000			逾期		0	0
2017.9.5	胡国利	18921366800	30000	0	10000	空放		22	10000	0	2000	20000	22000	李文广	周诗华车	逾期		0	0
2017.9.6	陈士猛	15206183989	8000	0	2500	空放		22	2500	0	0	5500	5500			逾期		0	0
2017.10.6	郁浩	18262264684	10000	0	3000	空放		22	3000	0	1000	7000	8000	吕晓慧	周诗华车	逾期		0	0
2017.11.1	童爱军	15861462825	6000	0	2400	空放		22	2400	0	360	3600	3960	彭加双		逾期		0	0
2017.12.24	孙欣	18861533597	7000	0	3000	空放		22	3000	0	400	4000	4400	徐宽		逾期		0	0
2016.12.28	郑娟萍	13961882787	40000	1000	500	车贷	周二	40	7700	2000	2000	30300	32300	邵静		逾期		17	25500
2017.9.4	田建康	18006163676	10000	500	350	车贷	周一	20	2500	1000	2000	6500	7900	李文广	成乃柏车	逾期	江阴	7	6950
2017.11.15	杨虎	13585083514	6000	250	0	打卡	每天	30	1900	0	410	4100	4510	吕晓慧		逾期	无锡	4	1000
2017.9.6	陈士猛	15206183989	8000	0	2500	空放		22	2500	0	0	5500	5500			逾期		1	1500';
        $data = explode("\n",$data_array);
        foreach ($data as $key => $value) {
            $item = explode("\t", $value);
            // 借款时间
            $loan_data = str_replace('.','-',$item['0']);
            // 客户经理，如果客户经理为空，则默认为信息部
            if(strtotime(date('Y-m',strtotime($loan_data))) == strtotime('2018-01')) {
                continue;
            }


            if(!$item['14']) {
                $staff_name = '信息部';
            }else {
                $staff_name = $item['14'];
            }





            if(!$item['1']) {
                $customer_name = '未知';
            }else {
                preg_match('/[\x80-\xff]{6,30}/', $item['1'], $matches);
                $customer_name = $matches[0];
            }

            // 手机号码
            if(!$item['2']) {
                $customer_phone = '00000000000';
            }else {
                $customer_phone = $item['2'];
            }

            // 客户地址
            if(!$item['17']) {
                $customer_address = '';
            }else {
                $customer_address = $item['17'];
            }

            // 借款金额
            if(!$item['3']) {
                $principal = 0;
            }else {
                $principal = $item['3'] . '.00';
            }


            $company_id = 1;

            // 查看客户是否存在
            $customerCondition = array(
                'name' => $customer_name,
                'phone' => $customer_phone,
                'company_id' => $company_id,
                'is_delete' => 0,
            );

            $customer = D('Customer')->findOneCustomerByCondition($customerCondition);
            if($customer) {
                $customer_id = $customer['id'];
            }


            // 一期本金
            if(!$item['4']) {
                $cyc_principal = 0;
            }else {
                $cyc_principal = $item['4'];
            }

            // 一期利息
            if(!$item['5']) {
                $cyc_interest = 0;
            }else {
                $cyc_interest = $item['5'];
            }

            $cyc_principal = $cyc_principal + $cyc_interest;

            // 贷款类型
            if(!$item['6']) {
                continue;
            }
            switch ($item['6']) {
                case '零用贷' : $product_id = 1;break;
                case '打卡' : $product_id = 2;break;
                case '空放' : $product_id = 3;break;
                case '车贷' : $product_id = 4;break;
                case '红包贷' : $product_id = 5;break;
                default: $product_id = 1;break;
            }

            // 借款周期
            if(!$item['8']) {
                $cyclical = 0;
            }else {
                $cyclical = $item['8'];
            }

            // 共计利息
            $interest = $cyclical * $cyc_interest;

            // 管理费
            if(!$item['9']) {
                $poundage = 0;
            }else {
                $poundage = $item['9'];
            }

            // 保证金
            if(!$item['10']) {
                $bond = 0;
            }else {
                $bond = $item['10'];
            }

            // 同行返点
            if(!$item['11']) {
                $rebate = 0;
            }else {
                $rebate = $item['11'];
            }

            // 实际到账
            if(!$item['12']) {
                $arrival = 0;
            }else {
                $arrival = $item['12'];
            }

            // 实际到账
            if(!$item['13']) {
                $expenditure = 0;
            }else {
                $expenditure = $item['13'];
            }

            // 已还期数
            if(!$item['18']) {
                $repay_cyc = 0;
            }else {
                $repay_cyc = $item['18'];
            }

            // 已还金额
            if(!$item['19']) {
                $repay_money = 0;
            }else {
                $repay_money = $item['19'];
            }

            // 已还金额和
            $repay_rmoney = $cyc_principal * $repay_cyc;
            // 违约金和
            $repay_bmoney = $repay_money - $repay_rmoney;



            $loan_status = -1;

            // 具体还款时间
            $juti_data = 1;
            if($product_id == 2 || $product_id == 5) {
                // 打卡红包贷
                $juti_data = 1;
            }

            if($product_id == 1 || $product_id == 4) {
                // 零用贷 车贷
                switch ($item['7']) {
                    case '周一' : $juti_data = 1;break;
                    case '周二' : $juti_data = 2;break;
                    case '周三' : $juti_data = 3;break;
                    case '周四' : $juti_data = 4;break;
                    case '周五' : $juti_data = 5;break;
                    case '周六' : $juti_data = 6;break;
                    case '周日' : $juti_data = 7;break;
                    default: $loan_status = 1;break;
                }
            }

            if($product_id == 3) {
                // 空放
                $juti_data = $item['7'];
            }

            // 到期时间
            $create_time = strtotime($loan_data);
            $weekDay = date("w",$create_time) > 0 ? date("w",$create_time) : 7;
            if($product_id == 1 || $product_id == 4) {
                // 零用贷或车贷
                $add_data = 7 + ($juti_data - $weekDay) + ($cyclical - 1) * 7;
                $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.$add_data.' day');
            }else if($product_id == 2 || $product_id == 5) {
                // 打卡或红包贷
                $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.($cyclical-1).' day');

            }else if($product_id == 3) {
                // 空放
                $exp_time = strtotime(date('Y-m-d H:i:s',$create_time) . ' +'.($juti_data-1)*$cyclical.' day');
            }

            // 写入借款数据
            $loanData = array(
                'customer_id' => $customer_id,
                'principal' => $principal,
                'interest' => $interest,
                'cyc_principal' => $cyc_principal,
                'cyc_interest' => $cyc_interest,
                'product_id' => $product_id,
                'loan_status' => $loan_status,
                'create_time' => strtotime($loan_data),
                'company_id' => $company_id,
                'is_old' => 1, //老数据
            );
            $updateData = array(
                'loan_status' => -1,
            );
            $loan = D('Loan')->selectLoanByCondition($loanData);
            if($loan) {
                $updateLoan = D('Loan')->updateLoanByID($loan['loan_id'],$updateData);
            }else {
                continue;
            }

            // 删除还款信息 重新写入
            $deleteRepay = D('Repayments')->deleteRepayByLoanID($loan['loan_id']);

            // 写入还款信息
            for ($i = 1; $i <= $repay_cyc; $i++) {
                if($product_id == 2 || $product_id == 5) {
                    // 打卡 每天还
                    $k = $i - 1;
                    $gmt_repay = date('Y-m-d',strtotime($loan_data . ' +' . $k .' day')) . ' 16:00:00';
                }else if($product_id == 1 || $product_id == 4) {
                    // 零用贷 每周还款
                    $k = $i;
                    $gmt_repay = date('Y-m-d',strtotime($loan_data . ' -1 day +' . $k . ' week')) . ' 16:00:00';
                }else if($product_id == 3) {
                    // 空放
                    $gmt_repay = date('Y-m-d',strtotime($loan_data . ' +' . $juti_data . ' day')) . ' 16:00:00';
                }

                if($i == $repay_cyc && $repay_bmoney > 0) {
                    $r_money = $cyc_principal + $repay_bmoney;
                    $b_money = $repay_bmoney;
                }else {
                    $r_money = $cyc_principal;
                    $b_money = 0;
                }
                $repayData = array(
                    'loan_id' => $loan['loan_id'],
                    'cycles' => $i,
                    's_money' => $cyc_principal,
                    'r_money' => $r_money,
                    'b_money' => $b_money,
                    'gmt_create' => $gmt_repay,
                    'staff_id' => 77,
                    'pay_style' => 1,
                    'gmt_repay' => $gmt_repay,
                    'company_id' => $company_id,
                    'is_old' => 3,
                );
                $repay_id = D('Repayments')->addRepayments($repayData);
            }
        }
    }

    public function checkLoanJieqing() {
        $condition = array(
            'loan_status' => 1,
            'is_delete' => array('neq',1),
        );
        $loans = D('Loan')->selectAllBycondition($condition);

        foreach ($loans as $key => $item) {
            $loanCondition['loan_id'] = $item['loan_id'];
            $repay = D('Repayments')->listRepaymentsByConditionB($loanCondition);
            $lastRepayTime = $repay[0]['gmt_repay'];

            $loanData = array(
                'gmt_overdue' => strtotime($lastRepayTime),
            );
            $updateLoan = D('Loan')->updateLoanByID($item['loan_id'],$loanData);
        }

    }

    public function checkLoanYuqi() {
        $condition = array(
            'loan_status' => -1,
            'is_delete' => array('neq',1),
        );
        $loans = D('Loan')->selectAllBycondition($condition);

        foreach ($loans as $key => $item) {
            $repayCondition = array(
                'is_delete' => 1,
                'loan_id' => $item['loan_id']
            );
            $countRepayments = D('Repayments')->countRepaymentsByCondition($repayCondition);
            if(!$countRepayments) {
                $countRepayments = 0;
            }

            // 计算下一次还款时间
            if($item['product_id'] == 2 || $item['product_id'] == 5) {
                // 打卡
                $yuqiTime = strtotime(date('Y-m-d H:i:s',$item['create_time']) . '+' . $countRepayments .' day');
            }else if($item['product_id'] == 1 || $item['product_id'] == 4) {

                $yuqiTime = strtotime(date('Y-m-d H:i:s',$item['create_time']) . '+' . ($countRepayments+1) .' week');

            }else{
                $yuqiTime = strtotime(date('Y-m-d H:i:s',$item['create_time']) . '+' . (($countRepayments+1)*$item['juti_data']) .' day');

            }

            $loanData = array(
                'gmt_overdue' => $yuqiTime,
            );
            $updateLoan = D('Loan')->updateLoanByID($item['loan_id'],$loanData);
        }

    }

}