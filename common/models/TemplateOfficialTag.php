<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%template_official_tag}}".
 *
 * @property int $template_id 模板id
 * @property int $tag_id tag_id
 * @property int $created_at 修改时间
 */
class TemplateOfficialTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%template_official_tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id', 'tag_id', 'created_at'], 'required'],
            [['template_id', 'tag_id', 'created_at'], 'integer'],
            [['template_id', 'tag_id'], 'unique', 'targetAttribute' => ['template_id', 'tag_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'template_id' => '模板id',
            'tag_id' => 'tag_id',
            'created_at' => '修改时间',
        ];
    }
}
