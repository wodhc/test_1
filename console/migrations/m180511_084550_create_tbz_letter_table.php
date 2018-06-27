<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tbz_letter`.
 */
class m180511_084550_create_tbz_letter_table extends Migration
{
    public $table_name = '{{%tbz_letter}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table_name, [
            'id' => $this->primaryKey()->unsigned(),
            'title' => $this->string(50)->notNull()->comment('文章标题')->defaultValue(''),
            'subtitle' => $this->string(200)->notNull()->comment('副标题')->defaultValue(''),
            'description' => $this->string(500)->notNull()->comment('消息内容')->defaultValue(''),
            'type' => $this->tinyInteger(2)->comment('消息类型(1为公共通知，2为活动通知，3为个人消息')->defaultValue(1)->notNull()->unsigned(),
            'status' => $this->tinyInteger(2)->comment('信息状态(10为待发布，20为直接发布，7为到回收站,3为彻底删除)')->defaultValue(1)->notNull()->unsigned(),
            'sort' => $this->integer(10)->notNull()->comment('排序逆序')->defaultValue(0)->unsigned(),
            'user_id'=> $this->integer(10)->notNull()->comment('当消息为个人消息时，接收消息的用户')->defaultValue(0)->unsigned(),
            'created_at' => $this->integer(11)->notNull()->comment('创建日期')->defaultValue(0)->unsigned(),
            'updated_at' => $this->integer(11)->notNull()->comment('修改时间')->defaultValue(0)->unsigned(),
        ]);
        $this->addCommentOnTable($this->table_name, '图帮主信息通知表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
