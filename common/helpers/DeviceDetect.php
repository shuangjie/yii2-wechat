<?php
namespace common\helpers;

use Yii;
use yii\base\Object;

use Detection\MobileDetect;

/**
 * 移动设备检测功能
 * @link http://mobiledetect.net/
 * usage
 * 1、注册组件
 * ```php
 * Yii::$app->set('deviceDetect', [
 *     'class' => 'common\service\DeviceDetect',
 * ]);
 * Yii::$app->deviceDetect->isMobile();
 * ```
 * 2、create
 */

class DeviceDetect extends Object
{
    //MobileDetect对象
    protected $detector;

    //初始化
    public function init()
    {
        parent::init();
        $this->detector = new MobileDetect();
    }

    public function __call($name, $params)
    {
        return call_user_func_array(
            array($this->detector, $name),
            $params
        );
    }

    /**
     * 是否微信
     */
    public function isWechat(){
        return (strpos(strtolower(Yii::$app->request->headers['user-agent']),"micromessenger") !== FALSE);
    }
}