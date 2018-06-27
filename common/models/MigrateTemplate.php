<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;

/**
 * This is the model class for table "{{%migrate_template}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="MigrateTemplate"))
 *
 * @property int $migrate_id @SWG\Property(property="migrateId", type="integer", description="")
 * @property int $template_id @SWG\Property(property="templateId", type="integer", description="")
 * @property int $template_type @SWG\Property(property="templateType", type="integer", description="")
 * @property int $status @SWG\Property(property="status", type="integer", description="")
 * @property int $created_at @SWG\Property(property="createdAt", type="integer", description="")
 * @property int $updated_at @SWG\Property(property="updatedAt", type="integer", description="")
 */
class MigrateTemplate extends \yii\db\ActiveRecord
{

    use TimestampTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%migrate_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id', 'template_type', 'status', 'created_at', 'updated_at'], 'required'],
            [['template_id', 'created_at', 'updated_at'], 'integer'],
            [['template_type', 'status'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'migrate_id' => 'Migrate ID',
            'template_id' => 'Template ID',
            'template_type' => 'Template Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
