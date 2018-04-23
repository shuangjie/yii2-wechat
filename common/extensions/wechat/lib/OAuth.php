<?php
namespace common\extensions\wechat\lib;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * 微信OAuth2.0获取认证
 * 暂时不对用户的access token做缓存
 * 微信网页开发-微信网页授权
 * usage：
 * ```php
 * use common\extensions\wechat\lib\OAuth;
 * $oAthClient = Yii::createObject([
 *     'class' => OAuth::className(),
 *     'appid' => $wechat->appid,
 *     'appsecret' =>  $wechat->appsecret,
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
     * 生成授权链接
     * @param string $redirect_uri 授权成功后的跳转了链接
     * @param string  $state [a-zA-Z0-9]的参数值，最多128字节,重定向时返回这个参数。防止CSRF
     * @param string $scope snsapi_base|snsapi_userinfo
     * */
    public function getCode($redirect_uri, $scope = self::SCOPE_BASE, $state = 'linkces'){
        $url = $this->generateOAthLink($redirect_uri, $scope, $state);
        header('Location: '.$url, true, 301);
    }

    /**
     * 生成授权链接
     * @param string $redirect_uri 授权成功后的跳转了链接
     * @param string $state [a-zA-Z0-9]的参数值，最多128字节,重定向时返回这个参数。防止CSRF
     * @param string $scope snsapi_base|snsapi_userinfo
     * @return string
     * */
    public function generateOAthLink($redirect_uri, $scope = self::SCOPE_BASE, $state='linkces'){
        $redirect_uri = urlencode($redirect_uri);
        //返回类型，请填写code，只能填code
        $response_type = 'code';
        //构造请求微信接口的URL
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->_appid.'&redirect_uri='.$redirect_uri.'&response_type='.$response_type.'&scope='.$scope.'&state='.$state.'#wechat_redirect';
        return $url;
    }

    /**
     * 根据code换openid和seesionKey
     */
    private static $_sessionKey;
    /**
     * @return SessionKey
     */
    /**
     * 通过code换取小程序OpenId 和 SessionKey*
     * @return array [openid, session_key] (满足union_id情况 也返回union_id)
     * Todo 缓存这个返回结果
     */
    public function getOpenIdAndSessionKey($code){
        //填写为authorization_code
        $grant_type = 'authorization_code';
        //构造请求微信接口的URL
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$this->_appid.'&secret='.$this->_appsecret.'&js_code='.$code.'&grant_type='.$grant_type.'';
        //请求微信接口, Array(access_token, expires_in, refresh_token, openid, scope)
        $data = HttpClient::api($url);
        return $data;
    }

    /**
     * 通过code换取网页授权access_token
     * 首先请注意，这里通过code换取的网页授权access_token,与基础支持中的access_token不同。
     * 公众号可通过下述接口来获取网页授权access_token。
     * 如果网页授权的作用域为snsapi_base，则本步骤中获取到网页授权access_token的同时，也获取到了openid，snsapi_base式的网页授权流程即到此为止。
     * @param string $code getCode()获取的code参数
     *
     * @return array [access_token, expires_in, refresh_token, openid, scope]
     * Todo 缓存这个返回结果
     */
    public function getAccessTokenAndOpenId($code){
        //填写为authorization_code
        $grant_type = 'authorization_code';
        //构造请求微信接口的URL
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->_appid.'&secret='.$this->_appsecret.'&code='.$code.'&grant_type='.$grant_type.'';
        //请求微信接口, Array(access_token, expires_in, refresh_token, openid, scope)
        return HttpClient::api($url);
    }

    /**
     * 刷新access_token（如果需要）
     * 由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新，refresh_token拥有较长的有效期（7天、30天、60天、90天），当refresh_token失效的后，需要用户重新授权。
     * @param string $refreshToken 通过本类的第二个方法getAccessTokenAndOpenId可以获得一个数组，数组中有一个字段是refresh_token，就是这里的参数
     *
     * @return array(
    "access_token"=>"网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同",
    "expires_in"=>access_token接口调用凭证超时时间，单位（秒）,
    "refresh_token"=>"用户刷新access_token",
    "openid"=>"用户唯一标识",
    "scope"=>"用户授权的作用域，使用逗号（,）分隔")
     */
    public function refreshToken($refreshToken){
        $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$this->_appid.'&grant_type=refresh_token&refresh_token='.$refreshToken;
        return HttpClient::api($url);
    }

    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     * 如果网页授权作用域为snsapi_userinfo，则此时开发者可以通过access_token和openid拉取用户信息了。
     * @param string $accessToken 网页授权接口调用凭证。通过本类的第二个方法getAccessTokenAndOpenId可以获得一个数组，数组中有一个字段是access_token，就是这里的参数。注意：此access_token与基础支持的access_token不同
     * @param string $openId 用户的唯一标识
     * @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     *
     * @return array array("openid"=>"用户的唯一标识",
    "nickname"=>'用户昵称',
    "sex"=>"1是男，2是女，0是未知",
    "province"=>"用户个人资料填写的省份"
    "city"=>"普通用户个人资料填写的城市",
    "country"=>"国家，如中国为CN",
    //户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空
    "headimgurl"=>"http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
    //用户特权信息，json 数组，如微信沃卡用户为chinaunicom
    "privilege"=>array("PRIVILEGE1", "PRIVILEGE2"),
    );
     */
    public function getUserInfo($accessToken, $openId, $lang='zh_CN'){
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='. $accessToken . '&openid='. $openId .'&lang='. $lang;
        return HttpClient::api($url);
    }

    /**
     * 检验授权凭证（access_token）是否有效
     * @param string $accessToken 网页授权接口调用凭证。通过本类的第二个方法getAccessTokenAndOpenId可以获得一个数组，数组中有一个字段是access_token，就是这里的参数。注意：此access_token与基础支持的access_token不同
     * @param string $openId
     * @return array array("errcode"=>0,"errmsg"=>"ok")
     */
    public function checkAccessToken($accessToken, $openId){
        $url = 'https://api.weixin.qq.com/sns/auth?access_token='.$accessToken.'&openid='.$openId;
        return HttpClient::api($url);
    }
}
