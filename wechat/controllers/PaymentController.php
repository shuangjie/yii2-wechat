<?php
namespace wechat\controllers;
use common\components\ResponseComponent;
use common\extensions\wechatpay\lib\ResultCollection;
use common\extensions\wechatpay\WechatPay;
use common\models\Auth;
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
use wechat\models\payment\UnifiedOrderForm;
use yii\base\ErrorException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2018/4/23
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

        //判断设备环境
        //判断交易类型和支付方式
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
     * 用户砸蛋，请求该接口
     * 先检查是否关注公众号，没有的话生成二维码
     * @param int $emotion_id  live id ， 名字待定
     * @return array
     */
    public function actionRefund($emotion_id){
        //live 实例
        /**
         * @var Live $live
         */
        $live = Live::findOne($emotion_id);
        if(is_null($live)){
            return ResponseComponent::failed(404, "ID does not exist");
        }
        //当前用户
        /**
         * @var User $user;
         */
        $user = Yii::$app->user->identity;
        $user_id = Yii::$app->user->id;
//        echo $user_id;
        //查找票据
        $ticket = LiveTicket::findOne([
            'live_id' => $emotion_id,
            'user_id' => $user_id,
            'status' => LiveTicket::STATUS_VALID,
        ]);
        if(is_null($ticket)){
            return ResponseComponent::failed(404, "Ticket does not exist");
        }
        $refundService = new RefundService();
        $refund = $refundService->refundLiveTicketAuto($ticket->order_id);
//        print_r($refund);exit;

        $result = [];
        $result['refund_code'] = $refund->status == OrderRefund::STATUS_COMPLETED ? 1 : 0;
        $result['subscribe'] = UserService::isSubscribeWechatAccount($user) ? 1 : 0;
        //如果状态为退款中，那么说明没有关注公众号
//        if($refund->status == OrderRefund::STATUS_WAIT){
            //返回二维码图片
            $result['qrcode_url'] = "/static/img/refund-qr.jpg";  //不管关没关注，都给二维码
//        }
        return ResponseComponent::success($result);
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