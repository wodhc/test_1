<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/17
 * Time: 9:33
 */

namespace common\models\search;

use common\models\ShareTemplate;
use common\models\TemplateMember;
use common\components\vendor\Model;
use common\models\TemplateTeam;
use yii\data\ActiveDataProvider;
use common\models\CacheDependency;

class TemplateUserSearch extends Model
{
    /** @var integer 默认文件夹 */
    const DEFAULT_FOLDER = 0;
    /** @var int 模板正常状态 */
    const NORMAL_STATUS = 10;
    /** @var int 回收站状态 */
    const RECYCLE_STATUS = 7;

    public $status;
    public $classify_id;
    public $folder;
    public $sort;

    private $_cacheKey;
    private $_query;
    private $_condition;

    public function rules()
    {
        return [
            [['status', 'classify_id', 'folder', 'sort'], 'integer'],
            ['status', 'in', 'range' => [static::NORMAL_STATUS, static::RECYCLE_STATUS]]
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            static::SCENARIO_DEFAULT => ['status', 'classify_id', 'folder', 'sort'],
            static::SCENARIO_BACKEND => ['status', 'classify_id', 'sort'],
            static::SCENARIO_FRONTEND => ['status', 'classify_id', 'folder', 'sort']
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
     * @return bool|mixed|null
     */
    public function searchFrontend()
    {
        /** @var  $query \yii\db\ActiveQuery */
        $query = $this->query;
        //如果为回收站查询，则不按文件夹进行查询
        if ($this->status != static::RECYCLE_STATUS) {
            //按默认文件夹查询，个人查询时，如果按文件夹查询，只查个人模板，如果按默认文件夹查询，则两个都查，且分享过来的模板不受文件夹限制
            if ($query->modelClass == TemplateMember::class) {
                if (!$this->folder) {
                    $query->andWhere(['or', ['and', $this->_condition, [TemplateMember::tableName() . '.folder_id' => static::DEFAULT_FOLDER]], [ShareTemplate::tableName() . '.shared_person' => \Yii::$app->user->id]]);
                } else {
                    $query->andWhere($this->_condition);
                    $query->andWhere([TemplateMember::tableName() . '.folder_id' => $this->folder]);
                }
            } else {
                $query->andWhere($this->_condition);
                $query->andWhere(['folder_id' => $this->folder ?: static::DEFAULT_FOLDER]);
            }
        } else {
            if ($query->modelClass == TemplateMember::class) {
                $query->andWhere(['or', $this->_condition, [ShareTemplate::tableName() . '.shared_person' => \Yii::$app->user->id]]);
            } else {
                $query->andWhere($this->_condition);
            }
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 18,
            ],
        ]);
        //$this->removeCache();die;
        try {
            $result = \Yii::$app->dataCache->cache(function () use ($provider) {
                $result = $provider->getModels();
                return $result;
            }, $this->getcacheKey($provider->getKeys()), CacheDependency::TEMPLATE_USER);
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
                TemplateMember::tableName(),
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
        \Yii::$app->cache->delete($this->_cacheKey);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        if ($this->_query === null) {
            $user = \Yii::$app->user->identity;
            if ($user->team) {
                $query = TemplateTeam::sort();
                $table_name = TemplateTeam::tableName();
                $this->_condition = ['team_id' => $user->team->id];
            } else {
                $table_name = TemplateMember::tableName();
                $query = TemplateMember::sort();
                $this->_condition = [$table_name . '.user_id' => \Yii::$app->user->id];
                $query->joinWith('shares')->with('classify');
            }
            //按小分类查询
            if ($this->classify_id) {
                $query->where([$table_name . '.classify_id' => $this->classify_id]);
            }
            //按状态查询
            $query->andWhere([$table_name . '.status' => $this->status ?: static::NORMAL_STATUS]);
            //按时间排序
            if ($this->sort && $this->sort == 1) {
                $query->orderBy([$table_name . '.updated_at' => SORT_ASC]);
            } else {
                $query->orderBy([$table_name . '.updated_at' => SORT_DESC]);
            }
            $this->_query = $query;
        }
        return $this->_query;
    }
}