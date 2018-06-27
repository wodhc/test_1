<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\traits;

use Yii;

trait OrderTrait
{

    /**
     * 生成唯一的订单号
     * @return string
     * @author thanatos <thanatos915@163.com>
     */
    public function generateOrderSn()
    {
        /** @var string $data 年月日时分 */
        $data = date('ymdHi');

        // 4位随机数
        $rand = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // 取5位用户的字符串
        $user = substr(str_pad(Yii::$app->user->id, 5, '0', STR_PAD_LEFT), -5);

        return $data.$rand.$user;
    }

}