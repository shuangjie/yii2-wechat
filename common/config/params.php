<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,

    //域名
    'domain.mobile' => 'm.xxx.com',
    'domain.wechat' => 'wechat.xxx.com',
    'domain.weapp' => 'weapp.xxx.com',

    //OSS bucket
    'bucket.media' => 'media',
    'bucket.media_output' => 'media-output',

    //access-token缓存过期时间
    'access.token.expires.in' => (3600 * 24),

    //模板id
    'wxTemplateId.pay.notify' => 'xxxx-xxxx',
    'weappTemplateId.pay.notify' => 'xxxx-xxxx',
    'wxTemplateId.new.comment.notify' => 'xxxx-xxxx',
    'weappTemplateId.new.comment.notify' => 'xxxx-xxxx',
];
