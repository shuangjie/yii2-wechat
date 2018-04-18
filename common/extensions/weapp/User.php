<?php
namespace common\extensions\wxs;
use common\models\Auth;
use Yii;
use yii\base\Exception;

/**
 * 用户类
 */
class User extends \common\models\User {
    /**
     * @inheritdoc
     * 根据微信openid查找
     * @param string $openId
     * @return \common\models\User|null
     */
    public static function findIdentityByWeappOpenId($openId)
    {
        $auth = Auth::findOne([
            'source' => Auth::SOURCE_WXS,
            'source_id' => $openId
        ]);
        return $auth ? $auth->user : null;
    }

    /**
     * @inheritdoc
     * 用微信用户信息生成用户
     * 针对用户授权操作有效
     * @param array $info 用户openId
     * @return \common\models\User|boolean
     * */
    public static function SignUpByWeappOAuth($info){
        if($info){
            $user = self::findIdentityByWeappOpenId($info['openId']);
            if($user) return $user;
        }
        //没有则注册
        $user = self::signUp($info);
        return $user;
    }



    /**
     * @param array $userInfo 通过 UserManage::getUserInfo() 或者 OAuth::getUserInfo()获得的用户信息
     * 用户信息字段如下：
     * {
    "openId": "OPENID",
    "nickName": "NICKNAME",
    "gender": GENDER,
    "city": "CITY",
    "province": "PROVINCE",
    "country": "COUNTRY",
    "avatarUrl": "AVATARURL",
    "unionId": "UNIONID",
    "watermark":
    {
    "appid":"APPID",
    "timestamp":TIMESTAMP
    }
    }
     * @return \common\models\User|boolean
     * */
    public static function signUp($userInfo){
        $openId = $userInfo['openId'];
        //注册用户
        $connection = \common\models\User::getDb();
        $transaction = $connection->beginTransaction();
        try{
            $user = new \common\models\User();
            $user->username = $user->nickname = $userInfo['nickName']; //注意emoji
            $user->sex = $userInfo['gender'];
            $user->language = $userInfo['language'];
            $user->city = $userInfo['city'];
            $user->province = $userInfo['province'];
            $user->country = $userInfo['country'];
            $user->avatar = $userInfo['avatarUrl']; //TODO 应该下载
            $user->source = \common\models\User::SOURCE_WXAPP;
            $user->setPassword(Yii::$app->security->generateRandomString(8));
            $user->generateAuthKey();
            if(!$user->save()){
                Yii::error("error to create user ".print_r($user->attributes, true). ", errors".print_r($user->errors, true), 'weapp');
                return false;
            }
            $auth = new Auth([
                'user_id' => $user->id,
                'source' => Auth::SOURCE_WXS,
                'source_id' => $openId,
            ]);
            if($auth->save()){
                $transaction->commit();
                return $user;
            }else{
                Yii::error("error to create auth ".print_r($auth->attributes, true). ", errors".print_r($auth->errors, true), 'weapp');
                $transaction->rollback();
                return false;
            }
        }catch(Exception $e){
            Yii::error($e->getMessage(), 'weapp');
            $transaction->rollback();
            return false;
        }
    }
}