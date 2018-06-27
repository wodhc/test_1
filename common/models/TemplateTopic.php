<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;
use common\components\traits\ModelErrorTrait;
use common\components\traits\ModelFieldsTrait;

/**
 * This is the model class for table "{{%template_topic}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="TemplateTopic"))
 *
 * @property int $id @SWG\Property(property="id", type="integer", description="")
 * @property int $template_id 官方模板id @SWG\Property(property="templateId", type="integer", description=" 官方模板id")
 * @property int $topic_id 模板专题id @SWG\Property(property="topicId", type="integer", description=" 模板专题id")
 * @property int $created_at 创建日期 @SWG\Property(property="createdAt", type="integer", description=" 创建日期")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 * @property Classify[] $classifies
 */
class TemplateTopic extends \yii\db\ActiveRecord
{

    use TimestampTrait;
    use ModelErrorTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%template_topic}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id', 'topic_id', 'created_at', 'updated_at'], 'integer'],
            [['template_id', 'topic_id'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => '模板id',
            'topic_id' => '模板专题id',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * 更新缓存
     */
    public function afterSave($insert, $changedAttributes)
    {
        // 更新缓存
        if ($changedAttributes) {
            Yii::$app->dataCache->updateCache(static::class);
        }
        parent::afterSave($insert, $changedAttributes);
    }
    /**
     * 关联小分类表，获取小分类信息
     * @return \yii\db\ActiveQuery
     */
    public function getClassifies()
    {
        return $this->hasOne(Classify::class, ['classify_id' => 'classify_id'])
            ->via('tempClassify');
    }

    /**
     * 关联官方模板表，去除重复的小分类
     * @return \yii\db\ActiveQuery
     */
    public function getTempClassify()
    {
        return $this->hasOne(TemplateOfficial::class, ['template_id' => 'template_id'])
            ->where(['status' => TemplateOfficial::STATUS_ONLINE])
            ->groupBy('classify_id');
    }
}
