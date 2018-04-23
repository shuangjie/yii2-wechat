<?php
namespace wechat\modules\test;
use yii\base\Module;
class TestModule extends Module
{
    public $controllerNamespace = 'wechat\modules\test\controllers';
    public $defaultRoute = 'test';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
