<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],

        //redis
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'xxx.redis.rds.aliyuncs.com',
            'password' => '',
            'port' => 6379,
            'database' => 0, //默认
        ],
        
        //微信公众号
        'wechat' => [
            'class' => 'common\extensions\wechat\Wechat',
            'token' => '',
            'encoding_aes_key' => '',
            'appid' => '',
            'appsecret' => '',
            'requestHandler' => 'common\services\wechat\RequestHandler',
        ],

        //微信小程序
        'weapp' => [
            'class' => 'common\extensions\weapp\Weapp',
            'token' => '',
            'appid' => '',
            'appsecret' => '',
        ],

        //微信redis
        'redis-wechat' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'xxx.redis.rds.aliyuncs.com',
            'password' => '',
            'port' => 6379,
            'database' => 2, //微信公众号
        ],

        //微信redis
        'redis-weapp' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'xxx.redis.rds.aliyuncs.com',
            'password' => '',
            'port' => 6379,
            'database' => 3, //微信小程序
        ],

        //公众号 -- 微信支付
        'wechatPay' => [
            'class' => '\common\extensions\wechatpay\WechatPay',
            'appid' => '',
            'mch_id' => '',
            'key' => '',
            'notify_url' => 'https://wechat.xxx.com/wechat/pay/notify',
        ],
        
    ],
];