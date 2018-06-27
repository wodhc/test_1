<?php
/**

* @author thanatos <thanatos915@163.com>
 *
 */
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace console\controllers;


use Yii;
use common\models\CacheDependency;
use common\models\CacheGroup;
use yii\console\Controller;
use yii\helpers\Console;

class CacheDependencyController extends Controller
{

    /**
     * 初始化缓存表数据
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function actionInit()
    {
        $db = CacheDependency::getDb();
        $db->createCommand()->delete(CacheDependency::tableName())->execute();
        $db->createCommand()->delete(CacheGroup::tableName())->execute();

        // 添加系统缓存依赖记录
        $db->createCommand()->batchInsert(CacheDependency::tableName(), ['cache_name', 'cache_title', 'updated_at'], [
            ['cache_name' => 'official_classify', 'cache_title' => '官方分类缓存', 'updated_at' => time()],
            ['classify_search_template','模板中心页缓存',time()],
            ['folder_material','素材文件夹缓存',time()],
            ['folder_template','模板文件夹缓存',time()],
            ['official_hot_recommend','模板中心页热门推荐',time()],
            ['folder','文件夹缓存',time()],
            ['message','消息缓存',time()],
            ['template_cover','模板专题缓存',time()],
            ['template_user_search','个人、团队模板缓存',time()],
            ['material','素材缓存',time()],
            ['my_favorite','收藏缓存',time()],
            ['team_member','团队成员缓存',time()],
            ['team','团队缓存',time()],
            ['template_topic','模板专题页缓存',time()],
            ['material_official','官方素材缓存',time()],
            ['material_classify','官方素材分类缓存',time()],
            ['template_share','个人模板分享缓存',time()],
        ])->execute();

        $db->createCommand()->batchInsert(CacheGroup::tableName(), ['table_name', 'cache_name'], [
            ['table_name' => 'tu_category', 'official_classify'],
            ['table_name' => 'tu_classify', 'official_classify'],
            ['tu_template_official','classify_search_template'],
            ['tu_classify','classify_search_template'],
            ['tu_folder_material_member','folder_material'],
            ['tu_folder_material_team','folder_material'],
            ['tu_folder_template_member','folder_template'],
            ['tu_folder_template_team','folder_template'],
            ['tu_tbz_letter','message'],
            ['tu_tbz_subject','template_cover'],
            ['tu_template_member','template_user_search'],
            ['tu_template_team','template_user_search'],
            ['tu_material_member','material'],
            ['tu_material_team','material'],
            ['tu_my_favorite_member','my_favorite'],
            ['tu_my_favorite_team','my_favorite'],
            ['tu_my_favorite_member','official_hot_recommend'],
            ['tu_my_favorite_team','official_hot_recommend'],
            ['tu_classify','official_hot_recommend'],
            ['tu_template_official','official_hot_recommend'],
            ['tu_team_member','team_member'],
            ['tu_member','team_member'],
            ['tu_team_member','team'],
            ['tu_member','team'],
            ['tu_team','team'],
            ['tu_template_official','template_topic'],
            ['tu_template_topic','template_topic'],
            ['tu_material_official','material_official'],
            ['tu_material_classify','material_classify'],
            ['tu_share_template','template_share'],
            ['tu_share_template','template_user_search'],
        ])->execute();

        $this->stdout('Success' . "\n", Console::FG_GREEN);
    }

}