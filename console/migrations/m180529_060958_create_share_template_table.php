<?php

use yii\db\Migration;

/**
 * Handles the creation of table `share_template`.
 */
class m180529_060958_create_share_template_table extends Migration
{
    public $tableName = '{{%share_template}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'template_id' => $this->integer(10)->notNull()->comment('个人模板的template_id')->defaultValue(0)->unsigned(),
            'shared_person' =>$this->integer(10)->notNull()->comment('被分享人的user_id')->defaultValue(0)->unsigned(),
            'sharing_person' =>$this->integer(10)->notNull()->comment('分享人的user_id')->defaultValue(0)->unsigned(),
            'authority' =>$this->integer(10)->notNull()->comment('权限，10可同步修改，20修改不同步')->defaultValue(20)->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建日期')->defaultValue(0)->unsigned(),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->defaultValue(0)->unsigned(),
        ]);
        $this->addCommentOnTable($this->tableName, '个人模板分享表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
