<?php

use yii\db\Migration;

/**
 * Handles the creation of table `font`.
 */
class m180530_081509_create_font_table extends Migration
{
    public $tableName = '{{%font}}';

    /**
     * @return bool|void
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'font_id' => $this->primaryKey()->unsigned(),
            'font_name' => $this->string(255)->notNull()->comment('字体名称'),
            'thumbnail' => $this->string(255)->notNull()->comment('字体缩略图'),
            'thumbnail_id' => $this->integer(11)->notNull()->unsigned()->comment('缩略图ID'),
            'path' => $this->string(255)->notNull()->comment('字体原文件'),
            'path_id' => $this->integer(11)->notNull()->unsigned()->comment('原文件ID'),
            'is_official' => $this->tinyInteger(1)->notNull()->defaultValue(1)->unsigned()->comment('是否是官方字体'),
            'team_id' => $this->integer(11)->notNull()->unsigned()->comment('团队ID'),
            'copyright' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(0)->comment('是否显示版权'),
            'group' => $this->string(20)->notNull()->defaultValue('chinese')->comment('字体分组'),
            'status' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(20)->comment('状态'),
            'created_at' => $this->integer(11)->notNull()->unsigned()->comment('创建时间'),
        ]);

        $this->addCommentOnTable($this->tableName, '字体库');
        $this->createIndex('idx-is_official-status', $this->tableName, ['is_official', 'status']);
        $this->createIndex('idx-team_id', $this->tableName, 'team_id');

        $data = [];
        // A
        for ($i = 0; $i<= 39; $i++) {
            if ($i == 30 || $i == 31){
                continue;
            }
            if ($i ==  40) {
                $fontName = 'a' . '1b';
            } else {
                $fontName = 'a' . $i;
            }
            $path = 'updata/fonts/201805/' . $fontName . '.ttf';
            $fileCommon = $this->uploadFont($path);
            $data[] = [
                'font_name' => $fontName,
                'thumbnail' => '',
                'thumbnail_id' => 0,
                'path' => $path,
                'path_id' => $fileCommon->file_id,
                'is_official' => 1,
                'team_id' => 0,
                'copyright' => 0,
                'group' => 'chinese',
                'status' => 20,
                'created_at' => time()
            ];
        }
        // B
        for($i = 1; $i < 9; $i++) {
            $fontName = 'b' . $i;
            $path = 'updata/fonts/201805/' . $fontName . '.ttf';
            $fileCommon = $this->uploadFont($path);
            $data[] = [
                'font_name' => $fontName,
                'thumbnail' => '',
                'thumbnail_id' => 0,
                'path' => $path,
                'path_id' => $fileCommon->file_id,
                'is_official' => 1,
                'team_id' => 0,
                'copyright' => 0,
                'group' => 'chinese',
                'status' => 20,
                'created_at' => time()
            ];
        }

        // C
        for($i = 1; $i < 25; $i++) {
            if ($i <= 15) {
                $name = $i;
            } else {
                switch ($i) {
                    case 16:
                        $name = '1bd';
                        break;
                    case 17:
                        $name = '1bi';
                        break;
                    case 18:
                        $name = '1i';
                        break;
                    case 19:
                        $name = '2b';
                        break;
                    case 20:
                        $name = '2bi';
                        break;
                    case 21:
                        $name = '2i';
                        break;
                    case 22:
                        $name = '4b';
                        break;
                    case 23:
                        $name = '4bi';
                        break;
                    case 24:
                        $name = '4i';
                        break;

                }
            }
            $fontName = 'c' . $name;
            $path = 'updata/fonts/201805/' . $fontName . '.ttf';
            $fileCommon = $this->uploadFont($path);
            $data[] = [
                'font_name' => $fontName,
                'thumbnail' => '',
                'thumbnail_id' => 0,
                'path' => $path,
                'path_id' => $fileCommon->file_id,
                'is_official' => 1,
                'team_id' => 0,
                'copyright' => 0,
                'group' => 'english',
                'status' => 20,
                'created_at' => time()
            ];
        }

        // d
        for($i = 0; $i < 40; $i++) {
            $fontName = 'd' . $i;
            if ($i == 0) {
                $path = 'updata/fonts/201805/' . $fontName . '.otf';
            } else {
                $path = 'updata/fonts/201805/' . $fontName . '.ttf';
            }
            $fileCommon = $this->uploadFont($path);
            $data[] = [
                'font_name' => $fontName,
                'thumbnail' => '',
                'thumbnail_id' => 0,
                'path' => $path,
                'path_id' => $fileCommon->file_id,
                'is_official' => 1,
                'team_id' => 0,
                'copyright' => 0,
                'group' => 'english',
                'status' => 20,
                'created_at' => time()
            ];
        }

        // e
        for($i = 1; $i <= 7; $i++) {
            $fontName = 'e' . $i;
            $path = 'updata/fonts/201805/' . $fontName . '.ttf';
            $fileCommon = $this->uploadFont($path);
            $data[] = [
                'font_name' => $fontName,
                'thumbnail' => '',
                'thumbnail_id' => 0,
                'path' => $path,
                'path_id' => $fileCommon->file_id,
                'is_official' => 1,
                'team_id' => 0,
                'copyright' => 0,
                'group' => 'english',
                'status' => 20,
                'created_at' => time()
            ];
        }

        // f
        for($i = 1; $i <= 7; $i++) {
            $fontName = 'f' . $i;
            $path = 'updata/fonts/201805/' . $fontName . '.ttf';
            $fileCommon = $this->uploadFont($path);
            $data[] = [
                'font_name' => $fontName,
                'thumbnail' => '',
                'thumbnail_id' => 0,
                'path' => $path,
                'path_id' => $fileCommon->file_id,
                'is_official' => 1,
                'team_id' => 0,
                'copyright' => 0,
                'group' => 'english',
                'status' => 20,
                'created_at' => time()
            ];
        }

        // g
        for($i = 1; $i <= 20; $i++) {
            $fontName = 'g' . $i;
            $path = 'updata/fonts/201805/' . $fontName . '.ttf';
            $fileCommon = $this->uploadFont($path);
            $data[] = [
                'font_name' => $fontName,
                'thumbnail' => '',
                'thumbnail_id' => 0,
                'path' => $path,
                'path_id' => $fileCommon->file_id,
                'is_official' => 1,
                'team_id' => 0,
                'copyright' => 0,
                'group' => 'english',
                'status' => 20,
                'created_at' => time()
            ];
        }

        $path = 'updata/fonts/201805/simsun.ttf';
        $fileCommon = $this->uploadFont($path);
        $data[] = [
            'font_name' => 'simsun',
            'thumbnail' => '',
            'thumbnail_id' => 0,
            'path' => $path,
            'path_id' => $fileCommon->file_id,
            'is_official' => 1,
            'team_id' => 0,
            'copyright' => 0,
            'group' => 'english',
            'status' => 20,
            'created_at' => time()
        ];

        // 添加基本信息
        Yii::$app->db->createCommand()->batchInsert($this->tableName, [
           'font_name', 'thumbnail', 'thumbnail_id', 'path', 'path_id', 'is_official', 'team_id', 'copyright', 'group', 'status', 'created_at'
        ], $data)->execute();

        /** @var \common\models\Font[] $models */
        $models = \common\models\Font::find()->all();
        $file_path = [];
        foreach ($models as $model) {
            //\common\models\FileUsedRecord::createRecord(1, $model->path_id, \common\models\FileUsedRecord::PURPOSE_FONT, $model->font_id);
            $file_path[] = $model->path_id;
        }
        //增加文件引用记录
       \common\models\FileCommon::increaseSum($file_path);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }

    private function uploadFont($path)
    {
        // 上传字体文件
        $file = Yii::$app->oss->getObjectMeta($path);
        $model = new \common\models\FileCommon();
        $fileCommon = $model->create([
            'etag' => $file->etag,
            'path' => $path,
            'type' => \common\models\FileUsedRecord::PURPOSE_FONT,
            'size' => $file->download_content_length,
            'width' => 0,
            'height' => 0,
            'created_at' => time()
        ]);
        return $fileCommon;
    }

}
