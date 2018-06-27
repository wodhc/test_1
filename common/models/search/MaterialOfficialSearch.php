<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/28
 * Time: 9:06
 */
namespace common\models\search;
use common\models\MaterialOfficial;
use common\components\vendor\Model;
use yii\data\ActiveDataProvider;
use common\models\CacheDependency;
use common\components\traits\ModelErrorTrait;
class MaterialOfficialSearch extends Model
{
    use ModelErrorTrait;

    public $status;
    public $sort;
    public $cid;
    public $tags;

    private $_cacheKey;
    private $_query;

    public function rules()
    {
        return [
            [['status', 'sort', 'cid'], 'integer'],
            ['tags','string']
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            static::SCENARIO_DEFAULT => ['sort', 'cid', 'tags'],
            static::SCENARIO_BACKEND => ['status', 'sort', 'cid','tags'],
            static::SCENARIO_FRONTEND => ['sort', 'cid', 'tags']
        ];
    }

    /**
     * @param $params
     * @return array|mixed|null
     */
    public function search($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }
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
     * 前台查询官方素材
     * @return bool|mixed|null
     */
    public function searchFrontend()
    {
        $provider = new ActiveDataProvider([
            'query' => $this->query,
            'pagination' => [
                'pageSize' => 18,
            ],
        ]);
        //$this->removeCache();die;
        try {
            $result = \Yii::$app->dataCache->cache(function () use ($provider) {
                $result = $provider->getModels();
                return $result;
            }, $this->getCacheKey($provider->getKeys()), CacheDependency::MATERIAL_OFFICIAL);
        } catch (\Throwable $e) {
            $result = null;
        }
        return $result;
    }

    /**
     * 后台查询官方素材
     * @return array|bool
     */
    public function searchBackend()
    {
        $provider = new ActiveDataProvider([
            'query' => $this->query,
        ]);
        $result = $provider->getModels();
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * 查询缓存Key
     * @return array|null
     * @author thanatos <thanatos915@163.com>
     */
    public function getCacheKey($key)
    {
        if ($this->_cacheKey === null) {
            $this->_cacheKey = [
                __CLASS__,
                static::class,
                MaterialOfficial::tableName(),
                MaterialOfficial::tableName(),
                $this->scenario,
                $this->attributes,
                $key,
            ];
        }
        return $this->_cacheKey;
    }

    /**
     * 删除查询缓存
     * @author thanatos <thanatos915@163.com>
     */
    public function removeCache()
    {
        \Yii::$app->cache->delete($this->cacheKey);
    }

    /**
     * @return mixed|\yii\db\ActiveQuery 拼接查询条件
     */
    public function getQuery()
    {
        if ($this->_query === null) {
            $query = MaterialOfficial::active();
            //按素材分类查询
            if ($this->cid) {
                $query->andWhere(['cid' => $this->cid]);
            }
            //按标签查询
            if ($this->tags) {
                $query->andWhere(['like','tags',$this->tags]);
            }
            //按时间排序
            if ($this->sort && $this->sort == 1) {
                $query->orderBy(['created_at' => SORT_ASC]);
            } else {
                $query->orderBy(['created_at' => SORT_DESC]);
            }
            $this->_query = $query;
        }
        return $this->_query;
    }
}