<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/5
 * Time: 23:04
 */

namespace Home\Model;


use function foo\func;
use Think\Model;

class MessageModel extends Model
{
    /**
     * 新增短信记录
     * @param $data
     * @return mixed
     */
    public function addMessage($data = array()) {
        if(!$data) {
            throw_exception('Home Model MessageController addMessage data is null');
        }
        return $this->add($data);
    }

    /**
     * 更新短信记录
     * @param int $message_id
     * @param array $data
     * @return bool
     */
    public function updateMessage($message_id = 0, $data = array()) {
        if(!$message_id) {
            throw_exception('Home Model MessageController updateMessage message_id is null');
        }
        if(!$data) {
            throw_exception('Home Model MessageController updateMessage data is null');
        }
        $condition['message_id'] = $message_id;
        return $this->where($condition)->save($data);
    }

    /**
     * 根据条件查询短信信息
     * @param array $condition
     * @return mixed
     */
    public function getMessageByCondition($condition = array()) {
        if(!$condition) {
            throw_exception('Home Model MessageController getMessageByCondition condition is null');
        }
        return $this->where($condition)->select();
    }

    /**
     * 列出所有的短信信息记录，并分页
     * @param array $condition
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function listMessages($condition = array(), $page = 1, $pageSize = 10) {
        $offset = ($page -1) * $pageSize;
        $res =  $this->where($condition)->order('gmt_create desc')->limit($offset,$pageSize)->select();
        return $res;
    }

    public function listMessagesByCondition($condition = array()) {
        $res =  $this->where($condition)->order('gmt_create desc')->select();
        return $res;
    }

    /**
     * 获取短信的数量
     * @param array $condition
     * @return mixed
     */
    public function getCountMessage($condition = array()) {
        return $this->where($condition)->count();
    }

}