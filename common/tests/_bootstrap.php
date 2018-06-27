<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', __DIR__.'/../../');


require_once __DIR__ .  '/../../vendor/autoload.php';
require_once __DIR__ .  '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../config/constant.php';

// 设置OSS图片网址别名
Yii::setAlias('@oss', 'http://image.tbz.com/' . UPLOAD_BASE_DIR);
