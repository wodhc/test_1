<?php

use yii\db\Migration;

/**
 * Handles the creation of table `folder_template_team`.
 */
class m180522_014539_create_folder_template_team_table extends Migration
{
    public $table_name = '{{%folder_template_team}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table_name, [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(50)->notNull()->comment('文件夹名称')->defaultValue(''),
            'color' => $this->string(200)->notNull()->comment('文件夹颜色')->defaultValue(''),
            'status' => $this->tinyInteger(2)->comment('文件夹状态')->defaultValue(10)->notNull()->unsigned(),
            'user_id' => $this->integer(10)->notNull()->comment('用户id')->defaultValue(0)->unsigned(),
            'team_id'=> $this->integer(10)->notNull()->comment('团队id')->defaultValue(0)->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建日期')->defaultValue(0)->unsigned(),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->defaultValue(0)->unsigned(),
        ]);
        $this->addCommentOnTable($this->table_name, '团队模板文件夹信息表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
