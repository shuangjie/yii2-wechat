<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-weapp',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'weapp\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-weapp',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
//            'identityCookie' => ['name' => '_identity', 'httpOnly' => true, 'domain' => $params['domain.session']],
//            'loginUrl' => 'http://'.$params['domain.mobile'].'/login',
        ],
        'session' => [
            'name' => '_sid',
//            'class' => 'yii\redis\Session',
//            // this is the name of the session cookie used for login on the frontend
//            'name' => '_sid',
//            'keyPrefix' => '',
//            'redis' => 'redis',
////            'cookieParams' => ['domain' => $params['domain.session'], 'httpOnly' => true, 'lifetime' => 0],
//            'timeout' => 3600,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
    'modules' => [
        'weapp' => [
            'class' => 'weapp\modules\weapp\WeappModule',
        ],
    ],
    'name' => 'xxx',
    'language' => 'zh-CN',
    'params' => $params,
];
