<?php
/**
 * @user: thanatos
 */

namespace common\components\traits;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Trait TimestampTrait
 * @package common\traits
 */
trait TimestampTrait
{
    public function behaviors()
    {
        $hasCreatedAt = $this->hasAttribute('created_at');
        $hasUpdatedAt = $this->hasAttribute('updated_at');

        $config = [
            'class' => TimestampBehavior::class,
            'attributes' => []
        ];

        if ($hasCreatedAt && $hasUpdatedAt) {
            $config['attributes'] = [
                ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
            ];
        } elseif($hasCreatedAt && !$hasUpdatedAt) {
            $config['attributes'] = [
                ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
            ];
        } elseif(!$hasCreatedAt && $hasUpdatedAt) {
            $config['attributes'] = [
                ActiveRecord::EVENT_BEFORE_INSERT => ['updated_at'],
                ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
            ];
        } else {
            return parent::behaviors();
        }
        return [$config];
    }
}