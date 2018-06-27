<?php

use yii\db\Migration;

/**
 * Handles the creation of table `my_favorite_team`.
 */
class m180522_014814_create_my_favorite_team_table extends Migration
{
    public $table_name = '{{%my_favorite_team}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table_name, [
            'id' => $this->primaryKey()->unsigned(),
            'template_id' => $this->integer(11)->notNull()->comment('模板id')->defaultValue(0)->unsigned(),
            'user_id' => $this->integer(11)->notNull()->comment('用户id')->defaultValue(0)->unsigned(),
            'team_id' => $this->integer(11)->comment('团队id')->defaultValue(0)->notNull()->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建日期')->defaultValue(0)->unsigned(),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->defaultValue(0)->unsigned(),
        ]);
        $this->addCommentOnTable($this->table_name, '团队模板收藏记录表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
