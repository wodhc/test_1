<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/19
 * Time: 9:08
 */

namespace api\common\controllers;

use common\models\forms\MyFavoriteForm;
use common\models\search\MyFavoriteSearch;
use yii\web\NotFoundHttpException;
use common\extension\Code;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class MyFavoriteController extends BaseController
{
    /**
     * @SWG\Get(
     *     path="/my-favorite",
     *     operationId="getMyFavorite",
     *     schemes={"http"},
     *     tags={"收藏接口"},
     *     summary="获取收藏模板信息",
     *     description="此接口用来前台根据查询条件查询个人或团队收藏的模板信息，成功返回模板信息，只能前台调用",
     *     @SWG\Parameter(
     *         name="Client",
     *         in="header",
     *         required=true,
     *         type="string",
     *         description="公共参数",
     *     ),
     *     @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         type="string",
     *         description="公共参数,区分前后台，frontend为前台,backend为后台,默认为前台",
     *     ),
     *     @SWG\Parameter(
     *         name="Team",
     *         in="header",
     *         type="integer",
     *         description="团队的唯一标识team_id,当为团队收藏的操作时，此项必传，否则为查询当前用户的个人收藏",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="sort",
     *          type="integer",
     *          description="按时间排序，默认降序，1为升序",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="classify_id",
     *          type="integer",
     *          description="小分类的classify_id，可根据小分类进行筛选查询",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TemplateOfficial")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return bool|mixed|null|string
     */
    public function actionIndex()
    {
        $model = new MyFavoriteSearch();
        $result = $model->search(\Yii::$app->request->get());
        if ($result) {
            return $result;
        }
        return '';
    }
    /**
     * @SWG\Post(
     *     path="/my-favorite",
     *     operationId="addMyFavorite",
     *     schemes={"http"},
     *     tags={"收藏接口"},
     *     summary="添加收藏",
     *     description="此接口用来新增个人或团队新的收藏信息，同时会在官方模板表为收藏量加1，成功返回空字符串",
     *     @SWG\Parameter(
     *         name="Client",
     *         in="header",
     *         required=true,
     *         type="string",
     *         description="公共参数",
     *     ),
     *     @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         type="string",
     *         description="公共参数,区分前后台，frontend为前台,backend为后台,默认为前台",
     *     ),
     *     @SWG\Parameter(
     *         name="Team",
     *         in="header",
     *         type="integer",
     *         description="团队的唯一标识team_id,当为团队收藏的操作时，此项必传，否则为查询当前用户的个人收藏",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="template_id",
     *          type="integer",
     *          description="官方模板的template_id",
     *          required=true,
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
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * 添加收藏
     */
    public function actionCreate()
    {
        $model = new MyFavoriteForm();
        $model ->setScenario('create');
        if ($model->load(\Yii::$app->request->post(), '') && ($result = $model->addMyFavorite())) {
            return '';
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    public function actionUpdate()
    {

    }

    /**
     * @SWG\Delete(
     *     path="/my-favorite/{id}",
     *     operationId="deleteMyFavorite",
     *     schemes={"http"},
     *     tags={"收藏接口"},
     *     summary="删除收藏",
     *     description="此接口用来删除个人或团队收藏，成功返回空字符串",
     *     @SWG\Parameter(
     *         name="Client",
     *         in="header",
     *         required=true,
     *         type="string",
     *         description="公共参数",
     *     ),
     *     @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         type="string",
     *         description="公共参数,区分前后台，frontend为前台,backend为后台,默认为前台",
     *     ),
     *     @SWG\Parameter(
     *         name="Team",
     *         in="header",
     *         type="integer",
     *         description="团队的唯一标识team_id,当为团队收藏的操作时，此项必传，否则为查询当前用户的个人收藏",
     *     ),
     *     @SWG\Parameter(
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="个人或团队收藏的唯一标识id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @param $id
     * @return bool
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = new MyFavoriteForm();
        $model ->setScenario('delete');
        if ($model->load(['id'=>$id], '') && $model->deleteMyFavorite()) {
            return '';
        }
        throw new HttpException(500, $model->getStringErrors(), Code::SERVER_FAILED);
    }
}