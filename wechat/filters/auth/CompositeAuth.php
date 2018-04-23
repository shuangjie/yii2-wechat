<?php
namespace wechat\filters\auth;
use yii\web\UnauthorizedHttpException;

/**
 * 混合认证，增加认证失败回调
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 19:10
 */
class CompositeAuth extends \yii\filters\auth\CompositeAuth {

    /**
     * 认证失败回调
     * ```php
     * function ($response)
     * ```
     * */
    public $failureCallback;

    /**
     * @inheritdoc
     * 认证失败钩子。
     * 这里加入回调函数
     */
    public function handleFailure($response)
    {
        if(isset($this->failureCallback)){
            call_user_func($this->failureCallback, $response);
        }else{
            throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
        }
    }
}