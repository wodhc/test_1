<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\vendor;

use common\extension\Code;
use common\models\Member;
use common\models\OauthPublicKeys;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
class RestController extends Controller
{
    /** @var OauthPublicKeys */
    public $client;

    private $_handle;

    /**
     * @throws ForbiddenHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function init()
    {
        parent::init();
        // 设置OSS图片网址别名
        Yii::setAlias('@oss', Yii::$app->params['ossUrl']);

       /* if (!Yii::$app->request->isOptions && Yii::$app->request->client === false) {
            throw new ForbiddenHttpException('没有仅限', Code::SERVER_NOT_PERMISSION);
        }*/

    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => Yii::$app->params['Origin'],
                'Access-Control-Request-Method' => Yii::$app->params['Access-Control-Request-Method'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => "*",
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => Yii::$app->params['Access-Control-Expose-Headers'],
            ]
        ];
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['*']
        ];
        return $behaviors;
    }

    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ]
        ];
    }

}