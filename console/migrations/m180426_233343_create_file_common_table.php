<?php

use yii\db\Migration;

/**
 * Handles the creation of table `file_common`.
 */
class m180426_233343_create_file_common_table extends Migration
{
    public $tableName = '{{%file_common}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'file_id' => $this->primaryKey(11)->unsigned(),
            'etag' => $this->char(32)->notNull()->comment('文件唯一值'),
            'path' => $this->string(255)->notNull()->comment('文件路径'),
            'size' => $this->integer(1)->unsigned()->notNull()->comment('文件大小'),
            'type' => $this->tinyInteger(1)->unsigned()->notNull()->comment('文件类型'),
            'width' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('图片宽度'),
            'height' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('图片高度'),
            'sum' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('文件使用次数'),
            'created_at' => $this->integer(10)->unsigned()->notNull()->comment('创建时间'),
        ]);
        $this->addCommentOnTable($this->tableName, '上传文件总记录表');
        $this->createIndex('idx-etag', $this->tableName, 'etag', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
