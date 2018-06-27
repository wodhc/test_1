<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;

/**
 * This is the model class for table "{{%my_favorite_member}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="MyFavoriteMember"))
 *
 * @property int $id @SWG\Property(property="id", type="integer", description="")
 * @property int $template_id 模板id @SWG\Property(property="templateId", type="integer", description=" 模板id")
 * @property int $user_id 用户id @SWG\Property(property="userId", type="integer", description=" 用户id")
 * @property int $created_at 创建日期 @SWG\Property(property="createdAt", type="integer", description=" 创建日期")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 */
class MyFavoriteMember extends \yii\db\ActiveRecord
{

    use TimestampTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%my_favorite_member}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '唯一标识',
            'template_id' => '模板id',
            'user_id' => '用户id',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     * 关联TemplateOfficial
     */
    public function getTemplateOfficials()
    {
        return $this->hasOne(TemplateOfficial::class, ['template_id' => 'template_id'])
            ->where(['status' => TemplateOfficial::STATUS_ONLINE]);
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
     * 删除之后更新缓存
     */
    public function afterDelete()
    {
        // 更新缓存
        Yii::$app->dataCache->updateCache(static::class);
        parent::afterDelete();
    }
}
