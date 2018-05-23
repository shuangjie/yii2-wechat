<p align="center">
    <h1 align="center">Yii 2 整合 微信公众号 & 微信小程序 扩展</h1>
    <br>
</p>

借助[Yii 2](http://www.yiiframework.com/) 框架开发微信公众号&小程序扩展 <br>
空闲时间就会更新上来，_顺手点一下`star`吧_ (｡♥‿♥｡)<br>

<p align="center">
    <h1 align="center">最近项目比较忙，之后更新具体的model和小相关操作</h1>
    <br>
</p>


```
common
    components/              components组件
        ResponseComponent               
    extensions/
        weapp/               微信小程序扩展
        wechat/              微信公众号扩展
        wechatpay/           微信支付扩展
    helpers/
        redis/               redis helper
        ***Helper            base helper 
    model/                   
        Auth                 Auth
        User                 User
    services/
        notify/              Notify Service
        user/                User Service
        wechat/              wechat Service
weapp                        微信小程序
       .../
wechat                       微信公众号
       .../
```


DIRECTORY STRUCTURE <br> (not updated temporarily)
----------------------------------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
environments/            contains environment-based overrides
```
