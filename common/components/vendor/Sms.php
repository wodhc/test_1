<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\vendor;

use common\components\traits\ModelErrorTrait;
use Flc\Alidayu\App;
use Flc\Alidayu\Client;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use Yii;
use common\components\validators\MobileValidator;
use common\extension\Code;
use common\models\Member;
use yii\base\Component;
use yii\base\Model;

class Sms extends Model
{
    use ModelErrorTrait;

    public $app_key;
    public $app_secret;

    public $mobile;
    public $type;
    public $code;

    private $_code;
    private $_client;
    private $_req;

    const COUNT_CACHE_KEY = 'SMS_COUNT_';
    const VALUE_CACHE_KEY = 'SMS_VALUE_';

    const TYPE_BIND_MOBILE = 'bind-mobile';
    const TYPE_RESET_PASSWORD = 'reset-password';

    // 一小时最多发送多少次
    const MAX_TIMES = '5';

    const SCENARIO_SEND = 'send';
    const SCENARIO_VALIDATE = 'validate';

    public function rules()
    {
        return [
            [['mobile', 'type', 'code'], 'required'],
            ['type', 'string'],
            ['type', 'in', 'range' => [static::TYPE_BIND_MOBILE, static::TYPE_RESET_PASSWORD]],
            ['mobile', MobileValidator::class],
            ['mobile', 'unique', 'targetClass' => Member::class, 'targetAttribute' => 'mobile', 'message' => Code::USER_MOBILE_EXIST, 'on' => self::SCENARIO_DEFAULT],
            ['mobile', 'validateTimes', 'on' => self::SCENARIO_DEFAULT],
            ['code', 'string', 'on' => self::SCENARIO_VALIDATE],
        ];
    }

    /**
     * 验证发送次数
     * @param $attribute
     */
    public function validateTimes($attribute)
    {
        if (!$this->hasErrors()) {
            $count = Yii::$app->cache->get($this->getCountCacheKey());
            if ($count >= self::MAX_TIMES) {
                return $this->addError($attribute, '一小时只允许发送' . self::MAX_TIMES . '次');
            }
        }
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_SEND => ['mobile', 'type'],
            self::SCENARIO_VALIDATE => ['mobile', 'code', 'type'],
        ];
    }


    /**
     * 发送验证码
     * @param string $mobile
     * @param string $type
     * @return bool
     * @author thanatos <thanatos915@163.com>
     */
    public function send($mobile, $type)
    {
        $model = new static(['scenario' => static::SCENARIO_SEND]);
        $model->load(['mobile' => $mobile, 'type' => $type], '');

        if (!$model->validate()) {
            $this->addErrors($model->getErrors());
            return false;
        }
        $cache = Yii::$app->cache;

        // 只有现在环境才发送验证码
        if (YII_ENV_PROD) {
            $model->getReq()
                ->setRecNum($model->mobile)
                ->setSmsParam(['code' => $model->getCode()])
                ->setSmsFreeSignName('图帮主')
                ->setSmsTemplateCode($model->getTemplateCode());
            $result = json_decode(json_encode($model->getClient()->execute($model->getReq())), true);
        } else {
            // 增加开发环境日志
            $result['alibaba_aliqin_fc_sms_num_send_response'] = true;
        }
        if (isset($result['alibaba_aliqin_fc_sms_num_send_response'])) {
            // 记录发送值
            $cache->set($model->getValueCacheKey(), $model->getCode(), 1800);

            // 记录发送次数
            $count = $cache->get($model->getCountCacheKey());
            if ($count > 0) {
                // 发送次数+1
                $cache->set($model->getCountCacheKey(), $count + 1);
            } else {
                // 设置次数限制为一个小时
                $cache->set($model->getCountCacheKey(), 1, 3600);
            }

            return true;
        } else {
            $this->addError('mobile', $result['error_response']['sub_msg']);
            return false;
        }
    }


    /**
     * 验证验证码是否正确
     * @param string $mobile
     * @param string $code
     * @param string $type
     * @return bool
     * @author thanatos <thanatos915@163.com>
     */
    public function validateCode($mobile, $code, $type)
    {
        $model = new static(['scenario' => static::SCENARIO_VALIDATE]);
        $model->load(['mobile' => $mobile, 'code' => $code, 'type' => $type], '');
        $cache = Yii::$app->cache;
        if( $model->validate() && $cache->get($model->getValueCacheKey()) == $model->code ){
            // 清除缓存
            $cache->delete($model->getValueCacheKey());
            return true;
        }
        $this->addError('code', '验证码错误');
    }

    /**
     * 获取验证码
     * @return bool|string
     * @author thanatos <thanatos915@163.com>
     */
    private function getCode()
    {
        if ($this->_code === null) {
            $this->_code = $this->generateCode(6);
        }
        return $this->_code;
    }

    /**
     * 生成验证码
     * 开发模式下返回11111
     * @param int $length
     * @return string
     * @author thanatos <thanatos915@163.com>
     */
    private function generateCode($length = 6)
    {
        if (!YII_ENV_PROD) {
            return 111111;
        }
        $code = '';
        for ($i = 1; $i <= $length; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    /**
     * 根据不同场景返回模版ID
     * @return string
     */
    private function getTemplateCode()
    {
        $templateCode = '';
        switch ($this->type) {
            case static::TYPE_BIND_MOBILE:
                $templateCode = 'SMS_53585071';
                break;
        }

        return $templateCode;
    }

    private function getValueCacheKey()
    {
        return static::VALUE_CACHE_KEY . static::TYPE_BIND_MOBILE . $this->mobile;
    }

    private function getCountCacheKey()
    {
        return static::COUNT_CACHE_KEY . static::TYPE_BIND_MOBILE . $this->mobile;
    }

    private function getClient()
    {
        if ($this->_client === null) {
            try {
                $this->_client = new Client(new App(['app_key' => Yii::$app->sms->app_key, 'app_secret' => Yii::$app->sms->app_secret]));
            } catch (\Throwable $exception) {
                $this->_client = false;
            }
        }
        return $this->_client;
    }

    private function getReq()
    {
        if ($this->_req === null) {
           $this->_req = new AlibabaAliqinFcSmsNumSend();
        }
        return $this->_req;
    }

}