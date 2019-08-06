<?php
namespace wechat\modules\wechat\controllers;
use wechat\controllers\AuthController;
use common\components\ResponseComponent;
use common\extensions\wechat\User;
use common\helpers\redis\RedisClient;
use common\services\user\UserService;
use Yii;
use yii\base\ErrorException;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use common\extensions\wechat\Wechat;

class UserController extends AuthController  {

    protected $optionalActions = ['access-token'];

    /**
     * @param $code
     * @throws BadRequestHttpException
     * @return array
     */
    public function actionAccessToken($code){

        $expires_in = Yii::$app->params['access.token.expires.in'];

        if(!$code){
            throw new BadRequestHttpException("invalid code", 1);
        }
        $user = $this->_getUserByCode($code);

        //生成access token
        $accessToken = $user->generateAccessToken($code);
        $redis = RedisClient::getRedisClient();
        $redis->setex($accessToken, $expires_in ,json_encode(['id' => $user->id]));//有效期1小时

        $info = [];
        $info['id'] = $user->id;
        $info['nickname'] = base64_decode($user->nickname);
        $info['sex'] = $user->sex;
        $info['avatar'] = $user->avatar;
        $info['access_token'] = $accessToken;
        $info['expires_in'] = $expires_in - 100;
        // $info['subscribe'] = $this->_isSubscribe($user);
        return ResponseComponent::success($info);
    }

    public function actionInfo(){
        /**
         * @var User $user
         */
        $user = Yii::$app->user->identity;

        $info = [];
        $info['id'] = $user->id;
        $info['nickname'] = base64_decode($user->nickname);
        $info['sex'] = $user->sex;
        $info['avatar'] = $user->avatar;
//        $info['access_token'] = $accessToken;
        $info['subscribe'] = UserService::isSubscribeWechatAccount($user) ? 1 : 0;
        return ResponseComponent::success($info);
    }

    /**
     * 检查当前用户是否已经订阅公众号
     * @return array
     */
    public function actionIsSubscribe(){
        /**
         * @var User $user
         */
        $user = Yii::$app->user->identity;
        $isSubscribe = UserService::isSubscribeWechatAccount($user) ? 1 : 0;
        return ResponseComponent::success($isSubscribe);
    }



    private function _getUserByCode($code){
        /* @var Wechat $wechat */
        $wechat = Yii::$app->wechat;
        //获取 openid 和 access token
        $userTicket = $wechat->getOAuth()->getAccessTokenAndOpenId($code);
        if(isset($userTicket['errcode']) && $userTicket['errcode']){
            Yii::error("can't get access_token or openid from url param code:".$code.", api result:".print_r($userTicket, true), 'wechat_login');
            throw new ErrorException($userTicket['errmsg'], 1);
        }
        Yii::error(print_r($userTicket, true), 'wechat');
        //查找用户信息，如果不存在则注册
        $user = User::SignUpByWechatOAuth($userTicket['openid'], $userTicket['access_token']);
        if($user === false){
            throw new ErrorException("sign up error");
        }
        return $user;
    }


}