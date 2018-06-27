<?php

use yii\db\Migration;

/**
 * Handles the creation of table `material_classify`.
 */
class m180523_050714_create_material_classify_table extends Migration
{
    public $tableName = '{{%material_classify}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'cid' => $this->primaryKey()->unsigned(),
            'pid' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('父分类标识'),
            'name' => $this->string(30)->notNull()->comment('分类名称'),
            'status' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(10)->comment('素材分类状态'),
            'created_at' => $this->integer(11)->notNull()->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(11)->notNull()->unsigned()->comment('修改时间'),
        ]);
        $this->createIndex('idx-status', $this->tableName, 'status');
        $this->createIndex('idx-pid', $this->tableName, 'pid');
        $this->addCommentOnTable($this->tableName, '素材分类表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
