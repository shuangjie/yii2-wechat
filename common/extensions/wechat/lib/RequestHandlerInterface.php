<?php
namespace common\extensions\wechat\lib;
/**
 * 微信请求处理接口类
 */
interface RequestHandlerInterface{

    /**
     * 文本消息
     * @param $request
     * @return array
     */
    public static function text(&$request);

    /**
     * 图片消息
     * @param $request
     * @return array
     */
    public static function image(&$request);

    /**
     * 语音消息
     * @param $request
     * @return array
     */
    public static function voice(&$request);

    /**
     * 视频消息
     * @param $request
     * @return array
     */
    public static function video(&$request);

    /**
     * 短视频消息
     * @param $request
     * @return array
     */
    public static function shortvideo(&$request);

    /**
     * 地理位置
     * @param $request
     * @return array
     */
    public static function location(&$request);

    /**
     * 链接
     * @param $request
     * @return array
     */
    public static function link(&$request);

    /**
     * 关注
     * @param $request
     * @return array
     */
    public static function eventSubscribe(&$request);

    /**
     * 取消关注
     * @param $request
     * @return array
     */
    public static function eventUnsubscribe(&$request);

    /**
     * 扫描二维码关注（未关注时）
     * @param $request
     * @return array
     */
    public static function eventQrsceneSubscribe(&$request);

    /**
     * 扫描二维码（已关注时）
     * @param $request
     * @return array
     */
    public static function eventScan(&$request);

    /**
     * 上报地理位置
     * @param $request
     * @return array
     */
    public static function eventLocation(&$request);

    /**
     * 自定义菜单 - 点击菜单拉取消息时的事件推送
     * @param $request
     * @return array
     */
    public static function eventClick(&$request);

    /**
     * 自定义菜单 - 点击菜单跳转链接时的事件推送
     * @param $request
     * @return array
     */
    public static function eventView(&$request);

    /**
     * 自定义菜单 - 扫码推事件的事件推送
     * @param $request
     * @return array
     */
    public static function eventScancodePush(&$request);

    /**
     * 自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
     * @param $request
     * @return array
     */
    public static function eventScancodeWaitMsg(&$request);

    /**
     * 自定义菜单 - 弹出系统拍照发图的事件推送
     * @param $request
     * @return array
     */
    public static function eventPicSysPhoto(&$request);

    /**
     * 自定义菜单 - 弹出拍照或者相册发图的事件推送
     * @param $request
     * @return array
     */
    public static function eventPicPhotoOrAlbum(&$request);

    /**
     * 自定义菜单 - 弹出微信相册发图器的事件推送
     * @param $request
     * @return array
     */
    public static function eventPicWeixin(&$request);

    /**
     * 自定义菜单 - 弹出地理位置选择器的事件推送
     * @param $request
     * @return array
     */
    public static function eventLocationSelect(&$request);

    /**
     * 群发接口完成后推送的结果
     *
     * 本消息有公众号群发助手的微信号“mphelper”推送的消息
     * @param $request
     */
    public static function eventMassSendJobFinish(&$request);

    /**
     * 群发接口完成后推送的结果
     *
     * 本消息有公众号群发助手的微信号“mphelper”推送的消息
     * @param $request
     */
    public static function eventTemplateSendJobFinish(&$request);



}
