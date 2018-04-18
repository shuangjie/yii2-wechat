<?php
namespace common\extensions\wechat\lib;

/**
 * 用户管理
 * @link https://mp.weixin.qq.com/wiki
 */
class UserManage extends BaseClient {

    /**
     * 创建分组
     * @param string $groupName 组名 UTF-8
     * @return string JSON {"group": {"id": 107,"name": "test"}}
     */
    public function createGroup($groupName){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/groups/create?access_token='.$this->accessToken;
        $data = '{"group":{"name":"'.$groupName.'"}}';
        return HttpClient::api($queryUrl, 'POST', $data, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * 获取分组列表
     * @return string JSON {"groups":[{"id": 0,"name": "未分组", "count": 72596}]}
     */
    public function getGroupList(){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token='.$this->accessToken;
        return HttpClient::api($queryUrl);
    }

    /**
     * 查询用户所在分组
     * @param string $openId 用户唯一OPENID
     * @return array JSON {"groupid": 102}
     */
    public function getGroupByOpenId($openId){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/groups/getid?access_token='.$this->accessToken;
        $data = '{"openid":"'.$openId.'"}';
        return HttpClient::api($queryUrl, 'POST', $data, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * 修改分组名
     * @param integer $groupId 要修改的分组ID
     * @param string $groupName 新分组名
     * @return string JSON {"errcode": 0, "errmsg": "ok"}
     */
    public function editGroupName($groupId, $groupName){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/groups/update?access_token='.$this->accessToken;
        $data = '{"group":{"id":'.$groupId.',"name":"'.$groupName.'"}}';
        return HttpClient::api($queryUrl, 'POST', $data, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * 移动用户分组
     * @param integer $openid 要移动的用户OpenId
     * @param integer $to_groupid 移动到新的组ID
     * @return string JSON {"errcode": 0, "errmsg": "ok"}
     */
    public function editUserGroup($openid, $to_groupid){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token='.$this->accessToken;
        $data = '{"openid":"'.$openid.'","to_groupid":'.$to_groupid.'}';
        return HttpClient::api($queryUrl, 'POST', $data, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    //-----------------------------用-------户-------管--------理----------------------

    /**
     * 获取用户基本信息
     * @param string $openId 用户唯一OpenId
     * @return array JSON {
    "subscribe": 1,    //用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息
    "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
    "nickname": "Band",
    "sex": 1,          //用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
    "language": "zh_CN",
    "city": "广州",
    "province": "广东",
    "country": "中国",
    "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
    "subscribe_time": 1382694957
    }
     */
    public function getUserInfo($openId){
        //获取ACCESS_TOKEN
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->accessToken.'&openid='.$openId.'&lang=zh_CN ';
        return HttpClient::api($queryUrl);
    }

    /**
     * 获取关注者列表
     * @param string $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
     * @return string JSON {"total":2,"count":2,"data":{"openid":["OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}
     */
    public function getFansList($next_openid=''){
        if(empty($next_openid)){
            $queryUrl = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->accessToken;
        }else{
            $queryUrl = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->accessToken.'&next_openid='.$next_openid;
        }
        return HttpClient::api($queryUrl);
    }

    /**
     * 设置备注名 开发者可以通过该接口对指定用户设置备注名，该接口暂时开放给微信认证的服务号。
     * @param string $openId 用户的openId
     * @param string $remark 备注名称
     * @return array('errorcode'=>0, 'errmsg'=>'ok') 正常时是0
    }
     */
    public function setRemark($openId, $remark){
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token='.$this->accessToken;
        $data = json_encode(array('openid'=>$openId, 'remark'=>$remark));
        return HttpClient::api($queryUrl, 'POST', $data, ['Content-Type' => 'application/json; charset=utf-8']);
    }

}