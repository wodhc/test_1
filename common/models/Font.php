<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;

/**
 * This is the model class for table "{{%font}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="Front"))
 *
 * @property int $font_id @SWG\Property(property="fontId", type="integer", description="")
 * @property string $font_name 字体名称 @SWG\Property(property="fontName", type="string", description=" 字体名称")
 * @property string $thumbnail 字体缩略图 @SWG\Property(property="thumbnail", type="string", description=" 字体缩略图")
 * @property int $thumbnail_id 缩略图ID @SWG\Property(property="thumbnailId", type="integer", description=" 缩略图ID")
 * @property string $path 字体原文件 @SWG\Property(property="path", type="string", description=" 字体原文件")
 * @property int $path_id 原文件ID @SWG\Property(property="pathId", type="integer", description=" 原文件ID")
 * @property int $is_official 是否是官方字体 @SWG\Property(property="isOfficial", type="integer", description=" 是否是官方字体")
 * @property int $team_id 团队ID @SWG\Property(property="teamId", type="integer", description=" 团队ID")
 * @property int $copyright 是否显示版权 @SWG\Property(property="copyright", type="integer", description=" 是否显示版权")
 * @property string $group 字体分组 @SWG\Property(property="group", type="string", description=" 字体分组")
 * @property int $status 状态 @SWG\Property(property="status", type="integer", description=" 状态")
 * @property int $created_at 创建时间 @SWG\Property(property="createdAt", type="integer", description=" 创建时间")
 */
class Font extends \yii\db\ActiveRecord
{

    use TimestampTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%font}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['thumbnail', 'thumbnail_id', 'path', 'path_id'], 'required'],
            [['font_name', 'thumbnail'], 'default', 'value' => ''],
            [['is_official', 'team_id', 'copyright'], 'default', 'value' => 0],
            ['group', 'default', 'value' => 'chinese'],
            [['is_official', 'team_id'], 'filter', 'filter' => 'intval'],
            [['thumbnail_id', 'path_id', 'is_official', 'team_id', 'status', 'created_at', 'copyright'], 'integer'],
            [['font_name', 'thumbnail', 'path'], 'string', 'max' => 255],
            [['group'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'font_id' => 'Font ID',
            'font_name' => '字体名称',
            'thumbnail' => '字体缩略图',
            'thumbnail_id' => '缩略图ID',
            'path' => '字体原文件',
            'path_id' => '原文件ID',
            'is_official' => '是否是官方字体',
            'team_id' => '团队ID',
            'status' => '状态',
            'created_at' => '创建时间',
        ];
    }
}
