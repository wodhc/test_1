<?php

namespace common\models;

use common\components\traits\ModelErrorTrait;
use Yii;
use common\components\traits\TimestampTrait;
use yii\db\Exception;

/**
 * This is the model class for table "{{%member_coin_log}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="MemberCoinLog"))
 *
 * @property int $log_id @SWG\Property(property="logId", type="integer", description="")
 * @property int $user_id 用户id @SWG\Property(property="userId", type="integer", description=" 用户id")
 * @property int $log_type 变动类型 @SWG\Property(property="logType", type="integer", description=" 变动类型")
 * @property int $amount_coin 变动图币数 @SWG\Property(property="amountCoin", type="integer", description=" 变动图币数")
 * @property string $remark 备注信息 @SWG\Property(property="remark", type="string", description=" 备注信息")
 * @property int $created_at 变动时间 @SWG\Property(property="createdAt", type="integer", description=" 变动时间")
 */
class MemberCoinLog extends \yii\db\ActiveRecord
{
    use ModelErrorTrait;
    use TimestampTrait;

    /** @var string 管理员赠送 */
    const TYPE_ADMIN_GIVE = '1';

    /** @var string 自己充值 */
    const TYPE_SELF_RECHARGE = '2';

    /** @var string 管理员减少图币 */
    const TYPE_ADMIN_TAKE = '3';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%member_coin_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['remark', 'default', 'value' => ''],
            [['user_id', 'log_type'], 'required'],
            [['user_id', 'amount_coin', 'created_at'], 'integer'],
            [['log_type'], 'string', 'max' => 1],
            [['remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'log_id' => 'Log ID',
            'user_id' => '用户id',
            'log_type' => '变动类型',
            'amount_coin' => '变动图币数',
            'remark' => '备注信息',
            'created_at' => '变动时间',
        ];
    }

    /**
     * 保存用户图币
     * @throws Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function saveMemberCoin()
    {
        $member = Member::findIdentity($this->user_id);
        if (empty($member))
            throw new Exception('Save Member Coin Error:' . 'Member Not Exist');
        switch ($this->log_type) {
            case static::TYPE_ADMIN_GIVE:
            case static::TYPE_SELF_RECHARGE:
                $member->coin += $this->amount_coin;
                break;
            case static::TYPE_ADMIN_TAKE:
                $member->coin -= $this->amount_coin;
                $member->coin = ($member->coin < 0) ? 0 : $member->coin;
        }
        if (!$member->save()) {
            throw new Exception('Save Member Coin Error:' . $member->getStringErrors());
        }
    }

}
