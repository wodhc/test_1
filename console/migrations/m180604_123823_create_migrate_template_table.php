<?php

use yii\db\Migration;

/**
 * Handles the creation of table `migrate_template`.
 */
class m180604_123823_create_migrate_template_table extends Migration
{

    public $tableName = '{{%migrate_template}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'migrate_id' => $this->primaryKey()->unsigned(),
            'template_id' => $this->integer()->unsigned()->notNull(),
            'template_type' => $this->tinyInteger()->unsigned()->notNull(),
            'status' => $this->tinyInteger()->unsigned()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->createIndex('idx-template_id', $this->tableName, 'template_id');
        $this->createIndex('idx-template_type', $this->tableName, 'template_type');
        $this->createIndex('idx-status', $this->tableName, 'status');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
