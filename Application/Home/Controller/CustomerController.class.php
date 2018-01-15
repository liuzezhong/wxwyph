<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 14:22
 */

namespace Home\Controller;
use Think\Exception;
use Think\Page2;
/**
 * 客户管理
 * Class CustomerController
 * @package Home\Controller
 */
class CustomerController extends CommonController {
    //客户信息列表
    public function index() {
        $condition = array();
        $staffCondition = array();
        // 员工信息
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        if($userInfo['jurisdiction'] == 1) {
            $staffCondition['company_id'] = $userInfo['company_id'];
        }else if($userInfo['jurisdiction'] == 2) {
            $companyIDs = array_column($companys,'company_id');
            $staffCondition['company_id'] = array('IN',$companyIDs);
        }

        $staffs = D('Staff')->selectAllStaff($staffCondition);
        $statusArray = array(
            '0' => array('id' => 2 , 'name' => '正常'),
            '1' => array('id' => -1 , 'name' => '禁贷'),
        );

        if(I('get.name','','string')) {
            $condition['name'] = I('get.name','','string');
            $this->assign('input_name',$condition['name']);
        }

        if(I('get.phone','','string')) {
            $condition['phone'] = I('get.phone','','string');
            $this->assign('input_phone',$condition['phone']);
        }

        if(I('get.idcard','','string')) {
            $condition['idcard'] = I('get.idcard','','string');
            $this->assign('input_idcard',$condition['idcard']);
        }

        if(I('get.recommender','','string')) {
            $staffSearchArray = explode(',',I('get.recommender','','string'));
            foreach ($staffs as $key => $item) {
                $staffs[$key]['selected'] = 0;
                foreach ($staffSearchArray as $i => $j) {
                    if($item['staff_id'] == $j) {
                        $staffs[$key]['selected'] = 1;
                    }
                }
            }
            $condition['recommender'] = array('IN',$staffSearchArray);
        }

        if(I('get.status','','string')) {
            $statusSearchArray = explode(',',I('get.status','','string'));
            foreach ($statusArray as $key => $item) {
                $statusArray[$key]['selected'] = 0;
                foreach ($statusSearchArray as $i => $j) {
                    if($item['id'] == $j) {
                        $statusArray[$key]['selected'] = 1;
                    }
                }
            }
            foreach ($statusSearchArray as $i => $j) {
                if($j == 2) {
                    $statusSearchArray[$i] = 0;
                }
            }
            $condition['status'] = array('IN',$statusSearchArray);
        }

        if(I('get.reservationtime','','string')) {
            $search_datepicker = I('get.reservationtime','','string');
            // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
            // 分解为
            // 2017-11-17 12:30:00
            // 2017-11-25 15:30:20
            $s_time = strtotime(substr($search_datepicker,0,19));
            $e_time = strtotime(substr($search_datepicker,24,19));
            $condition['create_time'] = array('BETWEEN',array($s_time,$e_time));
            $this->assign('input_datepicker',$search_datepicker);
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

        try {
            //1.2 获取当前页码
            $now_page = I('request.page',1,'intval');
            $page_size = I('request.pageSize',10,'intval');
            $page = $now_page ? $now_page : 1;
            //1.3 设置默认分页条数
            $pageSize = $page_size ? $page_size : 10;
            //1.4 获取信息

            $customers = D('Customer')->selectALLCustomerByCondition($condition,$page,$page_size);
            $countCustomers = D('Customer')->countCustomers($condition);

            //1.5 实例化一个分页对象
            $res = new Page2($countCustomers,$pageSize);
            //1.6 调用show方法前台显示页码
            $pageRes = $res->show();



            foreach ($customers as $key => $value) {
                $staff = D('Staff')->findStaffByCondition('staff_id',$value['recommender']);
                $customers[$key]['recommender_name'] = $staff['staff_name'];
                // 获取公司信息
                $company = D('Company')->getCompanyByID($value['company_id']);
                $customers[$key]['company_name'] = $company['smallname'];
            }
            $this->assign(array(
                'customers' => $customers,
                'staffs' => $staffs,
                'statusSearchArray' => $statusArray,
                'pageRes' => $pageRes,
                'userInfo' => $userInfo,
                'companys' => $companys,
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
        // 员工信息
        if(I('get.name','','string')) {
            $condition['name'] = I('get.name','','string');
        }

        if(I('get.phone','','string')) {
            $condition['phone'] = I('get.phone','','string');
        }

        if(I('get.idcard','','string')) {
            $condition['idcard'] = I('get.idcard','','string');
        }

        if(I('get.recommender','','string')) {
            $staffSearchArray = explode(',',I('get.recommender','','string'));
            $condition['recommender'] = array('IN',$staffSearchArray);
        }

        if(I('get.status','','string')) {
            $statusSearchArray = explode(',',I('get.status','','string'));
            foreach ($statusSearchArray as $i => $j) {
                if($j == 2) {
                    $statusSearchArray[$i] = 0;
                }
            }
            $condition['status'] = array('IN',$statusSearchArray);
        }

        if(I('get.reservationtime','','string')) {
            $search_datepicker = I('get.reservationtime','','string');
            // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
            // 分解为
            // 2017-11-17 12:30:00
            // 2017-11-25 15:30:20
            $s_time = strtotime(substr($search_datepicker,0,19));
            $e_time = strtotime(substr($search_datepicker,24,19));
            $condition['create_time'] = array('BETWEEN',array($s_time,$e_time));
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

        try {
            $customers = D('Customer')->selectALLCustomerOnlyByCondition($condition);

            foreach ($customers as $key => $value) {
                $staff = D('Staff')->findStaffByCondition('staff_id',$value['recommender']);
                $customers[$key]['recommender_name'] = $staff['staff_name'];

                $customers[$key]['id'] = $key + 1;
                $customers[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                $customers[$key]['status'] = $value['status'] == 0 ? '可贷' : '禁贷';
                // 获取公司信息
                $company = D('Company')->getCompanyByID($value['company_id']);
                $customers[$key]['company_name'] = $company['smallname'];
            }
            $xlsName  = '客户信息表';

            if($userInfo['jurisdiction'] == 2) {
                $xlsCell  = array(
                    array('id','序号'),
                    array('name','客户姓名'),
                    array('phone','客户电话'),
                    array('idcard','身份证号码'),
                    array('address','家庭住址'),
                    array('loan_times','借款次数'),
                    array('recommender_name','客户经理'),
                    array('company_name','所属公司'),
                    array('create_time','创建时间'),
                    array('status','客户状态'),
                );
            }else {
                $xlsCell  = array(
                    array('id','序号'),
                    array('name','客户姓名'),
                    array('phone','客户电话'),
                    array('idcard','身份证号码'),
                    array('address','家庭住址'),
                    array('loan_times','借款次数'),
                    array('recommender_name','客户经理'),
                    array('create_time','创建时间'),
                    array('status','客户状态'),
                );
            }

            $xlsData = $customers;
            $excel = new PhpexcelController();
            $excel->exportExcel($xlsName,$xlsCell,$xlsData);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    //添加用户信息
    public function add() {
        $staffCondition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
        if($userInfo['jurisdiction'] == 1) {
            $staffCondition['company_id'] = $userInfo['company_id'];
        }else if($userInfo['jurisdiction'] == 2) {
            $companyIDs = array_column($companys,'company_id');
            $staffCondition['company_id'] = array('IN',$companyIDs);
        }
        $staffs = D('Staff')->selectAllStaff($staffCondition);
        $this->assign(array(
            'staffs' => $staffs,
            'companys' => $companys,
            'userInfo' => $userInfo,
        ));
        $this->display();
    }

    public function addCheck() {
        if($_POST) {
            $name = I('post.name','','trim,string');
            $phone = I('post.phone','','trim,string');
            $idcard = I('post.idcard','','trim,string');
            $address = I('post.address','','trim,string');
            $recommender = I('post.recommender','','trim,string');
            $company_id = I('post.company_id',0,'intval');
            $remark = I('post.remark','','trim,string');

            if(!$name) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '客户姓名不能为空',
                ));
            }
            if(!$phone) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '客户电话不能为空',
                ));
            }
            if(!$idcard) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '客户身份证号码不能为空',
                ));
            }
            if(!$address) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '客户家庭住址不能为空',
                ));
            }
            if($recommender == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请选择客户经理',
                ));
            }
            if(!$company_id) {
                $userInfo = $this->getUserNowInfo();
                $company_id = $userInfo['company_id'];
            }

            // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
            $userInfo = $this->getUserNowInfo();

            $data = array(
                'name' => $name,
                'phone' => $phone,
                'idcard' => $idcard,
                'address' => $address,
                'recommender' => $recommender,
                'loan_times' => 0,
                'create_time' => time(),
                'status' => 0,
                'company_id' => $company_id,
                'remark' => $remark,
            );


            try {

                $userInfo = $this->getUserNowInfo();
                $condition = array(
                    'idcard' => $idcard,
                    'is_delete' => array('neq',1),
                    'company_id' => $company_id,
                );
                if(D('Customer')->findOneCustomerByCondition($condition)) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '该客户已有记录',
                    ));
                }
                $res = D('Customer')->addCustomer($data);
                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '新增成功',
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '新增失败，请重试',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    public function changeStatus() {
        if($_POST) {
            $id = I('post.id',0,'intval');
            $status = I('post.status',0,'intval');
            if($id == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '客户ID不存在',
                ));
            }
            try {
                $res = D('Customer')->updateOneCustomerFieldByID($id,'status',$status);
                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '修改成功',
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '修改失败，请重新再试',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    public function deleteCustomer() {
        if($_POST) {
            $id = I('post.id',0,'intval');
            try {
                $res = D('Customer')->logicDeleteOneCustomerByID($id);
                // 获取该客户所有借款信息
                $loans = D('Loan')->selectLoanByCustomerID($id);
                // 逻辑删除所有借款信息
                $logicDeleteLoans = D('Loan')->logicDeleteByCustomer($id);
                /**
                 * 删除每一条借款记录对应的还款记录
                 */
                foreach ($loans as $key => $item) {
                    $logicDeleteRepay = D('Repayments')->logicDeleteByLoanID($item['loan_id']);
                }
                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '删除成功',
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '删除失败，请重新再试',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    public function editCustomer() {
        if($_POST) {
            $id = I('post.id',0,'intval');
            if(!$id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该条记录不存在',
                ));
            }
            try {
                $res = D('Customer')->findCustomerByCondition('id',$id);
                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '成功获取该记录',
                        'data' => $res,
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '数据加载失败，请重试',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    public function checkEditCustomer() {
        if($_POST) {
            $id = I('post.id',0,'intval');
            $name = I('post.name','','trim,string');
            $phone = I('post.phone','','trim,string');
            $idcard = I('post.idcard','','trim,string');
            $address = I('post.address','','trim,string');
            $remark = I('post.remark','','trim,string');
            $recommender = I('post.recommender',0,'intval');

            if(!$id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '数据加载失败，请重试',
                ));
            }
            try {
                $customer = D('Customer')->findCustomerByCondition('id',$id);
                if(!$customer) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '数据加载失败，请重试',
                    ));
                }
                if($customer['name'] == $name && $customer['phone'] == $phone && $customer['idcard'] == $idcard && $customer['address'] == $address && $customer['recommender'] == $recommender && $customer['remark'] == $remark) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '未作任何修改',
                    ));
                }

                if($customer['idcard'] != $idcard) {
                    $condition = array(
                        'idcard' => $idcard,
                        'is_delete' => array('neq',1),
                        'company_id' => $customer['company_id'],
                    );
                    if(D('Customer')->findOneCustomerByCondition($condition)) {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '该客户已有记录',
                        ));
                    }
                }


                $data = array(
                    'name' => $name,
                    'phone' => $phone,
                    'idcard' => $idcard,
                    'address' => $address,
                    'remark' => $remark,
                    'recommender' => $recommender,
                );
                $res = D('Customer')->updateCustomerByID($id,$data);
                if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '客户信息修改失败',
                    ));
                }
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '用户信息修改成功',
                ));

            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    public function getCustomerByID() {
        $id = I('post.id',0,'intval');
        try {
            if($id) {
                $customer = D('Customer')->findCustomerByCondition('id',$id);
                $company = D('Company')->getCompanyByID($customer['company_id']);
                $customer['company'] = $company;
                if($customer) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '查找成功',
                        'customer' => $customer,
                    ));
                }else {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '客户信息不存在',
                    ));
                }
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请选择一个客户',
                ));
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    public function getCustomerAndRepaymentsByID() {

        $id = I('post.id',0,'intval');
        try {
            if($id) {
                // 检索借款人信息
                $customer = D('Customer')->findCustomerByCondition('id',$id);
                // 检索借款信息
                $loan = D('Loan')->selectLoanByCustomerID($id);
                foreach ($loan as $key => $value) {
                    $loan[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                }
                $userInfo = $this->getUserNowInfo();
                $staff = D('Staff')->getStaffByID($userInfo['staff_id']);
                if($customer) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '查找成功',
                        'customer' => $customer,
                        'loan' => $loan,
                        'staff' => $staff,
                    ));
                }else {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '借款人信息不存在',
                    ));
                }
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '请选择一个借款人',
                ));
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}