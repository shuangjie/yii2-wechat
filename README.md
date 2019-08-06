<p align="center">
    <h1 align="center">Yii 2 整合 微信公众号 & 微信小程序 扩展</h1>
    <br>
</p>

借助[Yii 2](http://www.yiiframework.com/) 框架开发微信公众号&小程序扩展 <br>
建个仓库，给以后开发微信相关用的基础框架。<br>

## 运行环境要求

- Nginx 1.8+
- PHP 7.1+
- MySQL 5.7.7+
- Redis 3.0+

## 开发环境部署/安装

本项目代码使用 PHP 框架 [Yii 2](http://www.yiiframework.com) 开发.

### 基础安装

#### 克隆源代码

克隆源代码到本地：

    > git clone git@github.com:overtrue/api.yike.io.git

#### 安装扩展包依赖

    composer install

#### 初始化

    > php init

#### 其他事项

修改配置，执行migrate

    > php yii migrate

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
