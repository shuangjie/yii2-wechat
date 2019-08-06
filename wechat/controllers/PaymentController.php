<?php
namespace wechat\controllers;
use common\components\ResponseComponent;
use common\extensions\wechatpay\lib\ResultCollection;
use common\extensions\wechatpay\WechatPay;
use common\models\Auth;
use common\models\Order;
use common\models\Recharge;
use common\models\User;
use common\helpers\DeviceDetect;
use Detection\MobileDetect;
use Yii;
use wechat\models\payment\UnifiedOrderForm;
use yii\base\ErrorException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class PaymentController extends AuthController{


    /**
     * 统一下单
     * @type $params [['amount'=>integer ]]
     * @throws mixed
     * @return mixed
     */
    public function actionUnifiedOrder(){

        $params = Yii::$app->request->rawBody;
        $params = json_decode($params, true);

        //识别type
        $type = isset($params['type']) ? $params['type'] : null;
        if(!isset(UnifiedOrderForm::$type_config[$type])){
            //报错
            throw new BadRequestHttpException("invalid params:type", 1);
        }

        //判断设备环境、交易类型、支付方式

        !isset($params['pay_type']) && $params['pay_type'] = UnifiedOrderForm::PAY_TYPE_WECHATPAY;
        if($params['pay_type'] == UnifiedOrderForm::PAY_TYPE_WECHATPAY && !isset($params['trade_type'])){
            /* @var DeviceDetect|MobileDetect $deviceDetect */
            $deviceDetect = Yii::createObject(DeviceDetect::className());
            if($deviceDetect->isMobile() && $deviceDetect->isWechat()){
                $params['trade_type'] = UnifiedOrderForm::TRADE_TYPE_JSAPI;
                $params['openid'] = $this->getWechatOpenid();
            }else{
                $params['trade_type'] = UnifiedOrderForm::TRADE_TYPE_NATIVE;
            }
        }

        $unifiedOrderForm = new UnifiedOrderForm(['scenario' => $params['type']]);

        $unifiedOrderForm->load($params, '');
        if(!$unifiedOrderForm->validate()){
            throw new BadRequestHttpException("invalid params:".print_r($unifiedOrderForm->errors, true), 1);
        }
        $res = $unifiedOrderForm->create();
        /**
         * @var $order Order
         */
        $order = $res['order'];
        /* @var $unifiedOrderResult ResultCollection  */
        $unifiedOrderResult = $res['unifiedOrderResult'];
        if(!$unifiedOrderResult){
            Yii::error("微信下单失败", 'payment');
            throw new ErrorException("系统错误", 1);
        }
        /**
         * @var WechatPay $wxPay
         */
        $wxPay = Yii::$app->wechatPay;
        $return = [];
        $return['order_no'] = $order->order_no;
        $return['user_id'] = $order->user_id;
        $return['amount'] = $order->amount;
        $return['trade_type'] = $unifiedOrderForm->trade_type;
        $return['payment_packet'] = $return['trade_type'] == UnifiedOrderForm::TRADE_TYPE_JSAPI ? $wxPay->getJsApiParameters($unifiedOrderResult) : ['code_url' => $unifiedOrderResult['code_url']];
        return ResponseComponent::success($return);
    }


    /**
     * 查询订单
     * @param $order_no string 订单号
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionOrderStatus($order_no){
        $order = Order::findOne(['order_no' => $order_no, 'user_id' => Yii::$app->user->id]);
        if(is_null($order)){
            throw new NotFoundHttpException("order not found", 1);
        }
        $ret = [];
        if($order->pay_status == Order::PAY_STATUS_YES){
            $ret['order_no'] = $order->order_no;
            $ret['pay_status'] = $order->pay_status;
        }
        return ResponseComponent::buildResponse(ResponseComponent::CODE_ACTIVATED, 'ok', $ret);
    }

    /**
     * 退款接口
     * @return array
     */
    public function actionRefund($emotion_id){
        
    }


    /**
     * 根据当前用户获取微信openid
     * @return string|boolean openid
     */
    private function getWechatOpenid(){
        /* @var User $user */
        $user = Yii::$app->user->identity;
        $user->auths;
        /* @var Auth $auth */
        $auth = $user->wechatAuth;
        if(is_null($auth)){
            return false;
        }
        return $auth->source_id;
    }






}