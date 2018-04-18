<?php

namespace common\extensions\aliyunOss;

use OSS\OssClient;
use yii\base\Component;
/**
 * 阿里云oss扩展
 *
 * @param string $accessKeyId 从OSS获得的AccessKeyId
 * @param string $accessKeySecret 从OSS获得的AccessKeySecret
 * @param string $endpoint 您选定的OSS数据中心访问域名，例如oss-cn-hangzhou.aliyuncs.com
 *
 * @property OssClient $client 阿里云oss客户端
 * */
class AliyunOssExtension extends Component{

    public $accessKeyId;
    public $accessKeySecret;
    public $endpoint;
    /* @var OssClient */
    private $_client;

    public function init()
    {
        parent::init();
        $this->_client = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
    }

    /**
     * @return OssClient
     */
    public function getClient(){
        return $this->_client;
    }

    public function __call($name, $params)
    {
        return call_user_func_array(
            array($this->_client, $name),
            $params
        );
    }


}