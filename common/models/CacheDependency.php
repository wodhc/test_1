<?php

namespace common\models;

use Yii;

/**
 * 系统缓存类
 * @property string $cache_name 缓存标识
 * @property string $cache_title 缓存名
 * @property int $updated_at 最后更新时间
 * @package common\models
 * @author thanatos <thanatos915@163.com>
 */
class CacheDependency extends \yii\db\ActiveRecord
{
    const OFFICIAL_CLASSIFY = 'official_classify';
    const OFFICIAL_HOT_RECOMMEND = 'official_hot_recommend';
    const OFFICIAL_TEMPLATE = 'official_template';
    const TEMPLATE_COVER = 'template_cover';
    /** @var string 消息缓存 */
    const MESSAGE = 'message';
    /** @var string 素材文件夹缓存 */
    const FOLDER_MATERIAL ='folder_material';
    /** @var string 模板文件夹缓存 */
    const FOLDER_TEMPLATE = 'folder_template';
    /** @var string 个人、团队模板缓存 */
    const TEMPLATE_USER = 'template_user_search';
    /** @var string 素材缓存 */
    const MATERIAL = 'material';
    /** @var string 收藏缓存 */
    const MY_FAVORITE = 'my_favorite';
    /** @var string 团队成员缓存 */
    const TEAM_MEMBER = 'team_member';
    /** @var string 团队缓存 */
    const TEAM = 'team';
    /** @var string 模板专题缓存 */
    const TEMPLATE_TOPIC = 'template_topic';
    /** @var string  官方素材缓存 */
    const MATERIAL_OFFICIAL = 'material_official';
    /** @var string 官方素材分类缓存 */
    const MATERIAL_CLASSIFY = 'material_classify';
    /** @var string 个人分享缓存 */
    const TEMPLATE_SHARE = 'template_share';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cache_dependency}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cache_name', 'cache_title', 'updated_at'], 'required'],
            [['updated_at'], 'integer'],
            [['cache_name', 'cache_title'], 'string', 'max' => 50],
            [['cache_name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cache_name' => '缓存标识',
            'cache_title' => '缓存名',
            'updated_at' => '最后更新时间',
        ];
    }

    /**
     * 根据缓存名生成缓存依赖sql
     * @param $name
     * @return string
     * @author thanatos <thanatos915@163.com>
     */
    public static function getDependencyCacheName($name)
    {
        return static::find()->where(['cache_name' => $name])->select('updated_at')->createCommand()->getRawSql();
    }

}
