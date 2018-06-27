<?php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'dbMigrateDdy' => [
            'dsn' => 'mysql:host=192.168.1.8;dbname=duoduoyin',
            'username' => 'tubangzhudev',
            'password' => 'jpEHu2JEXessDyUv',
            'charset' => 'utf8',
        ],
        'dbMigrateTbz' => [
            'dsn' => 'mysql:host=192.168.1.8;dbname=tbz_editor',
            'username' => 'tubangzhudev',
            'password' => 'jpEHu2JEXessDyUv',
            'charset' => 'utf8',
        ]
    ]
];
