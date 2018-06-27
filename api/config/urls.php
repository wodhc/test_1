<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */
$rules = [
    '' => 'site/index',
    'wechat/server' => 'wechat/server',
    'POST,GET,OPTIONS oss/callback' => 'oss/callback',
];

$restUrls = [
    'modules' => ['v1', 'v2'],
    'rules' => [
        [
            'controller' => ['user', 'category', 'classify', 'gain-template-cover','message','tag','folder','template-topic'],
            'extraPatterns' => [
                'POST,OPTIONS login' => 'login',
                'POST,OPTIONS bind' => 'bind',
                'POST,OPTIONS reset-password' => 'reset-password',
                'GET,OPTIONS classify-tag' => 'classify-tag',
                'GET,OPTIONS topic-list' => 'topic-list',
            ],
        ],
        [
            'controller' => ['template-official'],
            'extraPatterns' => [
                'GET,OPTIONS classify-search' => 'classify-search',
            ]
        ],
        [
            'controller' => [ 'template-member','folder-material','folder-template','material','my-favorite','team','team-member','material-official','material-classify','share-template'],
            'extraPatterns' => [
                'POST,OPTIONS template-operation' =>'template-operation',
                'POST,OPTIONS material-operation' =>'material-operation',
                'POST,OPTIONS team-operation' =>'team-operation',
            ]
        ],
        [
            'controller' => ['member-recharge']
        ],
        // Pay
        'GET pay/alipay' => 'pay/alipay',
        'GET,POST pay/alipay-notify' => 'pay/alipay-notify',
        // 微信配置
        'GET,POST,OPTIONS wechat/qrcode' => 'wechat/qrcode',
        'POST,OPTIONS wechat/session' => 'wechat/session',
        'POST,OPTIONS wechat/refresh' => 'wechat/refresh',
        // 验证码
        'POST,OPTIONS main/send-sms' => 'main/send-sms',
        'GET,POST,OPTIONS system/oss-policy' => 'system/oss-policy',
        'GET,OPTIONS system/get-errors' => 'system/get-errors',
        'GET,OPTIONS main/<action>' => 'main/<action>',

        // 开始文档
        'doc/index' => 'doc/index',
        'doc/api' => 'doc/api',
    ],
    'pluralize' => false
];
$builtUrls = \common\extension\RestUrlRules::prepare($restUrls);

return \yii\helpers\ArrayHelper::merge($rules, $builtUrls);