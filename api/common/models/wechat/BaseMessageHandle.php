<?php
/**
 * Created by PhpStorm.
 * User: thanatos
 * Date: 2018/4/25
 * Time: 上午3:11
 */

namespace api\common\models\wechat;

use common\models\forms\LoginForm;
use common\models\MemberOauth;
use Yii;
use thanatos\wechat\MessageHandler;

/**
 * Class BaseMessageHandle
 * @property string $unionid 用户唯一标识
 * @package api\common\models\wechat
 * @author thanatos <thanatos915@163.com>
 */
class BaseMessageHandle extends MessageHandler
{

    public function beforeHandle()
    {
        // 自动登录
        if (Yii::$app->user->isGuest) {
            $model = new LoginForm(['scenario' => LoginForm::SCENARIO_SYSTEM]);
            $model->submit([
                'oauth_name' => MemberOauth::OAUTH_WECHAT,
                'oauth_key' => $this->unionid,
            ]);
        }
        return parent::beforeHandle();
    }

    public function handleDefault()
    {
        $string = 123 . Yii::$app->user->id;
        return $string;
    }

    public function getUnionid()
    {
        return $this->wechatInfo->unionid;
    }

}