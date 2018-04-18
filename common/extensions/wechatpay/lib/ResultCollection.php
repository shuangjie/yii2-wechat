<?php
namespace common\extensions\wechatpay\lib;
/**
 * 接口调用（微信支付response）结果类
 * User: DC
 * Date: 2017/2/27
 * Time: 19:48
 */
class ResultCollection extends DataCollection {

    /**
     * 检查签名
     * @param string $key
     * @return boolean
     */
    public function checkSign($key = ''){
        empty($key) && $key = $this->_signKey;
        if(!$this->has('sign')){
            return false;
        }
        $sign = $this->makeSignature($key);
        return $sign == $this->get('sign');
    }

    /**
     * @param string $xml
     * @return self|false
     */
    public function loadResult($xml){
        $this->fromXml($xml);
        if($this->get('return_code') != 'SUCCESS'){
            return $this;
        }
        //成功的消息，检查签名；
        $this->checkSign();
        return $this->checkSign() ? $this : false;
    }

    /**
     * 验证数据是否合法
     * @param boolean $checkSign 是如否检查sign
     * @return boolean
     */
    public function validate($checkSign = true){
        if($this->get('return_code') != 'SUCCESS'){
            return true;
        }
        return $checkSign ? $this->checkSign() : true;
    }


}