<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/9
 * Time: 16:46
 */

namespace common\models\search;

use common\components\vendor\Model;
use common\models\FileCommon;
use common\models\TbzSubject;
use function GuzzleHttp\Promise\all;
use yii\data\ActiveDataProvider;
use Yii;
use common\models\CacheDependency;

class TbzSubjectSearch extends Model
{
    public $status;

    private $_cacheKey;

    public function rules()
    {
        return [
            ['status', 'integer']
        ];
    }

    public function scenarios()
    {
        return [
            static::SCENARIO_DEFAULT => [],
            static::SCENARIO_BACKEND => ['status'],
            static::SCENARIO_FRONTEND => []
        ];
    }

    /**
     * 查询数据
     * @param $params
     * @return TbzSubject[]|null|bool
     * @author thanatos <thanatos915@163.com>
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
     * @return mixed|null
     * 前端查询模板专题
     */
    public function searchFrontend()
    {
        $cover_data = TbzSubject::online()
            ->with(['thumbnailFile', 'bannerFile']);
        $provider = new ActiveDataProvider([
            'query' => $cover_data,
            'pagination' => [
                'pageSize' => 16,
            ],
        ]);
        try {
            $result = Yii::$app->dataCache->cache(function () use ($provider) {
                $result = $provider->getModels();
                return $result;
            }, $this->getcacheKey($provider->getKeys()), CacheDependency::TEMPLATE_COVER);
        } catch (\Throwable $e) {
            $result = null;
        }
        return $result;
    }

    /**
     * @param $params
     * @return array|bool
     * 后台查询模板专题
     */
    public function searchBackend()
    {
        $cover_data = TbzSubject::sortHot()
            ->with(['thumbnailFile', 'bannerFile']);;
        //根据状态查询模板专题
        if ($this->status) {
            $cover_data->andWhere(['status' => $this->status]);
        }
        $provider = new ActiveDataProvider([
            'query' => $cover_data,
            'pagination' => [
                'pageSize' => 16,
            ],
        ]);
        $result_data = $provider->getModels();
        if ($result_data) {
            return $result_data;
        } else {
            return false;
        }
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
                TbzSubject::tableName(),
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
        Yii::$app->cache->delete($this->cacheKey);
    }
}