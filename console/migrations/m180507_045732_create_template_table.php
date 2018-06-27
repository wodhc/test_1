<?php

use yii\db\Migration;

/**
 * Handles the creation of table `template`.
 */
class m180507_045732_create_template_table extends Migration
{
    public $official = '{{%template_official}}';
    public $member = '{{%template_member}}';
    public $team = '{{%template_team}}';
    public $official_tag = '{{%template_official_tag}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        // official
        $this->createTable($this->official, [
            'template_id' => $this->primaryKey(11)->unsigned(),
            'user_id' => $this->integer(11)->unsigned()->notNull()->comment('用户id'),
            'cooperation_id' => $this->integer(11)->unsigned()->notNull()->comment('商户id'),
            'category_id' => $this->integer(11)->unsigned()->notNull()->comment('品类ID'),
            'classify_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('分类id'),
            'title' => $this->string(50)->notNull()->defaultValue('')->comment('模板标题'),
            'thumbnail_url' => $this->string(255)->notNull()->defaultValue('')->comment('模板缩略图'),
            'thumbnail_id' => $this->integer(11)->notNull()->unsigned()->defaultValue(0)->comment('模板id'),
            'status' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(5)->comment('状态'),
            'created_at' => $this->integer(11)->notNull()->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(11)->notNull()->unsigned()->comment('修改时间'),
            'thumbnail_updated_at' => $this->integer(11)->notNull()->unsigned()->comment('缩略图修改时间'),
            'price' => $this->smallInteger(1)->notNull()->defaultValue(0)->unsigned()->comment('模板价格'),
            'amount_edit' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('编辑量'),
            'virtual_edit' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('虚拟编辑量'),
            'amount_view' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('浏览量'),
            'virtual_view' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('虚拟浏览量'),
            'amount_favorite' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('收藏量'),
            'virtual_favorite' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('虚拟收藏量'),
            'amount_buy' => $this->integer(11)->notNull()->unsigned()->defaultValue(0)->comment('购买量'),
            'sort' => $this->smallInteger(1)->notNull()->unsigned()->defaultValue(0)->comment('排序'),
            'recommend_at' => $this->integer(11)->notNull()->unsigned()->defaultValue(0)->comment('推荐到热门场景时间'),
            'content' => 'longtext not null default "" COMMENT "模板数据"',
        ]);
        $this->addCommentOnTable($this->official, '官方模板信息表');

        // official_tag
        $this->createTable($this->official_tag, [
            'template_id' => $this->integer(11)->unsigned()->notNull()->comment('模板id'),
            'tag_id' => $this->integer(11)->unsigned()->notNull()->comment('tag_id'),
            'created_at' => $this->integer(10)->unsigned()->notNull()->comment('修改时间'),
            'PRIMARY KEY(template_id, tag_id)'
        ]);
        $this->addCommentOnTable($this->official, '官方模板tag表');

        $this->createIndex('idx-template_id', $this->official_tag, 'template_id');
        $this->createIndex('idx-tag_id', $this->official_tag, 'tag_id');


        // member
        $this->createTable($this->member, [
            'template_id' => $this->primaryKey(11)->unsigned(),
            'classify_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('分类id'),
            'open_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('openid'),
            'user_id' => $this->integer(11)->unsigned()->notNull()->comment('用户id'),
            'folder_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('文件夹id'),
            'cooperation_id' => $this->integer(11)->unsigned()->notNull()->comment('商户id'),
            'title' => $this->string(50)->notNull()->defaultValue('')->comment('模板标题'),
            'thumbnail_url' => $this->string(255)->notNull()->defaultValue('')->comment('模板缩略图'),
            'thumbnail_id' => $this->integer(11)->notNull()->unsigned()->defaultValue(0)->comment('模板id'),
            'status' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(5)->comment('状态'),
            'created_at' => $this->integer(11)->notNull()->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(11)->notNull()->unsigned()->comment('修改时间'),
            'is_diy' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('是否是自定义模板'),
            'edit_from' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('编辑来源官方模板id'),
            'amount_print' => $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('印刷次数'),
            'content' => 'longtext not null default "" COMMENT "模板数据"',
        ]);

        $this->addCommentOnTable($this->member, '用户模板信息表');


        // team
        $this->createTable($this->team, [
            'template_id' => $this->primaryKey(11)->unsigned(),
            'classify_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('分类id'),
            'open_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('openid'),
            'user_id' => $this->integer(11)->unsigned()->notNull()->comment('用户id'),
            'team_id' => $this->integer(11)->unsigned()->notNull()->comment('团队id'),
            'folder_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('文件夹id'),
            'cooperation_id' => $this->integer(11)->unsigned()->notNull()->comment('商户id'),
            'title' => $this->string(50)->notNull()->defaultValue('')->comment('模板标题'),
            'thumbnail_url' => $this->string(255)->notNull()->defaultValue('')->comment('模板缩略图'),
            'thumbnail_id' => $this->integer(11)->notNull()->unsigned()->defaultValue(0)->comment('模板id'),
            'status' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(5)->comment('状态'),
            'created_at' => $this->integer(11)->notNull()->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(11)->notNull()->unsigned()->comment('修改时间'),
            'is_diy' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('是否是自定义模板'),
            'edit_from' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('编辑来源官方模板id'),
            'amount_print' => $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('印刷次数'),
            'content' => 'longtext not null default "" COMMENT "模板数据"',
        ]);

        $this->addCommentOnTable($this->team, '团队模板信息表');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->official);
        $this->dropTable($this->official_tag);
        $this->dropTable($this->member);
        $this->dropTable($this->team);
    }
}
