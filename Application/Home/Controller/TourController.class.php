<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/3
 * Time: 21:20
 */

namespace Home\Controller;


use Think\Exception;
use Think\Page2;
class TourController extends CommonController
{
    public function index() {
        $condition = array();
        $companys = D('Company')->selectAllCompany();
        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
        $userInfo = $this->getUserNowInfo();
        if($userInfo['jurisdiction'] == 1) {
            $condition['company_id'] = $userInfo['company_id'];
        }else if($userInfo['jurisdiction'] == 2) {
            $companyIDs = array_column($companys,'company_id');
            $condition['company_id'] = array('IN',$companyIDs);
        }

        //客户列表
        $customers = D('Customer')->selectALLCustomer($condition);
        $staffs = D('Staff')->selectALLStaff($condition);
        $locations = D('Location')->selectLoactionByCondition($condition);
        $paystyles = D('Paystyle')->selectPaystyleByCondition($condition);

        $isloans = array(
            0 => array(
                'id' => 2,
                'name' => '未放款',
            ),
            1 => array(
                'id' => 1,
                'name' => '已放款',
            ),
        );



        if(I('get.tour_id',0,'intval')) {
            $condition['tour_id'] = I('get.tour_id',0,'intval');
        }


        if(I('get.customer_id','','string')) {
            $customerSearchArray = explode(',',I('get.customer_id','','string'));
            $condition['customer_id'] = array('IN',$customerSearchArray);
            foreach ($customers as $key => $item) {
                $customers[$key]['selected'] = 0;
                foreach ($customerSearchArray as $i => $j) {
                    if($item['id'] == $j) {
                        $customers[$key]['selected'] = 1;
                    }
                }

            }
        }

        if(I('get.paystyle_id','','string')) {
            $paystyleSearchArray = explode(',',I('get.paystyle_id','','string'));
            $condition['paystyle_id'] = array('IN',$paystyleSearchArray);
            foreach ($paystyles as $key => $item) {
                $paystyles[$key]['selected'] = 0;
                foreach ($paystyleSearchArray as $i => $j) {
                    if($item['style_id'] == $j) {
                        $paystyles[$key]['selected'] = 1;
                    }
                }

            }
        }

        if(I('get.location_id','','string')) {
            $LocationSearchArray = explode(',',I('get.location_id','','string'));
            $condition['location_id'] = array('IN',$LocationSearchArray);
            foreach ($locations as $key => $item) {
                $locations[$key]['selected'] = 0;
                foreach ($LocationSearchArray as $i => $j) {
                    if($item['location_id'] == $j) {
                        $locations[$key]['selected'] = 1;
                    }
                }

            }
        }

        if(I('get.staff_id','','string')) {
            $staffSearchArray = explode(',',I('get.staff_id','','string'));
            foreach ($staffs as $key => $item) {
                $staffs[$key]['selected'] = 0;
                foreach ($staffSearchArray as $i => $j) {
                    if($item['staff_id'] == $j) {
                        $staffs[$key]['selected'] = 1;
                    }
                }
            }
            $condition['staff_id'] = array('IN',$staffSearchArray);
        }

        if(I('get.is_loan','','string')) {
            $isloanSearchArray = explode(',',I('get.is_loan','','string'));
            foreach ($isloans as $key => $item) {
                $isloans[$key]['selected'] = 0;
                foreach ($isloanSearchArray as $i => $j) {
                    if($item['id'] == $j) {
                        $isloans[$key]['selected'] = 1;
                    }
                }
            }
            foreach ($isloanSearchArray as $key => $item) {
                if($item == 2) {
                    $isloanSearchArray[$key] = 0;
                }
            }
            $condition['is_loan'] = array('IN',$isloanSearchArray);
        }


        if(I('get.search_datepicker','','string')) {
            $search_datepicker = I('get.search_datepicker','','string');
            // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
            // 分解为
            // 2017-11-17 12:30:00
            // 2017-11-25 15:30:20
            $s_time = substr($search_datepicker,0,19);
            $e_time = substr($search_datepicker,24,19);
            $condition['gmt_tour'] = array('BETWEEN',array($s_time,$e_time));
            $this->assign('input_datepicker',$search_datepicker);
        }else {
            $s_time = date('Y-m-d H:i:s',strtotime(date('Y-m',time())));
            $e_time = date('Y-m-d H:i:s',strtotime(date('Y-m',time()) . ' +1 month -1 second'));
            $condition['gmt_tour'] = array('BETWEEN',array($s_time,$e_time));
            //2018-01-01 00:00:00 至 2018-01-31 23:59:59
            $this->assign('input_datepicker',$s_time . ' 至 ' . $e_time);
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


        //1.2 获取当前页码
        $now_page = I('request.page',1,'intval');
        $page_size = I('request.pageSize',10,'intval');
        $page = $now_page ? $now_page : 1;
        //1.3 设置默认分页条数
        $pageSize = $page_size ? $page_size : 10;
        //1.4 数据库查询

        $tours = D('Tour')->listTourLimit($condition,$page,$page_size);
        $sum_money = D('Tour')->sumMoney($condition);
        $countTours = D('Tour')->getCountTour($condition);


        //1.5 实例化一个分页对象
        $res = new Page2($countTours,$pageSize);
        //1.6 调用show方法前台显示页码
        $pageRes = $res->show();
        //1.7 处理数据

        foreach ($tours as $key => $item) {

            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $tours[$key]['company_name'] = $company['smallname'];

            // 客户信息
            $customer = D('Customer')->getCustomerByID($item['customer_id']);
            $tours[$key]['customer_name'] = $customer['name'];
            $tours[$key]['customer_phone'] = $customer['phone'];

            // 位置信息
            $location = D('Location')->getLocationByID($item['location_id']);
            $tours[$key]['location_name'] = $location['name'];

            // 外访经理
            $staff = D('Staff')->getStaffByID($item['staff_id']);
            $tours[$key]['staff_name'] = $staff['staff_name'];

            // 收款方式
            $paystyle = D('Paystyle')->getPaystyleByID($item['paystyle_id']);
            $tours[$key]['paystyle_name'] = $paystyle['name'];

            // 是否放款
            $tours[$key]['is_loan'] = $tours[$key]['is_loan'] == 0 ? '未放款' : '已放款';
            $tours[$key]['gmt_tour'] = date('Y-m-d',strtotime($tours[$key]['gmt_tour']));

        }

        $this->assign(array(
            'customers' => $customers,
            'locations' => $locations,
            'staffs' => $staffs,
            'paystyles' => $paystyles,
            'tours' => $tours,
            'pageRes' => $pageRes,
            'isloans' => $isloans,
            'userInfo' => $userInfo,
            'companys' => $companys,
            'sum_money' => $sum_money,
        ));
        $this->display();
    }

    public function checkAdd() {

        $customer_id = I('post.customer_id',0,'intval');
        $location_id = I('post.location_id',0,'intval');
        $staff_id = I('post.staff_id',0,'intval');
        $money = I('post.money',0,'float');
        $style_id = I('post.style_id',0,'intval');
        $is_loan = I('post.is_loan',0,'intval');
        $remark = I('post.remark','','string');
        $company_id = I('post.company_id',0,'intval');
        $gmt_tour = I('post.gmt_tour','','string');


        if(!$customer_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择客户姓名',
            ));
        }

