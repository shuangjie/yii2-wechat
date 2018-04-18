<?php
namespace common\services\notify;
use common\extensions\wechat\Wechat;
use common\extensions\weapp\Weapp;
use yii\base\Component;
use Yii;
/**
 * 微信模板消息
 */
class TemplateMessageService extends Component {

    /**
     * @var Wechat
     */
    private $_wechat;
    /**
     * @var Weapp
     */
    private $weapp;

    public function setWechat($wechat){
        $this->_wechat = $wechat;
    }

    public function setWxs($wxs){
        $this->weapp = $wxs;
    }

    /**
     * @return Wechat
     */
    public function getWechat(){
        is_null($this->_wechat) && $this->_wechat = Yii::$app->wechat;
        return $this->_wechat;
    }

    /**
     * @return Weapp
     */
    public function getWxs(){
        is_null($this->weapp) && $this->weapp = Yii::$app->weapp;
        return $this->weapp;
    }


    /**
     * 小程序
     * 支付通知
     * ```template
     * 物品名称：{{keyword1.DATA}}
     * 付款时间：{{keyword2.DATA}}
     * 付款金额：{{keyword3.DATA}}
     * ```
     * @param string $openId
     * @param string $formid
     * @param $messageContent [['keyword1' => string, 'keyword2' => string, 'keyword3' => string]]
     * @param string $page
     * @return array
     */
    public function payNotify($openId, $messageContent,$formid,$page = 'index'){
        $template_id = Yii::$app->params['weappTemplateId.pay.notify'];
        return $this->getWxs()->getTemplateMessage()->sendWxsTemplateMessage($messageContent, $openId, $template_id, $formid,$page);
    }


}