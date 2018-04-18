<?php
namespace common\extensions\wechatpay;
use common\extensions\wechatpay\lib\BaseClient;
use common\extensions\wechatpay\lib\DataCollection;
use common\extensions\wechatpay\lib\ResultCollection;
use common\extensions\wechatpay\lib\WechatPayApi;
use yii\base\Component;
use yii\base\InvalidParamException;

/**
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2018/4/18
 * Time: 18:06
 * @property string $appid
 * @property string $mch_id
 * @property string $key
 * @property string $notify_url
 *
 * @property WechatPayApi $wechatPayApiCli
 */
class WechatPay extends Component {
    /**
     * @var string $appid  app id of wechat account
     * */
    private $_appid;
    /**
     * @var string
     * */
    private $_mch_id;

    /**
     * @var string
     * */
    private $_key;

    /**
     * @var string
     * */
    private $_notify_url;


    /**
     * @inheritdoc
     */
    public function setAppid($appid){
        $this->_appid = $appid;
    }
    /**
     * @inheritdoc
     */
    public function getAppid(){
        return $this->_appid;
    }

    /**
     * @inheritdoc
     */
    public function setMch_id($mch_id){
        $this->_mch_id = $mch_id;
    }
    /**
     * @inheritdoc
     */
    public function getMch_id(){
        return $this->_mch_id;
    }

    /**
     * @inheritdoc
     */
    public function setKey($key){
        $this->_key = $key;
    }
    /**
     * @inheritdoc
     */
    public function getKey(){
        return $this->_key;
    }

    /**
     * @inheritdoc
     */
    public function setNotify_url($notify_url){
        $this->_notify_url = $notify_url;
    }
    /**
     * @inheritdoc
     */
    public function getNotify_url(){
        return $this->_notify_url;
    }

    /**
     * @var WechatPayApi
     */
    private static $_wechatPayApiCli;

    /**
     * @return WechatPayApi
     */
    public function getWechatPayApiCli(){
        if(!(self::$_wechatPayApiCli instanceof WechatPayApi)){
            $config = [
                'class' => WechatPayApi::className(),
                'appid' => $this->_appid,
                'mch_id' => $this->_mch_id,
                'key' => $this->_key,
                'notify_url' => $this->_notify_url
            ];
            self::$_wechatPayApiCli = \Yii::createObject($config);
        }
        self::$_wechatPayApiCli->initValues();
        self::$_wechatPayApiCli->setResultValuesNull();
        return self::$_wechatPayApiCli;
    }

    /**
     * 根据统一下单接口返回接口生成js api数据包
     * @param ResultCollection $UnifiedOrderResult 统一支付接口返回的数据
     * @throws InvalidParamException
     * @return array js api数据包;
     * */
    public function getJsApiParameters($UnifiedOrderResult){
        if(!$UnifiedOrderResult->has('appid')
        || !$UnifiedOrderResult->has('prepay_id')
        || $UnifiedOrderResult['prepay_id'] == ''){
            throw new InvalidParamException("参数错误", 1);
        }

        $jsApi = new DataCollection();
        $jsApi['appId'] = $this->_appid;
        $time = time();
        $jsApi['timeStamp'] = "$time";
        $jsApi['nonceStr'] = BaseClient::generateNonceStr();
        $jsApi['package'] = "prepay_id=" . $UnifiedOrderResult['prepay_id'];
        $jsApi['signType'] = "MD5";
        $jsApi['paySign'] = $jsApi->makeSignature($this->_key);
        return $jsApi->toArray();
    }


    public function __call($name, $params)
    {
        return call_user_func_array(
            array($this->getWechatPayApiCli(), $name),
            $params
        );
    }









}