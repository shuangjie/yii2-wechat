<?php
namespace weapp\filters\auth;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

/**
 * SessionAuth is an action filter that supports the authentication based on the identity passed through session id.
 * session方式进行认证
 * RESTful APIs 本应该是无状态的（stateless），这意味着不能使用 sessions 或 cookies。
 * 每个请求应该要携带某种认证证书来实现访问的安全性控制。一个通用的方法是在每个请求中发送一个秘密访问令牌（secret access token）来进行用户认证。由于一个访问令牌可以被用来唯一的识别和认证一个用户，API 请求应该总是通过 HTTPS 发送以防止 中间人（man-in-the-middle (MitM)）攻击
 * 但是我们给浏览器的接口中有些接口需要涉及session认证。这里扩展一下，供这些业务使用。
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 19:18
 */
class SessionAuth extends AuthMethod {

    /**
     * 认证失败回调
     * ```php
     * function ($response)
     * ```
     * */
    public $failureCallback;


    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        return $user->identity;
    }

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