<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */
namespace console\controllers;


use common\models\OauthPublicKeys;
use yii\helpers\Console;

class CacheController extends \yii\console\controllers\CacheController
{

    /**
     * 清除JWT clients缓存
     * @author thanatos <thanatos915@163.com>
     */
    public function actionFlushJwt()
    {
        $cache = \Yii::$app->cache;
        $cacheKey = [
            OauthPublicKeys::class,
            'JWT_clients_cache',
        ];

        $cache->delete($cacheKey);
        $this->stdout("\t清除JWT成功\n", Console::FG_YELLOW);

    }


}