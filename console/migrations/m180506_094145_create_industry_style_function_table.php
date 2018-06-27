<?php

use yii\db\Migration;

/**
 *
 */
class m180506_094145_create_industry_style_function_table extends Migration
{
    public $tableName = '{{%tag}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'tag_id' => $this->primaryKey(11)->unsigned(),
            'name' => $this->string(10)->notNull()->comment('Tag名称'),
            'type' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(0)->comment('tag种类'),
            'sort' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('排序名称'),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建时间')->unsigned(),
        ]);

        $this->addCommentOnTable($this->tableName, '平台Tag表');
        $this->createIndex('idx-type', $this->tableName, 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
