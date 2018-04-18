<?php
namespace common\extensions\wechatpay\lib;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\InvalidParamException;

/**
 * @property DataCollection $values
 * @property string $appid
 * @property string $mch_id
 * @property string $key
 * @property string $notify_url 回调url
 */
abstract class BaseClient extends Component {

    protected $_appid;
    protected $_mch_id;
    protected $_key;

    /**
     *
     * ssl 证书设置
     */
    protected $_ssl_cert;
    protected $_ssl_key;

    /**
     * @var DataCollection
     */
    private $_values;

    /**
     * @var ResultCollection  接口调用结果
     */
    private $_resultValues;


    /**
     * @inheritdoc
     */
    public function init()
    {
        if(!is_string($this->_appid)){
            throw new InvalidParamException("invalid param appid");
        }
        if(!is_string($this->_mch_id)){
            throw new InvalidParamException("invalid param mch_id");
        }
        if(!is_string($this->_key)){
            throw new InvalidParamException("invalid param key");
        }
        if(is_null($this->_ssl_cert)){
            $this->_ssl_cert = dirname(dirname(__FILE__)).'/cert/apiclient_cert.pem';
        }
        if(is_null($this->_ssl_key)){
            $this->_ssl_key = dirname(dirname(__FILE__)).'/cert/apiclient_key.pem';
        }

        //初始化数据
        $values = $this->getValues();
        // api secret
        $values->setSignKey($this->_key);
        //appid
        $values->set('appid', $this->_appid);
        //商户号
        $values->set('mch_id', $this->_mch_id);
        //随机字符
        $values->set('nonce_str', self::generateNonceStr());
    }


    /**
     * Returns the data collection.
     * The data collection contains the currently registered api params.
     * @return DataCollection the header collection
     */
    public function getValues()
    {
        if ($this->_values === null) {
            $this->_values = new DataCollection;
            $this->_values->setSignKey($this->_key); //设置api秘钥
        }
        return $this->_values;
    }

    /**
     * set values null
     */
    public function initValues(){
        if($this->_values instanceof DataCollection){
            foreach($this->_values as $key => $v ){
                !in_array($key, ['appid', 'mch_id', 'nonce_str']) && $this->_values->remove($key);
            }
        }
    }

    /**
     * set result values null
     */
    public function setResultValuesNull(){
        $this->_resultValues = null;
    }

    /**
     * Returns the data collection.
     * The data collection contains the currently registered api params.
     * @return ResultCollection the header collection
     */
    public function getResultValues(){
        if ($this->_resultValues === null) {
            $this->_resultValues = new ResultCollection();
            $this->_resultValues->setSignKey($this->_key); //设置api秘钥
        }
        return $this->_resultValues;
    }



    /**
     * @param string $appid  APP ID
     * */
    public function setAppid($appid){
        $this->_appid = $appid;
    }
    public function getAppid(){
        return $this->_appid;
    }
    public function setMch_id($mch_id){
        $this->_mch_id = $mch_id;
    }
    public function getMch_id(){
        return $this->_mch_id;
    }
    public function setKey($key){
        $this->_key = $key;
    }
    public function getKey(){
        return $this->_key;
    }



    /**
     *
     * 产生随机字符串，不长于32位
     * @param integer $length
     * @return string 产生的随机字符串
     */
    public static function generateNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }


    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws ErrorException
     * @return mixed
     */
    protected function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if($useCert == true){
            //设置证书
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $this->_ssl_cert);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $this->_ssl_key);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new ErrorException("curl出错，错误码:$error");
        }
    }


    /**
     * 获取毫秒级别的时间戳
     */
    protected static function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }



}