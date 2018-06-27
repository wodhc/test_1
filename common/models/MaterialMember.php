<?php

namespace common\models;

use common\components\traits\ModelErrorTrait;
use Yii;
use common\components\traits\TimestampTrait;
use common\components\traits\ModelFieldsTrait;
use yii\helpers\Url;
/**
 * This is the model class for table "{{%material_member}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="MaterialMember"))
 *
 * @property int $id @SWG\Property(property="id", type="integer", description="")
 * @property int $user_id 用户id @SWG\Property(property="userId", type="integer", description=" 用户id")
 * @property int $folder_id 文件夹 @SWG\Property(property="folderId", type="integer", description=" 文件夹")
 * @property string $file_name 文件名 @SWG\Property(property="fileName", type="string", description=" 文件名")
 * @property string $thumbnail 图片路径 @SWG\Property(property="thumbnail", type="string", description=" 图片路径")
 * @property int $file_id 文件id @SWG\Property(property="fileId", type="integer", description=" 文件id")
 * @property int $created_at 创建时间 @SWG\Property(property="createdAt", type="integer", description=" 创建时间")
 */
class MaterialMember extends \yii\db\ActiveRecord
{

    use TimestampTrait;
    use ModelFieldsTrait;
    use ModelErrorTrait;
    /** @var string 素材正常状态 */
    const STATUS_NORMAL = '10';

    /** @var string 回收站 */
    const STATUS_TRASH = '7';

    /** @var string 删除状态 */
    const STATUS_DELETE = '3';

    public function frontendFields()
    {
        return ['id', 'user_id', 'folder_id', 'file_id', 'file_name', 'thumbnail'];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%material_member}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['file_name', 'default', 'value' => ''],
            [['user_id', 'folder_id', 'file_id'], 'default', 'value' => 0],
            [['user_id', 'folder_id', 'file_id', 'created_at','status'], 'integer'],
            [['file_name', 'thumbnail'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '唯一标识',
            'user_id' => '用户id',
            'folder_id' => '文件夹',
            'file_name' => '文件名',
            'thumbnail' => '图片路径',
            'file_id' => '文件id',
            'status' => '状态，3为删除，7为到回收站，10为正常',
            'created_at' => '创建时间',
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
     * @return array|mixed
     */
    public function expandFields()
    {
        $data = ['thumbnail' => function() {
            return Url::to('@oss') . DIRECTORY_SEPARATOR .'uploads'. $this->thumbnail;
        }];
        $data['width'] = function () {
            return $this->fileCommon->width;
        };
        $data ['height'] = function () {
            return $this->fileCommon->height;
        };
        $data['type'] = function () {
            return $this->fileCommon->type;
        };
        return $data;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFileCommon()
    {
        return $this->hasOne(FileCommon::class, ['file_id' => 'file_id']);
    }
}
