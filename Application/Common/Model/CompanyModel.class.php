<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/1
 * Time: 21:00
 */

namespace Common\Model;


use Think\Model;

class CompanyModel extends Model
{
    /**
     * 根据公司ID获取公司信息
     * @param int $company_id
     * @return mixed
     */
    public function getCompanyByID($company_id = 0) {
        if(!$company_id) {
            throw_exception('Home Model CompanyModel getCompanyByID company_id is null');
        }
        $condition['company_id'] = $company_id;
        return $this->where($condition)->find();
    }

    /**
     * 根据条件搜索公司信息
     * @param array $condition
     * @return mixed
     */
    public function selectAllCompany($condition = array()) {
        return $this->where($condition)->select();
    }
}