        if(!$location_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择外访区域',
            ));
        }

        if(!$staff_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择外访客户',
            ));
        }

        /*if(!$money) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择外访金额',
            ));
        }*/

        if(!$style_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择支付方式',
            ));
        }

        if(!$gmt_tour) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择外访时间',
            ));
        }

        if(!$company_id) {
            $userInfo = $this->getUserNowInfo();
            $company_id = $userInfo['company_id'];
        }

        $gmt_tour = str_replace('年','-',$gmt_tour);
        $gmt_tour = str_replace('月','-',$gmt_tour);
        $gmt_tour = str_replace('日','-',$gmt_tour);
        $data = array(
            'customer_id' => $customer_id,
            'location_id' => $location_id,
            'staff_id' => $staff_id,
            'money' => $money,
            'is_loan' => $is_loan,
            'remark' => $remark,
            'company_id' => $company_id,
            'paystyle_id' => $style_id,
            'gmt_create' => date('Y-m-d H:i:s',time()),
            'gmt_tour' => $gmt_tour,
        );

        try {
            $tour = D('Tour')->addTour($data);
            if(!$tour) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '外访资料新增失败，请稍后再试',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '外访资料新增成功',
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }

    }

    public function editTour() {
        $tour_id = I('post.id',0,'intval');
        if(!$tour_id) {
           throw_exception('外访ID不存在');
        }
        try {
            $tour = D('Tour')->getTourByID($tour_id);
            if(!$tour) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '外访记录不存在',
                ));
            }
            $tour['gmt_tour'] = date('Y-m-d',strtotime($tour['gmt_tour']));
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '外访记录存在',
                'tour' => $tour,
            ));
        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function checkEditValue() {

        $tour_id = I('post.tour_id',0,'intval');
        $customer_id = I('post.customer_id',0,'intval');
        $location_id = I('post.location_id',0,'intval');
        $staff_id = I('post.staff_id',0,'intval');
        $money = I('post.money',0,'float');
        $style_id = I('post.style_id',0,'intval');
        $is_loan = I('post.is_loan',0,'intval');
        $remark = I('post.remark','','string');
        $gmt_tour = I('post.gmt_tour','','string');

        if(!$tour_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '外访记录ID号不存在',
            ));
        }

        if(!$customer_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择客户姓名',
            ));
        }

        if(!$location_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择外访区域',
            ));
        }

        if(!$staff_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择外访客户',
            ));
        }

        /*if(!$money) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择外访金额',
            ));
        }*/

        if(!$style_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择支付方式',
            ));
        }

        if(!$gmt_tour) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择外访时间',
            ));
        }

        $gmt_tour = str_replace('年','-',$gmt_tour);
        $gmt_tour = str_replace('月','-',$gmt_tour);
        $gmt_tour = str_replace('日','-',$gmt_tour);
        $data = array(
            'customer_id' => $customer_id,
            'location_id' => $location_id,
            'staff_id' => $staff_id,
            'money' => $money,
            'is_loan' => $is_loan,
            'remark' => $remark,
            'paystyle_id' => $style_id,
            'gmt_modify' => date('Y-m-d H:i:s',time()),
            'gmt_tour' => $gmt_tour,
        );

        try {
            $tour = D('Tour')->updateTour($tour_id,$data);
            if(!$tour) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '修改失败，请稍后再试',
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
    public function deleteTour() {
        $tour_id = I('post.id',0,'intval');
        if(!$tour_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '外访记录ID号不存在',
            ));
        }

        try {
            $data = array(
                'is_delete' => 1,
            );
            $tour = D('Tour')->updateTour($tour_id,$data);
            if(!$tour){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '删除失败，请稍后再试',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '删除成功',
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }

    }

    public function export() {
        $condition = array();
        $companys = D('Company')->selectAllCompany();
        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
        $userInfo = $this->getUserNowInfo();
        if($userInfo['jurisdiction'] == 1) {
            $condition['company_id'] = $userInfo['company_id'];
        }else if($userInfo['jurisdiction'] == 2) {
            $companyIDs = array_column($companys,'company_id');
            $condition['company_id'] = array('IN',$companyIDs);
        }

        if(I('get.customer_id','','string')) {
            $customerSearchArray = explode(',',I('get.customer_id','','string'));
            $condition['customer_id'] = array('IN',$customerSearchArray);
        }

        if(I('get.paystyle_id','','string')) {
            $paystyleSearchArray = explode(',',I('get.paystyle_id','','string'));
            $condition['paystyle_id'] = array('IN',$paystyleSearchArray);
        }

        if(I('get.location_id','','string')) {
            $LocationSearchArray = explode(',',I('get.location_id','','string'));
            $condition['location_id'] = array('IN',$LocationSearchArray);
        }

        if(I('get.staff_id','','string')) {
            $staffSearchArray = explode(',',I('get.staff_id','','string'));
            $condition['staff_id'] = array('IN',$staffSearchArray);
        }

        if(I('get.is_loan','','string')) {
            $isloanSearchArray = explode(',',I('get.is_loan','','string'));
            $condition['is_loan'] = array('IN',$isloanSearchArray);
        }


        if(I('get.search_datepicker','','string')) {
            $search_datepicker = I('get.search_datepicker','','string');
            $s_time = substr($search_datepicker,0,19);
            $e_time = substr($search_datepicker,24,19);
            $condition['gmt_tour'] = array('BETWEEN',array($s_time,$e_time));
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

        $tours = D('Tour')->listTour($condition);

        foreach ($tours as $key => $item) {
            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $tours[$key]['company_name'] = $company['smallname'];

            // 客户信息
            $customer = D('Customer')->getCustomerByID($item['customer_id']);
            $tours[$key]['customer_name'] = $customer['name'];
            $tours[$key]['customer_phone'] = $customer['phone'];

            // 位置信息
            $location = D('Location')->getLocationByID($item['location_id']);
            $tours[$key]['location_name'] = $location['name'];

            // 外访经理
            $staff = D('Staff')->getStaffByID($item['staff_id']);
            $tours[$key]['staff_name'] = $staff['staff_name'];

            // 收款方式
            $paystyle = D('Paystyle')->getPaystyleByID($item['paystyle_id']);
            $tours[$key]['paystyle_name'] = $paystyle['name'];

            // 是否放款
            $tours[$key]['is_loan'] = $tours[$key]['is_loan'] == 0 ? '未放款' : '已放款';
            $tours[$key]['gmt_tour'] = date('Y-m-d',strtotime($tours[$key]['gmt_tour']));

        }

        $id = 1;
        foreach ($tours as $key => $item) {
            $tours[$key]['id'] = $id++;
        }

        $xlsName  = '外访记录表';

        if($userInfo['jurisdiction'] == 2) {
            $xlsCell  = array(
                array('id','序号'),
                array('gmt_create','外访时间'),
                array('customer_name','客户姓名'),
                array('customer_phone','手机号码'),
                array('location_name','外访区域'),
                array('staff_name','外访经理'),
                array('money','外访费用'),
                array('paystyle_name','收款方式'),
                array('is_loan','是否放款'),
                array('remark','备注信息'),
                array('company_name','所属公司'),
            );
        }else {
            $xlsCell  = array(
                array('id','序号'),
                array('gmt_create','外访时间'),
                array('customer_name','客户姓名'),
                array('customer_phone','手机号码'),
                array('location_name','外访区域'),
                array('staff_name','外访经理'),
                array('money','外访费用'),
                array('paystyle_name','收款方式'),
                array('is_loan','是否放款'),
                array('remark','备注信息'),
            );
        }


        $xlsData = $tours;
        $excel = new PhpexcelController();
        $excel->exportExcel($xlsName,$xlsCell,$xlsData);
    }
}