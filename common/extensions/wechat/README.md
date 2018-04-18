Yii2-wechat component
===============================
 微信公众号组件
 包括 api sdk， user， cache
 
 cache主要用来缓存access token，js ticket 这些数据。
 如果不配置cache 默认使用应用组件'cache'，即 Yii::$app->cache;
 yii的cache默认是文件缓存，如果是多台服务器使用，就不建议用文件缓存。建议用redis，memcached等

 Usage:

 1、component配置
 ```php
 'components' =>[
      'wechat' => [
          'class' = 'common\extensions\wechat\Wechat'
          'token' => 'TOKEN'
          'encoding_aes_key' => 'ENCODING AES KEY',
          'appid' => 'APP ID',
          'appsecret' => 'APP SECRET',
          'cache' => [
              'class' => ' yii\redis\Connection’,
              'hostname' => 'localhost',
              'port' => 6379,
              'database' => 0,
          ]
      ],
 ]
 //或者如果你已经配置了相关的缓存应用组件，也可以这样写：
 'components' =>[
      'wechat' => [
          'class' = 'common\extensions\wechat\Wechat'
          'token' => 'TOKEN'
          'encoding_aes_key' => 'ENCODING AES KEY',
          'appid' => 'APP ID',
          'appsecret' => 'APP SECRET',
          'cache' => 'cache-redis-wechat'
      ],
      'cache-redis-wechat' => [
          'hostname' => 'localhost',
           'port' => 6379,
           'database' => 0,
      ]
 ]

 $wechat = Yii::$app->wechat;
 //获取access token
 $wechat->getAccessToken();
 ```


 2、直接创建对象
 ```php
 use common\extensions\wechat\Wechat;

 $config = [
      'class' = Wechat::className(),
      'token' => 'TOKEN'
       'encoding_aes_key' => 'ENCODING AES KEY',
      'appid' => 'APP ID',
      'appsecret' => 'APP SECRET',
      'cache' => 'cache-redis-wechat'
 ];
 $wechat = Yii::createObject($config);
 //获取access token
 $wechat->getAccessToken();
 ```