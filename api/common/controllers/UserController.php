<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;

use common\extension\Code;
use common\models\forms\LoginForm;
use common\models\forms\PasswordForm;
use common\models\Member;
use Yii;
use common\components\vendor\RestController;
use common\models\forms\RegisterForm;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class UserController extends RestController
{

    /**
     * 用户账号密码登录
     * @SWG\Post(
     *     path="/user/login",
     *     tags={"用户相关接口"},
     *     summary="账号密码登录",
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
     *         name="password",
     *         description="密码",
     *         in="formData",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Member"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return array|bool
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionLogin()
    {
        $model = new LoginForm(['scenario' => LoginForm::SCENARIO_MOBILE]);
        if (!($result = $model->submit(Yii::$app->request->post()))) {
            throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }
        return $result;
    }

    /**
     * 绑定手机号
     * @SWG\Post(
     *     path="/user/bind",
     *     tags={"用户相关接口"},
     *     summary="绑定手机号",
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
     *         name="code",
     *         description="手机验证码",
     *         in="formData",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         description="密码",
     *         in="formData",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Member"
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
     * @return Member
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionBind()
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
        $model = new RegisterForm(['scenario' => RegisterForm::SCENARIO_BIND]);
        if ($result = $model->bind(Yii::$app->request->post())) {
            return Yii::$app->user->identity;
        } else {
            throw new BadRequestHttpException($model->getStringErrors());
        }
    }


    /**
     * 找回密码
     *
     * @SWG\Post(
     *     path="/user/reset-password",
     *     tags={"用户相关接口"},
     *     summary="找回密码",
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
     *         name="code",
     *         description="手机验证码",
     *         in="formData",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         description="密码",
     *         in="formData",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="password-repeat",
     *         description="密码",
     *         in="formData",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Member"
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
     * @return bool|Member
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionResetPassword()
    {
        $model = new PasswordForm(['scenario' => PasswordForm::SCENARIO_RESET]);
        if (!($result = $model->submit(Yii::$app->request->post()))) {
            throw new BadRequestHttpException($model->getStringErrors());
        }
        return $result;
    }




}