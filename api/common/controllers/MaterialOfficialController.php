<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/28
 * Time: 9:01
 */

namespace api\common\controllers;

use common\models\forms\MaterialOfficialForm;
use common\models\MaterialMember;
use common\models\MaterialOfficial;
use common\models\search\MaterialOfficialSearch;
use common\extension\Code;
use yii\web\BadRequestHttpException;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

class MaterialOfficialController extends BaseController
{
    /**
     * @SWG\Get(
     *     path="/material-official",
     *     operationId="getMaterialOfficial",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="查询官方素材",
     *     description="此接口用来前后台根据不同查询条件查询官方素材，成功后返回素材信息",
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
     *          in="query",
     *          name="status",
     *          type="integer",
     *          description="后台参数，素材状态,10正常，7回收站，3删除，默认为10",
     *     ),
     *     @SWG\Parameter(
     *          in="query",
     *          name="sort",
     *          type="integer",
     *          description="前后台参数，按创建时间排序，默认降序，1为升序",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="cid",
     *          type="integer",
     *          description="前后台参数，按素材分类查询",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="tags",
     *          type="string",
     *          description="前后台参数，按标签类型查询，为模糊查询",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/MaterialOfficial")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return array|mixed|null|string
     */
    public function actionIndex()
    {
        $model = new MaterialOfficialSearch();
        $result = $model->search(\Yii::$app->request->get());
        if ($result) {
            return $result;
        }
        return '';
    }

    /**
     * @SWG\Get(
     *     path="/material-official/{id}",
     *     operationId="getMaterialOfficialOne",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="查询单个官方素材",
     *     description="此接口用来前后台查询单个官方素材，前台成功返回正常官方素材信息，后台无限制",
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
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="素材唯一标识id",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/MaterialOfficial")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @param $id
     * @return array|null|string|\yii\db\ActiveRecord
     */
    public function actionView($id)
    {
        $result = MaterialOfficial::findById($id);
        if ($result) {
            return $result;
        }
        return '';
    }

    /**
     * 新增素材
     * @SWG\POST(
     *     path="/material-official",
     *     operationId="addMaterialOfficial",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="新增官方素材",
     *     description="此接口用于新增官方素材，成功返回新增的素材信息",
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
     *          in="formData",
     *          name="name",
     *          type="string",
     *          description="素材名",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="cid",
     *          type="integer",
     *          description="素材分类id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="tags",
     *          type="string",
     *          description="素材搜索标签",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="file_id",
     *          type="integer",
     *          description="文件id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="extra_contents",
     *          type="string",
     *          description="素材额外字段",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(
     *                     @SWG\Items(ref="#/definitions/MaterialOfficial")
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
     * @return bool|\common\models\MaterialMember|\common\models\False
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = new MaterialOfficialForm();
        $model->scenario = 'create';
        if ($result = $model->editOfficialMaterial(\Yii::$app->request->post())) {
            return $result;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * 编辑素材
     * @SWG\Put(
     *     path="/material-official/{id}",
     *     operationId="updateMaterialOfficial",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="编辑官方素材",
     *     description="此接口用于编辑官方素材信息，成功返回编辑后的官方素材信息",
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
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="素材唯一标识id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="name",
     *          type="string",
     *          description="素材名",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="cid",
     *          type="integer",
     *          description="素材分类id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="tags",
     *          type="string",
     *          description="素材搜索标签",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="file_id",
     *          type="integer",
     *          description="文件id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="extra_contents",
     *          type="string",
     *          description="素材额外字段",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/MaterialOfficial")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @param $id
     * @return bool|MaterialMember|False|null
     * @throws BadRequestHttpException
     */
    public function actionUpdate($id)
    {
        $model = new MaterialOfficialForm();
        $model->scenario = 'update';
        $data = ArrayHelper::merge(['id' => $id], \Yii::$app->request->post());
        if ($result = $model->editOfficialMaterial($data)) {
            return $result;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Delete(
     *     path="/material-official/{id}",
     *     operationId="deleteMaterialOfficial",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="官方素材放入回收站",
     *     description="此接口用于把官方素材放入回收站，成功返回空字符串",
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
     *         name="id",
     *         in="path",
     *         type="integer",
     *         required=true,
     *         description="所要删除素材的唯一标识id",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="",
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
     */
    public function actionDelete($id)
    {
        $model = new MaterialOfficialForm();
        $model->scenario = 'delete';
        $data = ['id' => $id, 'status' => MaterialOfficialForm::RECYCLE_BIN_STATUS];
        if (($result = $model->editOfficialMaterial($data))) {
            return "";
        }
        throw new HttpException(500, $model->getStringErrors(), Code::SERVER_FAILED);
    }
}