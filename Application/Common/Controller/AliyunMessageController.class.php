<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2018/1/5
 * Time: 11:20
 */

namespace Common\Controller;
vendor("aliMessage.vendor.autoload");
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;

Config::load();

class AliyunMessageController
{
    static $acsClient = null;
    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public function getAcsClient() {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = C('ALIYUN_MESSAGE_CONFIG')['product'];
        //产品域名,开发者无需替换
        $domain = C('ALIYUN_MESSAGE_CONFIG')['domain'];
        // AccessKeyId
        $accessKeyId = C('ALIYUN_MESSAGE_CONFIG')['accessKeyId'];
        // AccessKeySecret
        $accessKeySecret = C('ALIYUN_MESSAGE_CONFIG')['accessKeySecret'];
        // 暂时不支持多Region
        $region = C('ALIYUN_MESSAGE_CONFIG')['region'];
        // 服务结点
        $endPointName = C('ALIYUN_MESSAGE_CONFIG')['endPointName'];

        if(static::$acsClient == null) {
            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     * @return array|mixed|object|\SimpleXMLElement
     */
    public function sendSms($smsData = array()) {
        if(!$smsData)
            return -1;
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        // 必填，设置短信接收号码
        $request->setPhoneNumbers($smsData['phoneNumbers']);
        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($smsData['signName']);
        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($smsData['templateCode']);
        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam($smsData['templateParam']);
        // 可选，设置流水号
        $request->setOutId($smsData['outID']);
        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        //$request->setSmsUpExtendCode("1234567");
        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);
        return $acsResponse;
    }

    /**
     * 短信发送记录查询
     * @return stdClass
     */
    public static function querySendDetails() {

        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();
        // 必填，短信接收号码
        $request->setPhoneNumber("18852983610");
        // 必填，短信发送日期，格式Ymd，支持近30天记录查询
        $request->setSendDate("20170525");
        // 必填，分页大小
        $request->setPageSize(10);
        // 必填，当前页码
        $request->setCurrentPage(1);
        // 选填，短信发送流水号
        $request->setBizId("yourBizId");
        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);
        return $acsResponse;
    }
}