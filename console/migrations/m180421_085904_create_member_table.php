<?php

use yii\db\Migration;

/**
 * Handles the creation of table `member`.
 */
class m180421_085904_create_member_table extends Migration
{

    public $tableName = '{{%member}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(11)->unsigned(),
            'username' => $this->string(30)->notNull()->defaultValue('')->comment('用户名'),
            'mobile' => $this->char(11)->notNull()->defaultValue('')->comment('用户手机号'),
            'sex' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('姓别'),
            'headimg_id' => $this->integer(11)->notNull()->defaultValue(0)->comment('头像ID'),
            'headimg_url' => $this->string(255)->notNull()->defaultValue('')->comment('头像url'),
            'coin' => $this->integer(11)->notNull()->defaultValue(0)->comment('图币'),
            'last_login_time' => $this->integer(11)->notNull()->defaultValue(0)->comment('最后登录时间'),
            'password_hash' => $this->char(60)->notNull()->defaultValue('')->comment('密码hash'),
            'salt' => $this->string(16)->notNull()->defaultValue('')->comment('旧salt'),
            'password' => $this->string(32)->notNull()->defaultValue('')->comment('旧password'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(10)->comment('用户状态'),
            'created_at' => $this->integer(11)->notNull()->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(11)->notNull()->unsigned()->comment('修改时间'),
        ]);

        $this->createIndex('idx-mobile', $this->tableName, 'mobile');

        $this->addCommentOnTable($this->tableName, '用户表');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
