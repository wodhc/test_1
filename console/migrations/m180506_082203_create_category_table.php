<?php

use yii\db\Migration;

/**
 * 品类表
 */
class m180506_082203_create_category_table extends Migration
{

    public $tableName = '{{%category}}';

    /**
     * {@inheritdoc}
     */
    /**
     * @return bool|void
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(11)->unsigned(),
            'name' => $this->string(10)->notNull()->comment('品类名称'),
            'class_name' => $this->string(15)->notNull()->comment('品类class名'),
            'sort' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('品类排序'),
        ]);
        $this->addCommentOnTable($this->tableName, '品类表');

        // 添加初始记录
        $this->getDb()->createCommand()->batchInsert($this->tableName, ['name', 'class_name', 'sort'], [
            ['name' => '热门推荐', 'class_name' => 'icon-remen', 'sort' => 0],
            ['name' => '广告印刷', 'class_name' => 'icon-guanggaoyinshua', 'sort' => 0],
            ['name' => '展架画面', 'class_name' => 'icon-zhanjiahuamian', 'sort' => 0],
            ['name' => '社交媒体', 'class_name' => 'icon-shejiaomeiti', 'sort' => 0],
            ['name' => '网站电商', 'class_name' => 'icon-wangzhandianshang', 'sort' => 0],
            ['name' => '商务办公', 'class_name' => 'icon-shangwubangong', 'sort' => 0],
            ['name' => '创意生活', 'class_name' => 'icon-chuangyishenghuo', 'sort' => 0],
        ])->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
