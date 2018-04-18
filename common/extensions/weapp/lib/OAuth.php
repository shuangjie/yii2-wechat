<?php
namespace common\extensions\weapp\lib;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * 微信OAuth2.0获取认证
 * 暂时不对用户的access token做缓存
 * 微信小程序授权
 * usage：
 * ```php
 * use common\extensions\weapp\lib\OAuth;
 * $oAthClient = Yii::createObject([
 *     'class' => OAuth::className(),
 *     'appid' => $weapp->appid,
 *     'appsecret' =>  $weapp->appsecret,
 * ]);
 * $oAthClient->generateOAthLink('http://m.fvgou.com/login');
 * ```
 * @link https://mp.weixin.qq.com/wiki
 */
class OAuth extends Component {

    /**
     * @var string
     * 以snsapi_base为scope发起的网页授权，是用来获取进入页面的用户的openid的，并且是静默授权并自动跳转到回调页的。
     * 用户感知的就是直接进入了回调页（往往是业务页面）
     * */
    const SCOPE_BASE = 'snsapi_base';

    /**
     * @var string
     * 以snsapi_userinfo为scope发起的网页授权，是用来获取用户的基本信息的。
     * 但这种授权需要用户手动同意，并且由于用户同意过，所以无须关注，就可在授权后获取该用户的基本信息
     * */
    const SCOPE_USERINFO = 'snsapi_userinfo';

    /**
     * @var string appid
     */
    private $_appid;

    /**
     * @var string appsecret
     */
    private $_appsecret;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if(is_null($this->_appid) || is_null($this->_appsecret)){
            throw new InvalidConfigException('invalid appId or appSecret');
        }
    }

    /**
     * @param string $appid  APP ID
     * */
    public function setAppid($appid){
        $this->_appid = $appid;
    }

    /**
     * @param string $appsecret  APP SECRET
     * */
    public function setAppsecret($appsecret){
        $this->_appsecret = $appsecret;
    }



    /**
     * 根据code换openid和seesionKey
     */
    private static $_sessionKey;

    /**
     * 通过code换取小程序OpenId 和 SessionKey*
     * @return array [openid, session_key] (满足union_id情况 也返回union_id) TODO 这里只拿session_key
     * Todo 缓存这个返回结果
     */
    public function getOpenIdAndSessionKey($code){
        //填写为authorization_code
        $grant_type = 'authorization_code';
        //构造请求微信接口的URL
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$this->_appid.'&secret='.$this->_appsecret.'&js_code='.$code.'&grant_type='.$grant_type.'';
        $data = HttpClient::api($url);
        return $data['session_key'];
    }

}
