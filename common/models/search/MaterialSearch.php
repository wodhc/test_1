<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/21
 * Time: 17:09
 */

namespace common\models\search;

use common\models\MaterialMember;
use common\models\MaterialTeam;
use common\components\vendor\Model;
use yii\data\ActiveDataProvider;
use common\models\CacheDependency;
use common\components\traits\ModelErrorTrait;

class MaterialSearch extends Model
{
    use ModelErrorTrait;

    /** @var int 正常状态 */
    const NORMAL_STATUS = 10;
    /** @var integer 默认文件夹 */
    const DEFAULT_FOLDER = 0;
    /** @var int 正式素材 */
    const NORMAL_MODE = 20;
    /** @var int 回收站状态 */
    const RECYCLE_STATUS = 7;

    public $status;
    public $folder;
    public $sort;
    public $mode;

    private $_cacheKey;
    private $_query;
    private $_condition;
    private $_tableModel;

    public function rules()
    {
        return [
            [['status', 'folder', 'team_id', 'mode'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            static::SCENARIO_DEFAULT => ['status', 'mode', 'folder'],
            static::SCENARIO_BACKEND => ['status', 'mode'],
            static::SCENARIO_FRONTEND => ['status', 'folder','mode']
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
     * 前台查询个人或团队素材
     * @return bool|mixed|null
     */
    public function searchFrontend()
    {
        //查询当前用户的素材
        $this->query->andWhere($this->_condition);
        //当是回收站查询时，不按文件夹查询
        if ($this->status != static::RECYCLE_STATUS){
            $this->query->andWhere(['folder_id' => $this->folder ?: static::DEFAULT_FOLDER]);
        }
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
            }, $this->getCacheKey($provider->getKeys()), CacheDependency::MATERIAL);
        } catch (\Throwable $e) {
            $result = null;
        }
        return $result;
    }

    /**
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
                MaterialTeam::tableName(),
                MaterialMember::tableName(),
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
            if (!$query = $this->tableModel){
                return false;
            }
            $query->with('fileCommon');
            //按素材类型查询
            $query->andWhere(['mode' => $this->mode ?: static::NORMAL_MODE]);
            //按状态查询
            $query->andWhere(['status' => $this->status ?: static::NORMAL_STATUS]);
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

    /**
     * 获取模型
     * @return mixed|string
     */
    public function getTableModel()
    {
        if ($this->_tableModel === null) {
            $user = \Yii::$app->user->identity;
            if ($user->team){
                $tableModel = MaterialTeam::sort();
                $this->_condition = ['team_id'=>$user->team->id];
            }else{
                $tableModel = MaterialMember::sort();
                $this->_condition = ['user_id'=>\Yii::$app->user->id];
            }
            $this->_tableModel = $tableModel;
        }
        return $this->_tableModel;
    }
}