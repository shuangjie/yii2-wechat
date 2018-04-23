<?php
namespace weapp\models\payment;

use common\extensions\wechatpay\WechatPay;
use common\helpers\TimeHelper;
use common\models\Auth;
use Yii;
use common\models\User;
use yii\base\Model;

/**
 * 统一下单接口基础表单
 * User: DoubleJack
 * Date: 2018/4/23
 * Time: 22:42
 */
class UnifiedOrderForm extends Model {

    const TYPE_ONE = 'one'; //1
    const TYPE_TWO = 'two'; //2
    const TYPE_THREE = 'three'; //3
    public static $type_config = [
        self::TYPE_ONE => '分类1',
        self::TYPE_TWO => '分类2',
        self::TYPE_THREE => '分类3',
    ];

    const PAY_TYPE_WECHATPAY = 'wechatpay';
    const PAY_TYPE_ALIPAY = 'alipay';
    public static $pay_type_config = [
        self::PAY_TYPE_WECHATPAY => '微信支付',
        self::PAY_TYPE_ALIPAY => '支付宝'
    ];

    const TRADE_TYPE_NATIVE = 'NATIVE';
    const TRADE_TYPE_WEAPP = 'WEAPP';
    static $trade_type_config = [
        self::TRADE_TYPE_NATIVE => '扫码',
        self::TRADE_TYPE_WEAPP => '小程序',
    ];

    public $amount;
    public $type;
    public $user_id;
    public $pay_type; //wechat，alipay
    public $trade_type; //交易类型 jsapi,native,weapp等  微信支付

    public $task_id;
    public $message_id;
    public $to_user_id;

    public $openid; //微信用户openid

    /**
     * @inheritdoc
     * @return array
     */
    public function rules()
    {
        return [
            [['type', 'amount', 'user_id'], 'required'],
//            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['user_id'], 'compare', 'compareValue' => \Yii::$app->user->id],
            ['amount', 'compare', 'compareValue' => 0, 'operator' => '>='],
            ['type','in','range' => array_keys(self::$type_config)],
            ['message_id', 'string'] , //TODO
            ['pay_type', 'in', 'range' => array_keys(self::$pay_type_config)],
            ['trade_type', 'in', 'range' => array_keys(self::$trade_type_config)],
            ['pay_type', 'default', 'value' => self::PAY_TYPE_WECHATPAY],
//            ['trade_type', 'default', 'value' => ]
            //情景
            [['type', 'amount', 'user_id','pay_type', 'trade_type'], 'required', 'on' => self::TYPE_ONE],
            [['type', 'amount', 'user_id','pay_type', 'trade_type'], 'required', 'on' => self::TYPE_TWO],
            [['type', 'amount', 'user_id','pay_type', 'trade_type'], 'required', 'on' => self::TYPE_THREE],

        ];
    }

    /**
     * 情景模式
     * 根据type分情景
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::TYPE_ONE] = ['type', 'amount', 'user_id', 'pay_type', 'trade_type', 'openid'];
        $scenarios[self::TYPE_TWO] = ['type', 'amount', 'user_id', 'pay_type', 'trade_type', 'openid'];
        $scenarios[self::TYPE_THREE] = ['type', 'amount', 'user_id', 'pay_type',  'trade_type', 'openid'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'amount' => '金额',
            'type' => '订单类型',
            'user_id' => '提交用户',
            'pay_type' => '支付类型',
            'trade_type' => '交易类型',
        ];
    }


    /**
     * 创建支付订单
     */
     public function create(){

     }

    /**
     * 生成订单名称
     */
    public function generateOrderName()
    {
        $name = '';
        switch ($this->type){
            case self::TYPE_ONE:
                break;
        }
    }

    public function getOpenId(){
        /* @var User $user */
        $user = Yii::$app->user->identity;
        /* @var Auth $weappAuth */
        $weappAuth = $user->getAuths()->where(['source' => Auth::SOURCE_WEAPP])->one();
        return $weappAuth->source_id;
    }

}