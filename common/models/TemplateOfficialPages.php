<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;

/**
 * This is the model class for table "{{%template_official_pages}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="TemplateOfficialPages"))
 *
 * @property int $page_id @SWG\Property(property="pageId", type="integer", description="")
 * @property int $template_id 模板ID @SWG\Property(property="templateId", type="integer", description=" 模板ID")
 * @property int $page_index 页面索引 @SWG\Property(property="pageIndex", type="integer", description=" 页面索引")
 * @property string $thumbnail 页面缩略图 @SWG\Property(property="thumbnail", type="string", description=" 页面缩略图")
 * @property int $thumbnail_id 缩略图id @SWG\Property(property="thumbnailId", type="integer", description=" 缩略图id")
 * @property int $created_at 创建时间 @SWG\Property(property="createdAt", type="integer", description=" 创建时间")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 */
class TemplateOfficialPages extends \yii\db\ActiveRecord
{

    use TimestampTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%template_official_pages}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id', 'page_index', 'created_at', 'updated_at'], 'required'],
            [['template_id', 'thumbnail_id', 'created_at', 'updated_at'], 'integer'],
            [['page_index'], 'string', 'max' => 1],
            [['thumbnail'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'page_id' => 'Page ID',
            'template_id' => '模板ID',
            'page_index' => '页面索引',
            'thumbnail' => '页面缩略图',
            'thumbnail_id' => '缩略图id',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }
}
