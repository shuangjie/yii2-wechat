<?php
namespace weapp\modules\weapp\controllers;
use weapp\controllers\AuthController;
use common\components\ResponseComponent;
use common\extensions\weapp\User;
use common\helpers\redis\RedisClient;
use common\services\user\UserService;
use Yii;
use yii\base\ErrorException;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use common\extensions\weapp\Weapp;

class UserController extends AuthController  {

    protected $optionalActions = ['access-token'];

    /**
     * @param $code
     * @param $encryptedData
     * @param $iv
     * @throws BadRequestHttpException
     * @return array
     */
    public function actionAccessToken($code,$encryptedData,$iv){

        $expires_in = Yii::$app->params['access.token.expires.in'];

        if(!$code){
            throw new BadRequestHttpException("invalid code", 1);
        }
        $user = $this->_getBizDataCrypt($code,$encryptedData,$iv);
        //生成access token
        $accessToken = $user->generateAccessToken($code);
        $redis = RedisClient::getRedisClient();
        $redis->setex($accessToken, $expires_in ,json_encode(['id' => $user->id]));//有效期1小时

        $info = [];
        $info['id'] = $user->id;
        $info['nickname'] = $user->nickname;
        $info['sex'] = $user->sex;
        $info['avatar'] = $user->avatar;
        $info['access_token'] = $accessToken;
        $info['expires_in'] = $expires_in - 100;
        $info['data'] = $user;
        return ResponseComponent::success($info);
    }

    public function actionInfo(){
        /**
         * @var User $user
         */
        $user = Yii::$app->user->identity;

        $info = [];
        $info['id'] = $user->id;
        $info['nickname'] = $user->nickname;
        $info['sex'] = $user->sex;
        $info['avatar'] = $user->avatar;

        return ResponseComponent::success($info);
    }



    private function _getBizDataCrypt($code,$encryptedData,$iv){
        /* @var Weapp $weapp */
        $weapp = Yii::$app->weapp;

        $errCode =  $weapp->getBizDataCrypt($code)->decryptData($encryptedData,$iv,$data);
        if(isset($errCode) && $errCode['errcode']){
            Yii::error("can't get BizDataCrypt decryptData from url param code:".$code.", api result:".print_r($errCode, true), 'wxs_login');
            throw new ErrorException($errCode['errmsg'], 1);
        }
        Yii::error(print_r($errCode, true), 'weapp');
        //查找用户信息，如果不存在则注册
        $userInfo = json_decode($data,true);
        $user = User::SignUpByWeappOAuth($userInfo);
        if($user === false){
            throw new ErrorException("sign up error");
        }
        return $user;

    }


}