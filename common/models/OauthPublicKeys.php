<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%oauth_public_keys}}".
 *
 * @property string $client_id client_id
 * @property string $public_key 公钥
 * @property string $private_key 私钥
 * @property string $encryption_algorithm 加密方式
 */
class OauthPublicKeys extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_public_keys}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'public_key', 'private_key', 'encryption_algorithm'], 'required'],
            [['client_id'], 'string', 'max' => 255],
            [['public_key', 'private_key'], 'string', 'max' => 2000],
            [['encryption_algorithm'], 'string', 'max' => 100],
            [['client_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'client_id' => 'client_id',
            'public_key' => '公钥',
            'private_key' => '私钥',
            'encryption_algorithm' => '加密方式',
        ];
    }


    /**
     * 获取client信息
     * @param string $client_id 客户端ID
     * @return null
     * @author thanatos <thanatos915@163.com>
     */
    public static function getClientById($client_id)
    {
        $cacheKey = [
            OauthPublicKeys::class,
            'JWT_clients_cache',
            $client_id
        ];
        // 通过缓存取得密钥
        $cache = Yii::$app->cache;

        if (!$jwt = $cache->get($cacheKey)) {
            /** @var OauthPublicKeys $jwt */
            $jwt = OauthPublicKeys::find()->where(['client_id' => $client_id])->one();
            if ($jwt) {
                $cache->set($cacheKey, $jwt);
            }
        }
        return $jwt ?: null;
    }

}
