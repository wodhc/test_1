<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/14
 * Time: 13:46
 */

namespace api\common\controllers;

use common\components\vendor\RestController;
use common\models\forms\TemplateForm;
use common\models\search\TemplateCenterSearch;
use common\models\search\TemplateOfficialSearch;
use common\models\TemplateOfficial;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use common\extension\Code;
use yii\web\BadRequestHttpException;

class TemplateOfficialController extends RestController
{
    /**
     * @SWG\Get(
     *     path="/template-official/classify-search",
     *     operationId="classifySearch",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="模板中心首页根据分类展示模板信息",
     *     description="此接口模板中心首页根据是否推荐查询出相应的模板信息，成功返回推荐到热门场景的模板信息，最多展示12个模板",
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
     *         name="team",
     *         in="header",
     *         type="integer",
     *         description="团队的唯一标识team_id,团队版时必传",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TemplateOfficial"),
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return mixed|null|string
     */
    public function actionClassifySearch()
    {
        $model = new TemplateCenterSearch();
        $result = $model->search();
        if (!$result) {
            return '';
        }
        return $result;
    }

    /**
     * @SWG\Get(
     *     path="/template-official",
     *     operationId="getTemplate",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="官方模板查询接口",
     *     description="此接口用于前台模板中心页展示页根据若干查询条件查询出相应的模板信息，成功返回上线的模板信息，后台可根据状态查询对应的模板信息",
     *      @SWG\Parameter(
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
     *         in="query",
     *         name="category",
     *         type="integer",
     *         description="查询条件,大分类的category_id",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         in="query",
     *         name="classify",
     *         type="integer",
     *         description="查询条件，模板小分类的classify_id",
     *     ),
     *      @SWG\Parameter(
     *         in="query",
     *         name="price",
     *         type="integer",
     *         description="查询条件，模板价格,1-4对应不同价格区间，1为大于0图币，2为100-500图币，3为500-1000图币，4为大于1000图币",
     *     ),
     *     @SWG\Parameter(
     *         in="query",
     *         name="style",
     *         type="integer",
     *         description="查询条件,风格的tag_id",
     *     ),
     *     @SWG\Parameter(
     *         in="query",
     *         name="industry",
     *         type="integer",
     *         description="查询条件,行业的tag_id",
     *     ),
     *     @SWG\Parameter(
     *         in="query",
     *         name="sort",
     *         type="integer",
     *         description="查询条件,按热度排序，值为1时按热度降序排序，其他条件按时间降序排序",
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
     * @return null|string|\yii\data\ActiveDataProvider
     */
    public function actionIndex()
    {
        $config = [
            'scenario' => Yii::$app->request->isFrontend() ? TemplateOfficialSearch::SCENARIO_FRONTEND : TemplateOfficialSearch::SCENARIO_BACKEND
        ];
        $model = new TemplateOfficialSearch($config);
        $result = $model->search(Yii::$app->request->get());
        if ($result) {
            return $result;
        }
        return '';
    }

    /**
     * 查询官方模板数据
     *
     * @SWG\Get(
     *     path="/template-official/{templateId}",
     *     operationId="getTemplateView",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="查询官方模板详情",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *        name="templateId",
     *        in="path",
     *        required=true,
     *        type="integer"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TemplateOfficial"),
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
     * @param $id
     * @return TemplateOfficial|null|string|\yii\db\ActiveRecord
     */
    public function actionView($id)
    {
        $model = TemplateOfficial::findById($id);
        if (empty($model)) {
            return '';
        }
        return $model;
    }

    /**
     * 新增模板
     * @SWG\POST(
     *     path="/template-official",
     *     operationId="createTemplateOfficial",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="新增官方模板",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/TemplateOfficial")
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TemplateOfficial"),
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
     * @return bool|\common\models\TemplateMember|\common\models\TemplateOfficial
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionCreate()
    {
        $model = new TemplateForm();
        $data = ArrayHelper::merge(Yii::$app->request->post(), ['method' => TemplateForm::METHOD_SAVE_OFFICIAL]);
        if (!($result = $model->submit($data))) {
            throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }

        return $result;
    }

    /**
     * 保存官方模板
     * @SWG\PUT(
     *     path="/template-official/{templateId}",
     *     operationId="updateTemplateOfficial",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="保存官方模板",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *        name="templateId",
     *        in="path",
     *        required=true,
     *        type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/TemplateOfficial")
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TemplateOfficial"),
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
     * @param $id
     * @return bool|\common\models\TemplateMember|\common\models\TemplateOfficial
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionUpdate($id)
    {
        $model = new TemplateForm();
        $data = ArrayHelper::merge(Yii::$app->request->post(), ['template_id' => $id, 'method' => TemplateForm::METHOD_SAVE_OFFICIAL]);
        if (!($result = $model->submit($data))) {
            throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }
        return $result;
    }

    /**
     * 删除官方模板
     *
     * @SWG\Delete(
     *     path="/template-official/{templateId}",
     *     operationId="deleteTemplateOfficial",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="删除官方模板",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *        name="templateId",
     *        in="path",
     *        required=true,
     *        type="integer"
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
     *
     * @param integer $id
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionDelete($id)
    {
        $model = new TemplateForm();
        $data = ['template_id' => $id, 'method' => TemplateForm::METHOD_SAVE_OFFICIAL, 'status' => TemplateOfficial::STATUS_DELETE];
        if (!($result = $model->submit($data))) {
            throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }
    }


}