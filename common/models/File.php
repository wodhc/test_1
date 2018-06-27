<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%file}}".
 *
 * @property int $file_id
 * @property int $user_id 用户id
 * @property int $team_id 团队id
 * @property string $file_path 文件路径
 * @property string $file_type 文件类型
 * @property int $file_size 文件大小
 * @property string $file_name 文件原名
 * @property int $file_width 图片宽度
 * @property int $file_height 图片高度
 * @property int $status 文件状态
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 */
class File extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%file}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'team_id', 'file_path', 'file_type', 'file_size',], 'required'],
            [['user_id', 'team_id', 'file_size', 'file_width', 'file_height', 'created_at', 'updated_at'], 'integer'],
            [['file_path', 'file_name'], 'string', 'max' => 255],
            [['file_type'], 'string', 'max' => 10],
            [['status'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'File ID',
            'user_id' => 'User ID',
            'team_id' => 'Team ID',
            'file_path' => 'File Path',
            'file_type' => 'File Type',
            'file_size' => 'File Size',
            'file_name' => 'File Name',
            'file_width' => 'File Width',
            'file_height' => 'File Height',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
