<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\vendor;

use common\models\CacheDependency;
use common\models\CacheGroup;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\caching\DbDependency;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class DataCache
 * @property array $db
 * @property array $className
 * @property array $cacheKey
 * @package common\components\vendor
 * @author thanatos <thanatos915@163.com>
 */
class DataCache extends Component
{
    /** @var array */
    private $_db;

    /**
     * 系统数据缓存统一机制
     * @param callable $callable 查询数据的闭包
     * @param array $cacheKey 缓存的Key值
     * @param string $dependency 依赖的值，（在数据库中存的值）
     * @param null $db 默认的数据库连接
     * @return mixed
     * @author thanatos <thanatos915@163.com>
     */
    public function cache(callable $callable, $cacheKey, $dependency, $db = null)
    {
        $this->setDb($db);
        $result = false/*Yii::$app->cache->get($cacheKey)*/;
        if ($result === false) {
            // 查询数据
            $result = call_user_func($callable, $this);
            Yii::$app->cache->set($cacheKey, $result, null, $this->getCacheDependency($dependency));
        }

        return $result;
    }

    /**
     * 根据ActiveRecord类修改缓存信息
     * @param $class
     * @return bool|int
     * 返回true执行成功
     * @author thanatos <thanatos915@163.com>
     */
    public function updateCache($class)
    {
        if (class_exists($class)) {
            /** @var Connection $db */
            $db = $class::getDb();
            if ($db && ($table = $db->getTableSchema($class::tableName()))) {
                /** @var CacheGroup[] $data */
                $data = CacheGroup::find()->where(['table_name' => $table->fullName])->asArray()->all();
                $caches = ArrayHelper::getColumn($data, 'cache_name');
                // 修改数据缓存
                try {
                    $result = CacheDependency::getDb()->createCommand()->update(CacheDependency::tableName(), ['updated_at' => time()], ['cache_name' => $caches])->execute();
                    return $result;
                } catch (\Throwable $e) {
                    $message = $e->getMessage();
                }
            }
        }
        return $message ?: false;
    }

    /**
     * @param string $cacheName
     * @return DbDependency
     * @author thanatos <thanatos915@163.com>
     */
    public function getCacheDependency($cacheName)
    {
        return new DbDependency([
            'db' => $this->db,
            'sql' => CacheDependency::getDependencyCacheName($cacheName)
        ]);
    }

    /**
     * 设置当前连接的数据库
     * @param $db
     * @author thanatos <thanatos915@163.com>
     */
    public function setDb($db)
    {
        if ($db instanceof Connection) {
            $this->_db = $db;
        }
    }

    public function getDb()
    {
        if ($this->_db === null) {
            $this->_db = Yii::$app->db;
        }
        return $this->_db;
    }
}