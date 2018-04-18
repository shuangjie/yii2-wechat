<?php
namespace common\extensions\wechat\lib;
use yii\base\InvalidParamException;

/**
 * 被动回复消息
 */
class ResponsePassive {


    /**
     * 文本消息
     * @param string $fromUsername 开发者微信号
     * @param string $toUsername 接收方帐号（收到的OpenID）
     * @param string $content  回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）
     * @return string
     * ```xml
     * <xml>
     * <ToUserName><![CDATA[toUser]]></ToUserName>
     * <FromUserName><![CDATA[fromUser]]></FromUserName>
     * <CreateTime>12345678</CreateTime>
     * <MsgType><![CDATA[text]]></MsgType>
     * <Content><![CDATA[你好]]></Content>
     * </xml>
     * ```
     */
    public static function text($fromUsername, $toUsername, $content){
        $params = [
            'ToUserName' => strval($toUsername),
            'FromUserName' => strval($fromUsername),
            'CreateTime' => time(),
            'MsgType' => 'text',
            'Content' => strval($content),
        ];
        return self::toXml($params);
    }


    /**
     * 图片消息
     * @param string $fromUsername 开发者微信号
     * @param string $toUsername 接收方帐号（收到的OpenID）
     * @param string $mediaId  微信media id
     * @return string
     *
     * demo
     * ```xml
     * <xml>
     * <ToUserName><![CDATA[toUser]]></ToUserName>
     * <FromUserName><![CDATA[fromUser]]></FromUserName>
     * <CreateTime>12345678</CreateTime>
     * <MsgType><![CDATA[image]]></MsgType>
     * <Image>
     * <MediaId><![CDATA[media_id]]></MediaId>
     * </Image>
     * </xml>
     * ```
     */
    public static function image($fromUsername, $toUsername, $mediaId){
        $params = [
            'ToUserName' => strval($toUsername),
            'FromUserName' => strval($fromUsername),
            'CreateTime' => time(),
            'MsgType' => 'image',
            'Image' => [
                'MediaId' => strval($mediaId),
            ]
        ];
        return self::toXml($params);
    }


    /**
     * 语音消息
     * @param string $fromUsername 开发者微信号
     * @param string $toUsername 接收方帐号（收到的OpenID）
     * @param string $mediaId  通过素材管理中的接口上传多媒体文件，得到的id
     * @return string
     *
     * demo
     * ```xml
     * <xml>
     * 	<ToUserName><![CDATA[toUser]]></ToUserName>
     * 	<FromUserName><![CDATA[fromUser]]></FromUserName>
     * 	<CreateTime>12345678</CreateTime>
     * 	<MsgType><![CDATA[voice]]></MsgType>
     * 	<Voice>
     * 	<MediaId><![CDATA[media_id]]></MediaId>
     * 	</Voice>
     * </xml>
     *```
     */
    public static function voice($fromUsername, $toUsername, $mediaId){
        $params = [
            'ToUserName' => strval($toUsername),
            'FromUserName' => strval($fromUsername),
            'CreateTime' => time(),
            'MsgType' => 'voice',
            'Voice' => [
                'MediaId' => strval($mediaId),
            ]
        ];
        return self::toXml($params);
    }

    /**
     * 视频消息
     * @param string $fromUsername 开发者微信号
     * @param string $toUsername 接收方帐号（收到的OpenID）
     * @param string $mediaId  通过素材管理中的接口上传多媒体文件，得到的id
     * @param string $title 视频消息的标题
     * @param string $description 视频消息的描述
     * @return string
     *
     * demo
     * ```xml
     * <xml>
     * 	<ToUserName><![CDATA[toUser]]></ToUserName>
     * 	<FromUserName><![CDATA[fromUser]]></FromUserName>
     * 	<CreateTime>12345678</CreateTime>
     * 	<MsgType><![CDATA[video]]></MsgType>
     * 	<Video>
     * 	<MediaId><![CDATA[media_id]]></MediaId>
     * 	<Title><![CDATA[title]]></Title>
     * 	<Description><![CDATA[description]]></Description>
     * 	</Video>
     * </xml>
     *```
     */
    public static function video($fromUsername, $toUsername, $mediaId, $title, $description){
        $params = [
            'ToUserName' => strval($toUsername),
            'FromUserName' => strval($fromUsername),
            'CreateTime' => time(),
            'MsgType' => 'voice',
            'Video' => [
                'MediaId' => strval($mediaId),
                'Title' => strval($title),
                'Description' => strval($description),
            ]
        ];
        return self::toXml($params);
    }

    /**
     * 音乐消息
     * @param string $fromUsername 开发者微信号
     * @param string $toUsername 接收方帐号（收到的OpenID）
     * @param string $title 音乐标题
     * @param string $description 音乐描述
     * @param string $musicUrl 音乐链接
     * @param string $hQMusicUrl 高质量音乐链接，WIFI环境优先使用该链接播放音乐
     * @param string $thumbMediaId 缩略图的媒体id，通过素材管理中的接口上传多媒体文件，得到的id
     * @return string
     *
     * demo
     * ```xml
     * <xml>
     * <ToUserName><![CDATA[toUser]]></ToUserName>
     * <FromUserName><![CDATA[fromUser]]></FromUserName>
     * <CreateTime>12345678</CreateTime>
     * <MsgType><![CDATA[music]]></MsgType>
     * <Music>
     * <Title><![CDATA[TITLE]]></Title>
     * <Description><![CDATA[DESCRIPTION]]></Description>
     * <MusicUrl><![CDATA[MUSIC_Url]]></MusicUrl>
     * <HQMusicUrl><![CDATA[HQ_MUSIC_Url]]></HQMusicUrl>
     * <ThumbMediaId><![CDATA[media_id]]></ThumbMediaId>
     * </Music>
     * </xml>
     *```
     */
    public static function music($fromUsername, $toUsername, $title, $description, $musicUrl, $hQMusicUrl, $thumbMediaId){
        $params = [
            'ToUserName' => strval($toUsername),
            'FromUserName' => strval($fromUsername),
            'CreateTime' => time(),
            'MsgType' => 'music',
            'Music' => [
                'Title' => strval($title),
                'Description' => strval($description),
                'MusicUrl' => strval($musicUrl),
                'HQMusicUrl' => strval($hQMusicUrl),
                'ThumbMediaId' => strval($thumbMediaId),
            ]
        ];
        return self::toXml($params);
    }

