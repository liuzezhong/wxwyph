<?php
/**
 * Created by PhpStorm.
 * User: PC41
 * Date: 2017-08-03
 * Time: 13:44
 */

namespace Home\Controller;


use Think\Controller;
use Think\Exception;

class CycleController extends CommonController {
    public function index() {
        $this->display();
    }

    public function getCycleByProductID() {

        $product_id = I('post.product_id',0,'intval');

        if($product_id) {
            try {
                //根据产品ID获取产品信息
                $product = D('Product')->findProductByCondition('product_id',$product_id);

                if($product) {
                    //根据产品的还款时间ID获取还款时间信息
                    $cycle = D('Cycle')->findCycleByCondition('cycle_id',$product['cycle_id']);
                    if($cycle) {
                        $this->ajaxReturn(array(
                            'status' => 1,
                            'message' => '还款时间获取成功！',
                            'cycle' => $cycle,
                        ));
                    }else {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '该产品未设置对应还款时间，请手动选择！'
                        ));
                    }
                }else {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '该产品信息不存在！'
                    ));
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '请选择产品类型！'
            ));
        }
    }
}