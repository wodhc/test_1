<?php

namespace api\modules\v1;
use yii\base\BootstrapInterface;

/**
 * v1 module definition class
 */
class module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'api\modules\v1\controllers';

    public function bootstrap($app)
    {
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // custom initialization code goes here
    }
}
