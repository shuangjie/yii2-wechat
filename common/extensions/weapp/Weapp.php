<?php
namespace common\extensions\weapp;
use common\extensions\weapp\lib\AccessToken;
use common\extensions\weapp\lib\HttpClient;
use common\extensions\weapp\lib\JsSdk;
use common\extensions\weapp\lib\Material;
use common\extensions\weapp\lib\Menu;
use common\extensions\weapp\lib\OAuth;
use common\extensions\weapp\lib\Promotion;
use common\extensions\weapp\lib\Request;
use common\extensions\weapp\lib\TemplateMessage;
use common\extensions\weapp\lib\UserManage;
use common\extensions\weapp\lib\BizDataCrypt;
use yii\base\Component;
use Yii;
use yii\caching\Cache;

/**
 * 微信公众号扩展 入口
 * cache主要用来缓存access token，js ticket 这些数据。
 * 如果不配置cache 默认使用应用组件'cache'，即 Yii::$app->cache;
 * yii的cache默认是文件缓存，如果是多台服务器使用，就不建议用文件缓存。建议用redis，memcached等
 *
 * Usage:
 *
 * 1、写到components配置
 * ```php
 * 'components' =>[
 *      'weapp' => [
 *          'class' = 'common\extensions\weapp\Weapp'
 *          'token' => 'TOKEN'
 *          'encoding_aes_key' => 'ENCODING AES KEY',
 *          'appid' => 'APP ID',
 *          'appsecret' => 'APP SECRET',
 *          'cache' => [
 *              'class' => ' yii\redis\Connection’,
 *              'hostname' => 'localhost',
 *              'port' => 6379,
 *              'database' => 0,
 *          ]
 *      ],
 * ]
 * //或者如果你已经配置了相关的缓存应用组件，也可以这样写：
 * 'components' =>[
 *      'weapp' => [
 *          'class' = 'common\extensions\weapp\Weapp'
 *          'token' => 'TOKEN'
 *          'encoding_aes_key' => 'ENCODING AES KEY',
 *          'appid' => 'APP ID',
 *          'appsecret' => 'APP SECRET',
 *          'cache' => 'cache-redis-wechat'
 *      ],
 *      'cache-redis-weapp' => [
 *          'hostname' => 'localhost',
 *           'port' => 6379,
 *           'database' => 0,
 *      ]
 * ]
 *
 * $weapp = Yii::$app->weapp;
 * //获取access token
 * $weapp->getAccessToken();
 * ```
 *
 *
 * //2、直接创建对象
 * ```php
 * use common\extensions\weapp\Weapp;
 *
 * $config = [
 *      'class' = Weapp::className(),
 *      'token' => 'TOKEN'
*       'encoding_aes_key' => 'ENCODING AES KEY',
 *      'appid' => 'APP ID',
 *      'appsecret' => 'APP SECRET',
 *      'cache' => 'cache-redis-weapp'
 * ];
 * $weapp = Yii::createObject($config);
 * //获取access token
 * $weapp->getAccessToken();
 * ```
 *
 * @property string $accessToken
 * @property OAuth $oAuth 授权
 * @property \common\extensions\weapp\lib\Material $material 素材管理
 */
class Weapp extends Component {

//    public $url;
    public $token;
    public $encoding_aes_key;
    public $appid;
    public $appsecret;
    public $requestHandler;
    public $debug = false;

    /**
     * @var Cache|array|string cache object or the application component ID of the session object to be used.
     *
     * After the Cache object is created, if you want to change this property,
     * you should only assign it with a caching object.
     *
     * If not set - application 'cache' component will be used, but only, if it is available (e.g. in web application),
     * otherwise - no session will be used and no data saving will be performed.
     */
    public $cache; //access token缓存app

    // 加密签名
    public $sessionKey;

    public function init()
    {
        parent::init();
    }

    /**
     * 验证请求合法性
     */


    //处理入口消息
    public function run(){
        $this->getRequest()->handle();
    }

    /**
     * 处理入口消息
     * alias of run()
     * */
    public function watch(){
        $this->run();
    }

    /**
     * @var AccessToken $_accessToken 小程序access token
     * */
    private static $_accessToken;
    /**
     * @return string | boolean
     * */
    public function getAccessToken(){
        if(!(self::$_accessToken instanceof AccessToken)){
            /* @var  AccessToken $accessTokenClient */
            self::$_accessToken = Yii::createObject([
                'class' => AccessToken::className(),
                'appid' => $this->appid,
                'appsecret' =>  $this->appsecret,
                'cache' => $this->cache
            ]);
        }
        return self::$_accessToken->getAccessToken();
    }


    /**  ---- 授权 ---- **/

    /**
     * @var OAuth $_oAuth 授权对象实例
     */
    private static $_oAuth;
    /**
     * @return OAuth
     */
    public function getOAuth(){
        if(!(self::$_oAuth instanceof OAuth)){
            self::$_oAuth = Yii::createObject([
                'class' => OAuth::className(),
                'appid' => $this->appid,
                'appsecret' => $this->appsecret,
            ]);
        }
        return self::$_oAuth;
    }

    /**  ---- 模板消息 ---- **/
    /**
     * @var TemplateMessage $_templateMessage
     * */
    private static $_templateMessage;
    /**
     * @return TemplateMessage
     * */
    public function getTemplateMessage(){
        $accessToken = $this->getAccessToken();
        if(!(self::$_templateMessage instanceof TemplateMessage)){
            self::$_templateMessage = Yii::createObject([
                'class' => TemplateMessage::className(),
                'accessToken' => $accessToken,
            ]);
        }else{
            self::$_templateMessage->setAccessToken($accessToken);
        }
        return self::$_templateMessage;
    }

    /** ----  素材管理 ----  **/

    /**
     * @var Material $_material
     */
    private static $_material;

    /**
     * @return Material
     */
    public function getMaterial(){
        $accessToken = $this->getAccessToken();
        if(!(self::$_material instanceof Material)){
            self::$_material = Yii::createObject([
                'class' => Material::className(),
                'accessToken' => $accessToken,
            ]);
        }else{
            self::$_material->setAccessToken($accessToken);
        }
        return self::$_material;
    }



    private static $_bizDataCrypt;
    /**
     * @return BizDataCrypt
     */
    public function getBizDataCrypt($code){
        $sessionKey = $this->getOAuth()->getOpenIdAndSessionKey($code);

        if(!(self::$_bizDataCrypt instanceof BizDataCrypt)){
            self::$_bizDataCrypt = Yii::createObject([
                'class' => BizDataCrypt::className(),
                'appid' => $this->appid,
                'sessionKey' => $sessionKey,
            ]);
        }
        return self::$_bizDataCrypt;
    }


}