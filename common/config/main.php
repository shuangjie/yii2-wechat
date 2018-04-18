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
        //统计专用redis
        'redis-stat' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'xxx.redis.rds.aliyuncs.com',
            'password' => '',
            'port' => 6379,
            'database' => 1,
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

        'cache-redis-wechat' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis-wechat',
        ],

        //微信redis
        'redis-weapp' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'xxx.redis.rds.aliyuncs.com',
            'password' => '',
            'port' => 6379,
            'database' => 3, //微信小程序
        ],

        'cache-redis-weapp' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis-weapp',
        ],


        //公众号 -- 微信支付
        'wechatPay' => [
            'class' => '\common\extensions\wechatpay\WechatPay',
            'appid' => '',
            'mch_id' => '',
            'key' => '',
            'notify_url' => 'https://wechat.xxx.com/wechat/pay/notify',
        ],

        //小程序 -- 微信支付
        'weappPay' => [
            'class' => '\common\extensions\weapppay\WeappPay',
            'appid' => '',
            'mch_id' => '',
            'key' => '',
            'notify_url' => 'https://weapp.xxx.com/weapp/pay/notify',
        ],

        //阿里云oss
        'aliyunOss' => [
            'class' => 'common\extensions\aliyunOss\AliyunOssExtension',
            'accessKeyId' => '',
            'accessKeySecret' => '',
            'endpoint' => '',
        ],
        
    ],
];