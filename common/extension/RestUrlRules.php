<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\extension;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\CompositeUrlRule;
use yii\web\UrlRule as WebUrlRule;
use yii\web\UrlRule;
use yii\web\UrlRuleInterface;

class RestUrlRules
{

    public static $ruleConfig = 'yii\rest\UrlRule';

    /**
     * 生成版本化的url配置
     * @param $config
     * @return array
     * @throws InvalidConfigException
     */
    public static function prepare($config)
    {
        $modules = $config['modules'];
        $rules = $config['rules'];
        $pluralize = $config['pluralize'];

        if (!is_array($modules)) {
            throw new InvalidConfigException('"modules" must be set.');
        }
        $urls = [];
        foreach ($modules as $module) {
            foreach ($rules as $key => $item) {
                if (is_string($item)) {
                    // 区别有限制http方法的规则
                    if (strpos($key, ' ') !== false) {
                        $tmp = explode(' ', $key);
                        $key = $tmp[0] . ' ' . $module . '/' . $tmp[1];
                    } else {
                        $key = $module . '/' . $key;
                    }
                    $item = $module . '/' . $item;
                    $urls[$key] = $item;
                }
                if (is_array($item)) {
                    foreach ($item as $name => $value) {
                        if ($name != 'controller') {
                            $urls[$key][$name] = $value;
                        }
                    }
                    if (empty($item['class'])) {
                        $urls[$key]['class'] = static::$ruleConfig;
                    }
                    foreach ($item['controller'] as $k => $v) {
                        $urls[$key]['controller'][] = $module . '/' . $v;
                    }
                    $urls[$key]['pluralize'] = $pluralize;
                }
            }
        }
        return $urls;
    }

}