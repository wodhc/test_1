<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/21
 * Time: 17:07
 */

namespace api\common\controllers;

use common\models\forms\MaterialForm;
use common\models\MaterialMember;
use common\models\MaterialTeam;
use common\models\search\MaterialSearch;
use yii\web\NotFoundHttpException;
use common\extension\Code;
use common\models\forms\MaterialOperationForm;
use yii\web\BadRequestHttpException;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

class MaterialController extends BaseController
{
    /**
     * @SWG\Get(
     *     path="/material",
     *     operationId="getMaterial",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="查询素材(团队、个人)",
     *     description="此接口用来前后台根据不同查询条件查询素材（图队和个人），可用于素材展示页和素材回收站页，成功返回素材信息，有分页",
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
     *         description="团队的唯一标识team_id,当为团队素材的操作时，此项必传，否则为查询当前用户的个人素材",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="status",
     *          type="integer",
     *          description="前后台参数，素材状态,10正常，7回收站，3删除，不传默认按10查询",
     *     ),
     *     @SWG\Parameter(
     *          in="query",
     *          name="sort",
     *          type="integer",
     *          description="前后台参数，按创建时间排序，默认降序，1为升序",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="folder",
     *          type="integer",
     *          description="前台参数，素材所在文件夹的folder_id,默认显示默认文件夹的素材",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="mode",
     *          type="integer",
     *          description="前后台参数，素材类型，10为临时，20为正式,不传默认为20",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/MaterialMember")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return array|mixed|null
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $model = new MaterialSearch();
        $result = $model->search(\Yii::$app->request->get());
        if ($result) {
            return $result;
        }
       return '';
    }

    /**
     * @SWG\Get(
     *     path="/material/{id}",
     *     operationId="getMaterialOne",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="查询单个素材(团队、个人)",
     *     description="此接口用来前后台查询单个素材，前台成功返回当前用户或团队下正常素材信息，后台无限制",
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
     *         description="团队的唯一标识team_id,当为团队素材的操作时，此项必传，否则为查询当前用户的个人素材",
     *     ),
     *      @SWG\Parameter(
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="素材唯一标识id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/MaterialMember")
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
        if ($team = \Yii::$app->user->identity->team) {
            //团队
            $result = MaterialTeam::findById($id);
        } else {
            //个人
            $result = MaterialMember::findById($id);
        }
        if ($result) {
            return $result;
        }
        return '';
    }

    /**
     * 新增素材
     * @SWG\POST(
     *     path="/material",
     *     operationId="addMaterial",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="新增素材(团队、个人)",
     *     description="此接口用于新增个人或团队素材，成功返回新增素材信息",
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
     *         description="团队的唯一标识team_id,当为团队素材的操作时，此项必传，否则为操作当前用户的个人素材",
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="file_name",
     *          type="string",
     *          description="文件名",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="thumbnail",
     *          type="string",
     *          description="图片路径",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="file_id",
     *          type="string",
     *          description="文件id",
     *          required=true,
     *     ),
     *       @SWG\Parameter(
     *          in="formData",
     *          name="mode",
     *          type="integer",
     *          description="素材模式,10为临时,20为正式",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="folder_id",
     *          type="integer",
     *          description="文件夹folder_id,默认为0",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/MaterialMember"),
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
        $model = new MaterialForm();
        if ($result = $model->submit(\Yii::$app->request->post())) {
            return $result;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * 编辑素材
     * @SWG\Put(
     *     path="/material/{id}",
     *     operationId="updateMaterial",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="编辑素材(团队、个人)",
     *     description="此接口用于编辑素材信息，成功返回编辑后的素材信息",
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
     *         description="团队的唯一标识team_id,当为团队素材的操作时，此项必传，否则为操作当前用户的个人素材",
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
     *          name="file_name",
     *          type="string",
     *          description="文件名",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="thumbnail",
     *          type="string",
     *          description="图片路径",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="file_id",
     *          type="string",
     *          description="文件id",
     *          required=true,
     *     ),
     *       @SWG\Parameter(
     *          in="formData",
     *          name="mode",
     *          type="integer",
     *          description="素材模式",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="folder_id",
     *          type="integer",
     *          description="文件夹folder_id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/MaterialMember"),
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
     * @return bool|\common\models\MaterialMember|\common\models\False|null
     * @throws BadRequestHttpException
     */
    public function actionUpdate($id)
    {
        $data = ArrayHelper::merge(\Yii::$app->request->post(), ['id' => $id]);
        $model = new MaterialForm();
        if ($result = $model->submit($data)) {
            return $result;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Delete(
     *     path="/material/{id}",
     *     operationId="deleteMaterial",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="素材放入回收站(团队、个人)",
     *     description="此接口用于把个人或团队素材放入回收站，成功返回空字符串",
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
     *         description="团队的唯一标识team_id,当为团队素材的操作时，此项必传，否则为操作当前用户的个人素材",
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
     *          ref="$/responses/Success",
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
        $model = new MaterialForm();
        if ($result = $model->submit(['id'=>$id,'status'=>MaterialForm::RECYCLE_BIN_STATUS])) {
            return '';
        }
        throw new HttpException(500, $model->getStringErrors(), Code::SERVER_FAILED);
    }

    /**
     * @SWG\POST(
     *     path="/material/material-operation",
     *     operationId="materialOperation",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="素材的常规操作(单个重命名，删除，到回收站、还原、移动到文件夹)(团队、个人)",
     *     description="此接口用于个人或团队素材的单个重命名，到回收站、删除、还原、移动都指定文件夹等场景，只限前台使用，且只能用来操作当前用户下的素材，成功返回空字符串",
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
     *         description="团队的唯一标识team_id,当为团队素材的操作时，此项必传，否则为操作当前用户的个人素材",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="type",
     *          type="integer",
     *          description="操作类型,1重命名(单个),2移动到文件夹，3到回收站，4删除，5还原",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="ids",
     *          type="integer",
     *          description="素材的唯一标识，单操作时为integer，多操作时为数组",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="name",
     *          type="string",
     *          description="文件名称,重命名时（即type为1）时必传",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="folder",
     *          type="integer",
     *          description="文件夹的id,移动到指定文件夹时（即type为2）必传",
     *     ),
     *      @SWG\Response(
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
     * @return bool|null
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionMaterialOperation()
    {
        $model = new MaterialOperationForm();
        if ($result = $model->operation(\Yii::$app->request->post())) {
            return $result;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }
}