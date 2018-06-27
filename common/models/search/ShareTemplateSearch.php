<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/6/4
 * Time: 13:23
 */

namespace common\models\search;

use common\components\traits\ModelErrorTrait;
use common\models\ShareTemplate;
use common\components\vendor\Model;
use common\models\TemplateMember;
use yii\data\ActiveDataProvider;
use common\models\CacheDependency;

class ShareTemplateSearch extends Model
{
    use ModelErrorTrait;
    /** @var int 模板正常状态 */
    const NORMAL_STATUS = 10;

    public $classify_id;
    public $sort;
    public $authority;

    private $_cacheKey;
    private $_query;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['classify_id', 'sort','authority'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            static::SCENARIO_DEFAULT => ['classify_id', 'sort','authority'],
            static::SCENARIO_BACKEND => ['classify_id', 'sort','authority'],
            static::SCENARIO_FRONTEND => ['classify_id', 'sort','authority']
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
                return $this->searchBackend();
            default:
                return null;
        }
    }

    /**
     * @return ActiveDataProvider
     */
    public function searchFrontend()
    {
        $this->query->where([ShareTemplate::tableName().'.shared_person' => \Yii::$app->user->id]);
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
            }, $this->getcacheKey($provider->getKeys()), CacheDependency::TEMPLATE_SHARE);
        } catch (\Throwable $e) {
            $result = null;
        }
        return $result;
    }

    /**
     * @return ActiveDataProvider 后台查询个人模板信息
     */
    public function searchBackend()
    {
        $provider = new ActiveDataProvider([
            'query' => $this->query,
        ]);
        return $provider;
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
                ShareTemplate::tableName(),
                ShareTemplate::tableName(),
                $this->scenario,
                $this->attributes,
                $key
            ];
        }
        return $this->_cacheKey;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        if ($this->_query === null) {
            $query = TemplateMember::active()
                ->select(TemplateMember::tableName().'.*,'.ShareTemplate::tableName().'.*')
                ->joinwith('shares');
            if ($this->classify_id) {
                $query->where([TemplateMember::tableName().'.classify_id' => $this->classify_id]);
            }
            //按时间排序
            if ($this->sort && $this->sort == 1) {
                $query->orderBy([ShareTemplate::tableName().'.updated_at' => SORT_ASC]);
            } else {
                $query->orderBy([ShareTemplate::tableName().'.updated_at' => SORT_DESC]);
            }
            if ($this->authority){
                $query->orderBy([ShareTemplate::tableName().'.authority' => $this->authority]);
            }
            $this->_query = $query;
        }
        return $this->_query;
    }
}