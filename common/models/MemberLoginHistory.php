<?php

namespace common\models;

use common\components\traits\TimestampTrait;
use Yii;

/**
 * This is the model class for table "{{%member_login_history}}".
 *
 * @property int $history_id
 * @property int $user_id 用户id
 * @property int $method 登录方式
 * @property string $ip 登录ip
 * @property string $http_user_agent
 * @property string $http_referer 登录来源
 * @property string $login_url 登录页面url
 * @property int $created_at 创建时间
 */
class MemberLoginHistory extends \yii\db\ActiveRecord
{
    use TimestampTrait;

    /** @var int 微信扫码登录 */
    const LOGIN_METHOD_WECHAT = 1;
    /** @var int 手机号登录 */
    const LOGIN_METHOD_MOBILE = 2;
    const MAC_LOGIN_METHOD = self::LOGIN_METHOD_MOBILE;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%member_login_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'method', 'ip'], 'required'],
            [['http_user_agent', 'http_referer', 'login_url'], 'default', 'value' => ''],
            [['user_id', 'created_at'], 'integer'],
            [['method'], 'integer', 'max' => static::LOGIN_METHOD_MOBILE],
            [['ip'], 'string', 'max' => 64],
            [['http_user_agent', 'http_referer', 'login_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'history_id' => 'History ID',
            'user_id' => 'User ID',
            'method' => 'Method',
            'ip' => 'Ip',
            'http_user_agent' => 'Http User Agent',
            'http_referer' => 'Http Referer',
            'login_url' => 'Login Url',
            'created_at' => 'Created At',
        ];
    }

    /**
     * 登录登录日志
     * @param $method
     * @return bool
     */
    public static function createLoginHistory($method)
    {
        $request = Yii::$app->request;
        $model = new static();
        try {
            $loginUrl = $request->getAbsoluteUrl();
        } catch (\Throwable $e) {
            $loginUrl = '';
        }
        $model->load([
            'user_id' => Yii::$app->user->id,
            'method' => $method,
            'ip' => $request->getUserIP() ?: 'localhost',
            'http_user_agent' => $request->userAgent,
            'http_referer' => $request->getReferrer(),
            'login_url' => $loginUrl,
        ], '');
        if (!$model->validate() || !$model->save()) {
            Yii::error('login_history', $model->getErrors());
            return false;
        }
        return true;
    }

}
