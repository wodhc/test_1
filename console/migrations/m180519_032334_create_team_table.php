<?php

use yii\db\Migration;

/**
 * Handles the creation of table `team`.
 */
class m180519_032334_create_team_table extends Migration
{
    public $table_name = '{{%team}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table_name, [
            'id' => $this->primaryKey()->unsigned(),
            'coin' => $this->integer(11)->notNull()->comment('团队图币余额')->defaultValue(0)->unsigned(),
            'team_name' => $this->string(100)->notNull()->comment('团队名称')->defaultValue(''),
            'founder_id' => $this->integer(11)->comment('创建人id')->defaultValue(0)->notNull()->unsigned(),
            'colors'=> $this->string(500)->notNull()->comment('颜色')->defaultValue(''),
            'fonts'=> $this->string(500)->notNull()->comment('字体')->defaultValue(''),
            'team_mark'=> $this->string(200)->notNull()->comment('团队头像')->defaultValue(''),
            'file_id' => $this->integer(11)->comment('团队头像的文件id')->defaultValue(0)->notNull()->unsigned(),
            'team_level'=> $this->tinyInteger(2)->notNull()->comment('团队等级')->defaultValue(0)->unsigned(),
            'status'=> $this->tinyInteger(2)->notNull()->comment('团队状态')->defaultValue(10)->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建日期')->defaultValue(0)->unsigned(),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->defaultValue(0)->unsigned(),
        ]);
        $this->addCommentOnTable($this->table_name, '团队信息表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
