<?php
namespace wechat\modules\wechat\controllers;

use common\components\ResponseComponent;
use common\extensions\wechat\Wechat;
use Yii;
use yii\base\Response;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;

/**
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 19:55
 */
class QueryController extends Controller{

    public function actionIndex(){
    }

    public function actionSignPackage($url = ''){
        /**
         * @var Wechat $wechat
         */
        $wechat = \Yii::$app->wechat;
        $signPackage = $wechat->getSignPackage($url);
        return  ResponseComponent::success($signPackage);
    }

    public function actionWxKey($_sign){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        $sign = ''; //sign
        if($_sign != $sign){
            throw new ForbiddenHttpException('invalid sign');
        }
        return \Yii::$app->wechat->accessToken;
    }



}