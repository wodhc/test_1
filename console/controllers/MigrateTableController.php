<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace console\controllers;

use common\models\Classify;
use common\models\FileCommon;
use common\models\Font;
use common\models\forms\FileUpload;
use common\models\MaterialClassify;
use common\models\MaterialOfficial;
use common\models\Member;
use common\models\MemberOauth;
use common\models\MigrateTemplate;
use common\models\Tag;
use common\models\TagRelationClassify;
use common\models\TemplateMember;
use common\models\TemplateOfficial;
use common\models\TemplateOfficialPages;
use common\models\TemplateOfficialTag;
use console\models\OfficialTemplate;
use OSS\OssClient;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\data\SqlDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Response;

/**
 * 5.0数据迁移类
 * @package console\controllers
 * @author thanatos <thanatos915@163.com>
 */
class MigrateTableController extends Controller
{

    public $test;
    public $process = 10;
    public $server = 'http://localhost';
    public $pageSize = 5000;

    private $_fonts;
    private $_tables;

    public function options($actionID)
    {
        return ['test', 'server', 'pageSize', 'process'];
    }

    public function optionAliases()
    {
        return ['t' => 'test', 's' => 'server', 'p' => 'pageSize', 'process' => 'process'];
    }

    /**
     * 用户表迁移
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function actionUser()
    {
        $db = Yii::$app->dbMigrateDdy;

        $query = (new Query())
            ->from('com_member');

        $count = $db->createCommand($query->select('count(*)')->createCommand($db)->getRawSql())->queryScalar();

        $dataProvider = new SqlDataProvider([
            'db' => $db,
            'sql' => $query->select('*')->createCommand($db)->getRawSql(),
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => $this->getPageSize(),
            ],
        ]);

        $errorIds = [];
        $breakIds = [];
        $successAmount = 0;
        // 查询一次
        $dataProvider->prepare();
        // 循环分页
        for ($currentPage = 0; $currentPage < $dataProvider->pagination->getPageCount(); $currentPage++) {
            // 重置数据
            if ($currentPage > 0) {
                $dataProvider->pagination->setPage($currentPage);
                $dataProvider->prepare(true);
            }

            // 处理数据
            $models = $dataProvider->getModels();
            $data = [];
            foreach ($models as $key => $model) {
                $headimg_id = 0;
                $headimg_url = '';
                // 头像
                $imageUrl = Yii::$app->params['image_url'] . '/uploads/face/' . $model['id'] . '_180.png';
                echo $imageUrl . "\n";
                if ($result = FileUpload::upload($imageUrl, FileUpload::DIR_OTHER)) {
                    $headimg_id = $result->file_id ?? 0;
                    $headimg_url = $result->path ?? '';
                }


                $isBreak = false;
                // 用户状态
                if ($model['gid'] == 0) {
                    $isBreak = true;
                }
                if (!empty($model['punish'])) {
                    $punish = json_decode(stripslashes($model['punish']), true);
                    if (!empty($punish) && is_array($punish) && (empty($punish['deadline']) || time() < $punish['deadline'])) {
                        if (in_array('1', $punish['type'])) {
                            $isBreak = true;
                        }
                    }
                }
                if ($isBreak) {
                    // 跳过迁移，记录日志
                    $breakIds[] = $model['id'];
                    continue;
                }

                $data = [
                    'username' => $model['nickname'] ?: ($model['name'] ?: ($model['mobile'] ?: $model['email'])),
                    'mobile' => $model['mobile'] ?: '',
                    'sex' => $model['sex'],
                    'headimg_id' => $headimg_id ?: 0,
                    'headimg_url' => $headimg_url ?: '',
                    'coin' => $model['coin'],
                    'last_login_time' => strtotime($model['lastTime']),
                    'password_hash' => '',
                    'salt' => $model['salt'],
                    'password' => $model['password'],
                    'status' => 10,
                    'created_at' => strtotime($model['created']),
                    'updated_at' => time(),
                ];

                $transaction = Member::getDb()->beginTransaction();
                try {
                    $member = new Member();
                    $member->load($data, '');
                    $member->id = $model['id'];
                    if (!($member->validate() && $member->save())) {
                        throw new Exception('save member error');
                    }

                    $oauthKey = $model['qqUnionID'] ?: ($model['wxUnionID'] ?: '');
                    if ($oauthKey) {
                        $oauthModel = new MemberOauth();
                        $oauthModel->load([
                            'user_id' => $member->id,
                            'oauth_name' => MemberOauth::OAUTH_QQ,
                            'oauth_key' => $oauthKey
                        ], '');
                        if (!($oauthModel->validate() && $oauthModel->save())) {
                            throw new Exception('save member_oauth error');
                        }
                    }
                    // 添加文件使用日志
                    if ($member->headimg_id) {
                        /*$usedModel = new FileUsedRecord(['scenario' => FileUsedRecord::SCENARIO_CREATE]);
                        $usedModel->load([
                            'user_id' => $member->id,
                            'file_id' => $member->headimg_id,
                            'purpose' => FileUsedRecord::PURPOSE_HEADIMG,
                            'purpose_id' => $member->id,
                        ], '');
                        if (!$usedModel->save()) {
                            throw new Exception('save file_used_record error');
                        }*/
                        //增加文件引用记录
                        $file_result = FileCommon::increaseSum($member->headimg_id);
                        if (!$file_result) {
                            throw new Exception('save file_used_record error');
                        }
                    }

                    $successAmount++;
                    $this->stdout('Member: ' . $model['id'] . '迁移成功' . "\n", Console::FG_GREEN);
                    $transaction->commit();
                } catch (\Throwable $throwable) {
                    $transaction->rollBack();
                    // 记录错误
                    $errorIds[] = $model['id'];
                    Yii::error($throwable->getMessage(), 'migrateUser');
                    break;
                }
            }

            if ($this->test && $currentPage > 1) {
                break;
            }

        }

        $this->stdout('迁移失败: ' . (implode(',', $errorIds) ?: '""') . "\n", Console::FG_RED);
        $this->stdout('跳过迁移: ' . (implode(',', $breakIds) ?: '""') . "\n", Console::FG_YELLOW);
        $this->stdout('迁移成功数: ' . $successAmount . "\n", Console::FG_GREEN);

    }

    /**
     * 迁移分类表
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function actionProduct()
    {

        // 清空表
        Classify::getDb()->createCommand()->delete(Classify::tableName())->execute();
        TagRelationClassify::getDb()->createCommand()->delete(TagRelationClassify::tableName())->execute();

        $db = Yii::$app->dbMigrateDdy;
        $query = (new Query())
            ->from('com_template_product')
            ->where('coopId = 0 and status = 1 and name != parentName and product = parentProduct');

        $list = $query->all($db);
        $data = [];
        foreach ($list as $key => $model) {
            $data[] = [
                'category_id' => 0,
                'pid' => 0,
                'name' => $model['parentName'],
                'default_price' => 0,
                'is_hot' => 0,
                'is_new' => 0,
                'default_edit' => '',
                'order_link' => '',
                'thumbnail' => '',
                'thumbnail_id' => 0,
                'sort' => 0,
                'is_open' => 0,
                'status' => 20,
                'is_recommend' => 0,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }
        Classify::getDb()->createCommand()->batchInsert(Classify::tableName(), ['category_id', 'pid', 'name', 'default_price', 'is_hot', 'is_new', 'default_edit', 'order_link', 'thumbnail', 'thumbnail_id', 'sort', 'is_open', 'is_recommend', 'status', 'created_at', 'updated_at'], $data)->execute();

        $parentList = Classify::findAll(['pid' => 0]);
        foreach ($parentList as $k => $parent) {
            $query = (new Query())
                ->from('com_template_product')
                ->where('coopId = 0 and status = 1 and parentName = "' . $parent->name . '"');

            $list = $query->all($db);
            $data = [];
            foreach ($list as $key => $model) {
                $category = $this->getCategory($model['type']);
                if ($category) {
                    // 上传文件
                    $imageUrl = Yii::$app->params['image_url'] . '/uploads' . $model['thumbnail'];
                    if ($result = FileUpload::upload($imageUrl, FileUpload::DIR_OTHER)) {
                        $thumbnail_id = $result->file_id ?: 0;
                        $thumbnail = $result->path ?: '';
                    }

                    $data = [
                        'category_id' => $category,
                        'pid' => $parent->classify_id,
                        'name' => $model['name'],
                        'default_price' => $model['defaultPrice'],
                        'is_hot' => $model['recommend'] == 1 ? 1 : 0,
                        'is_new' => $model['recommend'] == 2 ? 1 : 0,
                        'default_edit' => $model['editConfig'],
                        'order_link' => $model['goodsLink'] ?: '',
                        'thumbnail' => $thumbnail ?: '',
                        'thumbnail_id' => $thumbnail_id ?: 0,
                        'sort' => $model['sort'] ?: 0,
                        'is_open' => $model['isOpen'],
                        'is_recommend' => (int)$model['recommend2'] ? 1 : 0,
                        'status' => 20,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                    $classifyModel = new Classify();
                    $classifyModel->load($data, '');
                    $classifyModel->save();
                    // 插入风格
                    $styles = (new Query())
                        ->from('com_template_style')
                        ->where(['id' => explode(',', $model['defaultStyle'])])->all($db);
                    // 查询新表的数据
                    /** @var Tag[] $newStyles */
                    $newStyles =  Tag::find()
                        ->where(['type' => Tag::TYPE_STYLE, 'name' => ArrayHelper::getColumn($styles, 'name')])->all();
                    $styleData = [];
                    foreach ($newStyles as $style) {
                        $styleData[] = [
                            'tag_id' => $style->tag_id,
                            'classify_id' => $classifyModel->primaryKey,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                    }
                    // 插入行业
                    $industries = (new Query())
                        ->from('com_template_industry')
                        ->where(['id' => explode(',', $model['defaultIndustry'])])->all($db);
                    // 查询新表的数据
                    /** @var Tag[] $newIndustries */
                    $newIndustries =  Tag::find()
                        ->where(['type' => Tag::TYPE_INDUSTRY, 'name' => ArrayHelper::getColumn($industries, 'name')])->all();
                    foreach ($newIndustries as $industry) {
                        $styleData[] = [
                            'tag_id' => $industry->tag_id,
                            'classify_id' => $classifyModel->primaryKey,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                    }
                    // 插入风格
                    $functions = (new Query())
                        ->from('com_template_function')
                        ->where(['id' => explode(',', $model['defaultFunction'])])->all($db);
                    // 查询新表的数据
                    /** @var Tag[] $newFunctions */
                    $newFunctions =  Tag::find()
                        ->where(['type' => Tag::TYPE_FUNCTION, 'name' => ArrayHelper::getColumn($functions, 'name')])->all();
                    foreach ($newFunctions as $function) {
                        $styleData[] = [
                            'tag_id' => $function->tag_id,
                            'classify_id' => $classifyModel->primaryKey,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                    }
                    TagRelationClassify::find()->createCommand()->batchInsert(TagRelationClassify::tableName(), [
                        'tag_id', 'classify_id', 'created_at', 'updated_at'
                    ], $styleData)->execute();
                }
            }


        }


        // 插入没有子分类的值
        $query = (new Query())
            ->from('com_template_product')
            ->where('coopId = 0 and status = 1 and name = parentName');

        $list = $query->all($db);

        $data = [];
        foreach ($list as $key => $model) {
            $category = $this->getCategory($model['type']);
            if ($category) {
                // 上传文件
                $imageUrl = Yii::$app->params['image_url'] . '/uploads' . $model['thumbnail'];
                if ($result = FileUpload::upload($imageUrl, FileUpload::DIR_OTHER)) {
                    $thumbnail_id = $result->file_id ?: 0;
                    $thumbnail = $result->path ?: '';
                }

                $data = [
                    'category_id' => $category,
                    'pid' => 0,
                    'name' => $model['name'],
                    'default_price' => $model['defaultPrice'],
                    'is_hot' => $model['recommend'] == 1 ? 1 : 0,
                    'is_new' => $model['recommend'] == 2 ? 1 : 0,
                    'default_edit' => $model['editConfig'],
                    'order_link' => $model['goodsLink'] ?: '',
                    'thumbnail' => $thumbnail ?: '',
                    'thumbnail_id' => $thumbnail_id ?: 0,
                    'sort' => $model['sort'] ?: 0,
                    'is_open' => $model['isOpen'],
                    'is_recommend' => (int)$model['recommend2'] ? 1 : 0,
                    'status' => 20,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                $classifyModel = new Classify();
                $classifyModel->load($data, '');
                $classifyModel->save();
                // 插入风格
                $styles = (new Query())
                    ->from('com_template_style')
                    ->where(['id' => explode(',', $model['defaultStyle'])])->all($db);
                // 查询新表的数据
                /** @var Tag[] $newStyles */
                $newStyles =  Tag::find()
                    ->where(['type' => Tag::TYPE_STYLE, 'name' => ArrayHelper::getColumn($styles, 'name')])->all();
                $styleData = [];
                foreach ($newStyles as $style) {
                    $styleData[] = [
                        'tag_id' => $style->tag_id,
                        'classify_id' => $classifyModel->primaryKey,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                }
                // 插入行业
                $industries = (new Query())
                    ->from('com_template_industry')
                    ->where(['id' => explode(',', $model['defaultIndustry'])])->all($db);
                // 查询新表的数据
                /** @var Tag[] $newIndustries */
                $newIndustries =  Tag::find()
                    ->where(['type' => Tag::TYPE_INDUSTRY, 'name' => ArrayHelper::getColumn($industries, 'name')])->all();
                foreach ($newIndustries as $industry) {
                    $styleData[] = [
                        'tag_id' => $industry->tag_id,
                        'classify_id' => $classifyModel->primaryKey,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                }
                // 插入风格
                $functions = (new Query())
                    ->from('com_template_function')
                    ->where(['id' => explode(',', $model['defaultFunction'])])->all($db);
                // 查询新表的数据
                /** @var Tag[] $newFunctions */
                $newFunctions =  Tag::find()
                    ->where(['type' => Tag::TYPE_FUNCTION, 'name' => ArrayHelper::getColumn($functions, 'name')])->all();
                foreach ($newFunctions as $function) {
                    $styleData[] = [
                        'tag_id' => $function->tag_id,
                        'classify_id' => $classifyModel->primaryKey,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                }
                TagRelationClassify::find()->createCommand()->batchInsert(TagRelationClassify::tableName(), [
                    'tag_id', 'classify_id', 'created_at', 'updated_at'
                ], $styleData)->execute();

            }
        }

        // 更新文件引用
        /** @var Classify[] $models */
        $models = Classify::find()->all();
        $data = [];
        foreach ($models as $key => $model) {
            if ($model->thumbnail_id) {
                /* $data[] = [
                     'user_id' => 1,
                     'file_id' => $model->thumbnail_id,
                     'purpose' => FileUsedRecord::PURPOSE_CLASSIFY,
                     'purpose_id' => $model->classify_id,
                     'created_at' => time(),
                 ];*/
                $data[] = $model->thumbnail_id;
            }
        }
        /* FileUsedRecord::getDb()->createCommand()->batchInsert(FileUsedRecord::tableName(), ['user_id', 'file_id', 'purpose', 'purpose_id', 'created_at'], $data)->execute();*/
        //增加文件引用记录
        FileCommon::increaseSum($data);
        $this->stdout('迁移成功' . "\n", Console::FG_GREEN);

    }

    /**
     * 迁移Tag表
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function actionTags()
    {
        $db = Yii::$app->dbMigrateDdy;
        Tag::getDb()->createCommand()->delete(Tag::tableName())->execute();
        $query = (new Query())
            ->from('com_template_industry')
            ->where(['coopId' => 0]);

        $list = $query->all($db);
        $successNum = 0;
        $data = [];
        foreach ($list as $key => $model) {
            $successNum++;
            $data[] = [
                'name' => $model['name'],
                'type' => Tag::TYPE_INDUSTRY,
                'sort' => 0,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }

        Tag::getDb()->createCommand()->batchInsert(Tag::tableName(), ['name', 'type', 'sort', 'created_at', 'updated_at'], $data)->execute();

        $query = (new Query())
            ->from('com_template_style')
            ->where(['coopId' => 0]);

        $list = $query->all($db);

        $data = [];
        foreach ($list as $key => $model) {
            $successNum++;
            $data[] = [
                'name' => $model['name'],
                'type' => Tag::TYPE_STYLE,
                'sort' => 0,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }

        Tag::getDb()->createCommand()->batchInsert(Tag::tableName(), ['name', 'type', 'sort', 'created_at', 'updated_at'], $data)->execute();

        $query = (new Query())
            ->from('com_template_function')
            ->where(['coopId' => 0]);

        $list = $query->all($db);

        $data = [];
        foreach ($list as $key => $model) {
            $successNum++;
            $data[] = [
                'name' => $model['name'],
                'type' => Tag::TYPE_FUNCTION,
                'sort' => 0,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }

        Tag::getDb()->createCommand()->batchInsert(Tag::tableName(), ['name', 'type', 'sort', 'created_at', 'updated_at'], $data)->execute();

        $this->stdout('迁移成功' . $successNum . '个' . "\n", Console::FG_GREEN);

    }

    /**
     * 迁移素材分类
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function actionMaterialClassify()
    {
        Yii::$app->db->createCommand()->delete(MaterialClassify::tableName())->execute();
        $classify = array(
            '99' => ['name' => '文字', 'classify' => []],
            '11' => ['name' => '容器', 'classify' => []],
            /*'10' => ['name' => '春节', 'classify' => []],*/
            '1' => ['name' => '形状', 'classify' => ['方形', '圆形', '心形', '星形', '多边形', '花型', '盾牌', '微标']],
            '2' => ['name' => '电商', 'classify' => ['电商元素', '618', '双11', '促销']],
            '13' => ['name' => '线条', 'classify' => []],
            '14' => ['name' => '箭头', 'classify' => []],
            '3' => ['name' => '标志', 'classify' => []],
            '4' => ['name' => '花纹', 'classify' => []],
            '5' => ['name' => '图片', 'classify' => ['商务', '科技', '金融', '生活', '节日', '人物', '建筑', '植物', '自然', '装饰', '动物', '交通', '教育', '医疗', '运动', '食品', '旅游', '艺术']],
            '6' => ['name' => '条幅', 'classify' => []],
            '7' => ['name' => '图标', 'classify' => ['名片专用', '商务', '社交', '科技', '金融', '人物', '动物', '运动', '自然', '交通', '教育', '地理', '美食', '标识', '箭头', '表情', '国旗']],
            '8' => ['name' => '插图', 'classify' => ['商务', '科技', '金融', '生活', '节日', '人物', '建筑', '植物', '自然', '装饰', '动物', '交通', '教育', '医疗', '运动', '食品', '旅游', '艺术']],
            '15' => ['name' => '表格', 'classify' => []],
            '16' => ['name' => '节日', 'classify' => ['元旦', '春节', '元宵节', '情人节', '清明节', '劳动节', '端午节', '国庆节', '中秋节', '圣诞节']],
            '12' => ['name' => '免扣素材', 'classify' => ['商务', '科技', '电商', '人物', '动物', '食品', '装饰', '建筑', '自然', '水墨', '特效']],
            '17' => ['name' => '双节素材', 'classify' => []],
            '18' => ['name' => '表单', 'classify' => []],
            '19' => ['name' => '音乐', 'classify' => ['商务', '复古', '节日', '大气', '浪漫', '轻松']],
            '20' => ['name' => '双十一素材', 'classify' => []],
            '21' => ['name' => '按钮', 'classify' => []],
            '22' => ['name' => '计时', 'classify' => []],
            '23' => ['name' => '点赞', 'classify' => []],
            '24' => ['name' => '投票', 'classify' => []],
            '25' => ['name' => '双旦素材', 'classify' => []],

            '50' => ['name' => '新年素材', 'classify' => []], //50-69是简页与图帮主共用key

            '98' => ['name' => '背景图', 'classify' => []]
        );

        // 添加素材
        $data = [];
        $sum = 0;
        foreach ($classify as $key => $value) {
            $sum++;
            $data[] = [
                'cid' => $key,
                'pid' => 0,
                'name' => $value['name'],
                'status' => MaterialClassify::STATUS_NORMAL,
                'created_at' => time(),
                'updated_at' => time()
            ];
        }
        Yii::$app->db->createCommand()->batchInsert(MaterialClassify::tableName(), [
            'cid', 'pid', 'name', 'status', 'created_at', 'updated_at'
        ], $data)->execute();
        $this->stdout('成功迁移' . $sum . '个素材主分类' . "\n", Console::FG_GREEN);
        /** @var MaterialClassify[] $materialList */
        $materialList = MaterialClassify::find()->all();
        $sum = 0;
        foreach ($materialList as $k => $material) {
            $pClassify = $classify[$material->cid]['classify'];
            if ($pClassify) {
                $pData = [];
                foreach ($pClassify as $pModel) {
                    $sum++;
                    $pData[] = [
                        'pid' => $material->cid,
                        'name' => $pModel,
                        'status' => MaterialClassify::STATUS_NORMAL,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                }
                Yii::$app->db->createCommand()->batchInsert(MaterialClassify::tableName(), [
                    'pid', 'name', 'status', 'created_at', 'updated_at'
                ], $pData)->execute();
            }
        }
        $this->stdout('成功迁移' . $sum . '个素材子分类' . "\n", Console::FG_GREEN);
    }

    /**
     * 迁移表格素材
     */
    public function actionMaterialTable()
    {
        $list = (new Query())->from('com_template_material')->where(['type' => 15, 'status' => 1])->all(Yii::$app->dbMigrateDdy);
        $data = [];
        foreach ($list as $k => $item) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                list('index' => $index, 'css' => $css) = $this->tableJsonToCss($item['tableStyle']);
                if (empty($css)) {
                    throw new Exception('CSS Not Exist');
                }
                $tmpFIle = UPLOAD_BASE_DIR . '/temporary/' . md5($css) . '.css';
                Yii::$app->oss->putObject($tmpFIle, $css, [OssClient::OSS_CONTENT_TYPE => 'text/css']);
                // 处理文件
                $path = FileUpload::upload($tmpFIle, FileUpload::DIR_MATERIAL);
                $thumbnail = FileUpload::upload('uploads' . $item['filePath'], FileUpload::DIR_MATERIAL);
                $data = [
                    'user_id' => 1,
                    'cid' => '15',
                    'name' => (string)$index,
                    'tags' => '',
                    'file_path' => $path->path,
                    'file_id' => $path->file_id,
                    'thumbnail' => $thumbnail->path,
                    'thumbnail_id' => $thumbnail->file_id,
                    'file_type' => $path->type,
                    'width' => 0,
                    'height' => 0,
                    'status' => MaterialOfficial::STATUS_NORMAL,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                $model = new MaterialOfficial();
                $model->load($data, '');
                if (!$model->save()) {
                    throw new Exception('Save MaterialOfficial failed');
                }
                if (!FileCommon::increaseSum([$path->file_id, $thumbnail->file_id])) {
                    throw new Exception('increase File failed');
                }
                $this->stdout('表格素材:' . $item['id'] . '处理成功' . "\n", Console::FG_GREEN);
                $transaction->commit();
            } catch (\Throwable $e) {
                $message = $e->getMessage();
                $this->stdout('表格素材:' . $item['id'] . '处理失败.' . $message . "\n", Console::FG_RED);
                $transaction->rollBack();
            }
        }
    }

    /**
     * 创建模板转移队列
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function actionQueueTemplate()
    {
        /** @var OfficialTemplate[] $models */
        $models = OfficialTemplate::find()->where([
            'status' => OfficialTemplate::STATUS_ONLINE,
            'coop_id' => 0,
        ])->all();
        $data = [];
        $sum = 0;
        foreach ($models as $key => $model) {
            $sum++;
            $data[$key] = [
                'template_id' => $model->id,
                'template_type' => 1,
                'status' => 0,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }
        MigrateTemplate::getDb()->createCommand()->batchInsert(MigrateTemplate::tableName(), [
            'template_id', 'template_type', 'status', 'created_at', 'updated_at'
        ], $data)->execute();

        $this->stdout('创建模板队列成功' . "\n", Console::FG_GREEN);
        $this->stdout('成功创建: ' . $sum . '个' . "\n", Console::FG_GREEN);
    }

    /**
     * 转移模板
     * @throws Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function actionTemplate()
    {
        $start = time();
        // 设置OSS图片网址别名
        Yii::setAlias('@oss', Yii::$app->params['ossUrl']);
        // 查询队列数
        $query = MigrateTemplate::find()->where(['status' => 0, 'template_type' => 1]);

        $this->stdout('开始执行' . "\n", Console::FG_GREEN);
        $process = 0;
        while (true) {
            if ($process < $this->process) {
                $process++;
                if ($this->processMigrate($query) === false) {
                    goto end;
                }
            } else {
                $this->stdout('当前进程已满' . "\n", Console::FG_GREEN);
                while (($result = \pcntl_waitpid(0, $status, WUNTRACED)) > 0) {
                    $process--;
                    $this->stdout('当前进程数:' . $process . "\n", Console::FG_GREEN);
                    break;
                }
            }
        }
        end:
        // 全部执行结束
        while (\pcntl_waitpid(0, $status) != -1) {
            sleep(0.5);
        }

        $end = time();
        $this->stdout('总耗时:' . ($end - $start) . "\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * 转移素材
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function actionMaterial()
    {
        $query = (new Query())->from('com_template_material')
            ->where(['!=', 'type', '15'])
            ->andWhere(['status' => 1]);
        $count = $query->count('*', Yii::$app->dbMigrateDdy);
        $this->stdout('解析完成，需要处理' . $count . '个元素' . "\n", Console::FG_YELLOW);
        $dataProvider = new SqlDataProvider([
            'db' => Yii::$app->dbMigrateDdy,
            'sql' => $query->createCommand()->getRawSql(),
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => $this->getPageSize()
            ]
        ]);
        $successNum = 0;
        $errors = [];
        $currentPage = 0;
        while (true) {
            $currentPage++;
            $dataProvider->pagination->setPage($currentPage - 1);
            $dataProvider->prepare(true);
            if ($currentPage >= $dataProvider->pagination->pageCount) {
                break;
            }
            $models = $dataProvider->getModels();
            $data = [];
            sleep(0.3);
            foreach ($models as $key => $model) {
                $thumbnail = '';
                $thumbnail_id = 0;
                try {
                    $tmpPath = preg_replace('/.*\/?(uploads)?\/?(.+)/', '$0', $model['filePath']);
                    // 原文件
                    $pathFIle = FileUpload::upload('uploads/' . trim($tmpPath, '/'), FileUpload::DIR_MATERIAL);
                    if (empty($pathFIle)) {
                        throw new Exception('Uploads Path File Failed ' . 'uploads/' . trim($tmpPath, '/'));
                    }
                    // SVG
                    if ($model['mimeType'] == 'eel') {
                        $thumbnail = $pathFIle->path;
                        $thumbnail_id = $pathFIle->file_id;
                    } else {
                        // 生成250的缩略图
                        $object = Yii::$app->oss->getObject(UPLOAD_BASE_DIR . '/' . $pathFIle->path, [
                            OssClient::OSS_PROCESS => 'image/resize,w_250',
                        ]);
                        $thumbnailFIle = FileUpload::uploadLocal($object, FileUpload::DIR_MATERIAL);
                        if (empty($thumbnailFIle)) {
                            throw new Exception('Uploads File Thumbnail Failed ' . $pathFIle->path);
                        }
                        $thumbnail = $thumbnailFIle->path;
                        $thumbnail_id = $thumbnailFIle->file_id;
                    }

                    // 分类
                    $tags = [];
                    $tags[] = $model['type'];
                    $classifies = explode(',', $model['classify']);
                    if ($classifies) {
                        /** @var MaterialClassify $classifyModels */
                        $classifyModels = MaterialClassify::find()->where(['pid' => $model['type'], 'name' => $classifies])->all();
                        foreach ($classifyModels as $classifyModel) {
                            $tags[] = $classifyModel->cid;
                        }
                    }
                    $data[] = [
                        'user_id' => 1,
                        'cid' => implode(',', $tags),
                        'name' => '',
                        'tags' => $model['tags'],
                        'thumbnail' => $thumbnail,
                        'thumbnail_id' => $thumbnail_id,
                        'file_path' => $pathFIle->path,
                        'file_id' => $pathFIle->file_id,
                        'file_type' => $pathFIle->type,
                        'width' => $model['width'],
                        'height' => $model['height'],
                        'status' => MaterialOfficial::STATUS_NORMAL,
                        'created_at' => time(),
                        'updated_at' => time()
                    ];
                    // 添加文件使用记录
                    FileCommon::increaseSum([$thumbnail_id, $pathFIle->file_id]);
                    $successNum++;
                    $this->stdout("\tid " . $model['id'] . ' 处理完成' . "\n", Console::FG_YELLOW);
                } catch (\Throwable $e) {
                    $errors[] = $model['id'];
                    $this->stdout('素材:' . $model['id'] . '处理失败：' . $e->getMessage() . "\n", Console::FG_RED);
                    Yii::error('素材:' . $model['id'] . '：' . $e->getMessage(), 'MigrateMaterial');
                }

            }

            MaterialOfficial::find()->createCommand()->batchInsert(MaterialOfficial::tableName(), [
                'user_id', 'cid', 'name', 'tags', 'thumbnail', 'thumbnail_id', 'file_path', 'file_id', 'file_type', 'width', 'height', 'status', 'created_at', 'updated_at'
            ], $data)->execute();
            $this->stdout("\t" . count($data) . '个插入成功' . "\n", Console::FG_YELLOW);
        }
        $this->stdout('执行成功:' . $successNum . '个' . "\n", Console::FG_GREEN);
        if ($errors) {
            $this->stdout('执行失败:' . implode(',', $errors) . "\n", Console::FG_RED);
        }

    }

    public function getPageSize()
    {
        return $this->test ? 80 : $this->pageSize;
    }

    private function getCategory($type)
    {
        switch ($type) {
            case 0:
                return 2;
            case 2:
                return 4;
            case 3:
                return 6;
            case 5:
                return 7;
            case 6:
                return 5;
            case 7:
                return 3;

        }
    }

    /**
     * @param TemplateOfficial|TemplateMember $model
     * @return string
     * @throws Exception
     * @author thanatos <thanatos915@163.com>
     */
    private function prepareContent($model)
    {
        // 替换字体
        $content = preg_replace_callback('/font_\w\d+/', function ($matches) {
            $key = explode('_', $matches[0])[1];
            return $this->getFonts()[$key]['font_id'];
        }, $model->content);
        // 替换表格
        $content = preg_replace_callback('/"source":(\d+)/', function ($matches) {
            return '"source":'. $this->getTables()[$matches[1]]['id'];
        }, $content);
        $content = Json::decode($content);
        $pagesThumb = [];
        $fileIds = [];
        // 删除模板id
        unset($content['id']);
        // 处理页面
        foreach ($content['pages'] as $key => &$page) {
            // 删除页面id
            unset($page['id']);
            // 处理页面缩略图
            if ($page['states']) {
                $thumb = preg_replace('/.+\/(uploads.+)/', '$1', $page['states']['thumb']);
                try {
                    if (!$result = FileUpload::upload($thumb, FileUpload::DIR_TEMPLATE)) {
                        throw new Exception('Upload Pages Thumbnail failed');
                    }
                    $pageThumbnail = $result->path;
                    $pageThumbnailId = $result->file_id;
                } catch (\Throwable $e) {
                    $pageThumbnail = '';
                    $pageThumbnailId = 0;
                }
                // 添加页面数据
                $pagesThumb[] = [
                    'template_id' => $model->template_id,
                    'page_index' => $key,
                    'thumbnail' => $pageThumbnail,
                    'thumbnail_id' => $pageThumbnailId,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                if (!FileCommon::increaseSum($pageThumbnailId)) {
                    throw new Exception('Increase Page Thumbnail failed. '. $pageThumbnailId);
                }

                if ($pageThumbnailId) {
                    $fileIds[] = $pageThumbnailId;
                }
                unset($page['states']);
            }
            // 处理页面下的元素
            foreach ($page['elements'] as $eKey => &$element) {
                unset($element['id']);
                // 处理元素资源路径
                $elementThumb = preg_match('/\/?(.+\.\w+)/', $element['options']['url'], $matches)[1];
                if ( $elementThumb ) {
                    if (!$result = FileUpload::upload(trim($elementThumb, '/'), FileUpload::DIR_MATERIAL)) {
                        throw new Exception('Upload Element Thumbnail failed' . $elementThumb);
                    }
                    unset($element['options']['url']);
                    $element['options']['source'] = $result->file_id;
                    $fileIds[] = $result->file_id;
                }

                $e4svg = preg_match('/\/?(.+\.\w+)/', $element['options']['e4svg'], $matches)[1];
                if ($e4svg) {
                    if (!$result = FileUpload::upload(trim($e4svg, '/'), FileUpload::DIR_MATERIAL)) {
                        throw new Exception('Upload Element Thumbnail failed' . $e4svg);
                    }
                    $element['options']['e4svg'] = $result->file_id;
                    $fileIds[] = $result->file_id;
                }

                // 删除临时文件

                /* TODO 统一删除
                if (preg_match('/updata/', $elementThumb)) {
                    Yii::$app->oss->deleteObject($elementThumb);
                }
                */
            }
        }
        // 添加页面缩略图
        TemplateOfficialPages::getDb()->createCommand()->batchInsert(TemplateOfficialPages::tableName(), [
            'template_id', 'page_index', 'thumbnail', 'thumbnail_id', 'created_at', 'updated_at'
        ], $pagesThumb)->execute();

        // 添加引用记录
        if (!FileCommon::increaseSum($fileIds)) {
            throw new Exception('Increase Files Failed');
        }
        return Json::encode($content);
    }

    public function getFonts()
    {
        if ($this->_fonts === null) {
            $this->_fonts = ArrayHelper::index(Font::find()->asArray()->select(['font_id', 'font_name'])->all(), 'font_name');
        }
        return $this->_fonts;
    }

    public function getTables()
    {
        if ($this->_tables === null) {
            $this->_tables = ArrayHelper::index(MaterialOfficial::find()->asArray()->where(['cid' => 15])->select(['id', 'name'])->all(), 'name');
        }
        return $this->_tables;
    }

    /**
     * @param ActiveQuery $query
     * @return bool
     * @throws Exception
     * @throws \yii\db\Exception
     * @author thanatos <thanatos915@163.com>
     */
    private function processMigrate($query)
    {
        sleep(1);
        Yii::$app->db->pdo = null;
        if (empty($query->count())) {
            return false;
        }
        $pid = \pcntl_fork();
        if ($pid == 0) {
            $pid = \posix_getpid();
            Yii::$app->db->pdo = null;
            $this->stdout('进程:' . $pid . ' 开始执行' . "\n", Console::FG_GREEN);
            $templateIds = ArrayHelper::getColumn($query->asArray()->limit($this->getPageSize())->all(), 'template_id');
            // 修改状态
            Yii::$app->db->createCommand()->update(MigrateTemplate::tableName(), ['status' => 3], ['template_id' => $templateIds])->execute();
            // 官方模板
            $query = OfficialTemplate::find()->where(['id' => $templateIds])->with('pages.elements');
            /** @var OfficialTemplate[] $models */
            $models = $query->all();
            // 整合处理数据
            $data = [];
            foreach ($models as $k => $val) {
                $data[$k] = $val->toArray();
                foreach ($val->pages as $key => $item) {
                    $data[$k]['pages'][$key] = $item->toArray();
                    foreach ($item->elements as $ke => $ve) {
                        $data[$k]['pages'][$key]['elements'][$ke] = $ve->toArray();
                    }
                }
            }

            // 查询分类信息分类
            $products = ArrayHelper::getColumn($data, 'product');
            $names = (new Query())->from('com_template_product')->where(['product' => $products])->all(Yii::$app->dbMigrateDdy);
            Yii::$app->db->pdo = null;
            $classifies = Classify::find()->asArray()->where(['name' => ArrayHelper::getColumn($names, 'name')])->all();
            $category = ArrayHelper::index($classifies, 'name');
            $productNames = ArrayHelper::index($names, 'product');

            $client = new Client([
                'transport' => CurlTransport::class
            ]);
            /** @var Response $response */

            $times = 0;
            while (true) {
                $times++;
                try {
                    $response = $client->createRequest()
                        ->setMethod('POST')
                        ->addHeaders(['content-type' => 'application/json'])
                        ->setUrl($this->server . ':8001/api/get-v5-json')
                        ->setContent(Json::encode($data))
                        ->send();
                    break;
                } catch (\Exception $exception) {
                    if ($times >= 3) {
                        throw new Exception($exception->getMessage());
                    }
                }
            }
            if ($response->isOk) {
                $result = $response->data;
                foreach ($result['success'] as $key => $item) {
                    $model = $models[$key];
                    // 执行成功
                    $template_type = $model instanceof OfficialTemplate ? 1 : 2;
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        // 转移模板缩略图
                        $thumbnail = $model->thumbnail;
                        if (!$templateFile = FileUpload::upload(trim($thumbnail, '/'), FileUpload::DIR_TEMPLATE)) {
                            throw new Exception('上传文件失败');
                        }
                        // 添加数据
                        $data = [
                            'user_id' => $model->uid,
                            'category_id' => $category[($productNames[$model->product])['name']]['category_id'],
                            'classify_id' => $category[($productNames[$model->product])['name']]['classify_id'],
                            'title' => $model->seo_title ?: ($model->title ?: ''),
                            'thumbnail_url' => $templateFile->path,
                            'thumbnail_id' => $templateFile->file_id,
                            'status' => TemplateOfficial::STATUS_ONLINE,
                            'created_at' => $model->created_at,
                            'updated_at' => time(),
                            'price' => $model->price_coin,
                            'amount_edit' => $model->num_edit_total,
                            'virtual_edit' => $model->num_edit_virtual,
                            'amount_view' => $model->num_view_total,
                            'virtual_view' => $model->num_view_virtual,
                            'amount_favorite' => $model->num_fav_total,
                            'virtual_favorite' => $model->num_fav_virtual,
                            'amount_buy' => $model->num_buy ?: 0,
                            'sort' => $model->sort,
                            'recommend_at' => $model->recommend_at,
                            'content' => Json::encode($item),
                            'thumbnail_updated_at' => time()
                        ];
                        $templateModel = new TemplateOfficial();
                        $templateModel->load($data, '');
                        if (!$templateModel->save()) {
                            throw new Exception('Save Template failed');
                        }

                        if (!FileCommon::increaseSum($templateModel->thumbnail_id)) {
                            throw new Exception('increase file failed');
                        }

                        // 处理页面内容
                        $templateModel->content = $this->prepareContent($templateModel);
                        if (!$templateModel->save()) {
                            throw new Exception('Save Template Content failed');
                        }

                        /** 处理模板标签信息 */
                        // 风格
                        $styleIds = explode(',', $model->style_ids);
                        if ($styleIds) {
                            $styles = (new Query())->from('com_template_style')->where(['coopId' => 0, 'id' => $styleIds])->all(Yii::$app->dbMigrateDdy);
                            $newStyles = ArrayHelper::index(Tag::find()->asArray()->where(['name' => ArrayHelper::getColumn($styles, 'name'), 'type' => Tag::TYPE_STYLE])->all(), 'name');
                            $tmpStyleIds = ArrayHelper::index($styles, 'id');
                            $styleData = [];
                            foreach ($styleIds as $sKey => $style) {
                                $tag_id = $newStyles[$tmpStyleIds[$style]['name']]['tag_id'];
                                if ($tag_id) {
                                    $styleData[$sKey] = [
                                        'template_id' => $templateModel->template_id,
                                        'tag_id' => $tag_id,
                                        'created_at' => time()
                                    ];
                                }
                            }
                            Yii::$app->db->createCommand()->batchInsert(TemplateOfficialTag::tableName(), [
                                'template_id', 'tag_id', 'created_at'
                            ], $styleData)->execute();
                        }
                        // 行业
                        $industryIds = explode(',', $model->industry_ids);
                        if ($industryIds) {
                            $industries = (new Query())->from('com_template_industry')->where(['coopId' => 0, 'id' => $industryIds])->all(Yii::$app->dbMigrateDdy);
                            $newIndustries = ArrayHelper::index(Tag::find()->asArray()->where(['name' => ArrayHelper::getColumn($industries, 'name'), 'type' => Tag::TYPE_INDUSTRY])->all(), 'name');
                            $tmpIndustries = ArrayHelper::index($industries, 'id');
                            $industryData = [];
                            foreach ($newIndustries as $sKey => $industry) {
                                $tag_id = $newIndustries[$tmpIndustries[$industry]['name']]['tag_id'];
                                if ($tag_id) {
                                    $industryData[$sKey] = [
                                        'template_id' => $templateModel->template_id,
                                        'tag_id' => $tag_id,
                                        'created_at' => time()
                                    ];
                                }
                            }
                            Yii::$app->db->createCommand()->batchInsert(TemplateOfficialTag::tableName(), [
                                'template_id', 'tag_id', 'created_at'
                            ], $industryData)->execute();
                        }
                        //  功能
                        $functionIds = explode(',', $model->function_ids);
                        if ($functionIds) {
                            $functions = (new Query())->from('com_template_industry')->where(['coopId' => 0, 'id' => $functionIds])->all(Yii::$app->dbMigrateDdy);
                            $newFunctions = ArrayHelper::index(Tag::find()->asArray()->where(['name' => ArrayHelper::getColumn($functions, 'name'), 'type' => Tag::TYPE_FUNCTION])->all(), 'name');
                            $tmpFunctions = ArrayHelper::index($functions, 'id');
                            $functionData = [];
                            foreach ($newFunctions as $sKey => $function) {
                                $tag_id = $newFunctions[$tmpFunctions[$function]['name']]['tag_id'];
                                $functionData[$sKey] = [
                                    'template_id' => $templateModel->template_id,
                                    'tag_id' => $tag_id,
                                    'created_at' => time()
                                ];
                            }
                            Yii::$app->db->createCommand()->batchInsert(TemplateOfficialTag::tableName(), [
                                'template_id', 'tag_id', 'created_at'
                            ], $functionData)->execute();
                        }

                        // 修改状态
                        Yii::$app->db->createCommand()->update(MigrateTemplate::tableName(), ['status' => 2], ['template_id' => $model->id, 'template_type' => $template_type])->execute();
                        $transaction->commit();
                        $this->stdout("\t" . $model->id . ': 迁移成功' . "\n", Console::FG_GREEN);
                    } catch (\Throwable $e) {
                        $transaction->rollBack();
                        $this->stdout("\t" . $model->id . ': 迁移失败' . "\n", Console::FG_RED);
                        Yii::$app->db->createCommand()->update(MigrateTemplate::tableName(), ['status' => 1], ['template_id' => $model->id, 'template_type' => $template_type])->execute();
                        Yii::error('模板:' . $model->id . '：' . $e->getMessage(), 'MigrateOfficialTemplate');
                    }
                }

                // 添加错误条目
                Yii::$app->db->createCommand()->update(MigrateTemplate::tableName(), ['status' => 1], ['template_id' => $result['fail'], 'template_type' => $template_type])->execute();
            } else {
                throw new Exception('接口请求失败');
            }
            exit;
        }
    }

    private function tableJsonToCss($json)
    {
        $css = [
            [
                // 51360
                'json' => '[{"rule":[["default",0,""]],"background":"#FFFFFF","borderTopWidth":1,"borderTopColor":"#FAD0A2","borderLeftWidth":1,"borderLeftColor":"#FAD0A2","color":"#B25230"},{"rule":[["row_final",0,""]],"background":"#FFFFFF","borderTopWidth":1,"borderTopColor":"#FAD0A2","borderLeftWidth":1,"borderLeftColor":"#FAD0A2","borderBottomWidth":1,"borderBottomColor":"#FAD0A2","color":"#B25230"},{"rule":[["col_final",0,""]],"background":"#FFFFFF","borderLeftWidth":1,"borderLeftColor":"#FAD0A2","borderTopWidth":1,"borderTopColor":"#FAD0A2","borderRightWidth":1,"borderRightColor":"#FAD0A2","color":"#B25230"},{"rule":[["col_final",0,""],["row_final",0,""]],"background":"#FFFFFF","borderLeftWidth":1,"borderLeftColor":"#FAD0A2","borderTopWidth":1,"borderTopColor":"#FAD0A2","borderBottomWidth":1,"borderBottomColor":"#FAD0A2","borderRightWidth":1,"borderRightColor":"#FAD0A2","color":"#B25230"},{"rule":[["row_index",0,""]],"background":"#FDECD9","color":"#B25230","borderLeftWidth":1,"borderLeftColor":"#FAD0A2","borderTopWidth":1,"borderTopColor":"#FAD0A2"},{"rule":[["col_final",0,""],["row_index",0,""]],"background":"#FDECD9","color":"#B25230","borderLeftWidth":1,"borderLeftColor":"#FAD0A2","borderTopWidth":1,"borderTopColor":"#FAD0A2","borderRightWidth":1,"borderRightColor":"#FAD0A2"}]',
                'css' => '.table[color=>#B25230;border=>none;border-spacing=> 0;table-layout=> fixed;overflow=>hidden;font-size=>16px;].table *[box-sizing=>border-box;].table tr[background-color=>#FFFFFF;].table tr=>first-child[background-color=>#FDECD9;].table tr=>last-child td [border-bottom=>1px solid;border-color=>#FAD0A2;].table td[text-align=>center;border-left=>1px solid;border-top=>1px solid;border-color=>#FAD0A2;overflow=>hidden;].table td=>last-child[border-right=>1px solid;border-color=>#FAD0A2;]'
            ],
            [
                // 51361
                'json' => '[{"rule":[["default",0,""]],"background":"#FFFFFF","borderTopWidth":1,"borderTopColor":"#969797","borderLeftWidth":1,"borderLeftColor":"#969797","color":"#404040"},{"rule":[["col_index",0,""]],"borderTopWidth":1,"borderTopColor":"#969797"},{"rule":[["row_final",0,""]],"borderTopWidth":1,"borderTopColor":"#969797","borderBottomWidth":1,"borderBottomColor":"#969797","borderLeftWidth":1,"borderLeftColor":"#969797"},{"rule":[["row_final",0,""],["col_index",0,""]],"borderTopWidth":1,"borderTopColor":"#969797","borderBottomWidth":1,"borderBottomColor":"#969797"}]',
                'css' => '.table [color=> #404040;border=> none;border-spacing=> 0;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table tr [background-color=> #FFFFFF;].table td [text-align=> center;overflow=> hidden;border-left=> 1px solid;border-top=> 1px solid;border-color=> #969797;].table tr td=>first-child [border-left=> none;].table tr=>last-child td [border-bottom=> 1px solid #969797;]'],
            [
                // 51362
                'json' => '[{"rule":[["col_even",0,""]],"background":"#FFFFFF","borderBottomWidth":2,"borderBottomColor":"#F8BA67","color":"#404040"},{"rule":[["col_odd",0,""]],"background":"#FFFFFF","borderBottomWidth":2,"borderBottomColor":"#FCE0BB","color":"#404040"},{"rule":[["col_even",0,""],["row_index",0,""]],"background":"#F8BA67","color":"#FFFFFF"},{"rule":[["col_odd",0,""],["row_index",0,""]],"background":"#FCE0BB","color":"#FFFFFF"}]',
                'css' => '.table [border=> none;border-spacing=> 0;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table td[background-color=>#ffffff;color=>#404040;].table tr td=>nth-child(odd)[ border-bottom=>2px solid #F8BA67;] .table tr td=>nth-child(even)[ border-bottom=>2px solid #FCE0BB;] .table tr=>first-child td[color=>#ffffff;].table tr=>first-child td=>nth-child(odd)[ background-color=> #F8BA67;] .table tr=>first-child td=>nth-child(even)[ background-color=> #FCE0BB;] .table td [text-align=> center;overflow=> hidden;]',
            ],
            [
                // 51363
                'json' => '[{"rule":[["col_even",0,""]],"background":"#D3D3D3","color":"#404040","fontSize":18},{"rule":[["col_odd",0,""]],"background":"#F0F0F0","color":"#404040","fontSize":18},{"rule":[["col_odd",0,""],["row_index",0,""]],"background":"#F0F0F0","color":"#404040","paddingBottom":1.5,"fontSize":18},{"rule":[["col_even",0,""],["row_index",0,""]],"background":"#D3D3D3","color":"#404040","paddingBottom":1.5,"fontSize":18}]',
                'css' => '.table [border=> none;border-spacing=> 0;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table td[text-align=>center;background-color=>#ffffff;color=>#404040;].table tr=>first-child td[border-bottom=>2px solid #ffffff;].table tr td=>nth-child(odd)[background-color=>#D3D3D3;] .table tr td=>nth-child(even)[background-color=>#F0F0F0;] '
            ],
            [
                // 51364
                'json' => '[{"rule":[["row_index",0,""]],"background":"#C0C0C0","color":"#404040","paddingBottom":1.5,"fontSize":18,"borderBottomWidth":2},{"rule":[["default",0,""]],"background":"#E7E7E7","color":"#404040","paddingBottom":1.5,"fontSize":18},{"rule":[["col_index",0,""]],"background":"#D3D3D3","color":"#404040","paddingBottom":1.5,"fontSize":18}]',
                'css' => '.table [border=> none;border-spacing=> 0;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table td[text-align=>center;background-color=>#E7E7E7;color=>#404040;border-bottom=>2px solid #ffffff;].table tr=>first-child td[background-color=>#C0C0C0;].table tr td=>first-child[background-color=>#D3D3D3;]'
            ],
            [
                // 51365
                'json' => '[{"rule":[["row_odd",0]],"background":"#EFEFEF","color":"#404040","fontSize":18,"borderRightWidth":1,"borderRightColor":"#BBBBBB"},{"rule":[["row_even",0]],"background":"#DDDDDD","color":"#404040","fontSize":18,"borderRightWidth":1,"borderRightColor":"#BBBBBB"},{"rule":[["row_odd",0],["col_final"]],"background":"#EFEFEF","color":"#404040","fontSize":18},{"rule":[["row_even",0],["col_final"]],"background":"#DDDDDD","color":"#404040","fontSize":18}]',
                'css' => '.table [border=> none;border-spacing=> 0;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table td[text-align=>center;color=>#404040;border-right=>1px solid;border-color=>#9e9e9e;].table td=>last-child[border=>none;].table tr=>nth-child(even) td[background-color=> #EFEFEF;].table tr=>nth-child(odd) td[background-color=> #DDDDDD;]'
            ],
            [
                // 51366
                'json' => '[{"rule":[["col_even",0]],"background":"#EFEFEF","color":"#404040","fontSize":18,"borderBottomWidth":1,"borderBottomColor":"#BBBBBB"},{"rule":[["col_odd",0]],"background":"#DDDDDD","color":"#404040","fontSize":18,"borderBottomWidth":1,"borderBottomColor":"#BBBBBB"},{"rule":[["col_even",0],["row_final",0]],"background":"#EFEFEF","color":"#404040","fontSize":18},{"rule":[["col_odd",0],["row_final",0]],"background":"#DDDDDD","color":"#404040","fontSize":18}]',
                'css' => '.table [border=> none;border-spacing=> 0;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table td[text-align=>center;color=>#404040;].table tr td=>nth-child(odd)[background-color=>#EFEFEF;].table tr td=>nth-child(even)[background-color=>#DDDDDD;]'
            ],
            [
                // 51367
                'json' => '[{"rule":[["col_index",0,""]],"background":"#D3D3D3","color":"#404040","paddingLeft":1.5,"paddingRight":1.5,"paddingTop":1.5,"paddingBottom":1.5,"fontSize":18,"borderLeftWidth":3,"borderLeftColor":"#E70012"},{"rule":[["default",0,""]],"background":"#E7E7E7","color":"#404040","paddingLeft":1.5,"paddingRight":1.5,"paddingTop":1.5,"paddingBottom":1.5,"fontSize":18}]',
                'css' => '.table [border=> none;border-spacing=> 0;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table td[text-align=>center;color=>#404040;border-right=>2px solid;border-bottom=>2px solid;border-color=>#ffffff;background-color=>#E7E7E7;].table tr=>last-child td[border-bottom=>none].table tr td=>first-child[background-color=>#D3D3D3;border-left=>2px solid #E70012;].table tr td=>last-child[border-right=>none;]'
            ],
            [
                // 51368
                'json' => '[{"rule":[["row_index",0,""]],"background":"#DCDDDD","color":"#404040","paddingLeft":1.5,"paddingRight":1.5,"paddingTop":1.5,"paddingBottom":1.5,"fontSize":18,"borderTopWidth":3,"borderTopColor":"#F39800"},{"rule":[["default",0,""]],"background":"#E7E7E7","color":"#404040","paddingLeft":1.5,"paddingRight":1.5,"paddingTop":1.5,"paddingBottom":1.5,"fontSize":18}]',
                'css' => '.table [border=> none;border-spacing=> 2px;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table td[text-align=>center;color=>#404040;background-color=>#E7E7E7;].table tr=>first-child td[border-top=>4px solid #F39800;background-color=>#DCDDDD;]'
            ],
            [
                // 51369
                'json' => '[{"rule":[["row_index",0]],"background":"#e0e0e0","color":"#404040","fontSize":18,"borderBottomWidth":3,"borderBottomColor":"#e70012","paddingLeft":1.5,"paddingRight":1.5,"paddingTop":1.5,"paddingBottom":1.5,"specialShape":"rect1"},{"rule":[["default"]],"background":"#e7e7e7","color":"#404040","fontSize":18,"paddingLeft":1.5,"paddingRight":1.5,"paddingTop":1.5,"paddingBottom":1.5}]',
                'css' => '.table [border=> none;border-spacing=> 2px;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table td [text-align=> center;color=> #404040;background-color=> #e0e0e0;].table tr=>first-child td [border-bottom=>3px solid #e70012;border-top-left-radius=>10px; border-top-right-radius=>10px;]'
            ],
            [
                // 51370
                'json' => '[{"rule":[["row_index",0,""]],"background":"#E70012","color":"#FFFFFF","fontWeight":900,"paddingLeft":1,"paddingRight":1,"paddingTop":1,"paddingBottom":1,"fontSize":18},{"rule":[["default",0,""]],"background":"#E7E7E7","color":"#404040","paddingLeft":1,"paddingRight":1,"paddingTop":1,"paddingBottom":1,"fontSize":18}]',
                'css' => '.table [border=> none;border-spacing=> 2px;table-layout=> fixed;overflow=> hidden;font-size=> 16px;].table * [box-sizing=> border-box;].table td[text-align=>center;color=>#404040;background-color=>#E7E7E7;].table tr=>first-child td[color=>#ffffff;background-color=>#E70012;]'
            ]
        ];
        foreach ($css as $key => $value) {
            if (preg_replace('/\s/', '', $json) == $value['json']) {
                return ['index' => $key, 'css' => $value['css']];
            }
        }
        return false;
    }

}