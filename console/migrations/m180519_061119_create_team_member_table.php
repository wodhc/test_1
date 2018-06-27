<?php

use yii\db\Migration;

/**
 * Handles the creation of table `team_member`.
 */
class m180519_061119_create_team_member_table extends Migration
{
    public $table_name = '{{%team_member}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table_name, [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer(11)->notNull()->comment('成员id')->defaultValue(0)->unsigned(),
            'team_id' => $this->integer(11)->comment('团队id')->defaultValue(0)->notNull()->unsigned(),
            'status'=> $this->tinyInteger(2)->notNull()->comment('状态')->defaultValue(10)->unsigned(),
            'role'=> $this->tinyInteger(2)->notNull()->comment('角色 1:创建者 2:管理员 4:设计师 4:成员')->defaultValue(4)->unsigned(),
            'invite_id' => $this->integer(11)->comment('邀请表的id')->defaultValue(0)->notNull()->unsigned(),
            'authority'=> $this->tinyInteger(2)->notNull()->comment('权限')->defaultValue(0)->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建日期')->defaultValue(0)->unsigned(),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->defaultValue(0)->unsigned(),
        ]);
        $this->addCommentOnTable($this->table_name, '团队成员信息表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
