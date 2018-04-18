<?php
namespace common\extensions\weapp\lib;
use yii\authclient\InvalidResponseException;
use yii\httpclient\Client;
use yii\di\Instance;

/**
 * Created by PhpStorm.
 * http客户端，依赖于 yii2-httpClient
 * TODO 实际接口请求好像没有那么复杂，自己做简单的curl封装就可以了。
 */
class HttpClient{

    /**
     * @var Client|array|string internal HTTP client.
     * @since 2.1
     */
    private $_httpClient = 'yii\httpclient\Client';
    /**
     * @var array cURL request options. Option values from this field will overwrite corresponding
     * values from [[defaultRequestOptions()]].
     */
    private $_requestOptions = [];

    /**
     * 构造函数
     * */
    public function __construct()
    {
//        return $this->getHttpClient();
    }

    /**
     * Returns HTTP client.
     * @return Client internal HTTP client.
     */
    public function getHttpClient()
    {
        if (!is_object($this->_httpClient)) {
            $this->_httpClient = $this->createHttpClient($this->_httpClient);
        }
        return $this->_httpClient;
    }



    /**
     * Creates HTTP client instance from reference or configuration.
     * @param string|array $reference component name or array configuration.
     * @return Client HTTP client instance.
     */
    protected function createHttpClient($reference)
    {
        return Instance::ensure($reference, Client::className());
    }

    /**
     * Sets HTTP client to be used.
     * @param array|Client $httpClient internal HTTP client.
     */
    public function setHttpClient($httpClient)
    {
        $this->_httpClient = $httpClient;
    }

    /**
     * @param array $options HTTP request options.
     */
    public function setRequestOptions(array $options)
    {
        $this->_requestOptions = $options;
    }

    /**
     * @return array HTTP request options.
     * @since 2.1
     */
    public function getRequestOptions()
    {
        return $this->_requestOptions;
    }

    /**
     * Creates HTTP request instance.
     * @return \yii\httpclient\Request HTTP request instance.
     */
    public function createRequest()
    {
        return $this->getHttpClient()
            ->createRequest()
            ->addOptions($this->defaultRequestOptions())
            ->addOptions($this->getRequestOptions());
    }

    /**
     * Returns default HTTP request options.
     * @return array HTTP request options.
     */
    protected function defaultRequestOptions()
    {
        return [
            'timeout' => 30,
            'sslVerifyPeer' => false,
        ];
    }

    /**
     * @var self 对象单例
     * */
    public static $_instance;
    public static function getInstance()
    {
        if(!(self::$_instance instanceof self))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Performs request to the OAuth API returning response data.
     * @param string $apiSubUrl API sub URL, which will be append to [[apiBaseUrl]], or absolute API URL.
     * @param string $method request method.
     * @param array|string $data request data or content.
     * @param array $headers additional request headers.
     * @return array API response data.
     */
    public static function api($apiSubUrl, $method = 'GET', $data = [], $headers = [])
    {
        $client = self::getInstance();
        $request = $client->createRequest()
            ->setMethod($method)
            ->setUrl($apiSubUrl)
            ->addHeaders($headers);

        if (!empty($data)) {
            if (is_array($data)) {
                $request->setData($data);
            } else {
                $request->setContent($data);
            }
        }
        $ret = $client->sendRequest($request);
        return $ret;
    }

    /**
     * 不经过parser
     * Performs request to the OAuth API returning response data.
     * @param string $apiSubUrl API sub URL, which will be append to [[apiBaseUrl]], or absolute API URL.
     * @param string $method request method.
     * @param array|string $data request data or content.
     * @param array $headers additional request headers.
     * @return array|boolean API response data.
     * [[
     *  'header' => Array(),
     *  'body' => String
     * ]]
     */
    public static function apiRaw($apiSubUrl, $method = 'GET', $data = [], $headers = []){
        $client = self::getInstance();
        $request = $client->createRequest()
            ->setMethod($method)
            ->setUrl($apiSubUrl)
            ->addHeaders($headers);

        if (!empty($data)) {
            if (is_array($data)) {
                $request->setData($data);
            } else {
                $request->setContent($data);
            }
        }
        $response = $request->send();
        if (!$response->getIsOk()) {
            return false;
//            throw new InvalidResponseException($response, 'Request failed with code: ' . $response->getStatusCode() . ', message: ' . $response->getContent());
        }
        $ret = [
            'header' => $response->getHeaders(),
            'body' => $response->getContent(),
        ];
        return $ret;
    }

    /**
     * Sends the given HTTP request, returning response data.
     * @param \yii\httpclient\Request $request HTTP request to be sent.
     * @return array|boolean response data.
     * @throws InvalidResponseException on invalid remote response.
     * @since 2.1
     */
    protected function sendRequest($request)
    {
        $response = $request->send();
        if (!$response->getIsOk()) {
            return false;
//            throw new InvalidResponseException($response, 'Request failed with code: ' . $response->getStatusCode() . ', message: ' . $response->getContent());
        }
        return $response->getData();
    }




}