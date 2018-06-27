<?php

namespace common\models;

use common\components\traits\ModelErrorTrait;
use common\components\traits\ModelFieldsTrait;
use common\components\traits\OrderTrait;
use common\models\forms\OrderForm;
use Yii;
use common\components\traits\TimestampTrait;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%member_coin_recharge}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="MemberCoinRecharge"))
 *
 * @property int $id @SWG\Property(property="id", type="integer", description="")
 * @property string $order_sn 唯一标示 @SWG\Property(property="orderSn", type="string", description=" 唯一标示")
 * @property int $user_id 用户id @SWG\Property(property="userId", type="integer", description=" 用户id")
 * @property int $amount_coin 充值图币数 @SWG\Property(property="amountCoin", type="integer", description=" 充值图币数")
 * @property string $amount_money 充值金额 @SWG\Property(property="amountMoney", type="string", description=" 充值金额")
 * @property string $payment_name 支付方式 @SWG\Property(property="paymentName", type="string", description=" 支付方式")
 * @property int $created_at 创建时间 @SWG\Property(property="createdAt", type="integer", description=" 创建时间")
 * @property int $status 状态: 10 未支付 @SWG\Property(property="status", type="integer", description=" 状态: 10 未支付")
 * @property int $payment_time 支付时间 @SWG\Property(property="paymentTime", type="integer", description=" 支付时间")
 */
class MemberCoinRecharge extends \yii\db\ActiveRecord
{
    use TimestampTrait;
    use ModelFieldsTrait;
    use ModelErrorTrait;
    use OrderTrait;

    public $admin_id;
    public $admin_name;
    public $remark;

    /** @var string 用户自己充值 */
    const SCENARIO_MEMBER = 'member';
    /** @var string 后台赠送 */
    const SCENARIO_ADMIN = 'admin';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%member_coin_recharge}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount_money', 'status'], 'filter', 'filter' => 'intval'],
            [['order_sn', 'user_id'], 'required'],
            [['order_sn', 'user_id', 'amount_coin', 'created_at', 'payment_time', 'admin_id'], 'integer'],
            [['amount_money', 'status'], 'number'],
            [['payment_name'], 'string', 'max' => 20],
            [['admin_name', 'remark'], 'string'],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return ArrayHelper::merge($scenarios, [
            static::SCENARIO_MEMBER => ['amount_coin', 'user_id', 'amount_money', 'status'],
            static::SCENARIO_ADMIN => ['amount_coin', 'user_id', 'amount_money', 'admin_id', 'admin_name', 'remark', 'status'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_sn' => '唯一标示',
            'user_id' => '用户id',
            'amount_coin' => '充值图币数',
            'amount_money' => '充值金额',
            'payment_name' => '支付方式',
            'created_at' => '创建时间',
            'status' => '状态: 10 未支付',
            'payment_time' => '支付时间',
        ];
    }

    /**
     * 支付成功回调
     * @return $this
     * @throws Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function doSuccess()
    {
        // 保存订单
        $this->status = Order::STATUS_READY_PAY;
        if (!$this->save())
            throw new Exception('Update Recharge Error:' . $this->getStringErrors());

        // 生成充值记录
        $this->saveCoinLog();

        return $this;
    }

    /**
     * 保存充值订单信息
     * @param $params
     * @return $this|bool
     * @author thanatos <thanatos915@163.com>
     */
    public function doSave($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }
        $transaction = static::getDb()->beginTransaction();
        try {
            // 生成订单号
            $this->order_sn = $this->generateOrderSn();
            // 保存充值表信息
            if (!$this->save()) {
                throw new Exception('Save Recharge Error:' . $this->getStringErrors());
            }

            if ($this->scenario == static::SCENARIO_ADMIN)
                $this->saveCoinLog();

            // 保存主订单表信息
            $scenario = ['scenario' => $this->scenario == static::SCENARIO_ADMIN ? Order::SCENARIO_ADMIN : Order::SCENARIO_DEFAULT];
            $orderModel = new Order($scenario);
            $result = $orderModel->submit([
                'user_id' => $this->user_id,
                'order_sn' => $this->order_sn,
                'order_purpose' => Order::PURPOSE_RECHARGE,
                'purpose_id' => $this->id,
                'order_amount' => $this->amount_money,
                'order_status' => $this->status,
                'admin_id' => $this->admin_id,
                'admin_name' => $this->admin_name,
                'remark' => $this->remark,
            ]);
            if (!$result) {
                throw new Exception('Save Order Error:' . $orderModel->getStringErrors());
            }

            $transaction->commit();
            return $this;
        } catch (\Throwable $exception) {
            // 处理错误
            try {
                // 回滚
                $transaction->rollBack();
            } catch (\Throwable $e) {}
            $message = $exception->getMessage();
            Yii::error($message);
            // 添加错误信息
            if (strpos($message, '=') === false)
                $this->addError('', $message);
            else
                $this->addErrors(Json::decode(explode(':', $message)[1]));
        }
    }


    /**
     * 基本Query
     * @return \yii\db\ActiveQuery
     * @author thanatos <thanatos915@163.com>
     */
    public static function active()
    {
        if (Yii::$app->request->isFrontend())
            return static::find()->where(['status' => Order::STATUS_READY_PAY]);
        else
            return static::find();
    }

    /**
     * 通过订单号查找
     * @param $order_sn
     * @return MemberCoinRecharge|null
     * @author thanatos <thanatos915@163.com>
     */
    public static function findByOrderSn($order_sn)
    {
        return static::findOne(['order_sn' => $order_sn]);
    }

    /**
     * 保存图币变更日志
     * @return bool|MemberCoinLog
     * @throws Exception
     * @author thanatos <thanatos915@163.com>
     */
    private function saveCoinLog()
    {
        $model = new MemberCoinLog();
        $model->attributes = $this->getAttributes($this->safeAttributes());
        // 后台充值
        switch ($this->scenario) {
            case static::SCENARIO_ADMIN:
                $model->log_type = MemberCoinLog::TYPE_ADMIN_GIVE;
                break;
            default:
                $model->log_type = MemberCoinLog::TYPE_SELF_RECHARGE;
                break;
        }
        if (!$model->save()) {
            throw new Exception('Save Coin Log Error:' . $model->getStringErrors());
        }

        // 保存用户图币
        $model->saveMemberCoin();

        return $model;
    }
}
