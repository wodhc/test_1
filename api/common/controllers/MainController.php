<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;


use common\models\forms\FileUpload;
use common\models\forms\MaterialForm;
use Yii;
use common\components\vendor\RestController;
use yii\web\BadRequestHttpException;

class MainController extends RestController
{

    /**
     * 发送验证码
     *
     * @SWG\Post(
     *     path="/main/send-sms",
     *     tags={"公共接口"},
     *     summary="发送验证码",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="mobile",
     *         description="手机号",
     *         in="formData",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         in="formData",
     *         required=true,
     *         type="string",
     *         enum={"bind-mobile", "reset-password"}
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     *
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionSendSms()
    {
        $mobile = Yii::$app->request->post('mobile');
        $type = Yii::$app->request->post('type');

        $smsModel = Yii::$app->sms;
        $result = $smsModel->send($mobile, $type);
        if (!$result) {
            throw new BadRequestHttpException($smsModel->getStringErrors());
        }

    }

    /**
     * TODO Test
     * @author thanatos <thanatos915@163.com>
     */
    public function actionIndex()
    {
        $path = 'updata/fonts/201805/a0.ttf';
        $file = Yii::$app->oss->getObjectMeta($path);
        var_dump($file);exit;
    }

}