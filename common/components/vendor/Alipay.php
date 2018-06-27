<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\vendor;

use Yansongda\Pay\Pay;
use Yii;
use yii\base\Component;
use yii\helpers\Url;

/**
 * Class Alipay
 * @property array $log
 * @property \Yansongda\Pay\Gateways\Alipay $pay
 * @package common\components\vendor
 * @author thanatos <thanatos915@163.com>
 */
class Alipay extends Component
{

    public $app_id;
    public $notify_url;
    public $return_url;
    public $ali_public_key;
    public $private_key;
    public $mode;

    private $_log;
    /** @var \Yansongda\Pay\Gateways\Alipay */
    private $_alipay;

    public function init()
    {
        parent::init();
        if (empty($this->notify_url))
            $this->notify_url =  Url::to(Yii::$app->controller->module->id . '/pay/alipay-notify', true);

    }

    /**
     * 支付宝支付
     * @return \Yansongda\Pay\Gateways\Alipay
     * @author thanatos <thanatos915@163.com>
     */
    public function getPay()
    {
        if ($this->_alipay === null) {
            $this->_alipay = Pay::alipay([
                'app_id' => $this->app_id,
                'notify_url' => $this->notify_url,
                'return_url' => $this->return_url,
                'ali_public_key' => $this->ali_public_key,
                'private_key' => $this->private_key,
                'log' => $this->log,
                'charset' => 'urf-8',
                'mode' => 'dev'
            ]);
        }
        return $this->_alipay;
    }

    public function getLog()
    {
        if ($this->_log === null) {
            if ($this->mode == 'dev') {
                $this->_log = [
                    'file' => Yii::getAlias('@runtime') . '/logs/alipay.log',
                    'level' => 'debug'
                ];
            }
        }
        return $this->_log;
    }

}