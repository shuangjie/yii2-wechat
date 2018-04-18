<?php
namespace common\extensions\weapp\lib;
/**
 * 模板消息接口
 *
 * 模板消息仅用于公众号向用户发送重要的服务通知，只能用于符合其要求的服务场景中，如信用卡刷卡通知，商品购买成功通知等。不支持广告等营销类消息以及其它所有可能对用户造成骚扰的消息。
 * 关于使用规则，请注意：
 *  1、所有服务号都可以在功能->添加功能插件处看到申请模板消息功能的入口，但只有认证后的服务号才可以申请模板消息的使用权限并获得该权限；
 *  2、需要选择公众账号服务所处的2个行业，每月可更改1次所选行业；
 *  3、在所选择行业的模板库中选用已有的模板进行调用；
 *  4、每个账号可以同时使用15个模板。
 *  5、当前每个模板的日调用上限为100000次。
 * 关于接口文档，请注意：
 *  1、模板消息调用时主要需要模板ID和模板中各参数的赋值内容；
 *  2、模板中参数内容必须以".DATA"结尾，否则视为保留字；
 *  3、模板保留符号"{{ }}"。
 * @link http://mp.weixin.qq.com/wiki/17/304c1885ea66dbedf7dc170d84999a9d.html
 */

class TemplateMessage extends BaseClient {

    /**
     * 设置所属行业
     * @link http://mp.weixin.qq.com/wiki/17/304c1885ea66dbedf7dc170d84999a9d.html#.E8.AE.BE.E7.BD.AE.E6.89.80.E5.B1.9E.E8.A1.8C.E4.B8.9A
     * @param string $industryId1 公众号模板消息所属行业编号 请打开连接查看行业编号
     * @param string $industryId2 公众号模板消息所属行业编号
     * @return mixed
     */
    public function setIndustry($industryId1, $industryId2){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token='.$this->accessToken;
        $method = 'POST';
        $template = [];
        $template['industry_id1'] = strval($industryId1);
        $template['industry_id2'] = strval($industryId2);
        $data = json_encode($template);
        return HttpClient::api($queryUrl, $method, $data, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * 获得模板ID
     * @param string $templateIdShort 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     *
     * @return array ["errcode"=>0, "errmsg"=>"ok", "template_id":"Doclyl5uP7Aciu-qZ7mJNPtWkbkYnWBWVja26EGbNyk"] "errcode"是0则表示没有出错
     */
    public function getTemplateId($templateIdShort){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token='.$this->accessToken;
        $method = 'POST';
        $template = [];
        $template['template_id_short'] = strval($templateIdShort);
        $data = json_encode($template);
        return HttpClient::api($queryUrl, $method, $data, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * 向用户推送模板消息
     * @param array $data array(
     *                  'first'=>array('value'=>'您好，您已成功消费。', 'color'=>'#0A0A0A')
     *                  'keynote1'=>array('value'=>'巧克力', 'color'=>'#CCCCCC')
     *                  'keynote2'=>array('value'=>'39.8元', 'color'=>'#CCCCCC')
     *                  'keynote3'=>array('value'=>'2014年9月16日', 'color'=>'#CCCCCC')
     *                  'keynote3'=>array('value'=>'欢迎再次购买。', 'color'=>'#173177')
     * );
     * @param string $toUser 接收方的OpenId。
     * @param string $templateId 模板Id。在公众平台线上模板库中选用模板获得ID
     * @param string $url URL
     * @param string $topColor 顶部颜色， 可以为空。默认是红色
     * @return array("errcode"=>0, "errmsg"=>"ok", "msgid"=>200228332} "errcode"是0则表示没有出错
     *
     * 注意：推送后用户到底是否成功接受，微信会向公众号推送一个消息。
     */
    public function sendTemplateMessage($data, $toUser, $templateId, $url, $topColor='#FF0000'){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->accessToken;
        $method = 'POST';
        $template = [];
        $template['touser'] = $toUser;
        $template['template_id'] = $templateId;
        $template['url'] = $url;
        $template['topcolor'] = $topColor;
        $template['data'] = $this->formatMessage($data);
        $data = json_encode($template);
        return HttpClient::api($queryUrl, $method, $data, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * 向用户推送模板消息
     * @param array $data array(
     *                  'keyword1'=>array('value'=>'巧克力', 'color'=>'#CCCCCC')
     *                  'keyword2'=>array('value'=>'39.8元', 'color'=>'#CCCCCC')
     *                  'keyword3'=>array('value'=>'2014年9月16日', 'color'=>'#CCCCCC')
     *                  'keyword4'=>array('value'=>'欢迎再次购买。', 'color'=>'#173177')
     * );
     * @param string $toUser 接收方的OpenId。
     * @param string $templateId 模板Id。在公众平台线上模板库中选用模板获得ID
     * @param string $form_id
     * @param string $page PAGE 小程序内页面
     * @param string $color 模板内容字体的颜色，可以为空，不填默认黑色。
     * @param string $emphasis_keyword 模板需要放大的关键词，不填则默认无放大
     * @return array("errcode"=>0, "errmsg"=>"ok", "msgid"=>200228332} "errcode"是0则表示没有出错
     *
     * 注意：推送后用户到底是否成功接受，微信会向公众号推送一个消息。
     *
     *
     * {
    "touser": "OPENID",
    "template_id": "TEMPLATE_ID",
    "page": "index",
    "form_id": "FORMID",
    "data": {
    "keyword1": {
    "value": "339208499",
    "color": "#173177"
    },
    "keyword2": {
    "value": "2015年01月05日 12:30",
    "color": "#173177"
    },
    "keyword3": {
    "value": "粤海喜来登酒店",
    "color": "#173177"
    } ,
    "keyword4": {
    "value": "广州市天河区天河路208号",
    "color": "#173177"
    }
    },
    "emphasis_keyword": "keyword1.DATA"
    }
     */
    public function sendWxsTemplateMessage($data, $toUser, $templateId, $form_id, $page = '', $color='#FF0000',$emphasis_keyword=''){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->accessToken;
        $method = 'POST';
        $template = [];
        $template['touser'] = $toUser;
        $template['template_id'] = $templateId;
        $template['form_id'] = $form_id;
        $template['page'] = $page;
        $template['color'] = $color;
        $template['data'] = $this->formatMessage($data);
        $template['emphasis_keyword	'] = $emphasis_keyword;
        $data = json_encode($template);
        return HttpClient::api($queryUrl, $method, $data, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * 格式化模板消息内容
     * 将 [['k' => string ]] 转为 [['k' => array()]]
     * @param array $data
     * @return array
     */
    public function formatMessage($data){
        return array_map(function($value){
            !is_array($value) && $value = ['value' => $value];
            return $value;
        }, $data);
    }
}
