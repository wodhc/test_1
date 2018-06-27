<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;
use common\components\traits\ModelFieldsTrait;
use common\components\traits\ModelErrorTrait;
/**
 * This is the model class for table "{{%material_classify}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="MaterialClassify"))
 *
 * @property int $cid @SWG\Property(property="cid", type="integer", description="")
 * @property int $pid 父分类标识 @SWG\Property(property="pid", type="integer", description=" 父分类标识")
 * @property string $name 分类名称 @SWG\Property(property="name", type="string", description=" 分类名称")
 * @property int $status 素材分类状态 @SWG\Property(property="status", type="integer", description=" 素材分类状态")
 * @property int $created_at 创建时间 @SWG\Property(property="createdAt", type="integer", description=" 创建时间")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 */
class MaterialClassify extends \yii\db\ActiveRecord
{

    use TimestampTrait;
    use ModelFieldsTrait;
    use ModelErrorTrait;
    /** @var string 素材分类正常状态 */
    const STATUS_NORMAL = '10';

    /** @var string 回收站 */
    const STATUS_TRASH = '7';

    /** @var string 删除状态 */
    const STATUS_DELETE = '3';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%material_classify}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['pid', 'default', 'value' => 0],
            ['pid', 'filter', 'filter' => 'intval'],
            [['name'], 'required'],
            [['status', 'created_at', 'updated_at', 'pid'], 'integer'],
            [['name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cid' => 'Cid',
            'pid' => '父分类标识',
            'name' => '分类名称',
            'status' => '素材分类状态',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * @param $status
     * @return \yii\db\ActiveQuery
     */
    public static function active($status)
    {
        if (Yii::$app->request->isFrontend()) {
            return static::find()->where(['status' => static::STATUS_NORMAL]);
        } else {
            return static::find()->where(['status'=>$status]);
        }
    }
    /**
     * @return array
     */
    public function frontendFields()
    {
        return ['cid','name'];
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
}
