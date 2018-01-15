<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/7
 * Time: 14:40
 */

namespace Home\Model;


use Think\Model;

class ImageModel extends Model
{
    /**
     * 新增图片信息
     * @param array $data
     * @return mixed
     */
    public function addImage($data = array()) {
        if(!$data) {
            throw_exception('Home Model ImageModel addImage data is null');
        }
        return $this->add($data);
    }

    /**
     * 根据借款ID批量搜索图片信息
     * @param int $loan_id
     * @return mixed
     */
    public function selectImageByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('Home Model ImageModel selectImageByLoanID loan_id is null');
        }
        $condition['loan_id'] = $loan_id;
        return $this->where($condition)->select();
    }

    public function listImageLoansID($condition = array(), $page = 1, $pageSize = 12) {
        //$condition['is_delete'] = array('neq',-1);
        $offset = ($page -1) * $pageSize;
        return $this->where($condition)->distinct(true)->field('loan_id')->limit($offset,$pageSize)->select();
    }

    public function listImageNoLimit($condition = array()) {
        return $this->where($condition)->distinct(true)->field('loan_id')->select();
    }

    public function getCountImageLoans($condition = array()) {
        return $this->where($condition)->distinct(true)->field('loan_id')->count();
    }

    public function getImageByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('Home Model ImageModel getImageByLoanID loan_id is null');
        }
        $condition['loan_id'] = $loan_id;
        return $this->where($condition)->find();
    }

    /**
     * 根据图片ID获取图片信息
     * @param int $image_id
     * @return mixed
     */
    public function getImageByID($image_id = 0) {
        if(!$image_id) {
            throw_exception('Home Model ImageModel getImageByID image_id is null');
        }
        $condition['image_id'] = $image_id;
        return $this->where($condition)->find();
    }

    /**
     * 批量删除
     * @param array $condition
     * @return mixed
     */
    public function deleteImageByCondition($condition = array()) {
        return $this->where($condition)->delete();
    }

    public function listImageLimit($condition = array(), $page = 1, $pageSize = 12) {
        //$condition['is_delete'] = array('neq',-1);
        $offset = ($page -1) * $pageSize;
        return $this->where($condition)->order('gmt_create desc')->limit($offset,$pageSize)->select();
    }

    public function countImageLimit($condition = array()) {
        return $this->where($condition)->order('gmt_create desc')->count();
    }

    /**
     * 根据借款ID统计个数
     * @param int $loan_id
     * @return mixed
     */
    public function countImageByLoanID($loan_id = 0) {
        if(!$loan_id) {
            throw_exception('Home Model ImageModel countImageByLoanID image_id is null');
        }
        $condition['loan_id'] = $loan_id;
        return $this->where($condition)->count();
    }
}