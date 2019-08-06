<?php
namespace weapp\filters\auth;

use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

class SessionAuth extends AuthMethod {

    public $failureCallback;

    public function authenticate($user, $request, $response)
    {
        return $user->identity;
    }

    public function handleFailure($response)
    {
        if(isset($this->failureCallback)){
            call_user_func($this->failureCallback, $response);
        }else{
            throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
        }
    }

}