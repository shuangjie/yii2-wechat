<?php
namespace wechat\modules\wechat\controllers;

use common\extensions\wechat\lib\BizDataCrypt;
use common\extensions\wechat\Wechat;
use Yii;
use wechat\controllers\FvBaseController;
use yii\rest\Controller;
/**
 * 默认controller
 *
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 19:51
 */

class WechatController extends Controller{

    public function actionIndex(){ //不需要登陆了
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