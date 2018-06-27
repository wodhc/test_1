<?php

use yii\db\Migration;

/**
 * Handles the creation of table `center_user_oauth`.
 */
class m180424_195400_create_center_user_oauth_table extends Migration
{
    public $tableName = '{{%member_oauth}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(11),
            'user_id' => $this->integer(11)->unsigned()->notNull()->comment('用户ID'),
            'oauth_name' => $this->tinyInteger(1)->notNull()->comment('第三方名称'),
            'oauth_key' => $this->string(50)->notNull()->comment('第三方key值'),
            'created_at' => $this->integer(11)->notNull()->unsigned()->comment('创建时间')
        ]);
        $this->addCommentOnTable($this->tableName, '第三方授权绑定信息');

        $this->createIndex('idx-oauth_name-oauth-key', $this->tableName, ['oauth_name', 'oauth_key']);
        $this->createIndex('idx-user_id', $this->tableName, 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
