<?php
namespace weapp\controllers;
use weapp\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;

/**
 * 基础的rest controller
 * 如果不需要做登录认证的controller，就不要继承这个类
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 22:26
 *
 * @property array $optionalActions 可选的action id。定义后的action可以不做登录限制
 * 具体在子类去设置
 * ```php
 * public function init(){
 *      $this->optionalActions = ['sign-up', 'login'];
 * }
 * //或者
 * protected $optionalActions = ['sign-up', 'login'];
 * ```
 */
abstract class AuthController extends Controller {

    /* @var array 可不认证的action id，支持正则表达式。 */
    protected $optionalActions = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'optional' => $this->optionalActions,
            'failureCallback' => [self::className(), 'handleAuthFailure'],
            'authMethods' => [
                QueryParamAuth::className(), //query param (access token)
                HttpBearerAuth::className(),
            ],
        ];
        return $behaviors;
    }

    /**
     * 登录失败的钩子
     * @param \yii\web\Response $response
     * TODO 补充返回内容
     * */
    public static function handleAuthFailure($response){
        $response->data = ['code' => 1, 'message' => 'Your request was made with invalid credentials.'];
    }


}