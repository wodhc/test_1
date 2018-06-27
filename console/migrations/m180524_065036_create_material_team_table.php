<?php

use yii\db\Migration;

/**
 * Handles the creation of table `material_team`.
 */
class m180524_065036_create_material_team_table extends Migration
{
    public $tableName = '{{%material_team}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer(11)->unsigned()->notNull()->comment('用户id')->defaultValue(0),
            'team_id' => $this->integer(11)->unsigned()->notNull()->comment('团队id')->defaultValue(0),
            'folder_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('文件夹'),
            'file_name' => $this->string(255)->notNull()->defaultValue('')->comment('文件名'),
            'thumbnail' => $this->string(255)->notNull()->defaultValue('')->comment('图片路径'),
            'file_id' => $this->integer(11)->notNull()->defaultValue(0)->unsigned()->comment('文件id'),
            'created_at' => $this->integer(11)->notNull()->comment('创建时间')->unsigned()->defaultValue(0),
            'status' => $this->tinyInteger(2)->notNull()->comment('状态，3为删除，7为到回收站，10为正常')->unsigned()->defaultValue(0),
        ]);
        $this->createIndex('idx-user_id-folder_id', $this->tableName, ['user_id', 'folder_id']);
        $this->addCommentOnTable($this->tableName, '团队素材列表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}