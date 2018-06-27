<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;

use api\common\models\wechat\EventMessageHandle;
use common\components\traits\FuncTrait;
use common\extension\Code;
use common\models\forms\LoginForm;
use common\models\Member;
use common\models\MemberOauth;
use common\models\OauthRefreshToken;
use Firebase\JWT\JWT;
use Yii;
use common\components\vendor\RestController;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class WechatController extends RestController
{
    const LOGIN_QRCODE_KEY = 'login_qrcode_cache';

    /**
     * 获取微信登录的二维码
     *
     * @SWG\Get(
     *     path="/wechat/qrcode",
     *     operationId="getQrcode",
     *     tags={"用户相关接口"},
     *     summary="获取微信登录的二维码",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="返回图片",
     *     )
     * )
     *
     * @SWG\Post(
     *     path="/wechat/qrcode",
     *     operationId="postQrcode",
     *     tags={"用户相关接口"},
     *     summary="获取微信登录的二维码",
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
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  @SWG\Property(
     *                      property="content",
     *                      type="string"
     *                  )
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return array|\common\components\vendor\Response|\yii\console\Response|Response
     * @author thanatos <thanatos915@163.com>
     */
    public function actionQrcode()
    {
        $app = Yii::$app->wechat->app;
        $result = $app->qrcode->temporary(EventMessageHandle::SCENE_LOGIN, 3600);
        $url = $app->qrcode->url($result->ticket);

        // 记录session缓存
        Yii::$app->session->set(self::LOGIN_QRCODE_KEY, $result->ticket);

        $content = FuncTrait::getSourceOrigin($url, false);
        // Ajax 返回base64
        if (Yii::$app->request->isPost) {
            return ['content' => FuncTrait::base64Image($content)];
        }
        // 直接输出图片
        $response = Yii::$app->response;
        // 移除格式化事件
        $response->headers->set('Content-type', $content['mime']);
        $response->format = Response::FORMAT_RAW;
        $response->data = $content['content'];
        return $response;
    }

    /**
     * 检查登录状态，完成微信登录
     *
     * @SWG\Post(
     *     path="/wechat/session",
     *     tags={"用户相关接口"},
     *     summary="检查微信登录状态",
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
     * @return array|bool
     * @throws UnauthorizedHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionSession()
    {
        if (Yii::$app->request->isPost) {
            $ticket = Yii::$app->session->get(self::LOGIN_QRCODE_KEY);
            $cacheKey = [
                $ticket
            ];
            $unionid = Yii::$app->cache->get($cacheKey);


            $model = new LoginForm(['scenario' => LoginForm::SCENARIO_OAUTH]);
            if ($result = $model->submit([
                'oauth_name' => MemberOauth::OAUTH_WECHAT,
                'oauth_key' => $unionid,
            ])) {
                Yii::$app->cache->delete($cacheKey);
                return $result;
            } else {
                throw new UnauthorizedHttpException('验证失败', Code::SERVER_UNAUTHORIZED);
            }
        }
    }

    /**
     * 刷新AccessToken
     * @SWG\Post(
     *     path="/wechat/refresh",
     *     tags={"用户相关接口"},
     *     summary="刷新AccessToken",
     *     operationId="refreshToken",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *        name="body",
     *        in="body",
     *        @SWG\Schema(
     *            @SWG\Property(
     *                property="refresh_token",
     *                type="string",
     *            ),
     *        ),
     *
     *     ),
     *     @SWG\Response(
     *        response=200,
     *        description="请求成功",
     *        ref="$/responses/Success",
     *        @SWG\Schema(
     *            @SWG\Property(
     *                property="data",
     *                ref="#/definitions/Member"
     *            )
     *        )
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
    public function actionRefresh()
    {
        $token = Yii::$app->request->post('refresh_token');

        if (empty($token)) {
            throw new BadRequestHttpException('token不正确', Code::USER_TOKEN_FAILED);
        }
        $model = OauthRefreshToken::findByToken($token);
        if (empty($model) || time() > ($model->expires)) {
            throw new BadRequestHttpException('token不正确', Code::USER_TOKEN_FAILED);
        }

        $member = Member::findIdentity($model->user_id);
        $access_token = $member->generateJwtToken();
        return ArrayHelper::merge($member->toArray(), ['accessToken' => $access_token]);
    }

}