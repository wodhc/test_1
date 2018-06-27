<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\models\forms;

use common\components\traits\ModelErrorTrait;
use common\components\traits\ModelFieldsTrait;
use common\models\Member;
use common\models\Order;
use Yii;
use common\components\traits\OrderTrait;
use common\components\vendor\Model;
use common\models\MemberCoinRecharge;

class RechargeForm extends Model
{
    use OrderTrait;
    use ModelErrorTrait;

    /** @var integer */
    public $money;
    /** @var integer */
    public $coin;
    public $user_id;
    public $remark;

    public function rules()
    {
        return [
            ['remark', 'trim'],
            [['money', 'coin', 'user_id'], 'required'],
            [['money', 'coin', 'user_id'], 'integer'],
            ['remark', 'string'],
            ['user_id', 'exist', 'targetClass' => Member::class, 'targetAttribute' => 'id', 'message' => '用户不存在']
        ];
    }

    public function scenarios()
    {
        return [
            static::SCENARIO_FRONTEND => ['money'],
            static::SCENARIO_BACKEND => ['coin', 'user_id', 'remark'],
        ];
    }


    /**
     * 提交订单
     * @param $params
     * @return bool|MemberCoinRecharge
     * @author thanatos <thanatos915@163.com>
     */
    public function submit($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }

        // 初始化变量
        $user_id = 0;
        $admin_id = 0;
        $admin_name = '';
        $status = Order::STATUS_NOT_PAY;
        $scenario = MemberCoinRecharge::SCENARIO_MEMBER;
        // 整理数据
        switch ($this->scenario) {
            // 用户提交订单
            case static::SCENARIO_FRONTEND:
                $user_id = Yii::$app->user->id;
                // 是否是首次充值 赠送20%
                if (MemberCoinRecharge::active()->where(['user_id' => $user_id])->one()) {
                    $this->coin = $this->money * 100 * 1.2;
                } else {
                    $this->coin = $this->money * 100;
                }
                break;
            // 后台管理员修改
            case static::SCENARIO_BACKEND:
                $this->money = 0;
                $user_id = $this->user_id;
                $admin_id = Yii::$app->user->id;
                $admin_name = Yii::$app->user->identity->username;
                $status = Order::STATUS_READY_PAY;
                $scenario = MemberCoinRecharge::SCENARIO_ADMIN;
                break;
        }

        $data = [
            'user_id' => $user_id,
            'order_sn' => $this->generateOrderSn(),
            'amount_coin' => $this->coin,
            'amount_money' => $this->money,
            'status' => $status,
            'admin_id' => $admin_id,
            'admin_name' => $admin_name,
            'remark' => $this->remark
        ];
        $model = new MemberCoinRecharge(['scenario' => $scenario]);
        // 保存订单
        if ($result = $model->doSave($data)) {
            return $result;
        } else {
            $this->addErrors($model->errors);
            return false;
        }

    }


}