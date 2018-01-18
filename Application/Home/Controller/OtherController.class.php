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

}