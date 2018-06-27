<?php

namespace console\models;

use Yii;
use yii\db\Exception;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%template_page}}".
 *
 * @property integer $id
 * @property string $front_id
 * @property integer $uid
 * @property integer $tpl_id
 * @property string $edit_config
 * @property integer $index
 * @property string $thumbnail
 * @property string $svg
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $removed_at
 * @property integer $status
 * @property integer $thumbnail_updated_at
 * @property integer $display_updated_at
 * @property TemplateElement[] $elements
 */
class TemplatePage extends \yii\db\ActiveRecord
{
    /** 状态: 物理删除*/
    const STATUS_DATA_DELETE = -2;
    /** 状态: 删除*/
    const STATUS_REMOVE = 0;
    /** 状态: 正常*/
    const STATUS_NORMAL = 1;
    
    /** @var TemplateCenter */
    private $_template;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%template_page}}';
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
            [['uid', 'tpl_id', 'index', 'created_at', 'updated_at', 'removed_at', 'status', 'thumbnail_updated_at', 'display_updated_at'], 'integer'],
            [['edit_config'], 'string'],
            [['front_id'], 'string', 'max' => 30],
            [['thumbnail', 'svg'], 'string', 'max' => 255],
        ];
    }
    
    public function fields(){
        return ['id', 'uid', 'tpl_id',
            'edit_config' => function(){
                return json_decode($this->edit_config, true);
            },
            'thumbnail' => function(){ return Url::to('@oss').$this->thumbnail; }, 'status'];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'front_id' => '前端自定义ID',
            'uid' => '用户uid',
            'tpl_id' => '模板id',
            'edit_config' => '页面的配置信息',
            'index' => '页面索引',
            'thumbnail' => '缩略图',
            'svg' => 'svg数据',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'removed_at' => '删除时间',
            'status' => '页面状态: 0删除 1正常',
            'thumbnail_updated_at' => '缩略图，最后更新时间',
            'display_updated_at' => '与显示相关内容，最后时间',
        ];
    }
    
    public function getElements()
    {
        return $this->hasMany(TemplateElement::className(), ['page_id' => 'id'])->onCondition(['status' => TemplateElement::STATUS_NORMAL])->orderBy(['index' => SORT_ASC]);
    }
    
    /**
     * 页面默认的edit_config
     * @param bool $encode
     * @return array|string
     */
    public function getDefaultEditConfig($encode = true) {
        $data =  [
            'backgroundID' => "",
            'backgroundColor' => '',
            'customBackgroundUrl' => '',
            'guides' => [],
            'groups' => [],
        ];
        return $encode ? json_encode($data) : $data;
    }
    
    /**
     * @param int   $template_id
     * @param array $ids
     * @author thanatos
     */
    /**
     * @param int   $template_id 模板ID
     * @param array $ids         页面IDs
     * @param bool  $front
     * @author thanatos
     * @return array|TemplatePage[]|\yii\db\ActiveRecord[]
     */
    public static function findByIds($template_id, $ids, $front = false)
    {
        $condition['tpl_id'] = $template_id;
        if($front){
            $condition['front_id'] = $ids;
        }else{
            $condition['id'] = $ids;
        }
        return static::find()->where($condition)->all();
    }
    
}
