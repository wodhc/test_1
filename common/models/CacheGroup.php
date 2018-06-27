<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;

/**
 * This is the model class for table "{{%cache_group}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="CacheGroup"))
 *
 * @property string $table_name 表名 @SWG\Property(property="tableName", type="string", description=" 表名")
 * @property string $cache_name 缓存名 @SWG\Property(property="cacheName", type="string", description=" 缓存名")
 */
class CacheGroup extends \yii\db\ActiveRecord
{

    use TimestampTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cache_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['table_name', 'cache_name'], 'required'],
            [['table_name'], 'string', 'max' => 100],
            [['cache_name'], 'string', 'max' => 50],
            [['table_name', 'cache_name'], 'unique', 'targetAttribute' => ['table_name', 'cache_name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'table_name' => '表名',
            'cache_name' => '缓存名',
        ];
    }
}
