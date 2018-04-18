wechat pay extension for yii2
=========================
微信支付组件

  * 统一下单接口 (https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1)
  * 查询订单 (https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_2)
  * 关闭订单 (https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_3)
  * 申请退款 (https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_4)
  * 查询退款 (https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_5)
  * 下载对账单 (https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_6)
  * 支付结果通用通知 (https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_7)
  * 提交被扫支付API (https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1)
  * 交易保障 (https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_8)
  
Usage:
----

* 添加应用配置

```php
'components' => [
    //微信支付
    'wechatPay' => [
        'class' => '\common\extensions\wechatpay\WechatPay',
        'appid' => 'APP ID', //apiid ，公众号和app模式不同
        'mch_id' => 'MCH ID', //商户号
        'key' => 'API SECRET', //api秘钥
        'notify_url' => 'NOTIFY URL', 
        'ssl_cert' => 'CERT PATH', //路径
        'ssl_key' => 'KEY PATH', //路径
    ],
    //...
],
```
* 统一下单

```php
 /**
  * @var WechatPay $wxPay
  */
 $wxPay = Yii::$app->wechatPay;
 $wxPayCli =  $wxPay->wechatPayApiCli;
 $values = $wxPayCli->values; //提交的数据
 $values['out_trade_no'] = $recharge->payment_no;
 $values['body'] = $order->name." #".$order->id;
 $values['total_fee'] = $recharge->amount;
 $values['trade_type'] = 'JSAPI';
 $values['openid'] = 'ozP4_xOEk13kb0YRaU7jDtJ9XvTI';
 $values['notify_url'] = 'http://m.fvgou.com/api/payment/wechat_pay_notify';
 $result = $wxPayCli->unifiedOrder();
 
 //return 
 /*
  Array(
     [appid] => wxef834f1c5466bc9e
     [mch_id] => 1422476802
     [nonce_str] => VcDxbpmwwqRGAQrZ
     [prepay_id] => wx20170228220133f2775f87e50526440246
     [result_code] => SUCCESS
     [return_code] => SUCCESS
     [return_msg] => OK
     [sign] => FAE071DF70119DEC1713CDA75B9732C2
     [trade_type] => JSAPI
  )
  */
```