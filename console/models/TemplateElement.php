<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "{{%template_element}}".
 *
 * @property integer $id
 * @property string $front_id
 * @property integer $uid
 * @property integer $tpl_id
 * @property integer $page_id
 * @property string $type
 * @property string $edit_data
 * @property string $edit_config
 * @property integer $index
 * @property integer $last_uid
 * @property integer $size
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $removed_at
 * @property integer $status
 * @property integer $page_status
 * @property string $copy_hash
 * @property integer $display_updated_at
 */
class TemplateElement extends \yii\db\ActiveRecord
{
    /** 状态: 物理删除*/
    const STATUS_DATA_DELETE = -2;
    /** 状态: 删除*/
    const STATUS_REMOVE = 0;
    /** 状态: 正常*/
    const STATUS_NORMAL = 1;
    
    const TYPE_SVG = 'svg';
    const TYPE_IMAGE = 'image';
    const TYPE_FORM  = 'interaction';
    const TYPE_CONTAINER = 'container';
    const TYPE_GROUPTEXT = 'groupText';
    const TYPE_TEXT = 'text';
    const TYPE_GRID = 'grid';
    const TYPE_PATTERN = 'pattern';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%template_element}}';
    }

    public static function getDb()
    {
        return Yii::$app->dbMigrateTbz;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'tpl_id', 'page_id', 'index', 'last_uid', 'size', 'created_at', 'updated_at', 'removed_at', 'status', 'page_status', 'display_updated_at'], 'integer'],
            [['edit_data', 'edit_config'], 'string'],
            [['front_id'], 'string', 'max' => 30],
            [['type', 'copy_hash'], 'string', 'max' => 50],
        ];
    }
    
    public function fields(){
        return ['id', 'uid', 'tpl_id', 'page_id', 'type',
            'edit_data' => function(){
                return json_decode($this->edit_data, true);
            },
            'edit_config' => function(){
                return json_decode($this->edit_config, true);
            },
            'status', 'page_status'];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'front_id' => 'Front ID',
            'uid' => 'Uid',
            'tpl_id' => 'Tpl ID',
            'page_id' => 'Page ID',
            'type' => 'Type',
            'edit_data' => 'Edit Data',
            'edit_config' => 'Edit Config',
            'index' => 'Index',
            'last_uid' => 'Last Uid',
            'size' => 'Size',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'removed_at' => 'Removed At',
            'status' => 'Status',
            'page_status' => 'Page Status',
            'copy_hash' => 'Copy Hash',
            'display_updated_at' => 'Display Updated At',
        ];
    }
    
    public static function findById($id)
    {
        return static::findOne(['id' => $id, 'status' => static::STATUS_NORMAL]);
    }
    
}
