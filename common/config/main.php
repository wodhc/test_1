<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'language' => 'zh-CN',
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'defaultDuration' => 43200,
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'queryCacheDuration' => '43200',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 0,
            'schemaCache' => 'cache',
            'tablePrefix' => 'tu_',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'common\models\Member',
        ],
        // 微信配置
        'wechat' => [
            'class' => 'thanatos\wechat\Wechat',
            'log' => [
                'level' => 'debug',
                'permission' => '0777',
                'file' => '@runtime/logs/wechat.log'
            ]
        ],
        // OSS配置
        'oss' => [
            'class' => 'thanatos\oss\Oss',
        ],
        // 缓存组件
        'dataCache' => [
            'class' => 'common\components\vendor\DataCache'
        ],
        //Sms 验证码
        'sms' => [
            'class' => 'common\components\vendor\Sms'
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
             'cache' => 'cache',
        ],
        // Alipay
        'alipay' => [
            'class' => 'common\components\vendor\Alipay',
        ]
    ],
];
