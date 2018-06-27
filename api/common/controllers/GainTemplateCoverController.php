<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;

use common\models\forms\TbzSubjectForm;
use common\models\TbzSubject;
use common\components\vendor\RestController;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use common\models\search\TbzSubjectSearch;
use common\extension\Code;
use yii\web\BadRequestHttpException;

class GainTemplateCoverController extends RestController
{
    /**
     * @SWG\Get(
     *     path="/gain-template-cover",
     *     operationId="GetCover",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="获取专题模板列表信息",
     *     description="此接口是查看专题模板信息的接口，前台返回上线模板信息，后台可根据状态值查询专题模板，成功返回专题模板信息，有分页",
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
     *      @SWG\Parameter(
     *          in="query",
     *          name="status",
     *          type="integer",
     *          description="后台查询条件，7为回收站，10为线下，20为线上",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TbzSubject")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return bool|TbzSubject[]|null|string
     */
    public function actionIndex()
    {
        $tbz_subject = new TbzSubjectSearch();
        $result_data = $tbz_subject->search(\Yii::$app->request->get());
        if ($result_data) {
            return $result_data;
        } else {
            return '';
        }
    }
    /**
     * @SWG\Get(
     *     path="/gain-template-cover/{id}",
     *     operationId="GetCoverOne",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="获取单个专题模板信息",
     *     description="此接口用于查看单个专题模板信息,前台成功返回线上模板信息，后台无限制",
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
     *      @SWG\Parameter(
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="专题模板唯一标识id",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TbzSubject")
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
    public function actionView($id){
        $result = TbzSubject::findById($id);
        if ($result){
            return $result;
        }
        return '';
    }
    /**
     * @SWG\Post(
     *     path="/gain-template-cover",
     *     operationId="AddCover",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="添加新的专题模板",
     *     description="此接口是后台管理者添加专题模板的接口，成功返回所添加的专题模板信息",
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
     *          name="title",
     *          type="string",
     *          description="模板标题",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="description",
     *          type="string",
     *          description="专题描述",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="banner",
     *          type="integer",
     *          description="专题内页banner图的文件id",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="thumbnail",
     *          type="integer",
     *          description="缩略图的文件id",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="seo_title",
     *          type="string",
     *          description="seo标题",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="seo_keyword",
     *          type="string",
     *          description="seo关键词",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="seo_description",
     *          type="string",
     *          description="seo描述",
     *          required=true,
     *     ),
     *       @SWG\Parameter(
     *          in="formData",
     *          name="status",
     *          type="integer",
     *          description="是否上线，10为线下，20为线上",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="sort",
     *          type="integer",
     *          description="热度，值越大热度越高",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/TbzSubject"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return bool|TbzSubject
     * @throws BadRequestHttpException
     * @author swz
     */
    public function actionCreate()
    {
        $add_data = \Yii::$app->request->post();
        $tbz_subject = new TbzSubjectForm();
        $tbz_subject->scenario = 'create';
        if ($result = $tbz_subject->submit($add_data)) {
            return $result;
        } else {
            throw new BadRequestHttpException($tbz_subject->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }
    }

    /**
     * @SWG\Put(
     *     path="/gain-template-cover/{id}",
     *     operationId="UpdateCover",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="修改专题模板信息",
     *     description="此接口是后台管理者修改专题模板的接口，成功返回所修改的专题模板信息",
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
     *          description="所要修改的模板唯一标识id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="title",
     *          type="string",
     *          description="模板标题",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="description",
     *          type="string",
     *          description="专题描述",
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="banner",
     *          type="integer",
     *          description="专题内页banner图的文件id",
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="thumbnail",
     *          type="integer",
     *          description="缩略图的文件id",
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="seo_title",
     *          type="string",
     *          description="seo标题",
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="seo_keyword",
     *          type="string",
     *          description="seo关键词",
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="seo_description",
     *          type="string",
     *          description="seo描述",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="sort",
     *          type="integer",
     *          description="热度，值越大热度越高",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/TbzSubject"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return bool|array|\common\components\vendor\Response|\yii\console\Response
     * @throws BadRequestHttpException
     * @author swz
     */
    public function actionUpdate($id)
    {
        $update_data = ArrayHelper::merge(\Yii::$app->request->post(), ['id' => $id]);
        $tbz_subject = new TbzSubjectForm();
        $tbz_subject->scenario = 'update';
        if ($result = $tbz_subject->submit($update_data)) {
            return $result;
        }
        throw new BadRequestHttpException($tbz_subject->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Delete(
     *     path="/gain-template-cover/{id}",
     *     operationId="deleteCover",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="删除专题模板信息",
     *     description="此接口是后台管理者删除专题模板的接口，成功返回空字符串",
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
     *          description="所要删除的模板唯一标识id",
     *          required=true,
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
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $tbz_subject = new TbzSubjectForm();
        $tbz_subject->scenario = 'delete';
        $data = ArrayHelper::merge(['status'=>$tbz_subject::DELETE_STATUS], ['id' => $id]);
        if ($tbz_subject->submit($data)) {
            return '';
        }
        throw new NotFoundHttpException('', Code::SOURCE_NOT_FOUND);
    }
}