<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\models\forms;

use common\components\traits\ModelErrorTrait;
use common\models\Member;
use Yii;
use common\components\validators\MobileValidator;
use common\components\vendor\Sms;
use common\extension\Code;
use yii\base\Model;

class PasswordForm extends Model
{
    use ModelErrorTrait;
    /** @var string 重置密码 */
    const SCENARIO_RESET = 'reset';
    /** @var string 找回密码 */
    const SCENARIO_FOUND = 'found';

    public $mobile;
    public $password;
    public $password_repeat;
    public $code;

    public function scenarios()
    {
        return [
            static::SCENARIO_RESET => ['mobile', 'password', 'password_repeat', 'code'],
        ];
    }

    public function rules()
    {
        return [
            [['mobile', 'password', 'password_repeat', 'code'], 'required'],
            [['mobile', 'password', 'password_repeat', 'code'], 'string'],
            ['mobile', MobileValidator::class],
            ['password', 'string', 'max' => 16, 'min' => '8', 'message' => Code::USER_PASSWORD_LENGTH_FAILED],
            ['password', 'compare', 'compareAttribute' => 'password_repeat'],
            ['code', 'validateCode']
        ];
    }

    /**
     * 验证验证码
     * @param $attribute
     * @return bool
     * @author thanatos <thanatos915@163.com>
     */
    public function validateCode($attribute)
    {
        if (!$this->hasErrors()) {
            $smsModel = Yii::$app->sms;
            if (!$smsModel->validateCode($this->mobile, $this->code, Sms::TYPE_RESET_PASSWORD)) {
                $this->addErrors($smsModel->getErrors());
                return false;
            }
        }
    }


    /**
     * 修改用户密码
     * @param $params
     * @return bool|Member
     * @author thanatos <thanatos915@163.com>
     */
    public function submit($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }
        switch ($this->scenario) {
            case static::SCENARIO_RESET:
                return $this->resetPassword();

            default:
                return false;
        }

    }

    /**
     * 找回密码
     * @return bool|Member
     * @author thanatos <thanatos915@163.com>
     */
    protected function resetPassword()
    {
        $member = Member::findByMobile($this->mobile);
        if (empty($member)) {
            $this->addError('mobile', '手机号不存在');
            return false;
        }

        // 生成密码
        try {
            $member->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        } catch (\Throwable $exception) {
            $this->addError('password', Code::SERVER_FAILED);
            return false;
        }

        if (!$member->save()) {
            $this->addErrors($member->getErrors());
            return false;
        }

        return $member;
    }

}