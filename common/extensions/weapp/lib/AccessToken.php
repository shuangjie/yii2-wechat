<?php
namespace common\extensions\weapp\lib;

use Yii;
use yii\base\Component;
use yii\caching\Cache;
use yii\di\Instance;

/**
 * access token 相关的client
 * usage：
 * ```php
 * use common\extensions\weapp\lib\AccessToken;
 *
 * $accessTokenClient = Yii::createObject([
 *     'class' => AccessToken::className(),
 *     'appid' => $wxs->appid,
 *     'appsecret' =>  $wxs->appsecret,
 * ]);
 * $accessToken = $accessTokenClient->getAccessToken();
 * ```
 */
class AccessToken extends Component {

    /**
     * @var string appid
     */
    public $appid;

    /**
     * @var string appsecret
     */
    public $appsecret;

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
     * @var string caching key for wxs access token
     * */
    public $cacheKey = 'wxs:accessToken';

    /**
     * @inheritdoc
     */
    public function init(){
        parent::init();

        if ($this->cache === null) {
            if (Yii::$app->has('cache')) {
                $this->cache = Yii::$app->get('cache');
            }
        } else {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        }
    }

    /**
     * @param string $key 缓存key
     * @return self;
     * */
    public function setCacheKey($key){
        $this->cacheKey = $key;
        return $this;
    }


    /**
     * get weapp Access Token of service account
     * 获取小程序access token
     * 先检查缓存是否存在
     * @return string|boolean
     */
    public function getAccessToken(){

        $accessToken = $this->getAccessTokenFromCache();

        if($accessToken === false){
            $accessToken = self::getAccessTokenFromApi();
            if($accessToken === false){
                return false;
            }
            $this->setAccessTokenToCache($accessToken);
        }

        return $accessToken['access_token'];
    }

    /**
     * 从缓存里获取公众号access token
     */
    private function getAccessTokenFromCache(){
        $accessTokenArr = $this->cache->get($this->cacheKey);
        if($accessTokenArr !== false){
            $accessTokenArr = json_decode($accessTokenArr, true);
        }
        return $accessTokenArr;
    }

    /**
     * 存入access token 到缓存
     * @param array $accessToken api返回信息
     * */
     private function setAccessTokenToCache($accessToken){
         $duration = $accessToken['expires_in'] - 200;
         $this->cache->set($this->cacheKey, json_encode($accessToken), $duration);
     }

    /**
     * 从api获取小程序 access token
     */
    private function getAccessTokenFromApi(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
        $accessToken = HttpClient::api($url);
        if(!isset($accessToken['access_token'])){
            //TODO 返回错误信息
            Yii::error("can't get access token from api: {$url}", "weapp");
            return false;
        }
        return $accessToken;
    }

}


