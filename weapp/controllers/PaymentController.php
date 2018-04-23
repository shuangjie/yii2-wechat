<?php
namespace weapp\controllers;
use common\components\ResponseComponent;
use common\extensions\wechatpay\lib\ResultCollection;
use common\extensions\wechatpay\WechatPay;
use common\models\Auth;
use common\models\FormId;
use common\models\Live;
use common\models\LiveTicket;
use common\models\Order;
use common\models\OrderRefund;
use common\models\Recharge;
use common\models\User;
use common\helpers\DeviceDetect;
use common\services\finance\RefundService;
use common\services\user\UserService;
use Detection\MobileDetect;
use Yii;
use weapp\models\payment\UnifiedOrderForm;
use yii\base\ErrorException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2018/4/28
 * Time: 17:37
 */
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
        //重命名
        $params['amount'] = floatval ($params['amount']) * 100;  // 将"1.88"字符串 转为 1.88 数字,再乘100
        $params['task_id'] = $params['id'];
        $source = Auth::getOpenidByUserId(Yii::$app->user->id,'weapp');
        $params['openid'] = $source['source_id'];


        $params['pay_type'] = UnifiedOrderForm::PAY_TYPE_WECHATPAY;
        $params['trade_type'] = UnifiedOrderForm::TRADE_TYPE_WEAPP;
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
        //保存form_id
        if ($unifiedOrderResult['prepay_id'] && $unifiedOrderResult['prepay_id'] != '') {
            (new FormId())->saveFormId($order->user_id,$unifiedOrderResult['prepay_id'],'pay');
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
        $return['payment_packet'] = $return['trade_type'] == UnifiedOrderForm::TRADE_TYPE_WEAPP ? $wxPay->getJsApiParameters($unifiedOrderResult) : ['code_url' => $unifiedOrderResult['code_url']];
        //注意里面 prepay_id 要保存到form id表
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