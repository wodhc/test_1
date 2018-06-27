<?php

use yii\db\Migration;

/**
 * Handles the creation of table `wechat_config`.
 */
class m180423_051335_create_wechat_config_table extends Migration
{

    public $tableName = '{{%wechat_config}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'wechat_id' => $this->primaryKey(11)->unsigned(),
            'app_id' => $this->string(32)->notNull()->comment('AppId'),
            'secret' => $this->string(32)->notNull()->comment('secret'),
            'name' => $this->string(32)->notNull()->comment('内部标识'),
            'token' => $this->string(200)->notNull()->comment('token值'),
            'aes_key' => $this->string(255)->notNull()->defaultValue('')->comment('加密值'),
            'merchant_id' => $this->string(32)->notNull()->defaultValue('')->comment('商户号'),
            'key' => $this->string(200)->notNull()->defaultValue('')->comment('支付密钥'),
            'created_at' => $this->integer(11)->notNull()->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(11)->notNull()->unsigned()->comment('修改时间'),
        ]);

        $this->addCommentOnTable($this->tableName, '微信的配置信息');
        $this->createIndex('idx-name', $this->tableName, 'name');

        if (YII_ENV_DEV) {
            $this->batchInsert($this->tableName, ['app_id', 'secret', 'name', 'token', 'aes_key', 'merchant_id', 'key', 'created_at', 'updated_at'], [
                ['wxc532cc9a793ef689', 'd4624c36b6795d1d99dcf0547af5443d', 'tubangzhu', 'dHVhYm5nemh1', '', '', '', time(), time()],
            ]);
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
