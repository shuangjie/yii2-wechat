<?php
namespace wechat\modules\test\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;

/**
 * TEST controller
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 14:24
 */
class TestController extends Controller{

    public function actionIndex(){ //不需要登陆了
        Yii::$app->response->format =  Response::FORMAT_JSON;
        echo "hello";
    }



}