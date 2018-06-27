<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/28
 * Time: 14:33
 */
namespace api\common\controllers;

use common\models\MaterialMember;
use common\models\MaterialClassify;
use common\models\search\MaterialClassifySearch;
use yii\web\NotFoundHttpException;
use common\extension\Code;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class MaterialClassifyController extends BaseController
{
    /**
     * @SWG\Get(
     *     path="/material-classify",
     *     operationId="getMaterialClassify",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="查询官方素才分类",
     *     description="此接口用来前后台根据不同查询条件查询官方素材分类，成功后返回素材分类信息，前台只返回正常状态的分类无分页，后台可根据状态值查询但有分页",
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
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/MaterialClassify")
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
        $model = new MaterialClassifySearch();
        $result = $model->search(\Yii::$app->request->get());
        if ($result) {
            return $result;
        }
        return '';
    }
    /**
     * 新增素材分类
     * @SWG\POST(
     *     path="/material-classify",
     *     operationId="addMaterialClassify",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="新增官方素材分类",
     *     description="此接口用于新增官方素材的分类，成功返回新增的素材分类信息，后台调用",
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
     *          description="分类名",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="pid",
     *          type="integer",
     *          description="大分类的唯一标识",
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
     *                  @SWG\Items(
     *                     @SWG\Items(ref="#/definitions/MaterialClassify")
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
     * @return MaterialClassify
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = new MaterialClassify();
        $pid_validate =  MaterialClassify::findOne(['cid'=>\Yii::$app->request->post('pid'),'status'=>MaterialClassify::STATUS_NORMAL]);
        if (!$pid_validate){
            throw new BadRequestHttpException('pid不存在', Code::SERVER_UNAUTHORIZED);
        }
        if ($model->load(\Yii::$app->request->post(), '') && ($model->save())) {
            return $model;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * 编辑素材
     * @SWG\Put(
     *     path="/material-classify/{id}",
     *     operationId="updateMaterialClassify",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="编辑官方素材分类",
     *     description="此接口用于编辑官方素材分类信息，现只能修改分类名，成功返回编辑后的官方素材分类信息",
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
     *          description="素材分类唯一标识id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="name",
     *          type="string",
     *          description="素材分类名",
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
     *                  @SWG\Items(ref="#/definitions/MaterialClassify")
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
     * @return MaterialClassify|null
     * @throws BadRequestHttpException
     */
    public function actionUpdate($id)
    {
        $model = MaterialClassify::findOne(['cid'=>$id,'status'=>MaterialClassify::STATUS_NORMAL]);
        if (!$model){
            throw new BadRequestHttpException('要编辑的信息不存在', Code::SERVER_UNAUTHORIZED);
        }
        $data = \Yii::$app->request->post();
        if ($data['name']){
            $model->name = $data['name'];
        }
        if ($data['pid'] && (MaterialClassify::findOne(['cid'=>$data['pid'],'status'=>MaterialClassify::STATUS_NORMAL]))){
            $model->pid;
        }
        if ($model->save()) {
            return $model;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Delete(
     *     path="/material-classify/{id}",
     *     operationId="deleteMaterialClassify",
     *     schemes={"http"},
     *     tags={"素材接口"},
     *     summary="官方素材分类放入回收站",
     *     description="此接口用于把官方素材分类放入回收站，成功返回空字符串",
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
     *         description="所要删除分类的唯一标识id",
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
        $model = MaterialClassify::findOne(['cid'=>$id,'status'=>MaterialClassify::STATUS_NORMAL]);
        if (!$model){
            throw new BadRequestHttpException('要删除的信息不存在', Code::SERVER_UNAUTHORIZED);
        }
        $model->status = MaterialClassify::STATUS_TRASH ;
        if (($result = $model->save())) {
            return "";
        }
        throw new HttpException(500, $model->getStringErrors(), Code::SERVER_FAILED);
    }
}