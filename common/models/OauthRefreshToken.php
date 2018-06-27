<?php

namespace common\models;

use Firebase\JWT\JWT;
use Yii;

/**
 * This is the model class for table "{{%oauth_refresh_token}}".
 *
 * @property string $refresh_token
 * @property string $client_id
 * @property int $user_id
 * @property int $expires
 */
class OauthRefreshToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_refresh_token}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['refresh_token', 'client_id', 'user_id', 'expires'], 'required'],
            [['user_id', 'expires'], 'integer'],
            [['refresh_token'], 'string', 'max' => 40],
            [['client_id'], 'string', 'max' => 255],
            [['refresh_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'refresh_token' => 'Refresh Token',
            'client_id' => 'Client ID',
            'user_id' => 'User ID',
            'expires' => 'Expires',
        ];
    }

    /**
     * @param $token
     * @return OauthRefreshToken|null
     * @author thanatos <thanatos915@163.com>
     */
    public static function findByToken($token)
    {
        return static::findOne(['refresh_token' => $token]);
    }

}
