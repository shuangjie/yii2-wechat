<?php
namespace common\extensions\wechat;
use common\extensions\wechat\lib\AccessToken;
use common\extensions\wechat\lib\HttpClient;
use common\extensions\wechat\lib\JsSdk;
use common\extensions\wechat\lib\Material;
use common\extensions\wechat\lib\Menu;
use common\extensions\wechat\lib\OAuth;
use common\extensions\wechat\lib\Promotion;
use common\extensions\wechat\lib\Request;
use common\extensions\wechat\lib\TemplateMessage;
use common\extensions\wechat\lib\UserManage;
use common\extensions\wechat\lib\BizDataCrypt;
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
 *      'wechat' => [
 *          'class' = 'common\extensions\wechat\Wechat'
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
 *      'wechat' => [
 *          'class' = 'common\extensions\wechat\Wechat'
 *          'token' => 'TOKEN'
 *          'encoding_aes_key' => 'ENCODING AES KEY',
 *          'appid' => 'APP ID',
 *          'appsecret' => 'APP SECRET',
 *          'cache' => 'cache-redis-wechat'
 *      ],
 *      'cache-redis-wechat' => [
 *          'hostname' => 'localhost',
 *           'port' => 6379,
 *           'database' => 0,
 *      ]
 * ]
 *
 * $wechat = Yii::$app->wechat;
 * //获取access token
 * $wechat->getAccessToken();
 * ```
 *
 *
 * //2、直接创建对象
 * ```php
 * use common\extensions\wechat\Wechat;
 *
 * $config = [
 *      'class' = Wechat::className(),
 *      'token' => 'TOKEN'
*       'encoding_aes_key' => 'ENCODING AES KEY',
 *      'appid' => 'APP ID',
 *      'appsecret' => 'APP SECRET',
 *      'cache' => 'cache-redis-wechat'
 * ];
 * $wechat = Yii::createObject($config);
 * //获取access token
 * $wechat->getAccessToken();
 * ```
 *
 *
 * @property string $accessToken
 * @property UserManage $userManage 用户管理
 * @property OAuth $oAuth 网页授权
 * @property JsSdk $jsSdk 网页授权
 * @property JsSdk::getSignPackage $oAuth 网页授权
 * @property Menu $menu 公众号菜单
 * @property \common\extensions\wechat\lib\Material $material 素材管理
 * @property Promotion $promotion 推广支持
 */
class Wechat extends Component {

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
     * @var AccessToken $_accessToken 公众号access token
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

    /** ---- 用户管理 ----  **/

    /**
     * @var UserManage $_userManage
     * */
    private static $_userManage;
    /**
     * @return UserManage
     * */
    public function getUserManage(){
        $accessToken = $this->getAccessToken();
        if(!(self::$_userManage instanceof UserManage)){
            self::$_userManage = Yii::createObject([
                'class' => UserManage::className(),
                'accessToken' => $accessToken,
            ]);
        }else{
            self::$_userManage->setAccessToken($accessToken);
        }
        return self::$_userManage;
    }

    /**  ---- 网页授权 ---- **/

    /**
     * @var OAuth $_oAuth 网页授权对象实例
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

    /**  ---- js sdk 签名数据包 ---- **/

    /**
     * @var JsSdk $_jsSdk js sdk对象实例
     */
    private static $_jsSdk;
    /**
     * @return JsSdk
     */
    public function getJsSdk(){
        $accessToken = $this->getAccessToken();
        if(!(self::$_jsSdk instanceof JsSdk)){
            self::$_jsSdk = Yii::createObject([
                'class' => JsSdk::className(),
                'accessToken' => $accessToken,
                'appid' => $this->appid,
                'cache' => $this->cache
            ]);
        }else{
            self::$_jsSdk->setAccessToken($accessToken);
        }
        return self::$_jsSdk;
    }
    /**
     * 获取js签名数据包
     * js sdk服务端基本只有这个方法会被用到，直接放入口这里
     * @see JsSdk::getSignPackage()
     * @param string $url 需要签名的url
     * @return array
     */
    public function getSignPackage($url = ''){
        return $this->getJsSdk()->getSignPackage($url);
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

    /** ---- 公众号菜单 ----  **/

    /**
     * @var Menu $_menu
     * */
    private static $_menu;
    /**
     * @return Menu
     * */
    public function getMenu(){
        $accessToken = $this->getAccessToken();
        if(!(self::$_menu instanceof Menu)){
            self::$_menu = Yii::createObject([
                'class' => Menu::className(),
                'accessToken' => $accessToken,
            ]);
        }else{
            self::$_menu->setAccessToken($accessToken);
        }
        return self::$_menu;
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

    /**
     * @var Request $_request
     */
    private static $_request;
    /**
     * @return Request
     */
    public function getRequest(){
        if(!(self::$_request instanceof Request)){
            self::$_request = Yii::createObject([
                'class' => Request::className(),
                'token' => $this->token,
                'handler' => $this->requestHandler,
            ]);
        }
        return self::$_request;
    }

    /**
     * @var Promotion $_promotion
     */
    private static $_promotion;

    /**
     * @return Promotion
     */
    public function getPromotion(){
        $accessToken = $this->getAccessToken();
        if(!(self::$_promotion instanceof Promotion)){
            self::$_promotion = Yii::createObject([
                'class' => Promotion::className(),
                'accessToken' => $accessToken,
            ]);
        }else{
            self::$_promotion->setAccessToken($accessToken);
        }
        return self::$_promotion;
    }


}