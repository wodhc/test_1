<?php

namespace common\models;

use common\components\traits\TimestampTrait;
use Yii;

/**
 * This is the model class for table "{{%center_user_oauth}}".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $oauth_name 第三方名称
 * @property string $oauth_key 第三方key值
 * @property int $created_at 创建时间
 * @property Member $member
 *
 */
class MemberOauth extends \yii\db\ActiveRecord
{
    use TimestampTrait;

    /** @var int 微信 */
    const OAUTH_WECHAT = 1;
    /** @var int QQ */
    const OAUTH_QQ = 2;
    /** @var int max oauth_name */
    const MAX_OAUTH_NAME = self::OAUTH_QQ;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%member_oauth}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'oauth_name', 'oauth_key'], 'required'],
            [['user_id', 'created_at'], 'integer'],
            [['oauth_name'], 'integer', 'max' => static::MAX_OAUTH_NAME, 'min' => 1],
            [['oauth_key'], 'string', 'max' => 50],
            ['oauth_key', 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'oauth_name' => 'Oauth Name',
            'oauth_key' => 'Oauth Key',
            'created_at' => 'Created At',
        ];
    }


    /**
     * 根据第三方名和唯一值查询
     * @param string $oauthName
     * @param string $oauthKey
     * @return null|static
     */
    public static function findByNameAndKey($oauthName, $oauthKey)
    {
        return static::findOne(['oauth_name' => $oauthName, 'oauth_key' => $oauthKey]);
    }

    /**
     * 根据第三方名和唯一值查询 返回member信息
     * @param string $oauthName
     * @param string $oauthKey
     * @return array|null|static
     */
    public static function findMemberByNameAndKey($oauthName, $oauthKey)
    {
        return static::find()->with('member')->where(['oauth_name' => $oauthName, 'oauth_key' => $oauthKey])->one();
    }

    /**
     * 一对一关联Member表
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::class, ['id' => 'user_id']);
    }

}
