<?php
/**
 * Created by PhpStorm.
 * User: DoubleJack
 * Date: 2018/4/18
 * Time: 17:48
 */
namespace common\components;


class ResponseComponent{

    //相应代码
    CONST CODE_ACTIVATED = 0;
    CONST CODE_ERROR = 1;
    CONST CODE_OPERATE_FAILED = 10;
    CONST CODE_PARAMS_ERROR = 20;
    CONST CODE_STATUS_ERROR = 30;
    CONST CODE_DATA_ERROR = 40;
    CONST CODE_SYSTEM_ERROR = 50;

    /**
     * @var array 代码配置
     */
    static $code_config = [
        self::CODE_ACTIVATED => 'success',
        self::CODE_ERROR => 'FAIL',
        self::CODE_PARAMS_ERROR => '参数错误',
        self::CODE_STATUS_ERROR => '状态错误',
        self::CODE_DATA_ERROR => '数据错误',
        self::CODE_SYSTEM_ERROR => '系统错误',
    ];

    public static function getCodeLabel($code){
        return self::$code_config[$code];
    }

    public static function response($code,$data = null, $message = ''){
        $message = $message ? : self::$code_config[$code];
        $response_data = self::buildResponse($code, $message, $data);
        //shell 环境下没有 request->get() 方法
        if(!\Yii::$app->request->isConsoleRequest && \Yii::$app->request->get("callback") )
        {
            $response_data = ['data'=>$response_data, 'callback' => \Yii::$app->request->get("callback")];
        }
        return $response_data;
    }

    /**
     * 成功响应
     * @param mixed $data 返回的数据
     * @return array self::buildResponse
     */
    public static function success($data = null){
        return self::response(self::CODE_ACTIVATED, $data);
    }

    /**
     * 失败响应
     * @param integer $code
     * @param string $message
     * @param mixed $data
     * @return array
     */
    public static function failed($code = self::CODE_ERROR, $message = '', $data = null)
    {
        return self::response($code, $data, $message);
    }

    public static function buildResponse($code, $message = "", $data = null){
        $format = ['code' => $code,'message' => $message, 'data' => $data];
        return $format;
    }
}