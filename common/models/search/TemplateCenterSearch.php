<?php


namespace common\models\search;

use common\components\traits\CacheDependencyTrait;
use common\models\CacheDependency;
use common\models\Category;
use common\models\TemplateOfficial;
use Yii;
use common\models\Classify;
use yii\base\Model;
use yii\db\ActiveQuery;

/**
 * Class TemplateCenterSearch
 * @property array $cacheKey
 * @package common\models\search
 * @author thanatos <thanatos915@163.com>
 */
class TemplateCenterSearch extends Model
{
    use CacheDependencyTrait;
    /** @var array */
    private $_cacheKey;

    /**
     * 删除查询缓存
     * @author thanatos <thanatos915@163.com>
     */
    public function removeCache()
    {
        Yii::$app->cache->delete($this->cacheKey);
    }

    /**
     * @return mixed|null
     */
    public function search()
    {
        $classify_recommend = Classify::online()
            ->andWhere(['is_recommend' => Classify::IS_RECOMMEND])
            ->with(['templates' => function($query){
                /** @var $query ActiveQuery */
                $query->andWhere(['>', 'recommend_at', '0']);
            }]);
        // 查询数据 使用缓存
        try {
            $result = Yii::$app->dataCache->cache(function () use ($classify_recommend) {
                return $result = $classify_recommend->all();
            }, $this->cacheKey, CacheDependency::OFFICIAL_HOT_RECOMMEND);
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
    public function getCacheKey()
    {
        if ($this->_cacheKey === null) {
            $this->_cacheKey = [
                __CLASS__,
                static::class,
                TemplateOfficial::tableName(),
                Classify::tableName(),
                $this->scenario,
            ];
        }
        return $this->_cacheKey;
    }
}