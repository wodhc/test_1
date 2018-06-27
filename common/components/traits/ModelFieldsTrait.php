<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\traits;

use Yii;
use yii\base\Arrayable;
use yii\helpers\ArrayHelper;

trait ModelFieldsTrait
{

    /**
     * 规范每个模型类返回值处理
     * @return mixed
     * @author thanatos <thanatos915@163.com>
     */
    public function fields()
    {
        $request = Yii::$app->request;
        $fields = parent::fields();

        if ($request->isFrontend() && $this->frontendFields())
            $fields = $this->frontendFields();

        // 整合其它值
        if (method_exists($this,'expandFields')){
            foreach ($this->expandFields() as $field => $definition) {
                $fields[$field] = $definition;
            }
        }
        // 删除掉一些敏感信息
        $newFields = [];
        foreach ($fields as $k => &$v) {
            if (!in_array($v, $this->deleteFields()))
                $newFields[$k] = $v;
        }
       return $this->filterName($newFields);
    }

    /**
     * 前端查询需要用到的字段
     * @return array
     * @author thanatos <thanatos915@163.com>
     */
    public function frontendFields()
    {
        return [];
    }

    /**
     * 过滤掉的一些敏感字段
     * @return array
     * @author thanatos <thanatos915@163.com>
     */
    public function deleteFields()
    {
        return [];
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        // 删除掉一些敏感信息
        $newFields = [];
        foreach ($fields as $k => $v) {
            if (!in_array($v, $this->deleteFields()))
                $newFields[$k] = $v;
        }
        $fields = $newFields;
        // 替换模型中带"_"的字段
        foreach ($fields as $k => &$val){
            $val = $this->toUcFirst($val);
        }
        $data = [];
        foreach ($this->resolveFields($fields, $expand) as $field => $definition) {
            $attribute = is_string($definition) ? $this->$definition : $definition($this, $field);

            if ($recursive) {
                $nestedFields = $this->extractFieldsFor($fields, $field);
                $nestedExpand = $this->extractFieldsFor($expand, $field);
                if ($attribute instanceof Arrayable) {
                    $attribute = $attribute->toArray($nestedFields, $nestedExpand);
                } elseif (is_array($attribute)) {
                    $attribute = array_map(
                        function ($item) use ($nestedFields, $nestedExpand) {
                            if ($item instanceof Arrayable) {
                                return $item->toArray($nestedFields, $nestedExpand);
                            }
                            return $item;
                        },
                        $attribute
                    );
                }
            }
            $data[$field] = $attribute;
        }

        if ($this instanceof Linkable) {
            $data['_links'] = Link::serialize($this->getLinks());
        }

        return $recursive ? ArrayHelper::toArray($data) : $data;
    }

    /**
     * 把带下划线的值改成大写
     * @param $fields
     * @return array
     * @author thanatos <thanatos915@163.com>
     */
    protected function filterName($fields)
    {
        $newFields = [];
        foreach ($fields as $k => $item) {
            if (is_string($item) && strpos($item, '_') !== false) {
                $newK = $this->toUcFirst($item);
                $newFields[$newK] = function() use($item){
                    return $this->$item;
                };
            } else {
                $newFields[$k] = $item;
            }
        }
        return $newFields;
    }

    /**
     * 替换字符串中的"_"
     * @param $value
     * @return null|string|string[]
     * @author thanatos <thanatos915@163.com>
     */
    protected function toUcFirst($value)
    {
        return preg_replace_callback('%_([a-z0-9_])%i', function ($matches) {
            return ucfirst($matches[1]);
        }, $value);
    }

}