<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/19
 * Time: 17:05
 */

namespace common\models\search;

use common\models\Team;
use common\components\vendor\Model;
use common\models\TeamMember;
use common\models\CacheDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class TeamSearch extends Model
{
    public $status;

    private $_cacheKey;

    public function rules()
    {
        return [
            [['status'], 'integer']
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
     * 获取团队信息
     * @return bool|mixed|null
     */
    public function searchFrontend()
    {
        if (!$team = \Yii::$app->user->identity->team) {
            $this->addError('', '无法获取团队信息');
            return false;
        }
        $team_data = Team::find()
            ->where(['id' => $team->id, 'status' => Team::NORMAL_STATUS])
            ->with(['members' => function ($query) {
                /** @var $query ActiveQuery */
                $query->with('memberMark')
                    ->where(['status' => TeamMember::NORMAL_STATUS])
                    ->orderBy(['role' => SORT_ASC]);
            }]);
        try {
            $result = \Yii::$app->dataCache->cache(function () use ($team_data) {
                $result = $team_data->one();
                return $result;
            }, $this->cacheKey, CacheDependency::TEAM);
        } catch (\Throwable $e) {
            $result = null;
        }
        return $result;
    }


    public function searchBackend()
    {
        $team_data = Team::find()
            ->orderBy(['role'=>SORT_ASC])
            ->with('memberMark');
        $provider = new ActiveDataProvider([
            'query' => $team_data,
            'pagination' => [
                'pageSize' => 16,
            ],
        ]);
        $result = $provider->getModels();
        return $result;
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
                Team::tableName(),
                $this->scenario,
                $this->attributes,
            ];
        }
        return $this->_cacheKey;
    }

    /**
     * 获取用户id
     */
    public function getUser()
    {
        if (!$this->user_id) {
            $this->user_id = 1 /*\Yii::$app->user->id*/
            ;
        }
        return $this->user_id;
    }

    /**
     * 删除查询缓存
     * @author thanatos <thanatos915@163.com>
     */
    public function removeCache()
    {
        \Yii::$app->cache->delete($this->cacheKey);
    }
}