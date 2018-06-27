<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */
namespace common\components\validators;

use Yii;
use yii\validators\Validator;

class SmsCodeValidator extends Validator
{
    public $type;
    public $mobile;

    public function init()
    {
        if ($this->message === null) {
            $this->message = '验证码不正确';
        }
        parent::init(); // TODO: Change the autogenerated stub
    }

    protected function validateValue($value)
    {
        return Yii::$app->sms->validateCode($this->mobile, $value, $this->type);
    }
}