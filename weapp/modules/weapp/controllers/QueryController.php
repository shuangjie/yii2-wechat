<?php
namespace weapp\modules\weapp\controllers;

use common\components\ResponseComponent;
use common\extensions\weapp\Weapp;
use Yii;
use yii\base\Response;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;

/**
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 19:22
 */
class QueryController extends Controller{

    public function actionIndex(){
    }

    public function actionSignPackage($url = ''){
        /**
         * @var Weapp $weapp
         */
        $weapp = \Yii::$app->weapp;
        $signPackage = $weapp->getSignPackage($url);
        return  ResponseComponent::success($signPackage);
    }

    public function actionWxKey($_sign){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        $sign = ''; //sign
        if($_sign != $sign){
            throw new ForbiddenHttpException('invalid sign');
        }
        return \Yii::$app->weapp->accessToken;
    }



}