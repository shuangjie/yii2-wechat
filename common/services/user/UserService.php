<?php
namespace common\services\user;
use common\extensions\wechat\Wechat;
use Yii;

class UserService {

    /**
     * @param $user \common\models\User
     * @return bool
     */
    public static function isSubscribeWechatAccount($user){
        /* @var Wechat $wechat */
        $wechat = Yii::$app->wechat;
        if(is_null($user->wechatAuth)){
            return false;
        }
        $wechatInfo = $wechat->getUserManage()->getUserInfo($user->wechatAuth->source_id);
        if(isset($wechatInfo['errcode'])){
            $errorMsg = "get user info error:".print_r($wechatInfo, true).". open id={$user->wechatAuth->source_id}";
            $wechatInfo['errcode'] == '40001' && $errorMsg .= ". access Token =".$wechat->getAccessToken();
            Yii::error($errorMsg);
            return false;
        }
        return $wechatInfo['subscribe'] == 1;
    }

}