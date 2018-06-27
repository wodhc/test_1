<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_pay}}".
 *
 * @property int $pay_id
 * @property string $pay_sn 支付单号
 * @property int $user_id 用户id
 * @property int $status 支付状态
 */
class OrderPay extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_pay}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pay_sn', 'user_id', 'status'], 'required'],
            [['user_id'], 'integer'],
            [['pay_sn'], 'string', 'max' => 32],
            [['status'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pay_id' => 'Pay ID',
            'pay_sn' => '支付单号',
            'user_id' => '用户id',
            'status' => '支付状态',
        ];
    }
}
