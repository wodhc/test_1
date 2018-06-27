<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;

use common\extension\Code;
use Yii;
use common\models\forms\RechargeForm;
use yii\web\BadRequestHttpException;

class MemberRechargeController extends BaseController
{

    /**
     * 提交充值订单
     * @SWG\Post(
     *     path="/member-recharge",
     *     operationId="createRecharge",
     *     tags={"用户相关接口"},
     *     summary="充值接口",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         in="formData",
     *         required=true,
     *         name="money",
     *         description="充值钱数",
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/MemberCoinRecharge"
     *              )
     *          )
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
    public function actionCreate()
    {
        $model = new RechargeForm();
        if (!$result = $model->submit(Yii::$app->request->post())) {
            throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }
        return $result;
    }

}