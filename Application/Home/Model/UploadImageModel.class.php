<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/11/16
 * Time: 15:42
 */

namespace Home\Model;


use Think\Model;

class UploadImageModel extends Model
{
    private $_uploadObj = '';
    const UPLOAD = 'Uploads';    //定义上传文件夹
    public function __construct() {
        $this->_uploadObj = new  \Think\Upload(C('UPLOAD_SITEIMG_QINIU'));
//        $this->_uploadObj->rootPath = './'.self::UPLOAD;
//        //$this->_uploadObj->subName = date(Y) . '_' . date(m) .'_' . date(d);
//        $this->_uploadObj->subName = '';
    }
    public function imageUpload() {
        $res = $this->_uploadObj->upload();
        if($res) {
            return '/' . self::UPLOAD . '/' . $res['file']['savepath'] . $res['file']['savename'];
        }else{
            return false;
        }
    }
    public function imagesUpload() {
        $res = $this->_uploadObj->upload();
        if($res) {
            return $res;
        }else{
            return $this->_uploadObj->getError();
        }
    }
}