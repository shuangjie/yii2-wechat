<?php
namespace common\extensions\wechat\lib;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\di\Instance;
/**
 * JSSDk 微信js sdk签名数据包
 * @link https://mp.weixin.qq.com/wiki
 * 文档位置： 微信网页开发-微信JS-SDK说明文档
 * usage：
 * ```php
 * use common\extensions\wechat\lib\JsSdk;
 *
 * $jsSdkClient = Yii::createObject([
 *     'class' => JsSdk::className(),
 *     'appid' => $wechat->appid,
 *     'accessToken' =>  $wechat->getAccessToken(),
 * ]);
 * $jsSignPackage = $jsSdkClient->getSignPackage();
 * ```
 */
class JsSdk extends BaseClient {
    /**
     * @var string $appid 生成签名数据包时用到
     * */
    private $_appid;


    /**
     * @var Cache|array|string cache object or the application component ID of the session object to be used.
     *
     * After the Cache object is created, if you want to change this property,
     * you should only assign it with a caching object.
     *
     * If not set - application 'cache' component will be used, but only, if it is available (e.g. in web application),
     * otherwise - no session will be used and no data saving will be performed.
     */
    public $cache;

    /**
     * @var string caching key for wechat js api ticket
     * */
    public $cacheKey = 'wechat:jsTicket';

    /**
     * @var string
     * */
    public static $jsApi = [
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'onMenuShareQZone',
        'hideMenuItems',
        'showMenuItems',
        'hideAllNonBaseMenuItem',
        'showAllNonBaseMenuItem',
        'translateVoice',
        'startRecord',
        'stopRecord',
        'onVoiceRecordEnd',
        'playVoice',
        'onVoicePlayEnd',
        'pauseVoice',
        'stopVoice',
        'uploadVoice',
        'downloadVoice',
        'chooseImage',
        'previewImage',
        'uploadImage',
        'downloadImage',
        'getNetworkType',
        'openLocation',
        'getLocation',
        'hideOptionMenu',
        'showOptionMenu',
        'closeWindow',
        'scanQRCode',
        'chooseWXPay',
        'openProductSpecificView',
        'addCard',
        'chooseCard',
        'openCard'
    ];

    /**
     * @inheritdoc
     */
    public function init(){
        parent::init();
        if(is_null($this->_appid)){
            throw new InvalidConfigException('invalid appId');
        }
        if ($this->cache === null) {
            if (Yii::$app->has('cache')) {
                $this->cache = Yii::$app->get('cache');
            }
        } else {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        }

    }

    /**
     * Set the app id of wechat account.
     * @param string $appid cookie parameters, valid keys include: `lifetime`, `path`, `domain`, `secure` and `httponly`.
     */
    public function setAppid($appid)
    {
        $this->_appid = $appid;
    }


    /**
     * 生成签名数据包
     * @param string $url
     * @return array
     *[
     *  "appId"     => $this->_appId,
     *  "nonceStr"  => $nonceStr,
     *  "timestamp" => $timestamp,
     *  "url"       => $url,
     *  "signature" => $signature,
     *  "rawString" => $string
     *]
     * */
    public function getSignPackage($url = '') {
        $params = [];
        $jsApiTicket = self::getJsApiTicket();
        if(!$url){
            $protocol = (!empty(@$_SERVER['HTTPS']) && @$_SERVER['HTTPS'] !== 'off' || @$_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = $protocol.@$_SERVER['HTTP_HOST'].@$_SERVER['REQUEST_URI'];
        }
        $params['jsapi_ticket'] = $jsApiTicket;
        $params['url'] = $url;
        $params['timestamp'] = time();
        $params['noncestr'] = self::createNonceStr();
        ksort($params);
//        $string = http_build_query($params);
        $param_queries = [];
        foreach ($params as $k => $v){
            $param_queries[] = $k.'='.$v;
        }
        $string = implode("&", $param_queries);
        $signature = sha1($string);
        $signPackage = [
            "appId"     => $this->_appid,
            "nonceStr"  => $params['noncestr'],
            "timestamp" => $params['timestamp'],
            "url"       => $params['url'],
            "signature" => $signature,
//            "rawString" => $string,  //这一行是原始信息，一定不能输出到前端
        ];
        return $signPackage;
    }

    /**
     * 生成随机字符
     * @param integer $length
     * @return string
     * */
    private static function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取JsApiTicket
     * @param boolean $userCache
     * @return string | boolean
     * */
    public function getJsApiTicket($userCache = true){
        $jsApiTicketResult = false;
        $userCache && $jsApiTicketResult = $this->getJsApiTicketFromCache();
        if($jsApiTicketResult === false){
            $jsApiTicketResult = $this->getJsApiTicketFromApi();
            if($jsApiTicketResult['errcode']){
                Yii::error("get js api error:".$jsApiTicketResult['errmsg'], 'wechat');
                return false;
            }
            //写入缓存
            $this->setJsApiTicketToCache($jsApiTicketResult);
        }
        return $jsApiTicketResult['ticket'];
    }


    /**
     * 从微信api取得js api ticket
     * @return array
     *  [
     *      "errcode":0,
     *      "errmsg":"ok",
     *      "ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKdvsdshFKA",
     *      "expires_in":7200
     *  ]
     * */
    private function getJsApiTicketFromApi(){
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$this->accessToken;
        return HttpClient::api($url);
    }

    /**
     * 从缓存中取得js api ticket
     * */
    private function getJsApiTicketFromCache(){
        $jsApiTicketResult = $this->cache->get($this->cacheKey);
        if($jsApiTicketResult !== false){
            $jsApiTicketResult = json_decode($jsApiTicketResult, true);
        }
        return $jsApiTicketResult;
    }

    /**
     * 存入access token 到缓存
     * @param array $jsApiTicket getJsApiTicketFromApi()返回信息
     * */
    private function setJsApiTicketToCache($jsApiTicket){
        $duration = $jsApiTicket['expires_in'] - 200;
        $this->cache->set($this->cacheKey, json_encode($jsApiTicket), $duration);
    }



}