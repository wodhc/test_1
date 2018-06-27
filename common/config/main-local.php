<?php
return [
    'components' => [
        // wechat 配置
        'wechat' => [
            'app_id' => 'wxc532cc9a793ef689',
            'secret' => 'd4624c36b6795d1d99dcf0547af5443d',
            'token' => 'dHVhYm5nemh1',
        ],
        // Oss 配置
        'oss' => [
            'accessKeyId' => 'LTAIv4N83CO6YfFy',
            'accessKeySecret' => 'H1nVZLptjrcxFdVmiXRJEXpAXUHck9',
            'endpoint' => 'oss-cn-beijing.aliyuncs.com',
            'bucket' => 'tubangzhu-dev'
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-2ze1pn92s109h67lx3o.mysql.rds.aliyuncs.com;dbname=tubangzhudev',
            'username' => 'tubangzhudev',
            'password' => 'jpEHu2JEXessDyUv',
            'charset' => 'utf8',
        ],
        'db_old' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=tbz_history.com;dbname=duoduoyin',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
    ],
];
