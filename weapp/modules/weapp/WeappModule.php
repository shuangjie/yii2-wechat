<?php
namespace weapp\modules\weapp;
use yii\base\Module;
class WeappModule extends Module
{
    public $controllerNamespace = 'weapp\modules\weapp\controllers';
    public $defaultRoute = 'weapp';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
