<?php
namespace weapp\filters\auth;

use yii\web\UnauthorizedHttpException;

class CompositeAuth extends \yii\filters\auth\CompositeAuth {

    public $failureCallback;

    public function handleFailure($response)
    {
        if(isset($this->failureCallback)){
            call_user_func($this->failureCallback, $response);
        }else{
            throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
        }
    }
}