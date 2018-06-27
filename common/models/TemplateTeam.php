<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;
use common\components\traits\ModelFieldsTrait;
use yii\helpers\Url;
/**
 * This is the model class for table "{{%template_team}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="TemplateTeam"))
 *
 * @property int $template_id @SWG\Property(property="templateId", type="integer", description="")
 * @property int $classify_id 分类id @SWG\Property(property="classifyId", type="integer", description=" 分类id")
 * @property int $open_id openid @SWG\Property(property="openId", type="integer", description=" openid")
 * @property int $user_id 用户id @SWG\Property(property="userId", type="integer", description=" 用户id")
 * @property int $team_id 团队id @SWG\Property(property="teamId", type="integer", description=" 团队id")
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
class TemplateTeam extends \yii\db\ActiveRecord
{

    use TimestampTrait;
    use ModelFieldsTrait;

    /** @var string 模板正常状态 */
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
        return '{{%template_team}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['classify_id', 'open_id', 'user_id', 'folder_id', 'cooperation_id', 'thumbnail_id', 'created_at', 'updated_at', 'edit_from', 'amount_print', 'status', 'is_diy', 'team_id'], 'filter', 'filter' => 'intval'],
            [['classify_id', 'open_id', 'user_id', 'folder_id', 'cooperation_id', 'thumbnail_id', 'created_at', 'updated_at', 'edit_from', 'amount_print', 'status', 'is_diy', 'team_id'], 'integer'],
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
            'team_id' => '团队id',
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
        return ['template_id', 'open_id', 'folder_id', 'title', 'classify_id', 'thumbnail_url', 'thumbnail_id', 'status', 'is_diy', 'edit_from', 'amount_print', 'team_id'];
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
     * 查找线上模板
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
     * @param $id
     * @param int $team_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function findById($id, $team_id = 0)
    {
        if (Yii::$app->request->isFrontend()) {
            return static::active()->andWhere(['template_id' => $id,'team_id'=>$team_id])->one();
        } else {
            return static::active()->andWhere(['template_id' => $id])->one();
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
        $data['thumbnailUrl'] = function () {
            return Url::to('@oss') . DIRECTORY_SEPARATOR . 'uploads' . $this->thumbnail_url;
        };
        return $data;
    }
}
