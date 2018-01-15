<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/7/24
 * Time: 22:03
 */

namespace Home\Controller;
use Think\Page2;

use Think\Controller;
use Think\Exception;

class StaffController extends CommonController {
    public function index() {
        $condition = array();
        $departments =D('Department')->selectAllDepartment();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        $statusArray = array(
            '0' => array('id' => 2 , 'name' => '在职'),
            '1' => array('id' => -1 , 'name' => '离职'),
        );

        if(I('get.staff_name','','string')) {
            $condition['staff_name'] = I('get.staff_name','','string');
            $this->assign('input_name',$condition['staff_name']);
        }

        if(I('get.phone_number','','string')) {
            $condition['phone_number'] = I('get.phone_number','','string');
            $this->assign('input_phone',$condition['phone_number']);
        }

        if(I('get.idcard','','string')) {
            $condition['idcard'] = I('get.idcard','','string');
            $this->assign('input_idcard',$condition['idcard']);
        }

        if(I('get.department_id','','string')) {
            $departmentSearchArray = explode(',',I('get.department_id','','string'));
            foreach ($departments as $key => $item) {
                $departments[$key]['selected'] = 0;
                foreach ($departmentSearchArray as $i => $j) {
                    if($item['department_id'] == $j) {
                        $departments[$key]['selected'] = 1;
                    }
                }
            }
            $condition['department_id'] = array('IN',$departmentSearchArray);
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

            if($userInfo['jurisdiction'] == 1) {
                $condition['company_id'] = $userInfo['company_id'];
            }else if($userInfo['jurisdiction'] == 2){
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
        }


        if(I('get.status','','string')) {
            $statusSearchArray = explode(',',I('get.status','','string'));
            foreach ($statusArray as $key => $item) {
                $statusArray[$key]['selected'] = 0;
                foreach ($statusSearchArray as $i => $j) {
                    if($item['department_id'] == $j) {
                        $statusArray[$key]['selected'] = 1;
                    }
                }
            }
            foreach ($statusSearchArray as $i => $j) {
                if($j == 2) {
                    $statusSearchArray[$i] = 0;
                }
            }
            $condition['staff_status'] = array('IN',$statusSearchArray);
        }

        if(I('get.reservationtime','','string')) {
            $search_datepicker = I('get.reservationtime','','string');
            // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
            // 分解为
            // 2017-11-17 12:30:00
            // 2017-11-25 15:30:20
            $s_time = strtotime(substr($search_datepicker,0,19));
            $e_time = strtotime(substr($search_datepicker,24,19));
            $condition['induction_time'] = array('BETWEEN',array($s_time,$e_time));
            $this->assign('input_datepicker',$search_datepicker);
        }


        try {
            //1.2 获取当前页码
            $now_page = I('request.page',1,'intval');
            $page_size = I('request.pageSize',10,'intval');
            $page = $now_page ? $now_page : 1;
            //1.3 设置默认分页条数
            $pageSize = $page_size ? $page_size : 10;
            //1.4 获取信息




            $staffs = D('Staff')->selectAllStaffByCondition($condition,$page,$page_size);
            $countStaff = D('Staff')->countStaff($condition);

            //1.5 实例化一个分页对象
            $res = new Page2($countStaff,$pageSize);
            //1.6 调用show方法前台显示页码
            $pageRes = $res->show();


            foreach ($staffs as $key => $value) {
                $department = D('Department')->findDataByCondition('department_id',$value['department_id']);
                if(!$department) {
                    throw_exception('部门信息有误！');
                }
                $staffs[$key]['department_name'] = $department['department_name'];
                // 获取公司信息
                $company = D('Company')->getCompanyByID($value['company_id']);
                $staffs[$key]['company_name'] = $company['smallname'];
            }

            $this->assign(array(
                'staffs' => $staffs,
                'departments' => $departments,
                'statusSearchArray' => $statusArray,
                'pageRes' => $pageRes,
                'userInfo' => $userInfo,
                'companys' => $companys
            ));
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
        $this->display();
    }

    public function export() {
        $condition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();

        if(I('get.staff_name','','string')) {
            $condition['staff_name'] = I('get.staff_name','','string');
        }

        if(I('get.phone_number','','string')) {
            $condition['phone_number'] = I('get.phone_number','','string');
        }

        if(I('get.idcard','','string')) {
            $condition['idcard'] = I('get.idcard','','string');
        }

        if(I('get.department_id','','string')) {
            $departmentSearchArray = explode(',',I('get.department_id','','string'));
            $condition['department_id'] = array('IN',$departmentSearchArray);
        }

        if(I('get.company_id','','string') && I('get.company_id','','string') != 'undefined') {
            $companySearchArray = explode(',',I('get.company_id','','string'));
            $condition['company_id'] = array('IN',$companySearchArray);
        }else {
            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
            if($userInfo['jurisdiction'] == 1) {
                $condition['company_id'] = $userInfo['company_id'];
            }else if($userInfo['jurisdiction'] == 2){
                $companyIDs = array_column($companys,'company_id');
                $condition['company_id'] = array('IN',$companyIDs);
            }
        }


        if(I('get.status','','string')) {
            $statusSearchArray = explode(',',I('get.status','','string'));
            foreach ($statusSearchArray as $i => $j) {
                if($j == 2) {
                    $statusSearchArray[$i] = 0;
                }
            }
            $condition['staff_status'] = array('IN',$statusSearchArray);
        }

        if(I('get.reservationtime','','string')) {
            $search_datepicker = I('get.reservationtime','','string');
            // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
            // 分解为
            // 2017-11-17 12:30:00
            // 2017-11-25 15:30:20
            $s_time = strtotime(substr($search_datepicker,0,19));
            $e_time = strtotime(substr($search_datepicker,24,19));
            $condition['induction_time'] = array('BETWEEN',array($s_time,$e_time));
        }


        try {


            $staffs = D('Staff')->selectAllStaffOnlyByCondition($condition);

            foreach ($staffs as $key => $value) {
                $department = D('Department')->findDataByCondition('department_id',$value['department_id']);
                if(!$department) {
                    throw_exception('部门信息有误！');
                }
                $staffs[$key]['id'] = $key + 1;
                $staffs[$key]['department_name'] = $department['department_name'];
                $staffs[$key]['create_time'] = date('Y-m-d',$value['create_time']);
                $staffs[$key]['staff_status'] = $value['staff_status'] == 0 ? '在职' : '离职';
                // 获取公司信息
                $company = D('Company')->getCompanyByID($value['company_id']);
                $staffs[$key]['company_name'] = $company['smallname'];
            }

            $xlsName  = '员工信息表';
            if($userInfo['jurisdiction'] == 2) {
                $xlsCell  = array(
                    array('id','序号'),
                    array('staff_name','员工姓名'),
                    array('phone_number','手机号码'),
                    array('idcard','身份证号码'),
                    array('address','家庭住址'),
                    array('department_name','所属部门'),
                    array('company_name','所属公司'),
                    array('create_time','创建时间'),
                    array('staff_status','员工状态'),
                );
            }else {
                $xlsCell  = array(
                    array('id','序号'),
                    array('staff_name','员工姓名'),
                    array('phone_number','手机号码'),
                    array('idcard','身份证号码'),
                    array('address','家庭住址'),
                    array('department_name','所属部门'),
                    array('create_time','创建时间'),
                    array('staff_status','员工状态'),
                );
            }

            $xlsData = $staffs;
            $excel = new PhpexcelController();
            $excel->exportExcel($xlsName,$xlsCell,$xlsData);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

    }

    public function changeStatus() {
        if($_POST) {
            $id = I('post.id',0,'intval');
            $status = I('post.status',0,'intval');
            if($id == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '员工ID不存在！',
                ));
            }
            try {
                $res = D('Staff')->updateOneStaffFieldByID($id,'staff_status',$status);
                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '修改成功！',
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '修改失败，请重新再试！',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    public function deleteStaff() {
        if($_POST) {
            $id = I('post.id',0,'intval');
            try {
                $res = D('Staff')->deleteOneStaffByID($id);
                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '删除成功！',
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '删除失败，请重新再试！',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    public function add() {
        $departments =D('Department')->selectAllDepartment();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        $this->assign(array(
            'departments' => $departments,
            'companys' => $companys,
            'userInfo' => $userInfo,
        ));
        $this->display();
    }

    public function checkAdd() {

        $staff_name = I('post.staff_name','','trim,string');
        $phone_number = I('post.phone_number','','trim,string');
        $idcard = I('post.idcard','','trim,string');
        $department_id = I('post.department_id',0,'intval');
        $company_id = I('post.company_id',0,'intval');
        /*$base_pay = I('post.base_pay',0,'intval');
        $bank_card = I('post.bank_card','','trim,string');*/
        $address = I('post.address','','trim,string');
        $induction_time = I('post.induction_time','','trim,string');

        if(!$staff_name) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入员工姓名！',
            ));
        }
        if(!$phone_number) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入手机号码！',
            ));
        }
        if(!$idcard) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入身份证号码！',
            ));
        }
        if($department_id == 0) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择所属部门！',
            ));
        }
        /*if($base_pay == 0) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入员工基本工资！',
            ));
        }
        if(!$bank_card) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入银行卡号！',
            ));
        }*/
        if(!$address) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请输入详细家庭住址！',
            ));
        }
        if(!$induction_time) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择入职时间!',
            ));
        }

        if(!$company_id) {
            $userInfo = $this->getUserNowInfo();
            $company_id = $userInfo['company_id'];
        }
        $induction_time = str_replace('年','-',$induction_time);
        $induction_time = str_replace('月','-',$induction_time);
        $induction_time = str_replace('日','',$induction_time);


        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
        $userInfo = $this->getUserNowInfo();

        $staffData = array(
            'staff_name' => $staff_name,
            'phone_number' => $phone_number,
            'idcard' => $idcard,
            'department_id' => $department_id,
            /*'base_pay' => $base_pay,
            'bank_card' => $bank_card,*/
            'address' => $address,
            'induction_time' => strtotime($induction_time),
            'staff_status' => 0,
            'create_time' => time(),
            'company_id' => $company_id,
        );

        try {
            //根据身份证判断是否已经存在
            $userInfo = $this->getUserNowInfo();
            $condition = array(
                'idcard' => $idcard,
                'company_id' => $company_id,
            );

            if(D('Staff')->findOneStaffByCondition($condition)) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '此身份证号码已存在，请勿重复添加员工!',
                ));
            }

            $staff = D('Staff')->addStaff($staffData);
            if(!$staff) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '添加失败，请重试!',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '添加成功!',
            ));
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    public function editStaff() {
        if($_POST) {
            $id = I('post.id',0,'intval');
            if(!$id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该条记录不存在！',
                ));
            }
            try {
                $res = D('Staff')->findStaffByCondition('staff_id',$id);
                if($res) {
                    $res['induction_time'] = date('Y年m月d日',$res['induction_time']);
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '成功获取该记录！',
                        'data' => $res,
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '数据加载失败，请重试！',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    public function checkEdit() {
        if($_POST) {

            $staff_id = I('post.staff_id',0,'intval');
            $staff_name = I('post.staff_name','','trim,string');
            $phone_number = I('post.phone_number','','trim,string');
            $idcard = I('post.idcard','','trim,string');
            $department_id = I('post.department_id',0,'intval');
            /*$base_pay = I('post.base_pay',0,'intval');
            $bank_card = I('post.bank_card','','trim,string');*/
            $address = I('post.address','','trim,string');
            $induction_time = I('post.induction_time','','trim,string');

            if(!$staff_id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '数据加载失败，请重试！',
                ));
            }
            if(!$staff_name) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请输入员工姓名！',
                ));
            }
            if(!$phone_number) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请输入手机号码！',
                ));
            }
            if(!$idcard) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请输入身份证号码！',
                ));
            }
            if($department_id == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请选择所属部门！',
                ));
            }
            /*if($base_pay == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请输入员工基本工资！',
                ));
            }
            if(!$bank_card) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请输入银行卡号！',
                ));
            }*/
            if(!$address) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请输入详细家庭住址！',
                ));
            }
            if(!$induction_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请选择入职时间!',
                ));
            }
            $induction_time = str_replace('年','-',$induction_time);
            $induction_time = str_replace('月','-',$induction_time);
            $induction_time = str_replace('日','',$induction_time);
            $induction_time = strtotime($induction_time);

            try {
                $staff = D('Staff')->findStaffByCondition('staff_id',$staff_id);
                if(!$staff) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '数据加载失败，请重试！',
                    ));
                }
                if($staff['staff_name'] == $staff_name && $staff['phone_number'] == $phone_number && $staff['idcard'] == $idcard && $staff['address'] == $address && $staff['department_id'] == $department_id
                /*&& $staff['base_pay'] == $base_pay*/ /*&& $staff['bank_card'] == $bank_card*/ && $staff['address'] == $address && $staff['induction_time'] == $induction_time ) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '未作任何修改！',
                    ));
                }


                if($staff['idcard'] != $idcard) {
                    $userInfo = $this->getUserNowInfo();
                    $condition = array(
                        'idcard' => $idcard,
                        'company_id' => $staff['company_id'],
                    );

                    if(D('Staff')->findOneStaffByCondition($condition)) {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '此身份证号码已存在，请勿重复添加员工!',
                        ));
                    }
                }


                $staffData = array(
                    'staff_name' => $staff_name,
                    'phone_number' => $phone_number,
                    'idcard' => $idcard,
                    'department_id' => $department_id,
                    /*'base_pay' => $base_pay,
                    'bank_card' => $bank_card,*/
                    'address' => $address,
                    'induction_time' => $induction_time,
                    'update_time' => time(),
                );

                $res = D('Staff')->updateStaffByID($staff_id,$staffData);
                if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '客户信息修改失败！',
                    ));
                }
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '用户信息修改成功！',
                ));

            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }
}