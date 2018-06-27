<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/28
 * Time: 14:45
 */
namespace common\models\search;
use common\models\MaterialClassify;
use common\components\vendor\Model;
use yii\data\ActiveDataProvider;
use common\models\CacheDependency;
use common\components\traits\ModelErrorTrait;
class MaterialClassifySearch extends Model
{
    use ModelErrorTrait;

    public $status;

    private $_cacheKey;
    private $_query;

    public function rules()
    {
        return [
            ['status', 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            static::SCENARIO_DEFAULT => [],
            static::SCENARIO_BACKEND => ['status'],
            static::SCENARIO_FRONTEND => []
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
     * 前台查询素材分类
     * @return bool|mixed|null
     */
    public function searchFrontend()
    {
        $query = $this->query;
        try {
            $result = \Yii::$app->dataCache->cache(function () use ($query) {
                $result = $query->all();
                return $result;
            }, $this->getCacheKey(), CacheDependency::MATERIAL_CLASSIFY);
        } catch (\Throwable $e) {
            $result = null;
        }
        return $result;
    }

    /**
     * 后台查询素材分类
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
     * @return array|null
     */
    public function getCacheKey()
    {
        if ($this->_cacheKey === null) {
            $this->_cacheKey = [
                __CLASS__,
                static::class,
                MaterialClassify::tableName(),
                MaterialClassify::tableName(),
                $this->scenario,
                $this->attributes,
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
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        if ($this->_query === null) {
            $status = $this->status ?: MaterialClassify::STATUS_NORMAL;
            $query = MaterialClassify::active($status);
            $this->_query = $query;
        }
        return $this->_query;
    }
}