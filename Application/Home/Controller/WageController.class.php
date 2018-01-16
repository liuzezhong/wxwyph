<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/15
 * Time: 22:59
 */

namespace Home\Controller;
use Think\Exception;
use Think\Page2;
class WageController extends CommonController
{
    public function index() {
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();

        if(I('get.search_datepicker_start','','string')) {
            $search_datepicker_start = I('get.search_datepicker_start','','string');
            $this->assign('input_datepicker_start',$search_datepicker_start);
        }else {
            // 今年的第一个月
            $search_datepicker_start = date('Y-m',strtotime(date('Y',time())));
            $this->assign('input_datepicker_start',$search_datepicker_start);
        }

        if(I('get.search_datepicker_end','','string')) {
            $search_datepicker_end = I('get.search_datepicker_end','','string');
            $this->assign('input_datepicker_end',$search_datepicker_end);
        }else {
            $search_datepicker_end = date('Y-m',time());
            $this->assign('input_datepicker_end',$search_datepicker_end);
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
        $condition['gmt_wage'] = array('BETWEEN',array(date('Y-m-d H:i:s',strtotime($search_datepicker_start)),date('Y-m-d H:i:s',strtotime($search_datepicker_end))));

        $wages = D('Wage')->listWagesLimit($condition,$page,$page_size);
        $countWages = D('Wage')->getCountWages($condition);
        $sumWage = D('Wage')->sumWageMoney($condition);
        $sumInsur = D('Wage')->sumInsurMoney($condition);
        $sumTotal = D('Wage')->sumTotalMoney($condition);

        //1.5 实例化一个分页对象
        $res = new Page2($countWages,$pageSize);
        //1.6 调用show方法前台显示页码
        $pageRes = $res->show();
        //1.7 处理数据

        foreach ($wages as $key => $item) {
            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $wages[$key]['company_name'] = $company['smallname'];
            $wages[$key]['gmt_wage'] = date('Y年m月',strtotime($item['gmt_wage']));
        }

        $this->assign(array(
            'userInfo' => $userInfo,
            'companys' => $companys,
            'wages' => $wages,
            'sumWage' => $sumWage,
            'sumInsur' => $sumInsur,
            'sumTotal' => $sumTotal,

        ));
        $this->display();
    }

    public function checkAdd() {
        $gmt_wage = I('post.gmt_wage','','string,trim');
        $number = I('post.number',0,'intval');
        $wage = I('post.number',0,'floatval');
        $insur = I('post.insur',0,'floatval');
        $remark = I('post.remark','','string');
        $company_id = I('post.company_id',0,'intval');
        $userInfo = $this->getUserNowInfo();

        if(!$gmt_wage) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '日期不能为空'
            ));
        }

        if(!$company_id) {
            $company_id = $userInfo['company_id'];
        }

        try {
            // 查看当前公司的当前月份是否已经存在
            $isExist = D('Wage')->checkExist(date('Y-m-d H:i:s',strtotime($gmt_wage)),$company_id);
            if($isExist) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '当前月份已有工资记录'
                ));
            }

            $data = array(
                'gmt_wage' => date('Y-m-d H:i:s',strtotime($gmt_wage)),
                'number' => $number,
                'wage' => $wage,
                'insur' => $insur,
                'remark' => $remark,
                'total' => $wage + $insur,
                'company_id' => $company_id,
                'gmt_create' => date('Y-m-d H:i:s',time()),
            );
            $wage = D('Wage')->addWage($data);
            if($wage) {
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '新增成功',
                ));
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '新增失败，请稍后重试！',
                ));
            }
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function export() {
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();

        if(I('get.search_datepicker_start','','string')) {
            $search_datepicker_start = I('get.search_datepicker_start','','string');
        }else {
            // 今年的第一个月
            $search_datepicker_start = date('Y-m',strtotime(date('Y',time())));
        }

        if(I('get.search_datepicker_end','','string')) {
            $search_datepicker_end = I('get.search_datepicker_end','','string');
        }else {
            $search_datepicker_end = date('Y-m',time());
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

        //1.4 数据库查询
        $condition['gmt_wage'] = array('BETWEEN',array(date('Y-m-d H:i:s',strtotime($search_datepicker_start)),date('Y-m-d H:i:s',strtotime($search_datepicker_end))));

        $wages = D('Wage')->listWages($condition);
        foreach ($wages as $key => $item) {
            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $wages[$key]['company_name'] = $company['smallname'];
            $wages[$key]['gmt_wage'] = date('Y年m月',strtotime($item['gmt_wage']));
        }

        $xlsName  = '员工工资表';

        if($userInfo['jurisdiction'] == 2) {
            $xlsCell  = array(
                array('gmt_wage','月份'),
                array('number','员工数'),
                array('wage','员工工资总额'),
                array('insur','员工社保总额'),
                array('total','合计'),
                array('remark','备注'),
                array('company_name','所属公司'),
            );
        }else {
            $xlsCell  = array(
                array('gmt_wage','月份'),
                array('number','员工数'),
                array('wage','员工工资总额'),
                array('insur','员工社保总额'),
                array('total','合计'),
                array('remark','备注'),
            );
        }


        $xlsData = $wages;
        $excel = new PhpexcelController();
        $excel->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    public function editWage() {
        $wage_id = I('post.id',0,'intval');
        if(!$wage_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '工资ID不存在！',
            ));
        }
        try {
            $wage = D('Wage')->getWageByID($wage_id);
            if(!$wage) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '无工资记录',
                ));
            }
            $wage['gmt_wage'] = date('Y-m',strtotime($wage['gmt_wage']));
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '有工资记录',
                'wage' => $wage,
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function checkEditValue() {
        $wage_id = I('post.wage_id',0,'intval');
        $gmt_wage = I('post.gmt_wage','','string,trim');
        $number = I('post.number',0,'intval');
        $wage = I('post.number',0,'floatval');
        $insur = I('post.insur',0,'floatval');
        $remark = I('post.remark','','string');
        $company_id = I('post.company_id',0,'intval');
        $userInfo = $this->getUserNowInfo();

        if(!$wage_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '工资ID不存在'
            ));
        }

        if(!$gmt_wage) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '日期不能为空'
            ));
        }

        if(!$company_id) {
            $company_id = $userInfo['company_id'];
        }

        try {
            // 查看当前公司的当前月份是否已经存在
            $isExist = D('Wage')->checkExist(date('Y-m-d H:i:s',strtotime($gmt_wage)),$company_id);

            if($isExist && $isExist['wage_id'] != $wage_id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '当前月份已有工资记录'
                ));
            }

            $data = array(
                'gmt_wage' => date('Y-m-d H:i:s',strtotime($gmt_wage)),
                'number' => $number,
                'wage' => $wage,
                'insur' => $insur,
                'remark' => $remark,
                'total' => $wage + $insur,
                'gmt_modify' => date('Y-m-d H:i:s',time()),
            );
            $wage = D('Wage')->updateWage($wage_id,$data);
            if($wage) {
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '修改成功',
                ));
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '修改失败，请稍后重试！',
                ));
            }
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function deleteWage() {
        $wage_id = I('post.id',0,'intval');
        if($wage_id) {
            try {
                $res = D('Wage')->logicDeleteWageByID($wage_id);
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
                'message' => '工资D不存在',
            ));
        }
    }
}