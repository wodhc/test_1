<?php

namespace common\models;

use common\components\traits\ModelErrorTrait;
use Yii;
use common\components\traits\TimestampTrait;
use yii\helpers\Url;
use common\components\traits\ModelFieldsTrait;
/**
 * This is the model class for table "{{%material_official}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="MaterialOfficial"))
 *
 * @property int $id @SWG\Property(property="id", type="integer", description="")
 * @property int $user_id 用户ID @SWG\Property(property="userId", type="integer", description=" 用户ID")
 * @property int $cid 素材分类ID @SWG\Property(property="cid", type="integer", description=" 素材分类ID")
 * @property string $name 素材名 @SWG\Property(property="name", type="string", description=" 素材名")
 * @property string $tags 素材搜索标签 @SWG\Property(property="tags", type="string", description=" 素材搜索标签")
 * @property string $thumbnail 缩略图路径 @SWG\Property(property="thumbnail", type="string", description=" 缩略图路径")
 * @property int $thumbnail_id 缩略图id @SWG\Property(property="thumbnailId", type="integer", description=" 缩略图id")
 * @property int $file_path 文件路径 @SWG\Property(property="filePath", type="integer", description=" 文件路径")
 * @property int $file_id 文件id @SWG\Property(property="fileId", type="integer", description=" 文件id")
 * @property int $file_type 文件类型 @SWG\Property(property="fileType", type="integer", description=" 文件类型")
 * @property int $width 宽度 @SWG\Property(property="width", type="integer", description=" 宽度")
 * @property int $height 高度 @SWG\Property(property="height", type="integer", description=" 高度")
 * @property int $status 素材状态 @SWG\Property(property="status", type="integer", description=" 素材状态")
 * @property int $created_at 创建时间 @SWG\Property(property="createdAt", type="integer", description=" 创建时间")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 */
class MaterialOfficial extends \yii\db\ActiveRecord
{
    use ModelErrorTrait;
    use TimestampTrait;
    use ModelFieldsTrait;

    /** @var string 素材正常状态 */
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
        return '{{%material_official}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'cid', 'file_type'], 'required'],
            [['user_id', 'thumbnail_id', 'file_id', 'width', 'height', 'created_at', 'updated_at', 'file_type'], 'filter', 'filter' => 'intval'],
            [['user_id', 'thumbnail_id', 'file_id', 'width', 'height', 'created_at', 'updated_at', 'file_type'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['cid'], 'string', 'max' => 255],
            [['tags', 'thumbnail', 'file_path'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'cid' => '素材分类ID',
            'name' => '素材名',
            'tags' => '素材搜索标签',
            'thumbnail' => '文件路径',
            'file_id' => '文件id',
            'file_type' => '文件类型',
            'width' => '宽度',
            'height' => '高度',
            'status' => '素材状态',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'extra_contents' => '素材额外字段',
        ];
    }
    /**
     * 排序
     * @return \yii\db\ActiveQuery
     */
    public static function sort()
    {
        return static::find()->orderBy(['id' => SORT_DESC]);
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
     * @return \yii\db\ActiveQuery
     */
    public static function active()
    {
        if (Yii::$app->request->isFrontend()) {
            return static::find()->where(['status' => static::STATUS_NORMAL]);
        } else {
            return static::find();
        }
    }
    /**
     * 单个素材查询
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function findById($id)
    {
        if (Yii::$app->request->isFrontend()) {
            return static::find()->where(['status' => static::STATUS_NORMAL, 'id' => $id])->one();
        } else {
            return static::find()->where(['id' => $id])->one();
        }
    }
    /**
     * @return array|mixed
     */
    public function expandFields()
    {
        $data = ['thumbnail' => function() {
            return Url::to('@oss') . DIRECTORY_SEPARATOR .'uploads'. $this->thumbnail;
        }];
        return $data;
    }
    public function frontendFields()
    {
        return ['id', 'user_id', 'name', 'file_type', 'tags', 'extra_contents', 'thumbnail','width','height'];
    }
}
