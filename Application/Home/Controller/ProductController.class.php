<?php
/**
 * Created by PhpStorm.
 * User: PC41
 * Date: 2017-08-03
 * Time: 13:40
 */

namespace Home\Controller;


use Think\Controller;

class ProductController extends CommonController {
    public function index() {

    }

    public function getProductByID() {
        $product_id = I('post.product_id',0,'intval');
        if($product_id) {
            //根据产品ID获取产品信息
            $product = D('Product')->findProductByCondition('product_id',$product_id);
            if($product) {
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '产品信息获取成功！',
                    'product' => $product,
                ));
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '该产品信息不存在！'
                ));
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择产品类型！'
            ));
        }
    }
}