<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/11
 * Time: 17:15
 */

namespace api\common\controllers;

use common\components\vendor\RestController;
use common\models\search\TagSearch;
use common\models\Tag;
use yii\web\NotFoundHttpException;
use common\extension\Code;
use yii\web\BadRequestHttpException;

class TagController extends RestController
{
    /**
     * @SWG\Get(
     *     path="/tag",
     *     operationId="getTag",
     *     schemes={"http"},
     *     tags={"分类相关接口"},
     *     summary="获取标签信息",
     *     description="此接口是查看所有tag表(即行业和风格)信息的接口，后台调用，成功返回标签信息",
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
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/Tag")
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
    public function actionIndex()
    {
        $tag_search = new TagSearch();
        if ($result = $tag_search->search()) {
            return $result;
        }
        return '';
    }

    /**
     * @SWG\Get(
     *     path="/tag/{id}",
     *     operationId="getTagOne",
     *     schemes={"http"},
     *     tags={"分类相关接口"},
     *     summary="获取单个标签信息",
     *     description="此接口用于查看单个标签信息，后台调用",
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
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="标签唯一标识tag_id",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/Tag")
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
     * @return Tag|null|string
     */
    public function actionView($id){
        $result = Tag::findOne(['tag_id'=>$id]);
        if ($result) {
            return $result;
        }
        return '';
    }
    /**
     * @SWG\Post(
     *     path="/tag",
     *     operationId="addTag",
     *     schemes={"http"},
     *     tags={"分类相关接口"},
     *     summary="添加标签",
     *     description="此接口是用来添加新的风格和行业，后台调用,成功后返回新添加的tag信息",
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
     *          description="标签名,例如：商务、互联网",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="type",
     *          type="integer",
     *          description="标签类型，1为风格(style)，2为行业(industry)",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="sort",
     *          type="integer",
     *          description="热度，值越大代表热度越高",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Tag"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return tag
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $create_data = \Yii::$app->request->post();
        $tag = new Tag();
        if ($tag->load($create_data, '') && ($tag->save())) {
            return $tag;
        }
        throw new BadRequestHttpException($tag->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Put(
     *     path="/tag/{id}",
     *     operationId="updateTag",
     *     schemes={"http"},
     *     tags={"分类相关接口"},
     *     summary="修改标签信息",
     *     description="此接口是用来修改标签信息的接口，后台调用,成功后返回修改后的tag信息",
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
     *          description="所要修改的标签的唯一标识id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="name",
     *          type="string",
     *          description="标签名,例如：商务、互联网",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="type",
     *          type="integer",
     *          description="tag类型(1为风格，2为行业)",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="sort",
     *          type="integer",
     *          description="热度，值越大代表热度越高",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Tag"
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
     * @return Tag|null
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $update_data = \Yii::$app->request->post();
        $tag = Tag::findOne($id);
        if (!$tag) {
            throw new NotFoundHttpException('', Code::SOURCE_NOT_FOUND);
        }
        if ($tag->load($update_data, '') && $tag->save()) {
            return $tag;
        }
        throw new BadRequestHttpException($tag->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Delete(
     *     path="/tag/{id}",
     *     operationId="deleteTag",
     *     schemes={"http"},
     *     tags={"分类相关接口"},
     *     summary="删除标签",
     *     description="此接口是用来删除标签信息的接口，后台调用,成功返回空字符串",
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
     *          description="tag的id",
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
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $tag = Tag::findOne($id);
        if (!$id) {
            throw new NotFoundHttpException('', Code::SOURCE_NOT_FOUND);
        }
        $tag->type = 0;
        if ($tag->save()) {
            return '';
        }
        throw new BadRequestHttpException($tag->getStringErrors(), Code::SERVER_FAILED);
    }
}