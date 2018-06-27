<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\traits;


use yii\helpers\Json;

trait ModelErrorTrait
{

    /**
     * 返回错误中的第一个信息
     * @return string
     * @author thanatos <thanatos915@163.com>
     */
    public function getOneError()
    {
        foreach ($this->getErrors() as $attribute => $errors) {
            return $this->getFirstError($attribute);
        }
        return '';
    }

    /**
     * 返回JSON格式的错误信息
     * @return string
     * @author thanatos <thanatos915@163.com>
     */
    public function getStringErrors()
    {
        return Json::encode($this->getErrors());
    }

}