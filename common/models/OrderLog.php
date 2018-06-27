<?php

namespace common\models;

use Yii;
use common\components\traits\TimestampTrait;

/**
 * This is the model class for table "{{%order_log}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="OrderLog"))
 *
 * @property int $log_id @SWG\Property(property="logId", type="integer", description="")
 * @property int $order_id 订单ID @SWG\Property(property="orderId", type="integer", description=" 订单ID")
 * @property string $order_sn 订单编号 @SWG\Property(property="orderSn", type="string", description=" 订单编号")
 * @property int $admin_id 管理员ID @SWG\Property(property="adminId", type="integer", description=" 管理员ID")
 * @property string $admin_name 管理员名 @SWG\Property(property="adminName", type="string", description=" 管理员名")
 * @property string $remark 备注 @SWG\Property(property="remark", type="string", description=" 备注")
 * @property int $order_status 操作后的状态 @SWG\Property(property="orderStatus", type="integer", description=" 操作后的状态")
 * @property int $created_at 操作时间 @SWG\Property(property="createdAt", type="integer", description=" 操作时间")
 */
class OrderLog extends \yii\db\ActiveRecord
{

    use TimestampTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['order_status', 'filter', 'filter' => 'intval'],
            [['order_id', 'order_sn', 'admin_id', 'order_status', 'created_at'], 'required'],
            [['order_id', 'order_sn', 'admin_id', 'created_at'], 'integer'],
            [['admin_name'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 255],
            [['order_status'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'log_id' => 'Log ID',
            'order_id' => '订单ID',
            'order_sn' => '订单编号',
            'admin_id' => '管理员ID',
            'admin_name' => '管理员名',
            'remark' => '备注',
            'order_status' => '操作后的状态',
            'created_at' => '操作时间',
        ];
    }
}
