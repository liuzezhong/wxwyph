<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/6
 * Time: 20:29
 */

namespace Home\Controller;


use Think\Exception;
use Think\Page2;
use Think\Upload\Driver\Qiniu\QiniuStorage;
class ImageController extends CommonController
{
    public function index() {
        //1.2 获取当前页码
        $condition = array();
        $companyCondition = array();
        //客户列表
        $companys = D('Company')->selectAllCompany();
        $userInfo = $this->getUserNowInfo();
        // 如果账户权限为普通管理员，那么只能查找所属公司的数据，否则默认全部数据
        if($userInfo['jurisdiction'] == 1) {
            $companyCondition['company_id'] = $userInfo['company_id'];
        }else if($userInfo['jurisdiction'] == 2) {
            $companyIDs = array_column($companys,'company_id');
            $companyCondition['company_id'] = array('IN',$companyIDs);
        }
        $customers = D('Customer')->selectALLCustomer($companyCondition);

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

        $now_page = I('request.page',1,'intval');
        $page_size = I('request.pageSize',24,'intval');
        $page = $now_page ? $now_page : 1;
        //1.3 设置默认分页条数
        $pageSize = $page_size ? $page_size : 24;
        //1.4 数据库查询

        $loans = D('Image')->listImageLoansID($condition,$page,$page_size);
        $noLimitLoans = D('Image')->listImageNoLimit($condition);
        $countLoans = D('Image')->getCountImageLoans($condition);
        
        //1.5 实例化一个分页对象
        $res = new Page2(count($noLimitLoans),$pageSize);
        //1.6 调用show方法前台显示页码
        $pageRes = $res->show();
        //1.7 处理数据

        foreach ($loans as $key => $item) {
            // 获取公司信息
            $loan = D('Loan')->getLoanByID($item['loan_id']);

            $company = D('Company')->getCompanyByID($loan['company_id']);
            $loans[$key]['company_name'] = $company['smallname'];
            $loans[$key]['gmt_create'] = date('Y-m-d',strtotime($loan['gmt_create']));

            $customer = D('Customer')->getCustomerByID($loan['customer_id']);
            $loans[$key]['name'] = $customer['name'];
            $image = D('Image')->getImageByLoanID($item['loan_id']);
            $loans[$key]['image'] = $image['url'] . '?' . C('QINIU_SUOTU_XIANGCE');
            $loans[$key]['create_time'] = date('Y年m月d日',$loan['create_time']);
            $loans[$key]['coutImage'] = D('Image')->countImageByLoanID($item['loan_id']);
        }

        $this->assign(array(
            'loans' => $loans,
            'pageRes' => $pageRes,
            'customers' => $customers,
            'userInfo' => $userInfo,
            'companys' => $companys,
//            'userInfo' => $userInfo,
//            'companys' => $companys
        ));

        $this->display();
    }

