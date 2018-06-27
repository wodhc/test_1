<?php

use yii\db\Migration;

/**
 * Handles the creation of table `member_login_history`.
 */
class m180421_092932_create_member_login_history_table extends Migration
{

    public $tablename = '{{%member_login_history}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tablename, [
            'history_id' => $this->primaryKey(11)->unsigned(),
            'user_id' => $this->integer(11)->unsigned()->notNull()->comment('用户id'),
            'method' => $this->tinyInteger(1)->notNull()->comment('登录方式'),
            'ip' => $this->string(64)->notNull()->comment('登录ip'),
            'http_user_agent' => $this->string(255)->notNull()->defaultValue('')->comment(''),
            'http_referer' => $this->string(255)->notNull()->defaultValue('')->comment('登录来源'),
            'login_url' => $this->string(255)->notNull()->defaultValue('')->comment('登录页面url'),
            'created_at' => $this->integer(11)->notNull()->unsigned()->comment('创建时间'),
        ]);

        $this->createIndex('idx-user_id-created_at', $this->tablename, ['user_id', 'created_at']);

        $this->addCommentOnTable($this->tablename, '用户登录记录表');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tablename);
    }
}
