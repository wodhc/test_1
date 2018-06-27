<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tag_relation_classify`.
 */
class m180511_090703_create_tag_relation_classify_table extends Migration
{
    public $table_name = '{{%tag_relation_classify}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table_name, [
            'id' => $this->primaryKey()->unsigned(),
            'tag_id' => $this->integer(10)->notNull()->comment('tag表id')->defaultValue(0)->unsigned(),
            'classify_id' =>$this->integer(10)->notNull()->comment('classify表id')->defaultValue(0)->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建日期')->defaultValue(0)->unsigned(),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->defaultValue(0)->unsigned(),
        ]);
        $this->addCommentOnTable($this->table_name, 'tag和classify关联表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
