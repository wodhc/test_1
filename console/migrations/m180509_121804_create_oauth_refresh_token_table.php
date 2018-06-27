<?php

use yii\db\Migration;

/**
 * Handles the creation of table `oauth_refresh_token`.
 */
class m180509_121804_create_oauth_refresh_token_table extends Migration
{

    public $tableName = '{{%oauth_refresh_token}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'refresh_token' => $this->string(40)->notNull(),
            'client_id' => $this->string(255)->notNull(),
            'user_id' => $this->integer(11)->notNull(),
            'expires' => $this->integer(10)->notNull(),
            'PRIMARY KEY(refresh_token)'
        ]);

        $this->createIndex('idx-client_id-user_id', $this->tableName, ['client_id', 'user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
