<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_log`.
 */
class m180517_174707_create_order_log_table extends Migration
{
    public $tableName = '{{%order_log}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'log_id' => $this->primaryKey()->unsigned(),
            'order_id' => $this->integer(11)->unsigned()->notNull()->comment('订单ID'),
            'order_sn' => $this->bigInteger(20)->unsigned()->notNull()->comment('订单编号'),
            'admin_id' => $this->integer(11)->unsigned()->notNull()->comment('管理员ID'),
            'admin_name' => $this->string(50)->notNull()->defaultValue('')->comment('管理员名'),
            'remark' => $this->string(255)->notNull()->defaultValue('')->comment('备注'),
            'order_status' => $this->tinyInteger(1)->notNull()->notNull()->comment('操作后的状态'),
            'created_at' => $this->integer(11)->unsigned()->notNull()->comment('操作时间'),
        ]);
        $this->addCommentOnTable($this->tableName, '管理员订单操作表');
        $this->createIndex('idx-order_id', $this->tableName, 'order_id');
        $this->createIndex('idx-order_sn', $this->tableName, 'order_sn');
        $this->createIndex('idx-admin_id', $this->tableName, 'admin_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
