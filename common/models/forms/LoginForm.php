<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\models\forms;

use common\components\traits\ModelErrorTrait;
use common\models\Member;
use common\models\MemberLoginHistory;
use common\models\OauthRefreshToken;
use Yii;
use common\components\validators\MobileValidator;
use common\extension\Code;
use common\models\MemberOauth;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * 统一登录类
 * @package common\models\forms
 * @author thanatos <thanatos915@163.com>
 */
class LoginForm extends Model
{
    use ModelErrorTrait;

    /** @var string 第三方授权登录 */
    const SCENARIO_OAUTH = 'oauth';
    /** @var string 手机号登录 */
    const SCENARIO_MOBILE = 'mobile';
    /** @var string 系统自动登录，不生成access_token */
    const SCENARIO_SYSTEM = 'system';

    public $oauth_name;
    public $oauth_key;
    public $mobile;
    public $password;

    public function rules()
    {
        return [
            [['oauth_name', 'oauth_key', 'mobile', 'password'], 'required'],
            [['oauth_name'], 'integer', 'max' => MemberOauth::MAX_OAUTH_NAME, 'min' => 1],
            [['oauth_key'], 'string', 'max' => 50],
            [['mobile'], 'string', 'max' => 11],
            ['mobile', MobileValidator::class],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            static::SCENARIO_OAUTH => ['oauth_name', 'oauth_key'],
            static::SCENARIO_SYSTEM => ['oauth_name', 'oauth_key'],
            static::SCENARIO_MOBILE => ['mobile', 'password'],
        ];
        return ArrayHelper::merge(parent::scenarios(), $scenarios);
    }

    /**
     * 统一登录入口
     * @param $params
     * @return array|bool
     * @author thanatos <thanatos915@163.com>
     */
    public function submit($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }

        switch ($this->scenario) {
            // 微信登录
            case static::SCENARIO_OAUTH:
            case static::SCENARIO_SYSTEM:
                return $this->loginByOauth();
                break;
            // 手机号登录
            case static::SCENARIO_MOBILE:
                return $this->loginByMobile();
            default:
                $this->addError('scenario', 'scenario not exist');
                return false;
        }
    }

    /**
     * 第三方登录
     * @return array|bool
     * @author thanatos <thanatos915@163.com>
     */
    protected function loginByOauth()
    {
        // 查找第三方名和key的用户
        if (!$memberOauth = MemberOauth::findMemberByNameAndKey($this->oauth_name, $this->oauth_key)) {
            $this->addError('oauth', Code::USER_NOT_FOUND);
            return false;
        }
        // 登录
        return $this->doLogin($memberOauth->member);
    }

    /**
     * 手机号登录
     */
    protected function loginByMobile()
    {
        // 验证用户
        if (!$user = Member::findByMobile($this->mobile)) {
            $this->addError('mobile', Code::USER_NOT_FOUND);
            return false;
        }

        // 验证密码
        if (!$user->validatePassword($this->password)) {
            $this->addError('mobile', Code::USER_WRONG_PASSWORD);
            return false;
        }
        // 登录
        return $this->doLogin($user);
    }

    /**
     * 执行登录动作
     * @param Member $member
     * @return array|bool
     * @author thanatos <thanatos915@163.com>
     */
    private function doLogin($member)
    {
        if ($member && $member instanceof Member) {
            // 登录
            if (!Yii::$app->user->login($member)) {
                $this->addError('oauth', Code::SERVER_FAILED);
                return false;
            }

            // 系统自动登录不生成access_token和登录日志
            if ($this->scenario != static::SCENARIO_SYSTEM) {
                // 添加登录日志
                MemberLoginHistory::createLoginHistory(MemberLoginHistory::LOGIN_METHOD_WECHAT);

                // 生成access_token
                $user = Yii::$app->user->identity;
                $access_token = $user->generateJwtToken();

                return ArrayHelper::merge($user->toArray(), ['accessToken' => $access_token]);
            }
        }
        return true;
    }

}