    public function detail() {
        $loan_id = I('get.loan_id',0,'intval');
        //$loan_id = 1177;
        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'ID不存在',
            ));
        }
        try {

            $condition = array();
            $now_page = I('request.page',1,'intval');
            $page_size = I('request.pageSize',24,'intval');
            $page = $now_page ? $now_page : 1;
            //1.3 设置默认分页条数
            $pageSize = $page_size ? $page_size : 24;
            //1.4 数据库查询
            $condition['loan_id'] = $loan_id;
            $images = D('Image')->listImageLimit($condition,$page,$page_size);
            $countImages = D('Image')->countImageLimit($condition);

            //1.5 实例化一个分页对象
            $res = new Page2($countImages,$pageSize);
            //1.6 调用show方法前台显示页码
            $pageRes = $res->show();
            //1.7 处理数据
            $loan = D('Loan')->getLoanByID($loan_id);
            $loan['create_time'] = date('Y年m月d日',$loan['create_time']);
            if($loan) {
                $customer = D('Customer')->getCustomerByID($loan['customer_id']);
            }

            foreach ($images as $key => $item) {
                $images[$key]['suo'] = $item['url'] . '?' . C('QINIU_SUOTU_ZHAOPIAN');
            }

//            $images = D('Image')->selectImageByLoanID($loan_id);
            $this->assign(
                array(
                    'images' => $images,
                    'pageRes' => $pageRes,
                    'loan_id' => $loan_id,
                    'loan' => $loan,
                    'customer' => $customer,
                    'countImages' => $countImages,
                )
            );
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
        $this->display();
    }

    /**
     * 上传图片
     */
    public function ajaxUploadImage() {
        $res = D("UploadImage")->imagesUpload();
        if($res===false) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '图片上传失败',
                'res' => $res,
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '图片上传成功',
                'res' => $res,
            ));
        }
    }

    public function getLoanImage() {
        $loan_id = I('post.loan_id',0,'intval');
        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '借款ID不存在',
            ));
        }

        try {
            // 借款信息
            $loan = D('Loan')->getLoanByID($loan_id);
            if(!$loan_id) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '借款记录不存在',
                ));
            }
            // 客户信息
            $customer = D('Customer')->getCustomerByID($loan['customer_id']);
            // 图片信息
            $images = D('Image')->selectImageByLoanID($loan_id);
            $data = array();
            foreach ($images as $key => $item) {
                $data[] = array(
                    "alt" => $customer['name'] . '  ' . date('Y年m月d日',$loan['create_time']),
                    "pid" => $key,
                    "src" => $item['url'],
                    "thumb" => $item['url'],
                );
            }
            $photos = array(
                'title' => $customer['name'] . '  ' . date('Y年m月d日',$loan['create_time']),
                'id' => $loan_id,
                'start' => 0,
                'data' => $data,
            );

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '图片查找成功',
                'photos' => $photos,
            ));



        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    /**
     * 删除图片
     */
    public function deleteImage() {
        $imageValue = I('post.imageValue');
        if(!$imageValue) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择一个张图片',
            ));
        }
        $sub_length = strlen(C('QINIU_DOMAIN_URL'));
        try {
            foreach ($imageValue as $key => $item) {
                $image = D('Image')->getImageByID($item);
                $image_name = substr($image['url'],$sub_length);
                $dele_qiniu = $this->deleQiniu($image_name);
            }
            $condition['image_id'] = array('IN',$imageValue);
            $dele_image = D('Image')->deleteImageByCondition($condition);
            if($dele_image) {
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '删除成功',
                ));
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '删除失败，请稍后再试',
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
     * 删除七牛图片
     * @param string $file_name
     * @return array|int
     */
    public function deleQiniu($file_name = '') {
        if(!$file_name)
            return -1;
        $setting = C('UPLOAD_SITEIMG_QINIU');
        $Qiniu = new QiniuStorage($setting['driverConfig']);
        $result = $Qiniu->del($file_name);
        $error = $Qiniu->errorStr;//错误信息
        if(is_array($result) && !($error)){
            return array(
                'status' => 1,
                'message' => '七牛云文件删除成功'
            );
        }else {
            return array(
                'status' => -1,
                'message' => '七牛云文件删除失败',
                'error' => $error
            );
        }
    }

    public function saveLoanImage() {
        $valArr = I('post.valArr');
        $loan_id = I('post.loan_id',0,'intval');

        if(!$valArr) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请上传一个张图片',
            ));
        }

        if(!$loan_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '借款ID不存在',
            ));
        }

        try {
            $loan = D('Loan')->getLoanByID($loan_id);
            $userInfo = $this->getUserNowInfo();

            foreach ($valArr as $key => $item) {
                $imageData = array(
                    'url' => $item,
                    'user_id' => $userInfo['user_id'],
                    'gmt_create' => date('Y-m-d H:i:s',time()),
                    'company_id' => $loan['company_id'],
                    'loan_id' => $loan_id,
                    'customer_id' => $loan['customer_id'],
                );
                $image = D('Image')->addImage($imageData);
            }

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '外访资料上传成功',
            ));
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }
}