    /**
     * 图文消息
     * 多条图文消息信息，默认第一个item为大图,注意，如果图文数超过8，则将会无响应
     * @param string $fromUsername 开发者微信号
     * @param string $toUsername 接收方帐号（收到的OpenID）
     * @param self::newsItem()[]|array $items 音乐标题
     * @return string
     *
     * <xml>
     * <ToUserName><![CDATA[toUser]]></ToUserName>
     * <FromUserName><![CDATA[fromUser]]></FromUserName>
     * <CreateTime>12345678</CreateTime>
     * <MsgType><![CDATA[news]]></MsgType>
     * <ArticleCount>2</ArticleCount>
     * <Articles>
     * <item>
     * <Title><![CDATA[title1]]></Title>
     * <Description><![CDATA[description1]]></Description>
     * <PicUrl><![CDATA[picurl]]></PicUrl>
     * <Url><![CDATA[url]]></Url>
     * </item>
     * <item>
     * <Title><![CDATA[title]]></Title>
     * <Description><![CDATA[description]]></Description>
     * <PicUrl><![CDATA[picurl]]></PicUrl>
     * <Url><![CDATA[url]]></Url>
     * </item>
     * </Articles>
     * </xml>
     */
    public static function news($fromUsername, $toUsername, $items){
        $articleCount = count($items);
        if(!$articleCount){
            throw new InvalidParamException("消息不能为空", 1);
        }
        if($articleCount > 8){
            throw new InvalidParamException("图文消息个数不能超过8条", 1);
        }

        $articles = [];
        foreach($items as $item){
            is_string($item) && $articles[] = $item;
            is_array($item) && $articles[] = self::newsItem($item['title'], $item['description'],$item['picUrl'],$item['url']);
        }
        $articles = implode("\n", $articles);

        $params = [
            'ToUserName' => strval($toUsername),
            'FromUserName' => strval($fromUsername),
            'CreateTime' => time(),
            'MsgType' => 'news',
            'ArticleCount' => $articleCount,
            'Articles' => $articles,
        ];
        return self::toXml($params);
    }

    /**
     * 单条图文消息消息格式化
     * 用于 self::news() 中的消息条目
     * @param string $title 音乐标题
     * @param string $description 音乐描述
     * @param string $picUrl 图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200
     * @param string $url 点击图文消息跳转链接
     * @return string
     *
     * demo
     * ```xml
     * <item>
     * <Title><![CDATA[title]]></Title>
     * <Description><![CDATA[description]]></Description>
     * <PicUrl><![CDATA[picurl]]></PicUrl>
     * <Url><![CDATA[url]]></Url>
     * </item>
     * ```
     */
    public static function newsItem($title, $description, $picUrl, $url){
        $params = [
            'Title' => strval($title),
            'Description' => strval($description),
            'PicUrl' => strval($picUrl),
            'Url' => strval($url)
        ];
        return self::toXml($params, 'item');
    }


    /**
     * 将消息转发到多客服
     * 如果公众号处于开发模式，需要在接收到用户发送的消息时，返回一个MsgType为transfer_customer_service的消息，微信服务器在收到这条消息时，会把这次发送的消息转到多客服系统。用户被客服接入以后，客服关闭会话以前，处于会话过程中，用户发送的消息均会被直接转发至客服系统。
     * @param string $fromUsername 开发者微信号
     * @param string $toUsername 接收方帐号（收到的OpenID）
     * @return string
     * ```xml
     * <xml>
     * <ToUserName><![CDATA[%s]]></ToUserName>
     * <FromUserName><![CDATA[%s]]></FromUserName>
     * <CreateTime>%d</CreateTime>
     * <MsgType><![CDATA[transfer_customer_service]]></MsgType>
     * </xml>
     */
    public static function transferCustomerService($fromUsername, $toUsername){
        $params = [
            'ToUserName' => strval($toUsername),
            'FromUserName' => strval($fromUsername),
            'CreateTime' => time(),
            'MsgType' => 'transfer_customer_service',
        ];
        return self::toXml($params);
    }


    /**
     * return the params as a xml string.
     * @param array $params 参数
     * @param string|false $root 根标签。 false则不需要根标签
     * @return string|boolean the string representation of the collection.
     */
    public static function toXml($params, $root = "xml")
    {
        if(!is_array($params)
            || count($params) <= 0)
        {
            return false;
        }
        $xml = $root ? "<{$root}>" : "";
        foreach ($params as $key => $val)
        {
            if (is_numeric($val)){
                $xml .= "<".$key.">".$val."</".$key.">";
            }elseif(is_string($val)){
                $xml .= "<".$key."><![CDATA[".$val."]]></".$key.">";
            }elseif(is_array($val)){
                $xml .= self::toXml($val, $key);
            }
        }
        $xml .= $root ? "</{$root}>" : "";
        return $xml;
    }








}