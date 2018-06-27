<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tbz_subject`.
 */
class m180510_020848_create_tbz_subject_table extends Migration
{
    public $table_name = '{{%tbz_subject}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table_name, [
            'id' => $this->primaryKey()->unsigned(),
            'title' => $this->string(150)->notNull()->comment('文章标题')->defaultValue(''),
            'product' => $this->string(30)->notNull()->comment('专题url缩写'),
            'description' => $this->string(255)->notNull()->comment('专题描述')->defaultValue(''),
            'thumbnail' => $this->integer(10)->comment('缩略图的文件id')->defaultValue(0)->notNull(),
            'banner' => $this->integer(10)->comment('专题内页banner图的文件id')->defaultValue(0)->notNull(),
            'seo_title' => $this->string(100)->notNull()->comment('SEO标题')->defaultValue(''),
            'seo_keyword' => $this->string(255)->comment('SEO关键词')->notNull()->defaultValue(''),
            'seo_description' => $this->string(255)->notNull()->comment('SEO描述')->defaultValue(''),
            'status' => $this->tinyInteger(2)->notNull()->comment('是否上线')->defaultValue(20)->unsigned(),
            'sort' => $this->integer(10)->notNull()->comment('排序逆序')->defaultValue(0)->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建日期')->defaultValue(0)->unsigned(),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->defaultValue(0)->unsigned(),
        ]);
        $this->addCommentOnTable($this->table_name, '模板封面表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
