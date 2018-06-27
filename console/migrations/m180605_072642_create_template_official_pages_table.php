<?php

use yii\db\Migration;

/**
 * Handles the creation of table `template_official_pages`.
 */
class m180605_072642_create_template_official_pages_table extends Migration
{
    public $tableName = '{{%template_official_pages}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'page_id' => $this->primaryKey()->unsigned(),
            'template_id' => $this->integer(11)->unsigned()->notNull()->comment('模板ID'),
            'page_index' => $this->tinyInteger(1)->unsigned()->notNull()->comment('页面索引'),
            'thumbnail' => $this->string(255)->notNull()->defaultValue('')->comment('页面缩略图'),
            'thumbnail_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('缩略图id'),
            'created_at' => $this->integer(11)->notNull()->comment('创建时间'),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间'),
        ]);
        $this->addCommentOnTable($this->tableName, '官方模板页面缩略图');
        $this->createIndex('idx-template_id', $this->tableName, 'template_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
