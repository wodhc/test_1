<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/19
 * Time: 9:13
 */

namespace common\models\search;

use common\models\MyFavoriteMember;
use common\models\MyFavoriteTeam;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CacheDependency;

class MyFavoriteSearch extends Model
{
    public $classify_id;
    public $sort;

    public $_tableModel;
    private $_cacheKey;

    public function rules()
    {
        return [
            [['classify_id', 'sort'], 'integer'],
        ];
    }

    /**
     * 查询收藏模板
     * @param $params
     * @return bool|mixed|null
     */
    public function search($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }
        $favorite_data = $this->tableModel;
        $favorite_data->with(['templateOfficials' => function ($query) {
            if ($this->classify_id) {
                //按小分类查询
                /** @var $query \yii\db\ActiveQuery */
                $query->andWhere(['classify_id' => $this->classify_id]);
            }
        }]);
        //按时间排序,默认降序
        if ($this->sort && $this->sort == 1) {
            $favorite_data->orderBy(['created_at' => SORT_ASC]);
        } else {
            $favorite_data->orderBy(['created_at' => SORT_DESC]);
        }
        //分页
        $provider = new ActiveDataProvider([
            'query' => $favorite_data,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        // 查询数据 使用缓存
        try {
            $result = \Yii::$app->dataCache->cache(function () use ($provider) {
                $provider_data = $provider->getModels();
                $result = [];
                foreach ($provider_data as $key => $data) {
                    // 获取模板信息
                    if ($data->templateOfficials) {
                        $result[] = $data->templateOfficials;
                    }
                }
                return $result;
            }, $this->getCacheKey($provider->getKeys()), CacheDependency::MY_FAVORITE);
        } catch (\Throwable $e) {
            $result = null;
        }
        return $result;
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
                MyFavoriteMember::tableName(),
                MyFavoriteTeam::tableName(),
                $this->scenario,
                $this->attributes,
                $key
            ];
        }
        return $this->_cacheKey;
    }

    /**
     * 获取模型
     * @return mixed|\yii\db\ActiveQuery
     */
    public function getTableModel()
    {
        if ($this->_tableModel === null) {
            $user = \Yii::$app->user->identity;
            if ($user->team) {
                $favorite_data = MyFavoriteTeam::find()
                    ->where(['team_id' => $user->team->id]);
            } else {
                $favorite_data = MyFavoriteMember::find()
                    ->where(['user_id' => \Yii::$app->user->id]);
            }
            $this->_tableModel = $favorite_data;
        }
        return $this->_tableModel;
    }
}