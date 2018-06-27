<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\controllers;


use common\models\forms\FileUpload;
use common\models\Member;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class OssController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * @return bool|\common\models\FileCommon|null
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionCallback()
    {
        $data = Yii::$app->request->post();
        file_put_contents('1.txt', json_encode($data));
        exit;
        $data = json_decode(file_get_contents('1.txt'), true);
        $model = new FileUpload(['scenario' => FileUpload::SCENARIO_FRONTEND]);
        // 处理自动登录问题
        if ($data['user_id'] && $user = Member::findIdentity($data['user_id'])) {
            Yii::$app->user->login($user);
        }
        if ($data['Team']) {
            Yii::$app->request->headers->set('Team', $data['team']);
        }
        if ($result = $model->submit($data)) {
            return $result;
        }
        throw new BadRequestHttpException($model->getStringErrors());

    }
}