<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;


use common\extension\Code;
use common\models\forms\FileUpload;
use Yii;
use common\components\vendor\RestController;
use yii\helpers\ArrayHelper;

/**
 * Class SystemController
 * @package api\common\controllers
 * @author thanatos <thanatos915@163.com>
 */
class SystemController extends RestController
{

    /**
     * 获取Oss上传的JSSDK签名
     * @SWG\Get(
     *     path="/system/oss-policy",
     *     operationId="getOssPolicy",
     *     tags={"公共接口"},
     *     summary="获取Oss上传的JSSDK签名",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *     ),
     * )
     * @author thanatos <thanatos915@163.com>
     */
    public function actionOssPolicy()
    {
        return Yii::$app->oss->getSignature(UPLOAD_BASE_DIR . DIRECTORY_SEPARATOR . FileUpload::TEMP_DIR);
    }

    /**
     * @SWG\Get(
     *     path="/system/get-errors",
     *     operationId="getOssPolicy",
     *     tags={"公共接口"},
     *     summary="获取平台错误JSON",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *     ),
     * )
     * @return array
     * @author thanatos <thanatos915@163.com>
     */
    public function actionGetErrors()
    {
        $model = new Code();
        return $model->getErrors();
    }
}