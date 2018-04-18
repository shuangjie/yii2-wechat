<?php
namespace common\extensions\weapp\lib;
/**
 * 素材管理
 */
class Material extends BaseClient {

    const MEDIA_TYPE_IMAGE = 'image';
    const MEDIA_TYPE_VOICE = 'voice';
    const MEDIA_TYPE_VIDEO = 'video';
    const MEDIA_TYPE_THUMB = 'thumb';
    static $media_type_config = [
        self::MEDIA_TYPE_IMAGE => '图片',
        self::MEDIA_TYPE_VOICE => '语音',
        self::MEDIA_TYPE_VIDEO => '视频',
        self::MEDIA_TYPE_THUMB => '缩略图',
    ];

    /**
     * 新增临时素材
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115&token=1343482436&lang=zh_CN
     * 公众号经常有需要用到一些临时性的多媒体素材的场景，例如在使用接口特别是发送消息时，对多媒体文件、多媒体消息的获取和调用等操作，是通过media_id来进行的。素材管理接口对所有认证的订阅号和服务号开放。通过本接口，公众号可以新增临时素材（即上传临时多媒体文件）。
     * 注意点：
     * 1、临时素材media_id是可复用的。
     * 2、媒体文件在微信后台保存时间为3天，即3天后media_id失效。
     * 3、上传临时素材的格式、大小限制与公众平台官网一致。
     * 图片（image）: 2M，支持PNG\JPEG\JPG\GIF格式
     * 语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
     * 视频（video）：10MB，支持MP4格式
     * 缩略图（thumb）：64KB，支持JPG格式
     * 4、需使用https调用本接口。
     * @param string $filename file path and name
     * @param string $type
     * @return mixed
     */
    public function upload($filename, $type){
        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->accessToken.'&type='.$type;
        $data = [
            'media' => new \CURLFile($filename)
        ];
        $method = 'POST';
        return HttpClient::api($url, $method, $data, ['Content-Type' => 'application/json; charset=utf-8']);

    }


    /**
     * 获取临时素材/高清语音素材获取接口
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115&token=1343482436&lang=zh_CN
     * 公众号可以使用本接口获取临时素材（即下载临时的多媒体文件）。请注意，视频文件不支持https下载，调用该接口需http协议。
     * 本接口即为原“下载多媒体文件”接口。
     *
     * 附录：高清语音素材获取接口
     * 公众号可以使用本接口获取从JSSDK的uploadVoice接口上传的临时语音素材，格式为speex，16K采样率。该音频比上文的临时素材获取接口（格式为amr，8K采样率）更加清晰，适合用作语音识别等对音质要求较高的业务。
     *
     * @param string $media_id 媒体文件ID
     * @param boolean $is_js_sdk 是否 js sdk 的高清语音素材
     *
     * @return mixed
     * //如果是视频消息素材
     * {
     *   "video_url":DOWN_URL
     * }
     */
    public function get($media_id, $is_js_sdk = false){
        $url = !$is_js_sdk ? 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='. $this->accessToken .'&media_id='.$media_id
                : 'https://api.weixin.qq.com/cgi-bin/media/get/jssdk?access_token='. $this->accessToken .'&media_id='.$media_id;
//        echo $url;exit;
        $data = [
            'media_id' => $media_id
        ];
        $method = "POST";
        return HttpClient::apiRaw($url, $method, $data);
    }

    //TODO
    //新增永久素材
    //获取永久素材
    //删除永久素材
    //修改永久图文素材
    //获取素材总数
    //获取素材列表


    public function testUpload($filename, $type){
        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->accessToken.'&type='.$type;
        return self::http_post($url,$filename);
    }

    /**
     *  Http Post
     * Curl 上传文件
     * @param string $url       接口链接
     * @param string $file_url   带路径的文件
     * @param [type]            [description]
     *
     * @return array $result
     */
    public static function http_post($url = '', $file_url = '')
    {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_SAFE_UPLOAD,true);
        $data = [
            'media' => new \CURLFile($file_url)
        ];
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        return json_decode($result,true);
    }


}
