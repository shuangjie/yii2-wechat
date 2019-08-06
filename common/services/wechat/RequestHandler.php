<?php
namespace common\services\wechat;

use common\extensions\wechat\lib\RequestHandlerInterface;
use common\extensions\wechat\lib\ResponsePassive;
use common\extensions\wechat\User;
use Yii;


/**
 * 微信请求处理
 * 普通消息和事件推送
 * 普通消息包括：文本、图片、语音、视频、小视频、地理位置、链接
 * 事件包括：关注/取消关注、扫描带参数二维码、上报地理位置、自定义菜单、点击菜单拉取消息的事件、点击菜单跳转链接时的事件
 */
class RequestHandler implements RequestHandlerInterface {

    /**
     * 文本
     * @param $request
     * @return array
     */
    public static function text(&$request){
        //转发到客服
        return ResponsePassive::transferCustomerService($request['tousername'], $request['fromusername']);
//        $content = '收到文本消息';
//        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 图像
     * @param $request
     * @return array
     */
    public static function image(&$request){
        //转发到客服
        return ResponsePassive::transferCustomerService($request['tousername'], $request['fromusername']);
//        $content = '收到图片';
//        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 语音
     * @param $request
     * @return array
     */
    public static function voice(&$request){
        if(!isset($request['recognition'])){
            $content = '收到语音';
            return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
        }else{
            $content = '收到语音识别消息，语音识别结果为：'.$request['recognition'];
            return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
        }
    }

    /**
     * 视频
     * @param $request
     * @return array
     */
    public static function video(&$request){
        $content = '收到视频';
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 视频
     * @param $request
     * @return array
     */
    public static function shortvideo(&$request){
        $content = '收到小视频';
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 地理
     * @param $request
     * @return array
     */
    public static function location(&$request){
        $content = '收到上报的地理位置';
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 链接
     * @param $request
     * @return array
     */
    public static function link(&$request){
        //转发到客服
        //return ResponsePassive::transferCustomerService($request['tousername'], $request['fromusername']);
        $content = '系统已为您接入人工服务，请您耐心等待';
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 关注
     * @param $request
     * @return array
     */
    public static function eventSubscribe(&$request){
        $content = '欢迎您关注我们';
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 取消关注
     * @param $request
     * @return array
     */
    public static function eventUnsubscribe(&$request){
        $content = '取消关注';
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 扫描带参数二维码（未关注）
     * @param $request
     * @return array
     */
    public static function eventQrsceneSubscribe(&$request){
        $sceneId = str_replace("qrscene_","",$request['eventkey']);
        $params = Yii::$app->params;
        $content = '欢迎您关注我们';
        //注册用户
        $user = User::SignUpByWechatOpenId($request['fromusername']);
    
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * 扫描二维码（已关注时）
     * @param $request
     * @return array
     */
    public static function eventScan(&$request){
        $content = '您已经关注了';
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 扫描带参数二维码（已关注时）
     * @param $request
     * @return array
     */
    public static function eventQrsceneScan(&$request){
        $sceneId = $request['eventkey'];
        $params = Yii::$app->params;
        //注册用户
        $user = User::SignUpByWechatOpenId($request['fromusername']);
        //根据情景id，执行相应操作
        $content = "";
        // Do something
        if($content){
            return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
        }
    }

    /**
     * 上报地理位置
     * @param $request
     * @return array
     */
    public static function eventLocation(&$request){
        $content = '收到上报的地理位置';
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 自定义菜单 - 点击菜单拉取消息时的事件推送
     * @param $request
     * @return array
     */
    public static function eventClick(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        if($eventKey == 'question'){
            //用户反馈
            $content = '您好，请将您遇到的问题以文字或截图的形式发送给我们；收到反馈后，客服人员会及时的与您取得联系~';
            return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
        }elseif ($eventKey == 'contect'){
            return ResponsePassive::transferCustomerService($request['tousername'], $request['fromusername']);
        }else{
            $content = '收到点击菜单事件，您设置的key是' . $eventKey;
            return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
        }
    }

    /**
     * 自定义菜单 - 点击菜单跳转链接时的事件推送
     * @param $request
     * @return array
     */
    public static function eventView(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到跳转链接事件，您设置的key是' . $eventKey;
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 自定义菜单 - 扫码推事件的事件推送
     * @param $request
     * @return array
     */
    public static function eventScancodePush(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到扫码推事件的事件，您设置的key是' . $eventKey;
        $content .= '。扫描信息：'.$request['scancodeinfo'];
        $content .= '。扫描类型(一般是qrcode)：'.$request['scantype'];
        $content .= '。扫描结果(二维码对应的字符串信息)：'.$request['scanresult'];
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
     * @param $request
     * @return array
     */
    public static function eventScancodeWaitMsg(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到扫码推事件且弹出“消息接收中”提示框的事件，您设置的key是' . $eventKey;
        $content .= '。扫描信息：'.$request['scancodeinfo'];
        $content .= '。扫描类型(一般是qrcode)：'.$request['scantype'];
        $content .= '。扫描结果(二维码对应的字符串信息)：'.$request['scanresult'];
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 自定义菜单 - 弹出系统拍照发图的事件推送
     * @param $request
     * @return array
     */
    public static function eventPicSysPhoto(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到弹出系统拍照发图的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：'.$request['sendpicsinfo'];
        $content .= '。发送的图片数量：'.$request['count'];
        $content .= '。图片列表：'.$request['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$request['picmd5sum'];
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 自定义菜单 - 弹出拍照或者相册发图的事件推送
     * @param $request
     * @return array
     */
    public static function eventPicPhotoOrAlbum(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到弹出拍照或者相册发图的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：'.$request['sendpicsinfo'];
        $content .= '。发送的图片数量：'.$request['count'];
        $content .= '。图片列表：'.$request['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$request['picmd5sum'];
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 自定义菜单 - 弹出微信相册发图器的事件推送
     * @param $request
     * @return array
     */
    public static function eventPicWeixin(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到弹出微信相册发图器的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：'.$request['sendpicsinfo'];
        $content .= '。发送的图片数量：'.$request['count'];
        $content .= '。图片列表：'.$request['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$request['picmd5sum'];
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 自定义菜单 - 弹出地理位置选择器的事件推送
     * @param $request
     * @return array
     */
    public static function eventLocationSelect(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到点击跳转事件，您设置的key是' . $eventKey;
        $content .= '。发送的位置信息：'.$request['sendlocationinfo'];
        $content .= '。X坐标信息：'.$request['location_x'];
        $content .= '。Y坐标信息：'.$request['location_y'];
        $content .= '。精度(可理解为精度或者比例尺、越精细的话 scale越高)：'.$request['scale'];
        $content .= '。地理位置的字符串信息：'.$request['label'];
        $content .= '。朋友圈POI的名字，可能为空：'.$request['poiname'];
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 群发接口完成后推送的结果
     *
     * 本消息有公众号群发助手的微信号“mphelper”推送的消息
     * @param $request
     * @return array
     */
    public static function eventMassSendJobFinish(&$request){
        //发送状态，为“send success”或“send fail”或“err(num)”。但send success时，也有可能因用户拒收公众号的消息、系统错误等原因造成少量用户接收失败。err(num)是审核失败的具体原因，可能的情况如下：err(10001), //涉嫌广告 err(20001), //涉嫌政治 err(20004), //涉嫌社会 err(20002), //涉嫌色情 err(20006), //涉嫌违法犯罪 err(20008), //涉嫌欺诈 err(20013), //涉嫌版权 err(22000), //涉嫌互推(互相宣传) err(21000), //涉嫌其他
        $status = $request['status'];
        //计划发送的总粉丝数。group_id下粉丝数；或者openid_list中的粉丝数
        $totalCount = $request['totalcount'];
        //过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数，原则上，FilterCount = SentCount + ErrorCount
//        $filterCount = $request['filtercount'];
        //发送成功的粉丝数
        $sentCount = $request['sentcount'];
        //发送失败的粉丝数
        $errorCount = $request['errorcount'];
        $content = '发送完成，状态是'.$status.'。计划发送总粉丝数为'.$totalCount.'。发送成功'.$sentCount.'人，发送失败'.$errorCount.'人。';
        return ResponsePassive::text($request['tousername'], $request['fromusername'], $content);
    }

    /**
     * 群发接口完成后推送的结果
     *
     * 本消息有公众号群发助手的微信号“mphelper”推送的消息
     * @param $request
     * @return bool
     */
    public static function eventTemplateSendJobFinish(&$request){
        //发送状态，成功success，用户拒收failed:user block，其他原因发送失败failed: system failed
        $status = $request['status'];
        if($status == 'success'){
            //发送成功
        }else if($status == 'failed:user block'){
            //因为用户拒收而发送失败
        }else if($status == 'failed: system failed'){
            //其他原因发送失败
        }
        return true;
    }






}

