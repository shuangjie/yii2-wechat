<?php
namespace common\extensions\wechat;
use common\models\Auth;
use Yii;
use yii\base\Exception;

/**
 * 用户类
 * User: DoubleJack
 * Date: 2018/4/18
 * Time: 18:03
 */
class User extends \common\models\User {
    /**
     * @inheritdoc
     * 根据微信openid查找
     * @param string $openId
     * @return \common\models\User|null
     */
    public static function findIdentityByWechatOpenId($openId)
    {
        $auth = Auth::findOne([
            'source' => Auth::SOURCE_WECHAT,
            'source_id' => $openId
        ]);
        return $auth ? $auth->user : null;
    }

    /**
     * @inheritdoc
     * 用微信用户信息生成用户
     * 针对订阅用户有效
     * @return self
     * */
    public static function SignUpByWechatOpenId($openId, $checkDuplicate = true){
        /* @var Wechat $wechat */
        $wechat = Yii::$app->wechat; //初始化
        if($checkDuplicate){
            $user = self::findIdentityByWechatOpenId($openId);
            if($user) return $user;
        }
        //获取用户基本信息
        $userInfo = $wechat->getUserManage()->getUserInfo($openId);
        if(!$userInfo || isset($userInfo['errcode']) || $userInfo['subscribe'] == 0){
            Yii::error("can not get user info by openid:{$openId}, returns".print_r($userInfo, true), 'wechat');
            return false;
        }
        $user = self::signUp($userInfo);
        return $user;
    }

    /**
     * @inheritdoc
     * 用微信用户信息生成用户
     * 针对用户授权操作有效
     * @param string $openId 用户openId
     * @param string $access_token 用户授权token，不同于基础接口access token
     * 关于网页授权access_token和普通access_token的区别
     * 1、微信网页授权是通过OAuth2.0机制实现的，在用户授权给公众号后，公众号可以获取到一个网页授权特有的接口调用凭证（网页授权access_token），通过网页授权access_token可以进行授权后接口调用，如获取用户基本信息；
     * 2、其他微信接口，需要通过基础支持中的“获取access_token”接口来获取到的普通access_token调用。
     * @return \common\models\User|boolean
     * */
    public static function SignUpByWechatOAuth($openId, $access_token, $checkDuplicate = true){
        /* @var Wechat $wechat */
        $wechat = Yii::$app->wechat; //初始化
        if($checkDuplicate){
            $user = self::findIdentityByWechatOpenId($openId);
            if($user) return $user;
        }
        //获取用户基本信息
        $userInfo = $wechat->getOAuth()->getUserInfo($access_token, $openId);
        if(!$userInfo || isset($userInfo['errcode'])){
            Yii::error("can not get user info by access token:{$access_token}, open id:{$openId}, returns".print_r($userInfo, true), 'wechat');
            return false;
        }
        $user = self::signUp($userInfo);
        return $user;
    }



    /**
     * @param array $userInfo 通过 UserManage::getUserInfo() 或者 OAuth::getUserInfo()获得的用户信息
     * 用户信息字段如下：
     * [
     *     [openid] => ozP4_xOEk13kb0YRaU7jDtJ9XvTI
     *     [nickname] => DC
     *     [sex] => 1
     *     [language] => en
     *     [city] => 深圳
     *     [province] => 广东
     *     [country] => 中国
     *     [headimgurl] => http://wx.qlogo.cn/mmopen/a23SN8HhVLm51EIM4SEe0XjoDfgB4IBec8iaicAnibDkCztRC3w4jMtyr2ib32Yiaxib3yIEy65mLN2niaOUUzCeOPFWg9EwbIibAnUr/0
     *     [subscribe_time] => 1484053314
     *     [remark] =>
     *    [groupid] => 0
     * ]
     * @return \common\models\User|boolean
     * */
    public static function signUp($userInfo){
        $openId = $userInfo['openid'];
        //注册用户
        $connection = \common\models\User::getDb();
        $transaction = $connection->beginTransaction();
        try{
            $user = new \common\models\User();
            $user->username = $user->nickname = $userInfo['nickname'];  //TODO 如果含有emoji 数据库表对应字段要用utf8mb4，或者  base64_encode($userInfo['nickname'])
            $user->sex = $userInfo['sex'];
            $user->language = $userInfo['language'];
            $user->city = $userInfo['city'];
            $user->province = $userInfo['province'];
            $user->country = $userInfo['country'];
            $user->avatar = $userInfo['headimgurl']; //TODO 应该下载
            $user->source = \common\models\User::SOURCE_WECHAT;
            $user->setPassword(Yii::$app->security->generateRandomString(8));
            $user->generateAuthKey();
            if(!$user->save()){
                Yii::error("error to create user ".print_r($user->attributes, true). ", errors".print_r($user->errors, true), 'wechat');
                return false;
            }
            $auth = new Auth([
                'user_id' => $user->id,
                'source' => Auth::SOURCE_WECHAT,
                'source_id' => $openId,
            ]);
            if($auth->save()){
                $transaction->commit();
                return $user;
            }else{
                Yii::error("error to create auth ".print_r($auth->attributes, true). ", errors".print_r($auth->errors, true), 'wechat');
                $transaction->rollback();
                return false;
            }
        }catch(Exception $e){
            Yii::error($e->getMessage(), 'wechat');
            $transaction->rollback();
            return false;
        }
    }
}