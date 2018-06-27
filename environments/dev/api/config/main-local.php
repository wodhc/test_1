<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'cqAZj6UltdzyS7BTmNtTrd0T_AbeVT-h',
        ],
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],

    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
        'generators' => [
            'model' => [
                'class' => 'yii\gii\generators\model\Generator',
                'templates' => [
                    'swagger' => '@common/components/gii/model',
                ],
            ],
            'frontend' => [
                'class' => 'common\components\gii\frontend\Generator',
                'templates' => [
                    'frontend' => '@common/components/gii/frontend',
                ]
            ]
        ],
    ];
}

return $config;
