<?php

use yii\db\Migration;

/**
 * Handles the creation of table `material_official`.
 */
class m180522_113312_create_material_official_table extends Migration
{
    public $tableName = '{{%material_official}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer(11)->unsigned()->notNull()->comment('用户ID'),
            'cid' => $this->string(255)->notNull()->defaultValue('')->comment('素材分类ID'),
            'name' => $this->string(50)->unsigned()->notNull()->defaultValue('')->comment('素材名'),
            'tags' => $this->string(255)->notNull()->defaultValue('')->comment('素材搜索标签'),
            'thumbnail' => $this->string(255)->notNull()->defaultValue('')->comment('缩略图路径'),
            'thumbnail_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('缩略图id'),
            'file_path' => $this->string(255)->notNull()->defaultValue('')->comment('文件路径'),
            'file_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('文件id'),
            'file_type' => $this->tinyInteger(1)->unsigned()->notNull()->comment('文件类型'),
            'width' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('宽度'),
            'height' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('高度'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(10)->comment('素材状态'),
            'created_at' => $this->integer(11)->unsigned()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer(11)->unsigned()->notNull()->comment('修改时间'),
        ]);
        $this->addCommentOnTable($this->tableName, '官方素材表');
        $this->createIndex('idx-tags', $this->tableName, 'tags');
        $this->createIndex('idx-cid', $this->tableName, 'cid');
        $this->createIndex('idx-user_id', $this->tableName, 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
