<?php
namespace common\extensions\wechatpay\lib;
use yii\base\Component;

abstract class Notify extends Component {

    /**
     * @var WechatPayApi
     */
    public $apiCli;

    final public function handle($needSign = true){
        $msg = "OK";
        //当返回false的时候，表示notify中调用NotifyCallBack回调失败获取签名校验失败，此时直接回复失败
        $result = $this->apiCli->notify([$this, 'notifyCallBack'], $msg);
        $values = $this->apiCli->getValues();
        if($result == false){
            $values->set('return_code', 'FAIL');
            $values->set('return_msg', $msg);
            $this->replyNotify(false);
            return;
        } else {
            //该分支在成功回调到NotifyCallBack方法，处理完成之后流程
            $values->set('return_code', 'SUCCESS');
            $values->set('return_msg',  'OK');
        }
        $this->replyNotify($needSign);

    }


}