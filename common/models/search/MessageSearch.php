<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/11
 * Time: 11:42
 */
namespace common\models\search;
use common\models\TbzLetter;
use common\components\vendor\Model;
use yii\data\ActiveDataProvider;
use common\models\CacheDependency;
class MessageSearch extends Model
{
    public $status;
    public $type;

    private $_cacheKey;
    private $_user;

    public function rules()
    {
        return [
            [['status','type'],'integer']
        ];
    }
    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            static::SCENARIO_DEFAULT => [],
            static::SCENARIO_BACKEND => ['status','type'],
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
        if (!$this->validate()){
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
     * @return mixed|null 获取前台消息
     */
    public function searchFrontend(){
        $tbz_letter =  TbzLetter::online()
            ->andWhere(['or','user_id',0,$this->user]);   //查询所有公共消息和当前用户的个人消息
       $provider = new ActiveDataProvider([
            'query' => $tbz_letter,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        // 查询数据 使用缓存
        try {
            $result = \Yii::$app->dataCache->cache(function () use ($provider) {
                $result_data = $provider->getModels();
                return $result_data;
            }, $this->getcacheKey($provider->getKeys()), CacheDependency::MESSAGE);
        } catch (\Throwable $e) {
            $result = null;
        }
       return $result;
    }

    /**
     * @return array 后台获取数据
     */
    public function searchBackend(){
        $tbz_letter = TbzLetter::sortTime();
        if ($this->status){
            $tbz_letter->andWhere(['status'=>$this->status]);
        }
        if ($this->type){
            $tbz_letter->andWhere(['type'=>$this->type]);
        }
        $provider = new ActiveDataProvider([
            'query' => $tbz_letter,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $result_data = $provider->getModels();
        return $result_data;
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
                TbzLetter::tableName(),
                $this->scenario,
                $this->attributes,
                $key,
            ];
        }
        return $this->_cacheKey;
    }

    /**
     * 获取用户id
     * @return int|mixed
     */
   public function getUser(){
        if (!$this->_user){
            $this->_user = \Yii::$app->user->id;
        }
        return $this->_user;
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