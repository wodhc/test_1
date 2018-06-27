<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;

use common\extension\Code;
use common\models\Classify;
use Yii;
use common\components\vendor\RestController;
use common\models\search\ClassifySearch;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class ClassifyController extends RestController
{

    /**
     * 查询官方模板分类
     * @return \yii\data\ActiveDataProvider
     * @author thanatos <thanatos915@163.com>
     */
    public function actionIndex()
    {
        $model = new ClassifySearch();
        $result = $model->search(Yii::$app->request->get());
        return $result;
    }


    /**
     * 修改分类
     * @param $id
     * @return Classify|null
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionUpdate($id)
    {
        $model = Classify::findById($id);
        if (empty($model)) {
            throw new NotFoundHttpException('', Code::SOURCE_NOT_FOUND);
        }
        $model->load(Yii::$app->request->post());

        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            return $model;
        } else {
            throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }
    }

    /**
     * 新增分类
     * @return Classify
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionCreate()
    {
        $model = new Classify();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model;
        } else {
            throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }
    }

    /**
     * 删除分类
     * @param $id
     * @throws NotFoundHttpException
     * @throws HttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionDelete($id)
    {
        $model = Classify::findById($id);
        if (empty($model)) {
            throw new NotFoundHttpException('', Code::SOURCE_NOT_FOUND);
        }
        try {
            $model->delete();
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
        }
        if ($message)
            throw new HttpException(500, Code::SERVER_FAILED);
    }

    /**
     * @SWG\Get(
     *     path="/classify/classify-tag",
     *     operationId="getClassifyTag",
     *     schemes={"http"},
     *     tags={"分类相关接口"},
     *     summary="获取小分类或标签信息",
     *     description="此接口是前台模板中心页根据大分类获取小分类和标签信息，或者根据小分类获取标签信息，成功返回小分类信息或标签信息（风格和行业）",
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
     *          name="category",
     *          type="integer",
     *          description="大分类的category_id,",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="classify",
     *          type="integer",
     *          description="小分类的classify_id,如果此参数为空，只返回小分类的信息，如果不为空，且有对应标签的话，同时返回标签信息",
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
     *                       @SWG\Property(
     *                           property="classify",
     *                           type="array",
     *                           @SWG\Items(
     *                           )
     *                       ),
     *                       @SWG\Property(
     *                           property="style",
     *                           type="array",
     *                           @SWG\Items(
     *                           )
     *                       ),
     *                       @SWG\Property(
     *                           property="industry",
     *                           type="array",
     *                           @SWG\Items(
     *                           )
     *                       )
     *                   )
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
    public function actionClassifyTag(){
        $model = new ClassifySearch();
        $model->load(Yii::$app->request->get(),'');
        $result = $model->classifyTag();
        if ($result){
            return $result;
        }
        return '';
    }

}