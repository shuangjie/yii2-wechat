<?php
namespace weapp\modules\weapp\controllers;

use common\extensions\weapp\lib\BizDataCrypt;
use common\extensions\weapp\User;
use common\extensions\weapp\weapp;
use Yii;
use yii\rest\Controller;

class WeappController extends Controller{

    public function actionIndex(){
        /**
         * @var Weapp $weapp
         */
        $wxs = Yii::$app->weapp;
        $wxs->run();

    }

    public function actionSignPackage($url = ''){
        /**
         * @var Weapp $weapp
         */
        $weapp = Yii::$app->weapp;
        return $weapp->getSignPackage($url);
    }

}