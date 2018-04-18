<?php
namespace common\extensions\wechatpay\lib;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;

/**
 * @property DataCollection $values
 * @property string $appid
 * @property string $mch_id
 * @property string $key
 * @property string $notify_url 回调url
 */
class WechatPayApi extends BaseClient {


    private $_notify_url;

    public function setNotify_url($notify_url){
        $this->_notify_url = $notify_url;
    }
    public function getNotify_url(){
        return $this->_notify_url;
    }


    /**
     * 统一下单接口
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1
     * 应用场景：除被扫支付场景以外，商户系统先调用该接口在微信支付服务后台生成预支付交易单，返回正确的预支付交易回话标识后再按扫码、JSAPI、NATIVE、APP等不同场景生成交易串调起支付。
     * 不需要证书
     * out_trade_no、body、total_fee、trade_type必填
     * appid、mchid、spbill_create_ip、nonce_str 不需要填
     * @throws InvalidParamException
     * @return ResultCollection|boolean  出错返回false
     * */
    public function unifiedOrder(){
        $this->checkUnifiedOrder();
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $values = $this->getValues();
        //异步通知接口
        if(!$values->has('notify_url')){
            $values->set('notify_url', $this->_notify_url);
        }
        //终端IP
        $values->set('spbill_create_ip', Yii::$app->request->userIP);
        $values->setSign();
        $xml = $values->toXml();

        $response = $this->postXmlCurl($xml, $url, false, 6);
        if($this->getResultValues()->fromXml($response) && $this->getResultValues()->validate()){
            return $this->getResultValues();
        }else{
            return false;
        }
    }

    /**
     * 统一下单接口数据合法性
     * 这里可以独立成form表单（model），rule
     * @throws InvalidParamException
     */
    private function checkUnifiedOrder(){
        $value = $this->getValues();
        //检测必填参数
        if(!$value->has('out_trade_no')){
            throw new InvalidParamException("缺少统一支付接口必填参数out_trade_no！");
        }
        if(!$value->has('body')){
            throw new InvalidParamException("缺少统一支付接口必填参数body！");
        }
        if(!$value->has('total_fee')){
            throw new InvalidParamException("缺少统一支付接口必填参数total_fee！");
        }
        if(!$value->has('trade_type')){
            throw new InvalidParamException("缺少统一支付接口必填参数trade_type！");
        }
        if($value->get('trade_type') == 'JSAPI' && !$value->has('openid')){
            throw new InvalidParamException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！");
        }
        if($value->get('trade_type') == 'NATIVE' && !$value->has('product_id')){
            throw new InvalidParamException("统一支付接口中，缺少必填参数product_id！trade_type为NATIVE时，product_id为必填参数！");
        }
    }



