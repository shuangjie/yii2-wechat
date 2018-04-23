<?php
namespace wechat\modules\wechat\controllers;

use common\extensions\wechatpay\lib\ResultCollection;
use common\extensions\wechatpay\WechatPay;
use common\services\finance\RechargeService;
use yii\web\Response;
use yii\rest\Controller;
use Yii;
/**
 * 接收微信支付结果回调
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 19:52
 */
class PayController extends Controller {

    /**
     * 微信支付通知
     */
    public function actionNotify(){
        Yii::$app->response->format = Response::FORMAT_XML;
        Yii::$app->response->formatters = [
            Response::FORMAT_XML => [
                'class' => 'yii\web\XmlResponseFormatter',
                'rootTag' => 'xml',
            ]
        ];

        $msg = "OK";

        Yii::error($xml =  Yii::$app->request->rawBody, 'wechat-pay');

        /**
         * @var WechatPay $wxPay
         */
        $wxPay = Yii::$app->wechatPay;
        $wxPayCli =  $wxPay->getWechatPayApiCli();
        $result = $wxPayCli->notify([$this, 'notifyCallBack'], $msg);
        if($result == false){
            $wxPayCli->values->set('return_code', 'FAIL');
            $wxPayCli->values->set('return_msg', $msg);
        }else{
            $wxPayCli->values->set('return_code', 'SUCCESS');
            $wxPayCli->values->set('return_msg', "OK");
            $wxPayCli->values->setSign();
        }
        return $wxPayCli->values;
    }

    /**
     * @param ResultCollection $result
     * @return boolean
     */
    public function notifyCallback($result){
        $msg = "OK";
        return $result = $this->notifyProcess($result, $msg);
    }

    /**
     * 支付结果处理
     * @param ResultCollection $result
     * @param string $msg
     * @return boolean 处理成功返回true，失败返回false
     */
    public function notifyProcess($result, &$msg){
        if(!$result->get('transaction_id')){
            $msg = "参数错误";
            return false;
        }
        if(!$this->wechatOrderExists($result->get('transaction_id'))){
            $msg = "订单查询失败";
            return false;
        }
        //Do Something ...
        
        return true;
    }

    /**
     * 查询微信订单是否存在
     * @param string $transaction_id
     * @return boolean
     */
    public function wechatOrderExists($transaction_id){
        /**
         * @var WechatPay $wxPay
         */
        $wxPay = Yii::$app->wechatPay;
        $wxPayCli =  $wxPay->getWechatPayApiCli();
        $wxPayCli->values->set('transaction_id', $transaction_id);
        $result = $wxPayCli->orderQuery();
        if($result->get('return_code') == 'SUCCESS' && $result->get('result_code') == 'SUCCESS'){
            return true;
        }
        return false;
    }


}