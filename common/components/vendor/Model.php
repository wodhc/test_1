<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\vendor;

use Yii;

class Model extends \yii\base\Model
{
    /** @var string 前台保存 */
    const SCENARIO_FRONTEND = 'frontend';
    /** @var string 后台保存 */
    const SCENARIO_BACKEND = 'backend';

    public function __construct(array $config = [])
    {
        // 添加默认场景
        $config['scenario'] = Yii::$app->request->isFrontend() ? static::SCENARIO_FRONTEND : static::SCENARIO_BACKEND;

        parent::__construct($config);
    }
}