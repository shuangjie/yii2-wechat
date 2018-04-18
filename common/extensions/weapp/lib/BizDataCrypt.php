<?php
/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 * ```
 * User: DoubleJack
 * Date: 2018/3/27
 * Time: 14:35
 */

namespace common\extensions\weapp\lib;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\di\Instance;


class BizDataCrypt extends Component {
    private $_appid;
    private $_sessionKey;

    /**
     * error code 说明.
     * <ul>
     *    <li>-41001: encodingAesKey 非法</li>
     *    <li>-41003: aes 解密失败</li>
     *    <li>-41004: 解密后得到的buffer非法</li>
     *    <li>-41005: base64加密失败</li>
     *    <li>-41016: base64解密失败</li>
     * </ul>
     */
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if(is_null($this->_appid) || is_null($this->_sessionKey)){
            throw new InvalidConfigException('invalid appId or appSecret');
        }
    }

    /**
     * @param string $appid  小程序的appid
     * */
    public function setAppid($appid){
        $this->_appid = $appid;
    }

    /**
     * @param string $sessionKey  用户在小程序登录后获取的会话密钥
     * */
    public function setSessionKey($sessionKey){
        $this->_sessionKey = $sessionKey;
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $encryptedData, $iv, &$data )
    {
        if (strlen($this->_sessionKey) != 24) {
            return self::$IllegalAesKey;
        }
        $aesKey=base64_decode($this->_sessionKey);


        if (strlen($iv) != 24) {
            return self::$IllegalIv;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            return self::$IllegalBuffer;
        }
        if( $dataObj->watermark->appid != $this->_appid )
        {
            return self::$IllegalBuffer;
        }
        $data = $result;
        return self::$OK;
    }

}

