<?php

use yii\db\Migration;

/**
 * Handles the creation of table `template_topic`.
 */
class m180524_104611_create_template_topic_table extends Migration
{
    public $tableName = '{{%template_topic}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'template_id' => $this->integer(10)->notNull()->comment('官方模板id')->defaultValue(0)->unsigned(),
            'topic_id' =>$this->integer(10)->notNull()->comment('模板专题id')->defaultValue(0)->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建日期')->defaultValue(0)->unsigned(),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->defaultValue(0)->unsigned(),
        ]);
        $this->addCommentOnTable($this->tableName, '模板专题和官方模板关联表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
