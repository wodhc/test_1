<?php

namespace common\models;

use common\components\traits\ModelErrorTrait;
use common\components\traits\ModelFieldsTrait;
use common\components\traits\TimestampTrait;
use common\components\validators\MobileValidator;
use common\components\vendor\RestController;
use common\extension\Code;
use Firebase\JWT\JWT;
use Yii;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;
/**
 * 用户类
 * @SWG\Definition(type="object", @SWG\Xml(name="Member"))
 *
 * @property int $id @SWG\Property(property="id", type="integer", description="")
 * @property string $username 用户名 @SWG\Property(property="username", type="string", description=" 用户名")
 * @property string $mobile 用户手机号 @SWG\Property(property="mobile", type="string", description=" 用户手机号")
 * @property int $sex 姓别 @SWG\Property(property="sex", type="integer", description=" 姓别")
 * @property int $headimg_id 头像ID
 * @property string $headimg_url 头像url @SWG\Property(property="headimgUrl", type="string", description=" 头像url")
 * @property int $coin 图币 @SWG\Property(property="coin", type="integer", description=" 图币")
 * @property int $last_login_time 最后登录时间 @SWG\Property(property="lastLoginTime", type="integer", description=" 最后登录时间")
 * @property string $password_hash 密码hash
 * @property string $salt 旧salt
 * @property string $password 旧密码
 * @property int $status 用户状态
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property Team|false $team
 */
class Member extends \yii\db\ActiveRecord implements IdentityInterface
{
    use ModelErrorTrait;
    use TimestampTrait;
    use ModelFieldsTrait;

    /** @var int 男 */
    const SEX_MALE = 1;
    /** @var int 女 */
    const SEX_WOMAN = 2;
    /** @var int 未知 */
    const SEX_UNKNOWN = 0;
    /** @var int max sex */
    const SEX_MAX = self::SEX_WOMAN;

    /** @var int 用户正常状态 */
    const STATUS_NORMAL = 10;

    // token 过期时间 5小时
    const EXPIRED_TIME = 3600 * 5;
    // token 刷新时间 15天
    const REFRESH_TIME = 3600 * 24 * 15;

    /**
     * 用于接口返回
     * @SWG\Property(property="accessToken", type="string", description="")
     * @var string
     */
    public $access_token;

    /** @var Team|false|null */
    private $_team;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%member}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['headimg_id', 'coin', 'last_login_time'], 'integer'],
            [['username'], 'string', 'max' => 30],
            [['headimg_url'], 'string', 'max' => 255],
            ['headimg_url', 'default', 'value' => ''],
            [['mobile'], 'string', 'max' => 11],
            ['mobile', MobileValidator::class],
            [['sex', 'status'], 'integer', 'max' => 255],
            ['status', 'default', 'value' => 10],
            [['password_hash'], 'string', 'max' => 60],
            [['salt'], 'string', 'max' => 16],
            [['password'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'mobile' => 'Mobile',
            'sex' => 'Sex',
            'headimg_id' => 'Headimg ID',
            'headimg_url' => 'Headimg Url',
            'coin' => 'Coin',
            'last_login_time' => 'Last Login Time',
            'status' => 'Status',
            'password_hash' => 'Password Hash',
            'salt' => 'Salt',
            'password' => 'password',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function frontendFields()
    {
        return [
            'id', 'username', 'mobile', 'sex', 'coin'
        ];
    }

    public function expandFields()
    {
        return [
            'headimgUrl' => function () {
                return Url::to('@oss') . DIRECTORY_SEPARATOR . $this->headimg_url;
            },
            'accessToken' => function ($model) {
                return $model->access_token;
            }
        ];
    }

    /**
     * 通过mobile查找
     * @param $mobile
     * @return Member|null
     * @author thanatos <thanatos915@163.com>
     */
    public static function findByMobile($mobile)
    {
        return static::findOne(['mobile' => $mobile]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $request = Yii::$app->request;
        try {
            $data = JWT::decode($token, $request->client->public_key, [$request->client->encryption_algorithm]);
            if (isset($data->sub)) {
                return static::findIdentity($data->sub);
            }
        } catch (\Throwable $e) {
        }

        return null;
    }


    /**
     * 生成JWT TOKEN
     * 如果需要更新，需手动清除缓存
     * @return bool|string
     * @author thanatos <thanatos915@163.com>
     */
    public function generateJwtToken()
    {
        $time = time();
        $request = Yii::$app->request;
        // 定义payload属性
        $token = [
            'iss' => 'https://www.tubangzhu.com',
            'aud' => $request->client->client_id,
            'sub' => $this->id,
            'exp' => $time + static::EXPIRED_TIME,
            'iat' => $time,
            'ref' => $time + static::REFRESH_TIME,
        ];

        // 生成refresh_token
        try {
            $refresh_token = Yii::$app->security->generateRandomString(40);
        } catch (\Throwable $throwable) {
            $refresh_token = '';
        }
        $model = new OauthRefreshToken();
        $model->load([
            'refresh_token' => $refresh_token,
            'client_id' => Yii::$app->request->client->client_id,
            'user_id' => $this->id,
            'expires' => $time + static::REFRESH_TIME
        ], '');
        try {
            OauthRefreshToken::getDb()->createCommand()->delete(OauthRefreshToken::tableName(), [
                'client_id' => Yii::$app->request->client->client_id,
                'user_id' => $this->id,
            ])->execute();
        } catch (\Throwable $e) {

        }
        if (!$model->save()) {
            return false;
        }
        $token['data']['refreshToken'] = $refresh_token;
        return JWT::encode($token, $request->client->private_key, $request->client->encryption_algorithm);
    }

    /**
     * 验证用户密码
     * @param string $password 用户密码
     * @return bool
     * @author thanatos <thanatos915@163.com>
     */
    public function validatePassword($password)
    {
        // 验证老的密码体系
        if ($this->salt) {
            if ($this->password == md5(md5($password), $this->salt)) {
                // 通过后重置新的密码格式
                $this->setPassword($password);
                return $this->save() ?: false;
            }
        }
        // 验证新密码格式
        if ($this->password_hash) {
            return Yii::$app->security->validatePassword($password, $this->password_hash);
        }
        return false;
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
    }

    public function validateAuthKey($authKey)
    {
    }

    /**
     * @param $password
     * @author thanatos <thanatos915@163.com>
     */
    public function setPassword($password)
    {
        try {
            $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        } catch (\Throwable $exception) {
        }
    }

    /**
     * @return array|bool|Team|false|null|\yii\db\ActiveRecord
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function getTeam()
    {
        if ($this->_team === null) {
            $team_id = Yii::$app->request->headers->get('Team');
            if (!empty($team_id)) {
                if ($team_id >0 && $team = Team::findByIdFromMember($team_id))
                    $this->_team = $team;
                else
                    throw new BadRequestHttpException('系统错误', Code::SERVER_FAILED);
            } else {
              $this->_team = false;
            }
        }
        return $this->_team;
    }

}
