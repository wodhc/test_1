<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;

use common\components\vendor\RestController;
use common\extension\Code;
use common\models\CacheDependency;
use common\models\Classify;
use common\models\search\CategorySearch;
use Yii;
use common\models\Category;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class CategoryController extends RestController
{

    /**
     * 查询品类列表
     * @SWG\Get(
     *     path="/category",
     *     operationId="getCategory",
     *     tags={"分类相关接口"},
     *     summary="查询品类列表",
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
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/Category")
     *              )
     *          )
     *     ),
     * )
     * @return array|null
     * @return Category[]|null
     * @author thanatos <thanatos915@163.com>
     */
    public function actionIndex()
    {
        $model = new CategorySearch();
        $result = $model->search(Yii::$app->request->get());
        return $result;
    }

    /**
     * 创建一个品类
     * @SWG\Post(
     *     path="/category",
     *     operationId="postCategory",
     *     tags={"分类相关接口"},
     *     summary="添加一个品类种类",
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Category")
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Category"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @throws BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionCreate()
    {
        $model = new Category();
        $model->load(Yii::$app->request->post(), '');
        if (!$model->save()) {
            throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }
        return $model;
    }


    /**
     * 修改一个品类
     * @SWG\Put(
     *     path="/category/{categoryId}",
     *     operationId="putCategory",
     *     tags={"分类相关接口"},
     *     summary="修改一个品类种类",
     *     @SWG\Parameter(
     *        name="categoryId",
     *        in="path",
     *        required=true,
     *        type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Category")
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Category"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @param integer $id
     * @return Category|null
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionUpdate($id)
    {
        $model = Category::findById($id);
        // 资源不存在
        if (empty($model)) {
            throw new NotFoundHttpException('', Code::SOURCE_NOT_FOUND);
        }
        // 保存
        if ($model->load(Yii::$app->request->post(), '') && !$model->save()) {
            throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
        }
        return $model;
    }


    /**
     * 删除一个品类
     * @SWG\Delete(
     *     path="/category/{categoryId}",
     *     operationId="deleteCategory",
     *     tags={"分类相关接口"},
     *     summary="修改一个品类种类",
     *     @SWG\Parameter(
     *        name="categoryId",
     *        in="path",
     *        required=true,
     *        type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="client",
     *         in="header",
     *         required=true,
     *         type="string"
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
     * @param integer $id
     * @throws HttpException
     * @throws NotFoundHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionDelete($id)
    {
        $model = Category::findById($id);
        if (empty($model)) {
            throw new NotFoundHttpException('', Code::SOURCE_NOT_FOUND);
        }
        try {
            $model->delete();
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
        }
        if ($message)
            throw new HttpException(500, '',Code::SERVER_FAILED);
    }

}