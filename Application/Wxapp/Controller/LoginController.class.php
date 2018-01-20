<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/12/29
 * Time: 16:15
 */
namespace Wxapp\Controller;

use Think\Exception;

class LoginController extends \Think\Controller
{
    public function index() {
        // 发送 res.code 到后台换取 openId, sessionKey, unionId
        //获取登录凭证（code）进而换取用户登录态信息
        $code = $_POST['code'];
        $openArray = $this->getOpenID($code);

        //生成3rd_session
        $sessionKey = createNonceStr();

        //以3rd_session为key，openid+session_id为值写入session
        $session = D('session')->findSessionByOpenID($openArray['openid']);
        if(!$session) {
            $sessionArray = array(
                'session3key' => $sessionKey,
                'openid' => $openArray['openid'],
                'session_key' => $openArray['session_key'],
                'unionid' => $openArray['unionid'],
                'create_time' => time(),
            );
            $saveSession = D('session')->addSession($sessionArray);
        }else {
            $sessionArray = array(
                'session3key' => $sessionKey,
                'session_key' => $openArray['session_key'],
                'unionid' => $openArray['unionid'],
                'create_time' => time(),
            );
            $updateSession = D('session')->updateSession($openArray['openid'],$sessionArray);
        }

        $user = D('user')->getUserByOpenID($openArray['openid']);
        if(!$user) {
            $openArray['gmt_create'] = date('Y-m-d H:i:s',time());
            $openArray['nickname'] = '访客';
            $openArray['avatarurl'] = C('DEFAULT_AVATAR_URL');
            $saveUserInfo = D('User')->saveUserInfo($openArray);
        }

        $this->ajaxReturn($sessionKey);
    }


    public function checkSession() {
        if($_POST['sessionKey']) {
            $sessionKey = json_decode($_POST['sessionKey']);
            if(!$sessionKey) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session3key不存在'
                ));
            }
            $session = D('session')->findSessionBySession3key($sessionKey);
            if($session) {
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => 'session3key存在'
                ));
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session3key不存在'
                ));
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'sessionkey不存在'
            ));
        }

    }

    public function getOpenID($code = '') {
        if(!$code || $code == '') {
            return 0;
        }
        $apiUrl = 'https://api.weixin.qq.com/sns/jscode2session?appid='.C('WECHAT_SMALL_APPLICATION')['APPID'].'&secret='.C('WECHAT_SMALL_APPLICATION')['APPSECRET'].'&js_code='.$code.'&grant_type=authorization_code';
        return json_decode(curlGet($apiUrl),true);
    }

    public function saveUserInfo() {

        if($_POST['userInfo'] && $_POST['sessionKey']) {
            $sessionKey = json_decode($_POST['sessionKey']);
            $session = D('session')->findSessionBySession3key($sessionKey);
            if($session) {
                $openid = $session['openid'];
                $user = D('user')->getUserByOpenID($openid);
                if($user) {
                    //获取post的用户信息
                    $userInfo = json_decode($_POST['userInfo'],true);
                    $userInfo['gmt_modify'] = date('Y-m-d H:i:s',time());
                    //写入数据库
                    $updateUserInfo = D('User')->updateUserInfo($openid,$userInfo);
                    if($updateUserInfo) {
                        $this->ajaxReturn(array(
                            'status' => 1,
                            'message' => '用户信息新增成功！'
                        ));
                    }else {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '用户信息新增失败！'
                        ));
                    }
                }else {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '用户信息openid不存在！'
                    ));
                }

            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session信息不存在！'
                ));
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'POST信息不存在！'
            ));
        }
    }

    public function checkPermissions() {
        //$session3key = I('post.sessionKey','','sting,trim');
        $session3key = I('post.sessionKey','','');

        $companyInfo = array(
            'title' => '无锡万盈鹏辉企业管理',
            'subtitle' => 'WUXI WANYING PENGHUI INVESTMENT CO. LTD',
            'content' => '无锡市万盈鹏辉企业管理承接空放、零用贷、红包贷打卡贷、车子一抵二抵、配偶车、亲属车。本地人（江阴，宜兴）只要一张身份证就可以借款。外地人有房，有车，家人在无锡，满足任一个条件就可借。',
            'location' => '无锡市梁溪区华光大厦6楼',
            'date' => '周一 至 周六 9:00—18:00',
            'phone' => '18261525818  15895351351',
        );

        if(!$session3key) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'session3key不存在，无权限！',
                'companyInfo' => $companyInfo,
            ));
        }

        try {



            $session = D('session')->findSessionBySession3key($session3key);
            if(!$session) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session不存在，无权限！',
                    'companyInfo' => $companyInfo,
                ));
            }
            $openid = $session['openid'];
            $user = D('user')->getUserByOpenID($openid);
            if(!$user) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '用户不存在，无权限！',
                    'companyInfo' => $companyInfo,
                ));
            }

            if($user['status'] == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '无权限！',
                    'companyInfo' => $companyInfo,
                ));
            }


            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '有权限！',
                'companyInfo' => $companyInfo,
            ));
        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    public function getUserInfo() {
        $session3key = I('post.sessionKey','','');
        $functionArray = array('逾期客户查询','借款信息查询','内部客户信息查询','交易记录信息查询');
        if(!$session3key) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'session3key不存在，无权限！',
                'functionArray' => $functionArray,
            ));
        }
        try {
            $session = D('session')->findSessionBySession3key($session3key);
            if(!$session) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session不存在，无权限！',
                    'functionArray' => $functionArray,
                ));
            }
            $openid = $session['openid'];
            $user = D('user')->getUserByOpenID($openid);
            if(!$user) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '用户不存在，无权限！',
                    'functionArray' => $functionArray,
                ));
            }
            if($user['status'] == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '无权限！',
                ));
            }
            $company = D('Company')->getCompanyByID($user['company_id']);
            if($user['depart_id'] == 1 || $user['depart_id'] == 7) {
                $user['company_name'] = '万盈鹏辉企业管理';
            }else {

                $user['company_name'] = $company['name'];
            }


            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '有权限！',
                'userInfo' => $user,
                'functionArray' => $functionArray,
            ));
        }catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }
}