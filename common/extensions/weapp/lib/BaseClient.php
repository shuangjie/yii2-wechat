<?php
namespace common\extensions\weapp\lib;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * 抽象基础client类
 *
 * @property string $accessToken BaseClient::$_accessToken
 */
abstract class BaseClient extends Component
{
    /**
     * @var string wxs access token
     * 非常重要的属性，没有这个所有接口都不能使用
     * */
    private $_accessToken;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if(!is_string($this->_accessToken)){
            //throw new InvalidConfigException("invalid access token");
            var_dump($this->_accessToken);
        }
    }

    /**
     * @param string $accessToken access token
     * @return string
     * */
    public function setAccessToken($accessToken){
        $this->_accessToken = $accessToken;
    }

    /**
     * @return string BaseClient::$_accessToken 通过魔术方法返回私有成员$_accessToken。
     * */
    public function getAccessToken(){
        return $this->_accessToken;
    }



}