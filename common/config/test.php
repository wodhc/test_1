<?php
return [
    'id' => 'tubangzhu-tests',
    'basePath' => dirname(__DIR__),
    'components' => [
        'request' => [
            'class' => 'common\components\vendor\Request',
            'enableCsrfCookie' => false,
            'client' => 'tubangzhu_web',
            'handle' => 'backend',
        ]
    ],
];
