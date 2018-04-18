<?php
namespace common\extensions\wechat\lib;

/**
 * 推广支持
 * 为了满足用户渠道推广分析和用户帐号绑定等场景的需要，公众平台提供了生成带参数二维码的接口。使用该接口可以获得多个带不同场景值的二维码，用户扫描后，公众号可以接收到事件推送。
 * 目前有2种类型的二维码：
 * 1、临时二维码，是有过期时间的，最长可以设置为在二维码生成后的30天（即2592000秒）后过期，但能够生成较多数量。临时二维码主要用于帐号绑定等不要求二维码永久保存的业务场景
 * 2、永久二维码，是无过期时间的，但数量较少（目前为最多10万个）。永久二维码主要用于适用于帐号绑定、用户来源统计等场景。
 * 用户扫描带场景值二维码时，可能推送以下两种事件：
 * 如果用户还未关注公众号，则用户可以关注公众号，关注后微信会将带场景值关注事件推送给开发者。
 * 如果用户已经关注公众号，在用户扫描后会自动进入会话，微信也会将带场景值扫描事件推送给开发者。
 */
class Promotion extends BaseClient {

    /**
     * 二维码类型
     */
    const QR_SCENE = 'QR_SCENE';
    const QR_LIMIT_SCENE = 'QR_LIMIT_SCENE';
    const QR_LIMIT_STR_SCENE = 'QR_LIMIT_STR_SCENE';
    static $qr_config = [
        self::QR_SCENE => '临时',
        self::QR_LIMIT_SCENE => '永久',
        self::QR_LIMIT_STR_SCENE => '永久字符串',
    ];

    /**
     *
     * 创建二维码ticket
     * 生成带参数二维码 - 第一步
     * 每次创建二维码ticket需要提供一个开发者自行设定的参数（scene_id）
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433542&token=&lang=zh_CN
     *
     * 1临时二维码请求说明
     * ```
     * http请求方式: POST
     * URL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKENPOST数据格式：json
     * POST数据例子：{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
     * ```
     * 2永久二维码请求说明
     * ```
     * http请求方式: POST
     * RL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKENPOST数据格式：json
     * POST数据例子：{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}
     * 或者也可以使用以下POST数据创建字符串形式的二维码参数：
     * {"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "123"}}}
     * ```
     *  @param string $type
     * @param integer|string $sceneId  类型为 QR_LIMIT_STR_SCENE 时为字符串
     * @param integer $expireSeconds  过期时间  最长为30天即2592000秒。仅对临时二维码有效
     * @return array
     * {"ticket":"gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2taZ2Z3TVRtNzJXV1Brb3ZhYmJJAAIEZ23sUwMEmm3sUw==","expire_seconds":60,"url":"http:\/\/weixin.qq.com\/q\/kZgfwMTm72WWPkovabbI"}
     * ticket	获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
     * expire_seconds	该二维码有效时间，以秒为单位。 最大不超过2592000（即30天）。
     * url	二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片
     */
    public function creteTicket($type, $sceneId, $expireSeconds = 2592000){
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
        $method = 'POST';
        $data = [];
        if($type == self::QR_SCENE){
            $data['expire_seconds'] = $expireSeconds;
            $data['action_name'] = self::QR_SCENE;
            $data['action_info']['scene']['scene_id'] = $sceneId;
        }elseif($type == self::QR_LIMIT_STR_SCENE){ //永久字符串
            $data['action_name'] = self::QR_LIMIT_STR_SCENE;
            $data['action_info']['scene']['scene_str'] = $sceneId;
        }else{ //默认永久
            $data['action_name'] = self::QR_LIMIT_SCENE;
            $data['action_info']['scene']['scene_id'] = $sceneId;
        }
        return HttpClient::api($url, $method, json_encode($data), ['Content-Type' => 'application/json; charset=utf-8']);
    }


    /**
     * 通过ticket换取二维码
     * 生成带参数的二维码 - 第二步
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433542&token=&lang=zh_CN
     * @param string $ticket self::createTicket()获得的
     * @param $filename String 文件路径，如果不为空，则会创建一个图片文件，二维码文件为jpg格式，保存到指定的路径
     * @return array   header 头信息， body 图片内容，保存到文件里就是二维码图片
     */
    public function getQrcode($ticket, $filename=''){
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
        $result = HttpClient::apiRaw($url);
        if(!empty($filename)){
            file_put_contents($filename, $result['body']);
        }
        return $result;
    }


    /**
     * 长链接转短链接接口
     * 将一条长链接转成短链接。
     * 主要使用场景：开发者用于生成二维码的原链接（商品、支付二维码等）太长导致扫码速度和成功率下降，将原长链接通过此接口转成短链接再生成二维码将大大提升扫码速度和成功率。
     * @param String $longUrl  需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url
     * @return array array('errcode'=>0, 'errmsg'=>'错误信息', 'short_url'=>'http://t.cn/asdasd')错误码为0表示正常
     */
    public function long2short($longUrl){
        $url = 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token='.$this->getAccessToken();
        $method = 'POST';
        $data = [];
        $data['long_url'] = $longUrl;
        $data['action'] = 'long2short';
        return HttpClient::api($url, $method, json_encode($data), ['Content-Type' => 'application/json; charset=utf-8']);
    }


}