    /**
     * 查询订单
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_2
     * 该接口提供所有微信支付订单的查询，商户可以通过查询订单接口主动查询订单状态，完成下一步的业务逻辑。
     *
     * 需要调用查询接口的情况：
     *    ◆ 当商户后台、网络、服务器等出现异常，商户系统最终未接收到支付通知；
     *    ◆ 调用支付接口后，返回系统错误或未知交易状态情况；
     *    ◆ 调用被扫支付API，返回USERPAYING的状态；
     *    ◆ 调用关单或撤销接口API之前，需确认支付状态；
     *
     * out_trade_no、transaction_id至少填一个
     * @throws InvalidParamException
     * @return ResultCollection|boolean
     */
    public function orderQuery()
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $values = $this->getValues();
        //检测必填参数
        if(!$values->has('out_trade_no') && !$values->has('transaction_id')) {
            throw new InvalidParamException("订单查询接口中，out_trade_no、transaction_id至少填一个！");
        }
        $values->setSign();//签名
        $xml = $values->toXml();
        $response = $this->postXmlCurl($xml, $url, false, 6);
        if($this->getResultValues()->fromXml($response) && $this->getResultValues()->validate()){
            return $this->getResultValues();
        }else{
            return false;
        }
    }

    /**
     * 关闭订单
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_3
     * 以下情况需要调用关单接口：商户订单支付失败需要生成新单号重新发起支付，要对原订单号调用关单，避免重复支付；系统下单后，用户支付超时，系统退出不再受理，避免用户继续，请调用关单接口。
     * 注意：订单生成后不能马上调用关单接口，最短调用时间间隔为5分钟。
     *
     * out_trade_no必填
     * @throws InvalidParamException
     * @return bool|ResultCollection
     */
    public function closeOrder()
    {
        $url = "https://api.mch.weixin.qq.com/pay/closeorder";
        $values = $this->getValues();
        //检测必填参数
        if(!$values->has('out_trade_no')){
            throw new InvalidParamException("订单查询接口中，out_trade_no、transaction_id至少填一个！");
        }

        $values->setSign();//签名
        $xml = $values->toXml();
        $response = $this->postXmlCurl($xml, $url, false, 6);
        if($this->getResultValues()->fromXml($response) && $this->getResultValues()->validate()){
            return $this->getResultValues();
        }else{
            return false;
        }
    }

    /**
     * 申请退款
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_4
     * 当交易发生之后一段时间内，由于买家或者卖家的原因需要退款时，卖家可以通过退款接口将支付款退还给买家，微信支付将在收到退款请求并且验证成功之后，按照退款规则将支付款按原路退到买家帐号上。
     * 注意：
     *    1、交易时间超过一年的订单无法提交退款；
     *    2、微信支付退款支持单笔交易分多次退款，多次退款需要提交原支付订单的商户订单号和设置不同的退款单号。总退款金额不能超过用户实际支付金额。 一笔退款失败后重新提交，请不要更换退款单号，请使用原商户退款单号。
     * 需要证书
     * out_trade_no、transaction_id至少填一个且
     * out_refund_no、total_fee、refund_fee、op_user_id为必填参数
     * @throws InvalidParamException
     * @return bool|ResultCollection
     */
    public function refund()
    {
        $this->checkRefund();
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $values = $this->getValues();

        $values->setSign();//签名
        $xml = $values->toXml();
        $response = $this->postXmlCurl($xml, $url, true, 6);
        if($this->getResultValues()->fromXml($response) && $this->getResultValues()->validate()){
            return $this->getResultValues();
        }else{
            return false;
        }
    }

    /**
     * 检查退款接口请求数据合法性
     * out_trade_no、transaction_id至少填一个且
     * out_refund_no、total_fee、refund_fee、op_user_id为必填参数
     * @throws InvalidParamException
     */
    private function checkRefund(){
        //检测必填参数
        $values = $this->getValues();
        if(!$values->has('out_trade_no') && !$values->has('transaction_id')){
            throw new InvalidParamException("退款申请接口中，out_trade_no、transaction_id至少填一个！");
        }
        if(!$values->has('out_refund_no')){
            throw new InvalidParamException("退款申请接口中，缺少必填参数out_refund_no！");
        }
        if(!$values->has('total_fee')){
            throw new InvalidParamException("退款申请接口中，缺少必填参数total_fee！");
        }
        if(!$values->has('op_user_id')){
            throw new InvalidParamException("退款申请接口中，缺少必填参数op_user_id！");
        }
    }

    /**
     * 查询退款
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_5
     * 提交退款申请后，通过调用该接口查询退款状态。退款有一定延时，用零钱支付的退款20分钟内到账，银行卡支付的退款3个工作日后重新查询退款状态。
     *
     * out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个
     * @return mixed
     */
    public function refundQuery()
    {
        $url = "https://api.mch.weixin.qq.com/pay/refundquery";
        //检测必填参数
        $values = $this->getValues();
        if(!$values->has('out_refund_no') &&
            !$values->has('out_trade_no') &&
            !$values->has('transaction_id') &&
            !$values->has('refund_id')) {
            throw new InvalidParamException("退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！");
        }

        $values->setSign();//签名
        $xml = $values->toXml();
        $response = $this->postXmlCurl($xml, $url, false, 6);
        if($this->getResultValues()->fromXml($response) && $this->getResultValues()->validate()){
            return $this->getResultValues();
        }else{
            return false;
        }
    }

    /**
     * 下载对账单
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_6
     * 商户可以通过该接口下载历史交易清单。比如掉单、系统错误等导致商户侧和微信侧数据不一致，通过对账单核对后可校正支付状态。
     * 注意：
     *     1、微信侧未成功下单的交易不会出现在对账单中。支付成功后撤销的交易会出现在对账单中，跟原支付单订单号一致；
     *     2、微信在次日9点启动生成前一天的对账单，建议商户10点后再获取；
     *     3、对账单中涉及金额的字段单位为“元”。
     *     4、对账单接口只能下载三个月以内的账单。
     *
     * bill_date为必填参数
     * @return string
     */
    public function downloadBill()
    {
        $url = "https://api.mch.weixin.qq.com/pay/downloadbill";
        //检测必填参数
        $values = $this->getValues();
        if(!$values->has('bill_date')) {
            throw new InvalidParamException("对账单接口中，缺少必填参数bill_date！");
        }

        $values->setSign();//签名
        $xml = $values->toXml();
        $response = $this->postXmlCurl($xml, $url, false, 6);
        if(substr($response, 0 , 5) == "<xml>"){
            return "";
        }
        return $response;
    }

    /**
     * 支付结果通用通知
     * @https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_7
     * 支付完成后，微信会把相关支付结果和用户信息发送给商户，商户需要接收处理，并返回应答。
     * 对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，微信会通过一定的策略定期重新发起通知，尽可能提高通知的成功率，但微信不保证通知最终能成功。 （通知频率为15/15/30/180/1800/1800/1800/1800/3600，单位：秒）
     * 注意：同样的通知可能会多次发送给商户系统。商户系统必须能够正确处理重复的通知。
     * 推荐的做法是，当收到通知进行处理时，首先检查对应业务数据的状态，判断该通知是否已经处理过，如果没有处理过再进行处理，如果处理过直接返回结果成功。在对业务数据进行状态检查和处理之前，要采用数据锁进行并发控制，以避免函数重入造成的数据混乱。
     * 特别提醒：商户系统对于支付结果通知的内容一定要做签名验证，防止数据泄漏导致出现“假通知”，造成资金损失。
     * 技术人员可登进微信商户后台扫描加入接口报警群。
     *
     * @param $callback
     * 结果回调函数
     * ```php
     * function cb($result){
     *    // @var $result ResultCollection

     * }
     * ```
     * @param string $msg
     * @throws BadRequestHttpException
     * @return bool|mixed
     */
    public function notify($callback, &$msg)
    {
        //获取通知的数据
        $xml =  file_get_contents('php://input');
        if(empty($xml)){
            throw new BadRequestHttpException("nothing to deal with");
        }
        //如果返回成功则验证签名
        if($this->getResultValues()->fromXml($xml) && $this->getResultValues()->validate()){
            return call_user_func($callback, $this->getResultValues());
        }else{
            $msg = "数据错误";
            return false;
        }
    }


    /**
     * 提交被扫支付API
     * @link https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1
     * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，由商户收银台或者商户后台调用该接口发起支付。
     * 提醒1：提交支付请求后微信会同步返回支付结果。当返回结果为“系统错误”时，商户系统等待5秒后调用【查询订单API】，查询支付实际交易结果；
     * 当返回结果为“USERPAYING”时，商户系统可设置间隔时间(建议10秒)重新查询支付结果，直到支付成功或超时(建议30秒)；
     * 提醒2：在调用查询接口返回后，如果交易状况不明晰，请调用【撤销订单API】，此时如果交易失败则关闭订单，该单不能再支付成功；
     * 如果交易成功，则将扣款退回到用户账户。当撤销无返回或错误时，请再次调用。注意：请勿扣款后立即调用【撤销订单API】,建议至少15秒后再调用。撤销订单API需要双向证书。
     *
     * body、out_trade_no、total_fee、auth_code参数必填
     * @return bool|ResultCollection
     */
    public function micropay()
    {
        $this->checkMicropay();
        $url = "https://api.mch.weixin.qq.com/pay/micropay";
        //检测必填参数
        $values = $this->getValues();

        $values->set('spbill_create_ip', Yii::$app->request->userIP);

        $values->setSign();//签名
        $xml = $values->toXml();

//        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = $this->postXmlCurl($xml, $url, false, 6);
        if($this->getResultValues()->fromXml($response) && $this->getResultValues()->validate()){
            return $this->getResultValues();
        }else{
            return false;
        }
    }

    /**
     * body、out_trade_no、total_fee、auth_code参数必填
     */
    private function checkMicropay(){
        $values = $this->getValues();
        if(!$values->has('body')){
            throw new InvalidParamException("提交被扫支付API接口中，缺少必填参数body！");
        }
        if(!$values->has('out_trade_no')){
            throw new InvalidParamException("提交被扫支付API接口中，缺少必填参数out_trade_no！");
        }
        if(!$values->has('total_fee')){
            throw new InvalidParamException("提交被扫支付API接口中，缺少必填参数total_fee！");
        }
        if(!$values->has('auth_code')){
            throw new InvalidParamException("提交被扫支付API接口中，缺少必填参数auth_code！");
        }
    }




    /**
     * 交易保障
     * 商户在调用微信支付提供的相关接口时，会得到微信支付返回的相关信息以及获得整个接口的响应时间。
     * 为提高整体的服务水平，协助商户一起提高服务质量，微信支付提供了相关接口调用耗时和返回信息的主动上报接口，微信支付可以根据商户侧上报的数据进一步优化网络部署，完善服务监控，和商户更好的协作为用户提供更好的业务体验。
     * interface_url、return_code、result_code、user_ip、execute_time_必填
     * @return string
     */
    public function report()
    {
        $this->checkReport();
        $url = "https://api.mch.weixin.qq.com/payitil/report";
        //检测必填参数
        $values = $this->getValues();
        $values->set('user_id', Yii::$app->request->userIP);
        $values->set('time', date("YmdHis"));//商户上报时间

        $values->setSign();//签名
        $xml = $values->toXml();

        $response = $this->postXmlCurl($xml, $url, false, 1);
        return $response;
    }

    /**
     * interface_url、return_code、result_code、user_ip、execute_time必填
     */
    private function checkReport(){
        $values = $this->getValues();
        if(!$values->has('interface_url')){
            throw new InvalidParamException("接口URL，缺少必填参数interface_url！");
        }
        if(!$values->has('return_code')){
            throw new InvalidParamException("返回状态码，缺少必填参数return_code！");
        }
        if(!$values->has('result_code')){
            throw new InvalidParamException("业务结果，缺少必填参数result_code！");
        }
        if(!$values->has('user_ip')){
            throw new InvalidParamException("访问接口IP，缺少必填参数user_ip！");
        }
        if(!$values->has('execute_time')){
            throw new InvalidParamException("接口耗时，缺少必填参数execute_time_！");
        }
    }


}