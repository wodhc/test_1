<?php

namespace common\models;

use common\components\traits\ModelErrorTrait;
use Yii;
use common\components\traits\TimestampTrait;
use common\components\traits\ModelFieldsTrait;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%template_member}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="TemplateMember"))
 *
 * @property int $template_id @SWG\Property(property="templateId", type="integer", description="")
 * @property int $classify_id 分类id @SWG\Property(property="classifyId", type="integer", description=" 分类id")
 * @property int $open_id openid @SWG\Property(property="openId", type="integer", description=" openid")
 * @property int $user_id 用户id @SWG\Property(property="userId", type="integer", description=" 用户id")
 * @property int $folder_id 文件夹id @SWG\Property(property="folderId", type="integer", description=" 文件夹id")
 * @property int $cooperation_id 商户id @SWG\Property(property="cooperationId", type="integer", description=" 商户id")
 * @property string $title 模板标题 @SWG\Property(property="title", type="string", description=" 模板标题")
 * @property string $thumbnail_url 模板缩略图 @SWG\Property(property="thumbnailUrl", type="string", description=" 模板缩略图")
 * @property int $thumbnail_id 模板id @SWG\Property(property="thumbnailId", type="integer", description=" 模板id")
 * @property int $status 状态 @SWG\Property(property="status", type="integer", description=" 状态")
 * @property int $created_at 创建时间 @SWG\Property(property="createdAt", type="integer", description=" 创建时间")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 * @property int $is_diy 是否是自定义模板 @SWG\Property(property="isDiy", type="integer", description=" 是否是自定义模板")
 * @property int $edit_from 编辑来源官方模板id @SWG\Property(property="editFrom", type="integer", description=" 编辑来源官方模板id")
 * @property int $amount_print 印刷次数 @SWG\Property(property="amountPrint", type="integer", description=" 印刷次数")
 */
class TemplateMember extends \yii\db\ActiveRecord
{
    use TimestampTrait;
    use ModelFieldsTrait;
    use ModelErrorTrait;
    /** @var string 用户模板正常状态 */
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
        return '{{%template_member}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['classify_id', 'open_id', 'user_id', 'folder_id', 'cooperation_id', 'thumbnail_id', 'created_at', 'updated_at', 'edit_from', 'amount_print', 'status', 'is_diy'], 'filter', 'filter' => 'intval'],
            [['classify_id', 'open_id', 'user_id', 'folder_id', 'cooperation_id', 'thumbnail_id', 'created_at', 'updated_at', 'edit_from', 'amount_print', 'status', 'is_diy'], 'integer'],
            [['user_id', 'cooperation_id', 'created_at', 'updated_at'], 'required'],
            [['title'], 'string', 'max' => 50],
            [['thumbnail_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'template_id' => 'Template ID',
            'classify_id' => '分类id',
            'open_id' => 'openid',
            'user_id' => '用户id',
            'folder_id' => '文件夹id',
            'cooperation_id' => '商户id',
            'title' => '模板标题',
            'thumbnail_url' => '模板缩略图',
            'thumbnail_id' => '模板id',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'is_diy' => '是否是自定义模板',
            'edit_from' => '编辑来源官方模板id',
            'amount_print' => '印刷次数',
        ];
    }
    public function frontendFields()
    {
        return ['template_id', 'user_id', 'open_id', 'folder_id', 'title', 'classify_id', 'thumbnail_url', 'thumbnail_id', 'status', 'is_diy', 'edit_from', 'amount_print'];
    }

    /**
     * 按热度排序
     * @return \yii\db\ActiveQuery
     */
    public static function sort()
    {
        return static::find()->orderBy(['template_id' => SORT_DESC]);
    }

    /**
     * 查找线上模板(因为分享的模板的user_id可能不是当前用户，所以，不对用户进行限制)
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
     * 根据模板id查询
     * @param $id
     * @return TemplateMember|null|\yii\db\ActiveRecord
     * @author thanatos <thanatos915@163.com>
     */
    public static function findById($id)
    {
        return static::active()->andWhere(['template_id' => $id])->one();
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
        if ($this->thumbnail_url)
            $data['thumbnailUrl'] = function () {
                return Url::to('@oss') . DIRECTORY_SEPARATOR . $this->thumbnail_url;
            };
        if ($this->isRelationPopulated('shares')) {
            $data['shareAuthority'] = function () {
                return $this->shares->authority;
            };
            $data['sharingPerson'] = function () {
                return $this->shares->sharing_person;
            };
            $data['sharedPerson'] = function () {
                return $this->shares->shared_person;
            };
        }
        if ($this->isRelationPopulated('classify')) {
            $data['classifyName'] = function () {
                return $this->classify->name;
            };
        }
        return $data;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShares()
    {
        return $this->hasOne(ShareTemplate::class, ['template_id' => 'template_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClassify(){
        return $this->hasOne(Classify::class, ['classify_id' => 'classify_id']);
    }
}
