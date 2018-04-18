<?php

namespace common\extensions\wechat\lib;
use Yii;
use yii\base\Component;
use yii\di\Instance;
use yii\helpers\FileHelper;
use yii\log\FileTarget;
use yii\log\Logger;
use yii\web\ForbiddenHttpException;

class Request extends Component {

    /**
     * @var RequestHandlerInterface $handler
     */
    private $_handler; //请求处理类

    private $_body;
    private $_content;
    private $_token;

    public function setToken($token){
        $this->_token = $token;
    }

    /**
     * @return RequestHandlerInterface
     */
    public function getHandler(){
        if(is_string($this->_handler) || is_array($this->_handler)){
            $this->_handler = Instance::ensure($this->_handler);
        }
        return $this->_handler;
    }

    public function setHandler($handler){
        $this->_handler = $handler;
    }

    /**
     * 判断此次请求是否为验证请求
     * @return boolean
     */
    private function isValid() {
        return !is_null(Yii::$app->request->get('echostr'));
    }

    /**
     * 判断验证请求的签名信息是否正确
     * @param  string $token 验证信息
     * @return boolean
     */
    private function validateSignature($token = '') {
        !$token && $token = $this->_token;
        $signature = Yii::$app->request->get('signature');
        $timestamp = Yii::$app->request->get('timestamp');
        $nonce = Yii::$app->request->get('nonce');   //
        $signatureArray = [$token, $timestamp, $nonce];
        sort($signatureArray, SORT_STRING);
        return sha1(implode($signatureArray)) == $signature;
    }

    /**
     * 处理和分发微信请求
     */
    public function handle(){
        $this->recordLog("RAW:".Yii::$app->request->rawBody."GET:".print_r(Yii::$app->request->get(),true));
        if ($this->isValid()) {
            if($this->validateSignature()) echo Yii::$app->request->get('echostr');
            return null;
        }
        //不允许无raw body请求
        if(empty(Yii::$app->request->rawBody)){
            throw new ForbiddenHttpException();
        }

        //接受并解析微信中心POST发送XML数据
        $this->_body = (array) simplexml_load_string(Yii::$app->request->rawBody, 'SimpleXMLElement', LIBXML_NOCDATA);
        //将数组键名转换为小写
        $this->_content = array_change_key_case($this->_body, CASE_LOWER);
        //TODO 分发请求
        $res = $this->switchType($this->_content);
        echo $res;
        $this->recordLog("response:".$res);
    }

    public function recordLog($msg){
        //把微信推送的消息写到日志里
        $time = microtime(true);
        $log = new FileTarget();
        $cachePath =  Yii::$app->getRuntimePath() . '/logs/wechat/'.date("ym").'/'.date("d");
        FileHelper::createDirectory($cachePath);
        $log->logFile = $cachePath . '/request.log';
        $log->messages[] = [$msg, Logger::LEVEL_INFO, 'wechat', $time];
        $log->export();
    }


    /**
     * @descrpition 分发请求
     * @param $request
     * @return array|string
     */
    public function switchType(&$request){
        $data = array();
        $handler = $this->getHandler();
        switch ($request['msgtype']) {
            //事件
            case 'event':
                $request['event'] = strtolower($request['event']);
                switch ($request['event']) {
                    //关注
                    case 'subscribe':
                        //二维码关注
                        if(isset($request['eventkey']) && isset($request['ticket'])){
                            $data = $handler->eventQrsceneSubscribe($request);
                            //普通关注
                        }else{
                            $data = $handler->eventSubscribe($request);
                        }
                        break;
                    //扫描二维码
                    case 'scan':
                        if(isset($request['eventkey']) && isset($request['ticket'])){
                            $data = $handler->eventQrsceneScan($request);
                        }else{
                            $data = $handler->eventScan($request);
                        }

                        break;
                    //地理位置
                    case 'location':
                        $data = $handler->eventLocation($request);
                        break;
                    //自定义菜单 - 点击菜单拉取消息时的事件推送
                    case 'click':
                        $data = $handler->eventClick($request);
                        break;
                    //自定义菜单 - 点击菜单跳转链接时的事件推送
                    case 'view':
                        $data = $handler->eventView($request);
                        break;
                    //自定义菜单 - 扫码推事件的事件推送
                    case 'scancode_push':
                        $data = $handler->eventScancodePush($request);
                        break;
                    //自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
                    case 'scancode_waitmsg':
                        $data = $handler->eventScancodeWaitMsg($request);
                        break;
                    //自定义菜单 - 弹出系统拍照发图的事件推送
                    case 'pic_sysphoto':
                        $data = $handler->eventPicSysPhoto($request);
                        break;
                    //自定义菜单 - 弹出拍照或者相册发图的事件推送
                    case 'pic_photo_or_album':
                        $data = $handler->eventPicPhotoOrAlbum($request);
                        break;
                    //自定义菜单 - 弹出微信相册发图器的事件推送
                    case 'pic_weixin':
                        $data = $handler->eventPicWeixin($request);
                        break;
                    //自定义菜单 - 弹出地理位置选择器的事件推送
                    case 'location_select':
                        $data = $handler->eventLocationSelect($request);
                        break;
                    //取消关注
                    case 'unsubscribe':
                        $data = $handler->eventUnsubscribe($request);
                        break;
                    //群发接口完成后推送的结果
                    case 'masssendjobfinish':
                        $data = $handler->eventMassSendJobFinish($request);
                        break;
                    //模板消息完成后推送的结果
                    case 'templatesendjobfinish':
                        $data = $handler->eventTemplateSendJobFinish($request);
                        break;
                    default:
                        break;
                }
                break;
            //文本
            case 'text':
                $data = $handler->text($request);
                break;
            //图像
            case 'image':
                $data = $handler->image($request);
                break;
            //语音
            case 'voice':
                $data = $handler->voice($request);
                break;
            //视频
            case 'video':
                $data = $handler->video($request);
                break;
            //小视频
            case 'shortvideo':
                $data = $handler->shortvideo($request);
                break;
            //位置
            case 'location':
                $data = $handler->location($request);
                break;
            //链接
            case 'link':
                $data = $handler->link($request);
                break;
            default:
                return ResponsePassive::text($request['fromusername'], $request['tousername'], '收到未知的消息');
                break;
        }
        return $data;
    }




}
