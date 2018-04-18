<?php
namespace common\extensions\wechat;
use Yii;
/**
 * 请求分发
 * User: DoubleJack
 * Date: 2018/4/18
 * Time: 18:01
 */
class RequestDispatch{

    /**
     * 判断此次请求是否为验证请求
     * @return boolean
     */
    public static function isValid() {
        return !is_null(Yii::$app->request->get('echostr'));
    }

    /**
     * 判断验证请求的签名信息是否正确
     * @param  string $token 验证信息
     * @return boolean
     */
    public static function validateSignature($token = '') {
        $signature = Yii::$app->request->get('signature');
        $timestamp = Yii::$app->request->get('timestamp');
        $nonce = Yii::$app->request->get('nonce');
        $signatureArray = [$token, $timestamp, $nonce];
        sort($signatureArray, SORT_STRING);
        return sha1(implode($signatureArray)) == $signature;
    }

}


