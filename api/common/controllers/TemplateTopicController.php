<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/24
 * Time: 18:39
 */
namespace api\common\controllers;

use common\components\vendor\RestController;
use common\models\search\TemplateTopicSearch;
use common\models\TemplateTopic;
use yii\web\NotFoundHttpException;
use common\extension\Code;
use yii\web\BadRequestHttpException;

class TemplateTopicController extends RestController
{
    /**
     * @SWG\Get(
     *     path="/template-topic",
     *     operationId="getTemplateTopic",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="获取模板专题下的模板",
     *     description="此接口用于根据一些查询条件获取某一模板专题下的模板，成功返回该模板信息",
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
     *          name="product",
     *          type="string",
     *          description="前后台参数，模板专题的唯一标识product",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="query",
     *          name="classify_id",
     *          type="integer",
     *          description="前后台参数，小分类的classify_id",
     *     ),
     *     @SWG\Parameter(
     *          in="query",
     *          name="sort",
     *          type="integer",
     *          description="前后台参数，不传默认按创建时间排序，1为按热度排序（都是降序）",
     *     ),
     *     @SWG\Parameter(
     *          in="query",
     *          name="price",
     *          type="integer",
     *          description="前后台参数价格区间，1为收费，2为免费，3为会员免费",
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
     * @return array|mixed|null
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $model = new TemplateTopicSearch();
        if ($result = $model->search(\Yii::$app->request->get())) {
            return $result;
        }
        throw new NotFoundHttpException('未找到', Code::SOURCE_NOT_FOUND);
    }

    /**
     * @SWG\Post(
     *     path="/template-topic",
     *     operationId="addTemplateTopic",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="新增模板专题对应模板",
     *     description="此接口是用来添加对应某一模板专题的模板，成功返回空字符",
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
     *          name="topic_id",
     *          type="integer",
     *          description="模板专题的唯一标识id",
     *          required=true,
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
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $create_data = \Yii::$app->request->post();
        $model = new TemplateTopic();
        if ($model->load($create_data, '') && ($model->save())) {
            return '';
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Delete(
     *     path="/template-topic/{id}",
     *     operationId="deleteTemplateTopic",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="删除某一模板专题下的模板",
     *     description="此接口是用来删除某一模板专题下的模板，成功返回空字符串",
     *     @SWG\Parameter(
     *         name="Client",
     *         in="header",
     *         required=true,
     *         type="string"
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
     *          description="模板专题下模板的template_id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="topic_id",
     *          type="integer",
     *          description="模板专题的唯一标识id",
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
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        if (!$topic_id = \Yii::$app->request->post('topic_id') || !$id){
            throw new BadRequestHttpException('模板专题唯一标识和模板唯一标识都能为空', Code::SERVER_FAILED);
        }
        $model =  TemplateTopic::findOne(['topic_id'=>$topic_id,'template_id'=>$id]);
        if (!$model) {
            throw new NotFoundHttpException('未找到', Code::SOURCE_NOT_FOUND);
        }
        if ($model->delete()) {
            return '';
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_FAILED);
    }

    /**
     * @SWG\Get(
     *     path="/template-topic/topic-list",
     *     operationId="getTemplateTopicList",
     *     schemes={"http"},
     *     tags={"模板相关接口"},
     *     summary="获取模板专题下的小分类",
     *     description="此接口用于获取某一专题下的模板小分类列表，前台调用，成功返回小分类信息",
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
     *          name="product",
     *          type="integer",
     *          description="模板专题的唯一标识product",
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
     *                  @SWG\Items(ref="#/definitions/Classify")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return array|bool|string
     */
    public function actionTopicList()
    {
        $product = \Yii::$app->request->get('product');
        $model = new TemplateTopicSearch();
        if ($result = $model->getClassify($product)) {
            return $result;
        }
      return '';
    }
}