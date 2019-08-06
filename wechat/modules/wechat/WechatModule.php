<?php
namespace wechat\modules\wechat;

use yii\base\Module;

class WechatModule extends Module
{
    public $controllerNamespace = 'wechat\modules\wechat\controllers';
    public $defaultRoute = 'wechat';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
