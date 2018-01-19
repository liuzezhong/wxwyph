<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/12/31
 * Time: 14:04
 */

namespace Home\Controller;


use Think\Exception;
use Think\Page2;
class ChargeController extends CommonController
{
    public function index() {
        $condition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();

        if(I('get.search_datepicker','','string')) {
            $search_datepicker = I('get.search_datepicker','','string');
            // 将2017-11-17 12:30:00 至 2017-11-25 15:30:20
            // 分解为
            // 2017-11-17 12:30:00
            // 2017-11-25 15:30:20
            $s_time = substr($search_datepicker,0,19);
            $e_time = substr($search_datepicker,24,19);
            $condition['gmt_create'] = array('BETWEEN',array($s_time,$e_time));
            $this->assign('input_datepicker',$search_datepicker);
        }else {
            $s_time = date('Y-m-d H:i:s',strtotime(date('Y-m',time())));
            $e_time = date('Y-m-d H:i:s',strtotime(date('Y-m',time()) . ' +1 month -1 second'));
            $condition['gmt_create'] = array('BETWEEN',array($s_time,$e_time));
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

        $charges = D('Charge')->listCharges($condition,$page,$page_size);
        $sum_money = D('Charge')->sumMoney($condition);
        $countCharges = D('Charge')->getCountCharges($condition);


        //1.5 实例化一个分页对象
        $res = new Page2($countCharges,$pageSize);
        //1.6 调用show方法前台显示页码
        $pageRes = $res->show();
        //1.7 处理数据

        foreach ($charges as $key => $item) {
            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $charges[$key]['company_name'] = $company['smallname'];
            $charges[$key]['gmt_create'] = date('Y-m-d',strtotime($item['gmt_create']));
        }

        $this->assign(array(
            'charges' => $charges,
            'pageRes' => $pageRes,
            'sum_moeny' => $sum_money,
            'userInfo' => $userInfo,
            'companys' => $companys
        ));

        $this->display();
    }

    public function checkAdd() {
        $money = I('post.money','0','floatval');
        $matter = I('post.matter','','string,trim');
        $gmt_create = I('post.gmt_create','','');
        $remark = I('post.remark','','');
        $company_id = I('post.company_id',0,'intval');
        $userInfo = $this->getUserNowInfo();
        if(!$money) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '支出金额不能为零！'
            ));
        }

        if(!$matter) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '支出事项不能为空！'
            ));
        }

        if(!$gmt_create) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '支出时间不能为空！'
            ));
        }
        if(!$company_id) {
            $company_id = $userInfo['company_id'];
        }


        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据


        try {
            $data = array(
                'money' => $money,
                'matter' => $matter,
                'gmt_create' => $gmt_create,
                'remark' => $remark,
                'user_id' => $userInfo['user_id'],
                'company_id' => $company_id,
            );
            $charge = D('Charge')->addCharge($data);
            if($charge) {
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

    /**
     * 删除现金记录
     */
    public function deleteCharge() {
        $charge_id = I('post.id',0,'intval');
        if($charge_id) {
            try {
                $res = D('Charge')->deleteChargeByID($charge_id);
                if($res) {
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'message' => '删除成功！',
                    ));
                }else if(!$res) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '删除失败，请稍后重试！',
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '记账ID不存在！',
            ));
        }
    }

    public function editCharge() {
        $charge_id = I('post.id',0,'intval');
        if(!$charge_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '现金ID不存在！',
            ));
        }

        try {

            $charge = D('Charge')->getChargeByID($charge_id);
            $charge['gmt_create'] = date('Y-m-d',strtotime($charge['gmt_create']));

            if(!$charge) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '无现金记账记录',
                ));
            }
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '有借款记录',
                'charge' => $charge,
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function checkEditValue() {
        $charge_id = I('post.charge_id','0','intval');
        $money = I('post.money','0','floatval');
        $matter = I('post.matter','','string,trim');
        $gmt_create = I('post.gmt_create','','');
        $remark = I('post.remark','','');

        if(!$charge_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '记账ID不存在！'
            ));
        }

        if(!$money) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '支出金额不能为零！'
            ));
        }

        if(!$matter) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '支出事项不能为空！'
            ));
        }

        if(!$gmt_create) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '支出时间不能为空！'
            ));
        }

        try {
            $data = array(
                'money' => $money,
                'matter' => $matter,
                'gmt_create' => $gmt_create,
                'gmt_update' => date('Y-m-d H:i:s',time()),
                'remark' => $remark,
                'user_id' => session('adminUser')['user_id'],
            );
            $charge = D('Charge')->updateCharge($charge_id,$data);
            if($charge) {
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '修改成功',
                ));
            }else {
                $this->ajaxReturn(array(
                    'status' => 1,
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

    public function exportExcel() {
        $condition = array();
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();

        if(I('get.search_datepicker','','string')) {
            $search_datepicker = I('get.search_datepicker','','string');
            $s_time = substr($search_datepicker,0,19);
            $e_time = substr($search_datepicker,24,19);
            $condition['gmt_create'] = array('BETWEEN',array($s_time,$e_time));
            $this->assign('input_datepicker',$search_datepicker);
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

        $charges = D('Charge')->listChargesByCondition($condition);
        foreach ($charges as $key => $item) {
            $charges[$key]['id'] = $key + 1;
            // 获取公司信息
            $company = D('Company')->getCompanyByID($item['company_id']);
            $charges[$key]['company_name'] = $company['smallname'];
            $charges[$key]['gmt_create'] = date('Y-m-d',strtotime($item['gmt_create']));
        }

        $xlsName  = '现金记账表';

        if($userInfo['jurisdiction'] == 2) {
            $xlsCell  = array(
                array('id','序号'),
                array('gmt_create','支出时间'),
                array('money','支出金额'),
                array('matter','支出事项'),
                array('company_name','所属公司'),
                array('remark','备注信息'),
            );
        }else {
            $xlsCell  = array(
                array('id','序号'),
                array('gmt_create','支出时间'),
                array('money','支出金额'),
                array('matter','支出事项'),
                array('remark','备注信息'),
            );
        }


        $xlsData = $charges;
        $excel = new PhpexcelController();
        $excel->exportExcel($xlsName,$xlsCell,$xlsData);


    }
}