<?php

namespace common\models;

use common\components\traits\ModelErrorTrait;
use Yii;
use common\components\traits\ModelFieldsTrait;
use common\components\traits\TimestampTrait;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%classify}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="Classify"))
 *
 * @property int $classify_id @SWG\Property(property="classifyId", type="integer", description="")
 * @property int $category_id 所属品类id @SWG\Property(property="categoryId", type="integer", description=" 所属品类id")
 * @property int $pid 父分类id @SWG\Property(property="pid", type="integer", description=" 父分类id")
 * @property string $name 分类名称 @SWG\Property(property="name", type="string", description=" 分类名称")
 * @property int $default_price 默认价格 @SWG\Property(property="defaultPrice", type="integer", description=" 默认价格")
 * @property int $is_hot 是否是热门 @SWG\Property(property="isHot", type="integer", description=" 是否是热门")
 * @property int $is_new 是否是新上 @SWG\Property(property="isNew", type="integer", description=" 是否是新上")
 * @property string $default_edit 模板默认配置 @SWG\Property(property="defaultEdit", type="string", description=" 模板默认配置")
 * @property string $order_link 下单连接 @SWG\Property(property="orderLink", type="string", description=" 下单连接")
 * @property string $thumbnail 缩略图 @SWG\Property(property="thumbnail", type="string", description=" 缩略图")
 * @property int $thumbnail_id 缩略图file_id @SWG\Property(property="thumbnailId", type="integer", description=" 缩略图file_id")
 * @property int $sort 排序值 @SWG\Property(property="sort", type="integer", description=" 排序值")
 * @property int $is_open 是否对外开放 @SWG\Property(property="isOpen", type="integer", description=" 是否对外开放")
 * @property int $status 分类状态 @SWG\Property(property="status", type="integer", description=" 分类状态")
 * @property int $is_recommend 是否推荐到热门场景 @SWG\Property(property="isRecommend", type="integer", description=" 是否推荐到热门场景")
 * @property int $created_at 创建时间 @SWG\Property(property="createdAt", type="integer", description=" 创建时间")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 * @property Tag[] $tags
 */
class Classify extends \yii\db\ActiveRecord
{
    use TimestampTrait;
    use ModelFieldsTrait;
    use ModelErrorTrait;

    /** @var int 分类下线 */
    const STATUS_OFFLINE = 10;
    /** @var int 分类上线 */
    const STATUS_ONLINE = 20;

    /** @var int 推荐到热门场景 */
    const IS_RECOMMEND = 1;
    /** @var integer 模板状态 */
    const TEMPLATE_OFFICIAL_STATUS = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%classify}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'pid', 'is_hot', 'is_new', 'is_open', 'status', 'is_recommend'], 'filter', 'filter' => 'intval'],
            [['category_id', 'name', 'default_edit', 'created_at', 'updated_at'], 'required'],
            ['status', 'default', 'value' => 10],
            [['default_price', 'thumbnail_id', 'sort', 'status','created_at', 'updated_at', 'category_id', 'pid', 'is_hot', 'is_new', 'is_recommend', 'status'], 'integer'],
            [['default_edit'], 'string'],
            [['name',], 'string', 'max' => 15],
            [['order_link', 'thumbnail'], 'string', 'max' => 255],
        ];
    }

    /**
     * 公共返回数据
     * @return array
     * @author thanatos <thanatos915@163.com>
     */
    public function frontendFields()
    {
        return [
            'classify_id','name', 'is_hot', 'is_new', 'order_link',
        ];
    }

    public function expandFields()
    {
        $data = ['thumbnail' => function() {
            return Url::to('@oss') . DIRECTORY_SEPARATOR . $this->thumbnail;
        }];
        if ($this->isRelationPopulated('templates')) {
            $data['templates'] = function () {
                return $this->templates;
            };
        }
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product' => '模板分类标识',
            'parent_product' => '父分类',
            'category' => '所属品类标识',
            'name' => '分类名称',
            'parent_name' => '父分类名称',
            'default_price' => '默认价格',
            'is_hot' => '是否是热门',
            'is_new' => '是否是新上',
            'default_edit' => '模板默认配置',
            'order_link' => '下单连接',
            'thumbnail' => '缩略图',
            'thumbnail_id' => '缩略图file_id',
            'sort' => '排序值',
            'is_open' => '是否对外开放',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * 保存成功后更新缓存
     * @param bool $insert
     * @param array $changedAttributes
     * @author thanatos <thanatos915@163.com>
     */
    public function afterSave($insert, $changedAttributes)
    {
        // 更新缓存
        if ($changedAttributes) {
            Yii::$app->dataCache->updateCache(static::class);
        }
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    /**
     * @return \yii\db\ActiveQuery
     * @author thanatos <thanatos915@163.com>
     */
    public static function active()
    {
        return static::find()->orderBy(['sort' => SORT_ASC]);
    }

    /**
     * 上线分类
     * @return \yii\db\ActiveQuery
     * @author thanatos <thanatos915@163.com>
     */
    public static function online()
    {
        return static::active()->andWhere(['status' => static::STATUS_ONLINE]);
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @author thanatos <thanatos915@163.com>
     */
    public static function findHot()
    {
        return static::online()->where(['is_recommend' => static::IS_RECOMMEND])->all();
    }

    public static function findById($id)
    {
        return static::findOne(['classify_id' => $id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @author thanatos <thanatos915@163.com>
     */
    public function getTemplates()
    {
        return $this->hasMany(TemplateOfficial::class, ['classify_id' => 'classify_id'])
            ->where(['status' => static::TEMPLATE_OFFICIAL_STATUS])
            ->orderBy(['sort' => SORT_ASC])
            ->with('myFavorite');
    }

    /**
     * @return \yii\db\ActiveQuery
     * @author thanatos <thanatos915@163.com>
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['tag_id' => 'tag_id'])
            ->viaTable(TagRelationClassify::tableName(), ['classify_id' => 'classify_id']);
    }
}
