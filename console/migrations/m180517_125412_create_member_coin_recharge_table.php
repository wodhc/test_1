<?php

use yii\db\Migration;

/**
 * Handles the creation of table `member_coin_recharge`.
 */
class m180517_125412_create_member_coin_recharge_table extends Migration
{

    public $tableName = '{{%member_coin_recharge}}';
    public $logName = '{{%member_coin_log}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'order_sn' => $this->bigInteger(20)->unsigned()->notNull()->comment('唯一标示'),
            'user_id' => $this->integer(11)->unsigned()->notNull()->comment('用户id'),
            'amount_coin' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('充值图币数'),
            'amount_money' => $this->decimal(10, 2)->notNull()->defaultValue(0.00)->comment('充值金额'),
            'payment_name' => $this->string(20)->notNull()->defaultValue('')->comment('支付方式'),
            'created_at' => $this->integer(11)->unsigned()->notNull()->comment('创建时间'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(10)->comment('状态: 10 未支付'),
            'payment_time' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('支付时间'),
        ]);
        $this->addCommentOnTable($this->tableName, '图币充值记录');
        $this->createIndex('idx-order_sn', $this->tableName, 'order_sn');
        $this->createIndex('idx-user_id-status', $this->tableName, ['user_id', 'status']);


        $this->createTable($this->logName, [
            'log_id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer(11)->unsigned()->notNull()->comment('用户id'),
            'log_type' => $this->tinyInteger(1)->unsigned()->notNull()->comment('变动类型'),
            'amount_coin' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('变动图币数'),
            'remark' => $this->string(255)->notNull()->defaultValue('')->comment('备注信息'),
            'created_at' => $this->integer(11)->unsigned()->notNull()->comment('变动时间'),
        ]);

        $this->addCommentOnTable($this->logName, '图币变动表');
        $this->createIndex('idx-user_id', $this->logName, 'user_id');
        $this->createIndex('idx-log_type', $this->logName, 'log_type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
        $this->dropTable($this->logName);
    }
}
