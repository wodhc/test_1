<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/29
 * Time: 14:02
 */
namespace api\common\controllers;
use common\extension\Code;
use common\models\forms\ShareTemplateForm;
use common\models\search\ShareTemplateSearch;
use yii\web\BadRequestHttpException;

class ShareTemplateController extends \api\common\controllers\BaseController
{
    /**
     * @SWG\Get(
     *     path="/share-template",
     *     operationId="getShare",
     *     schemes={"http"},
     *     tags={"分享接口"},
     *     summary="根据条件查询用户分享的模板信息",
     *     description="此接口用于根据不同条件查询用户被分享过来的模板信息，成功返回模板信息",
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
     *          name="sort",
     *          type="integer",
     *          description="前后台的查询条件，按创建时间排序，默认降序，1为升序",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="classify_id",
     *          type="string",
     *          description="前后台查询条件，小分类的classify_id",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TemplateMember")
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
    public function actionIndex(){
        $model = new ShareTemplateSearch();
        if ($result = $model->search(\Yii::$app->request->get())){
            return $result;
        }
        return $model->getStringErrors();
    }
    /**
     * @SWG\Post(
     *     path="/share-template",
     *     operationId="addShare",
     *     schemes={"http"},
     *     tags={"分享接口"},
     *     summary="添加分享",
     *     description="此接口用来添加分享个人模板，成功返回分享的模板信息",
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
     *          name="template_id",
     *          type="integer",
     *          description="模板唯一标识template_id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="shared_person",
     *          type="integer",
     *          description="被分享人的唯一标识id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="authority",
     *          type="integer",
     *          description="分享类型，10为修改同步，20为修改不同步",
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
     * @return ShareTemplateForm
     * @throws BadRequestHttpException
     */
    public function actionCreate(){
        $model = new ShareTemplateForm();
        if ($model->addShare(\Yii::$app->request->post())){
            return $model;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }
    public function actionDelete(){

    }
}