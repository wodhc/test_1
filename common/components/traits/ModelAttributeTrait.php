<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\traits;


trait ModelAttributeTrait
{

    /**
     * 获取当前模型想要修改的值
     * @return array
     * @author thanatos <thanatos915@163.com>
     */
    public function getUpdateAttributes()
    {
        $safe = $this->safeAttributes();
        $values = [];
        foreach ($safe as $key => $item) {
            if ($this->$item !== null) {
                $values[$item] = $this->$item;
            }
        }
        return $values;
    }

}