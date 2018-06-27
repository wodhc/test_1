<?php

namespace common\models;

use common\components\traits\ModelErrorTrait;
use Yii;
use common\components\traits\TimestampTrait;
use common\components\traits\ModelFieldsTrait;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%tbz_subject}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="TbzSubject"))
 *
 * @property int $id @SWG\Property(property="id", type="integer", description="")
 * @property string $product 专题url缩写  @SWG\Property(property="product", type="string", description=" 专题url缩写 ")
 * @property string $title 文章标题 @SWG\Property(property="title", type="string", description=" 文章标题")
 * @property string $description 专题描述 @SWG\Property(property="description", type="string", description=" 专题描述")
 * @property string $thumbnail 缩略图 @SWG\Property(property="thumbnail", type="string", description=" 缩略图")
 * @property string $banner 专题内页banner图 @SWG\Property(property="banner", type="string", description=" 专题内页banner图")
 * @property string $seo_title SEO标题 @SWG\Property(property="seoTitle", type="string", description=" SEO标题")
 * @property string $seo_keyword SEO关键词 @SWG\Property(property="seoKeyword", type="string", description=" SEO关键词")
 * @property string $seo_description SEO描述 @SWG\Property(property="seoDescription", type="string", description=" SEO描述")
 * @property int $status 是否上线 @SWG\Property(property="status", type="integer", description=" 是否上线")
 * @property int $sort 排序逆序 @SWG\Property(property="sort", type="integer", description=" 排序逆序")
 * @property int $created_at 创建日期 @SWG\Property(property="createdAt", type="integer", description=" 创建日期")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 * @property TemplateOfficial[] $templates
 * @property FileCommon $thumbnailFile
 * @property $bannerFile
 */
class TbzSubject extends \yii\db\ActiveRecord
{

    use TimestampTrait;
    use ModelFieldsTrait;
    use ModelErrorTrait;
    /** @var int 模板专题上线 */
    const STATUS_ONLINE = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tbz_subject}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product'], 'default', 'value' => ''],
            [['status', 'sort', 'created_at', 'updated_at'], 'integer'],
            [['product'], 'string', 'max' => 30],
            [['title'], 'string', 'max' => 150],
            [['description', 'seo_keyword', 'seo_description'], 'string', 'max' => 255],
            [['thumbnail', 'seo_title'], 'string', 'max' => 100],
            [['banner'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '唯一标识',
            'title' => '模板标题',
            'description' => '描述',
            'thumbnail' => '缩略图路径的文件id',
            'banner' => '专题内页banner图路径的文件id',
            'seo_title' => 'Seo标题',
            'seo_keyword' => 'Seo关键词',
            'seo_description' => 'Seo描述',
            'status' => '模板状态',
            'sort' => '热度',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    public function frontendFields()
    {
        return ['id', 'title', 'description', 'product', 'seo_keyword', 'seo_description', 'thumbnail', 'seo_title', 'banner'];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function sortHot()
    {
        {
            return static::online()->orderBy(['sort' => SORT_DESC]);
        }
    }

    /**
     * 上线分类
     * @return \yii\db\ActiveQuery
     * @author thanatos <thanatos915@163.com>
     */
    public static function online()
    {
        if (Yii::$app->request->isFrontend()) {
            return static::find()->andWhere(['status' => static::STATUS_ONLINE]);
        } else {
            return static::find();
        }
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * 更新缓存
     */
    public function afterSave($insert, $changedAttributes)
    {
        // 更新缓存
        if ($changedAttributes) {
            Yii::$app->dataCache->updateCache(static::class);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function findById($id)
    {
        return static::online()->andWhere(['id' => $id])->one;
    }

    /**
     * @param $product
     * @return array|null|static
     * @author thanatos <thanatos915@163.com>
     */
    public static function findByProduct($product)
    {
        return static::online()->andWhere(['product' => $product])->one();
    }

    /**
     * @return array
     */
    public function expandFields()
    {
        if ($this->isRelationPopulated('templates')) {
            $data['templates'] = function (){
                return $this->templates;
            };
        }
        $data['thumbnail'] = function (){
            return $this->thumbnailFile ? Url::to('@oss') . DIRECTORY_SEPARATOR . $this->thumbnailFile->path : '';
        };
        $data['banner'] = function (){
            return $this->bannerFile ? Url::to('@oss') . DIRECTORY_SEPARATOR . $this->bannerFile->path : '';
        };
        return $data;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplates()
    {
        return $this->hasMany(TemplateOfficial::class, ['template_id' => 'template_id'])
            ->viaTable(TemplateTopic::tableName(),['topic_id'=>'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getThumbnailFile(){
        return $this->hasOne(FileCommon::class, ['file_id' => 'thumbnail']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBannerFile(){
        return $this->hasOne(FileCommon::class, ['file_id' => 'banner']);
    }
}
