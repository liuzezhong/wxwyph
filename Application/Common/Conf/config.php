<?php
return array(
	//'配置项'=>'配置值'
    'URL_MODEL' => 0,    //PATHINFO模式为1，0为默认模式
    'LOAD_EXT_CONFIG' => 'db',
    'DEFAULT_CONTROLLER' => 'Loan',
    'DEFAULT_AVATAR_URL' => 'http://imgbj.xianshikeji.com/%E9%BB%98%E8%AE%A4%E5%A4%B4%E5%83%8F.png',
    'WECHAT_SMALL_APPLICATION' => array(
        'APPID' => 'wx0454c08974dc7d66',
        'APPSECRET' => '29cce1874999158f55bc11ecc21423b6',
    ),
    'ALIYUN_MESSAGE_CONFIG' => array(
        'product' => 'Dysmsapi',
        'domain' => 'dysmsapi.aliyuncs.com',
        'accessKeyId' => 'LTAIrrafCoyqB5eB',
        'accessKeySecret' => '3vWxEdJeDaUIx5T8nEQB5J4o1FqKjn',
        'region' => 'cn-hangzhou',
        'endPointName' => 'cn-hangzhou',
        'templateCode' => array(
            'loan_template' => 'SMS_120130712',
        ),
    ),
    'UPLOAD_SITEIMG_QINIU' => array (
        'maxSize' => 0,
        'rootPath' => './',
        'saveName' => array('uniqid',''),
        'savePath' => '',
        'subName'    => array('date','Ymd'),
        'driver' => 'Qiniu',
        //'exts'   =>  array('jpg', 'gif', 'png', 'jpeg'),
        'exts'   =>  array(),
        'autoSub' => true,
        'driverConfig' => array (
            'secretKey' => 'AnZn9ccvgViG88qjuTmkz14O9Ql5xO-0dKUBTHGw', //七牛sk
            'accessKey' => 'VrKJPcQnEVCE2uav-EGcIOkrCitcFlvsT2Gb94Ka',  //七牛ak
            'domain' => 'imgbj.xianshikeji.com', //域名
            'bucket' => 'baoju', //空间名称
        )
    ),
    'QINIU_DOMAIN_URL' => 'http://imgbj.xianshikeji.com/',
    'QINIU_SUOTU_XIANGCE' => 'imageView2/1/w/200/h/200/interlace/1/q/75|imageslim',
    'QINIU_SUOTU_ZHAOPIAN' => 'imageView2/1/w/400/h/440/q/75|imageslim',
    'QINIU_SUOTU_WEIXIN' => 'imageView2/0/q/75|watermark/2/text/5LiH6LWi6bmP6L6J5YaF6YOo5a6i5oi36LWE5paZ77yM5Lil56aB56eB6Ieq5Lyg5pKt/font/5b6u6L2v6ZuF6buR/fontsize/700/fill/I0ZGRkZGRg==/dissolve/95/gravity/Center/dx/10/dy/10',

);