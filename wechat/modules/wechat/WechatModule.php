<?php
/**
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 19:50
 */
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
