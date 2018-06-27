<?php


namespace common\models\search;

use common\components\traits\CacheDependencyTrait;
use common\components\vendor\Model;
use common\models\Category;
use common\models\TemplateOfficial;
use common\models\TemplateOfficialTag;
use Yii;
use common\models\Classify;
use yii\db\ActiveQuery;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * Class TemplateOfficialSearch
 * @property string $cacheKey
 * @property ActiveQuery $query
 * @package common\models\search
 * @author thanatos <thanatos915@163.com>
 */
class TemplateOfficialSearch extends Model
{
    use CacheDependencyTrait;

    /** @var string 前台开启设计页 */
    const SCENARIO_FRONTEND = 'frontend';
    /** @var string 后台查询列表 */
    const SCENARIO_BACKEND = 'backend';
    /** @var string 小分类 */
    public $classify;
    /** @var integer 价格 */
    public $price;
    /** @var integer 风格 */
    public $style;
    /** @var integer 行业 */
    public $industry;
    /** @var integer 热度排序 */
    public $sort;
    /** @var integer 模板转态 */
    public $status;
    /** @var integer 大分类 */
    public $category;

    private $_query;
    private $_cacheKey;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['classify', 'price', 'style', 'industry', 'sort', 'status','category'], 'integer'],
            ['category','required'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            static::SCENARIO_DEFAULT => ['classify', 'price', 'style', 'industry', 'sort', 'status','category'],
            static::SCENARIO_BACKEND => ['classify', 'price', 'style', 'industry', 'sort', 'status','category'],
            static::SCENARIO_FRONTEND => ['classify', 'price', 'style', 'industry', 'sort','category']
        ];
    }

    /**
     * @param $params
     * @return null|ActiveDataProvider
     * @author thanatos <thanatos915@163.com>
     */
    public function search($params)
    {
        $this->load($params, '');
        switch ($this->scenario) {
            case static::SCENARIO_FRONTEND:
                return $this->searchFrontend();
            case static::SCENARIO_BACKEND:
            case static::SCENARIO_DEFAULT:
                return $this->searchBackend();
            default:
                return null;
        }
    }

    /**
     * 价格区间
     * @var array
     */
    public $prices = [
        1 => ['>=', 'price', 0],
        2 => ['between', 'price', 100, 500],
        3 => ['between', 'price', 500, 1000],
        4 => ['>=', 'price', 1000],
    ];

    /**
     * @return array|bool|ActiveDataProvider
     */
    public function searchFrontend()
    {
//        try {
//             $result = Yii::$app->dataCache->cache(function () use ($template_data) {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->query->with('myFavorite'),
        ]);
        $dataProvider = $dataProvider->getModels();
        if ($dataProvider){
            return $dataProvider;
        }
        return false;
//             }, $this->cacheKey, CacheDependency::OFFICIAL_TEMPLATE);
//         } catch (\Throwable $e) {
//             $result = null;
//         }
//        return $result;
    }

    /**
     * 后台查询
     * @return ActiveDataProvider|bool
     * @author thanatos <thanatos915@163.com>
     */
    public function searchBackend()
    {
        //状态查询
        if ($this->status) {
            $this->query->andWhere(['status' => $this->status]);
        }
        //分页
        $dataProvider = new ActiveDataProvider([
            'query' => $this->query,
        ]);
        if ($dataProvider){
            return $dataProvider;
        }
        return false;
    }


    /**
     * 删除查询缓存
     * @author thanatos <thanatos915@163.com>
     */
    public function removeCache()
    {
        Yii::$app->cache->delete($this->cacheKey);
    }

    /**
     * 查询缓存Key
     * @return array|null
     * @author thanatos <thanatos915@163.com>
     */
    public function getCacheKey()
    {
        if ($this->_cacheKey === null) {
            $this->_cacheKey = [
                __CLASS__,
                static::class,
                Category::tableName(),
                Classify::tableName(),
                $this->scenario,
            ];
        }
        return $this->_cacheKey;
    }

    /**
     * 拼接查询条件
     * @return bool|ActiveQuery
     * @author thanatos <thanatos915@163.com>
     */
    public function getQuery()
    {
        if ($this->_query === null) {
            $query = TemplateOfficial::active();
            if ($this->category){
                $query->andWhere(['category_id' => $this->category]);
            }
            if ($this->classify) {
                //按小分类查询
                $query->andWhere(['classify_id' => $this->classify]);
            }
            //按价格区间查询
            if ($this->price && array_key_exists($this->price, $this->prices)) {
                $query->andWhere(($this->prices)[$this->price]);
            }
            //按时间或者热度排序
            if ($this->sort && $this->sort == 1) {
                $query->orderBy(['sort' => SORT_DESC]);
            } else {
                $query->orderBy(['updated_at' => SORT_DESC]);
            }
            // 整合标签筛选
            /** @var ActiveQuery[] $subQueries */
            $subQueries = [];
            if ($this->industry) {
                $subQueries['industry'] = TemplateOfficialTag::find()->select(['template_id', 'tag_id'])->where(['tag_id' => $this->industry]);
            }

            if ($this->style) {
                $subQueries['style'] = TemplateOfficialTag::find()->select(['template_id', 'tag_id'])->where(['tag_id' => $this->style]);
            }

            /** @var Query $subQuery */
            $subQuery = (new Query());
            $oldKey = '';
            foreach ($subQueries as $k =>$sub) {
                if (count($subQueries) == 1) {
                    $subQuery = $sub->select('template_id');
                } else {
                    if ($oldKey) {
                        $subQuery->innerJoin([$k => $sub], $oldKey . '.template_id = '. $k . '.template_id');
                    } else {
                        $subQuery->from([$k => $sub])->select($k.'.template_id');
                        $oldKey = $k;
                    }
                }
            }
             if ($subQuery && $subQueries) {
                $query->andWhere(['template_id' => $subQuery]);
            }

            $this->_query = $query;
        }
        return $this->_query;
    }

}