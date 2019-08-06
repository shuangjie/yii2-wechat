<?php
namespace wechat\modules\wechat\controllers;

use common\extensions\wechat\lib\BizDataCrypt;
use common\extensions\wechat\Wechat;
use Yii;
use wechat\controllers\FvBaseController;
use yii\rest\Controller;

class WechatController extends Controller{

    public function actionIndex(){
        /**
         * @var Wechat $wechat
         */
        $wechat = Yii::$app->wechat;
        $wechat->run();

    }

    public function actionSignPackage($url = ''){
        /**
         * @var Wechat $wechat
         */
        $wechat = Yii::$app->wechat;
        return $wechat->getSignPackage($url);
    }

}