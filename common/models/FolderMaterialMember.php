<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;
use common\components\traits\ModelFieldsTrait;

/**
 * This is the model class for table "{{%folder_material_member}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="FolderMaterialMember"))
 *
 * @property int $id @SWG\Property(property="id", type="integer", description="")
 * @property string $name 文件夹名称 @SWG\Property(property="name", type="string", description=" 文件夹名称")
 * @property string $color 文件夹颜色 @SWG\Property(property="color", type="string", description=" 文件夹颜色")
 * @property int $status 文件夹状态 @SWG\Property(property="status", type="integer", description=" 文件夹状态")
 * @property int $user_id 用户id @SWG\Property(property="userId", type="integer", description=" 用户id")
 * @property int $created_at 创建日期 @SWG\Property(property="createdAt", type="integer", description=" 创建日期")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 */
class FolderMaterialMember extends \yii\db\ActiveRecord
{

    use TimestampTrait;
    use ModelFieldsTrait;
    /** @var int 正常状态 */
    const NORMAL_STATUS = 10;
    /**
     * @return array
     */
    public function frontendFields()
    {
        return ['id', 'color', 'name', 'user_id'];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%folder_material_member}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['color'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '唯一标识',
            'name' => '文件夹名称',
            'color' => '文件夹颜色',
            'status' => '文件夹状态',
            'user_id' => '用户id',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function sortTime()
    {
        {
            return FolderMaterialMember::find()->orderBy(['created_at' => SORT_DESC]);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function online()
    {
        return static::sortTime()->Where(['status' => static::NORMAL_STATUS]);
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
     * @return mixed
     */
    public function expandFields()
    {
        if ($this->isRelationPopulated('materials')) {
            //当前素材文件夹的素材数量
            $data['materialNum'] = function () {
                return count($this->materials);
            };
        }
        return $data;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaterials(){
        return $this->hasMany(MaterialMember::class,['folder_id'=>'id']);
    }
}
