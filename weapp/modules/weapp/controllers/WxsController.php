<?php
namespace weapp\modules\weapp\controllers;

use common\extensions\weapp\lib\BizDataCrypt;
use common\extensions\weapp\User;
use common\extensions\weapp\weapp;
use Yii;
use yii\rest\Controller;
/**
 * 默认controller
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2017/11/18
 * Time: 14:24
 */
class WxsController extends Controller{

    public function actionIndex(){ //不需要登陆了
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

    //小程序登录即注册
    public function actionSignupByWxs($code,$encryptedData,$iv){
        /**
         * @var Weapp $weapp
         */
        $weapp = Yii::$app->weapp;
        //$wxs->getOpenIdAndSessionKey($code);

        $errCode =  $weapp->getBizDataCrypt($code)->decryptData($encryptedData,$iv,$data);

        if ($errCode == 0) {
            $userInfo = json_decode($data,true);
            $user = User::SignUpByWxsOAuth($userInfo);
            unset($userInfo['watermark']);
            return $user ? $userInfo : $user;
        } else {
            return $errCode;
        }

    }

    public function actionTest($code){
        /**
         * @var Weapp $weapp
         */
        $weapp = Yii::$app->weapp;
        return $weapp->getOAuth()->getAccessTokenAndOpenId($code);
    }



}