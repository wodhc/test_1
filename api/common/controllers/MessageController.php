<?php
/**
 * Created by PhpStorm.
 * User: swz
 * Date: 2018/5/11
 * Time: 11:34
 */

namespace api\common\controllers;

use common\models\TbzLetter;
use common\models\search\MessageSearch;
use yii\web\NotFoundHttpException;
use common\extension\Code;
use common\models\forms\MessageForm;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class MessageController extends  BaseController
{
    /**
     * @SWG\Get(
     *     path="/message",
     *     operationId="getMessage",
     *     schemes={"http"},
     *     tags={"消息接口"},
     *     summary="根据状态获取消息",
     *     description="此接口是获取消息信息的接口，用来前台用户查看消息或者后台管理者根据查询条件查看信息,成功返回消息信息,有分页",
     *     @SWG\Parameter(
     *         name="Client",
     *         in="header",
     *         required=true,
     *         type="string",
     *         description="公共参数",
     *     ),
     *      @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         type="string",
     *         description="公共参数,区分前后台，frontend为前台,backend为后台,默认为前台",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="status",
     *          type="integer",
     *          description="消息状态,后台查询条件，3为已删除，7为回收站，10为待发布，20为直接发布",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="type",
     *          type="integer",
     *          description="消息类型,后台查询条件,1为公共通知，2为活动通知，3为个人消息",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TbzLetter"),
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
        $message = new MessageSearch();
        $result = $message->search(\Yii::$app->request->get());
        if ($result) {
            return $result;
        }
       return '';
    }
    /**
     * @SWG\Get(
     *     path="/message/{id}",
     *     operationId="getMessageOne",
     *     schemes={"http"},
     *     tags={"消息接口"},
     *     summary="获取单个消息信息",
     *     description="此接口用于查看单个消息，前台成功返回线上消息信息，后台无限制",
     *     @SWG\Parameter(
     *         name="Client",
     *         in="header",
     *         required=true,
     *         type="string",
     *         description="公共参数",
     *     ),
     *      @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         type="string",
     *         description="公共参数,区分前后台，frontend为前台,backend为后台,默认为前台",
     *     ),
     *      @SWG\Parameter(
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="消息的唯一标识id",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/TbzLetter"),
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
        $result = TbzLetter::findById($id);
        if ($result){
            return $result;
        }
        return '';
    }
    /**
     * @SWG\Post(
     *     path="/message",
     *     operationId="addMessage",
     *     schemes={"http"},
     *     tags={"消息接口"},
     *     summary="添加新消息",
     *     description="此接口是添加新消息的接口,用来后台管理者发布消息，成功返回所添加的消息信息",
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
     *          description="消息标题",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="subtitle",
     *          type="string",
     *          description="副标题",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="description",
     *          type="string",
     *          description="消息描述",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="type",
     *          type="integer",
     *          description="消息类型,1为公共通知，2为活动通知，3为个人消息",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="user_id",
     *          type="string",
     *          description="接收消息的用户id(消息类型为个人消息时必填，即type值为3时)",
     *     ),
     *       @SWG\Parameter(
     *          in="formData",
     *          name="status",
     *          type="integer",
     *          description="是否发布（10为待发布，20为直接发布)",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="sort",
     *          type="integer",
     *          description="热度,值越大，热度越高",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/TbzLetter"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return bool
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $create_data = \Yii::$app->request->post();
        $message = new MessageForm();
        if ($message->load($create_data, '') && ($result = $message->addMessage())) {
            return $result;
        }
        throw new BadRequestHttpException($message->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Put(
     *     path="/message/{id}",
     *     operationId="updateMessage",
     *     schemes={"http"},
     *     tags={"消息接口"},
     *     summary="修改消息",
     *     description="此接口是修改消息的接口,用来后台管理者修改消息信息，成功返回所修改的消息信息",
     *     @SWG\Parameter(
     *         name="Client",
     *         in="header",
     *         required=true,
     *         type="string",
     *         description="公共参数",
     *     ),
     *      @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         type="string",
     *         description="公共参数,区分前后台，frontend为前台,backend为后台,默认为前台",
     *     ),
     *     @SWG\Parameter(
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="所要修改的消息唯一标识id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="title",
     *          type="string",
     *          description="消息标题",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="subtitle",
     *          type="string",
     *          description="副标题",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="description",
     *          type="string",
     *          description="消息描述",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="type",
     *          type="integer",
     *          description="消息类型,1为公共通知，2为活动通知，3为个人消息",
     *          required=true,
     *     ),
     *      @SWG\Parameter(
     *          in="formData",
     *          name="user_id",
     *          type="string",
     *          description="接收消息的用户id(消息类型为个人消息时必填，即type值为3时)",
     *     ),
     *       @SWG\Parameter(
     *          in="formData",
     *          name="status",
     *          type="integer",
     *          description="是否发布（10为待发布，20为直接发布)",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="sort",
     *          type="integer",
     *          description="热度,值越大热度越高",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/TbzLetter"
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
     * @return bool|TbzLetter|null
     * @throws BadRequestHttpException
     */
    public function actionUpdate($id)
    {
        $update_data = \Yii::$app->request->post();
        $message = new MessageForm();
        if ($message->load($update_data, '') && ($result = $message->updateMessage($id))) {
            return $result;
        }
        throw new BadRequestHttpException($message->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Delete(
     *     path="/message/{id}",
     *     operationId="deleteMessage",
     *     schemes={"http"},
     *     tags={"消息接口"},
     *     summary="删除消息",
     *     description="此接口是删除消息的接口,用来后台管理者删除消息信息，成功返回空字符串",
     *     @SWG\Parameter(
     *         name="Client",
     *         in="header",
     *         required=true,
     *         type="string",
     *         description="公共参数",
     *     ),
     *      @SWG\Parameter(
     *         name="Handle",
     *         in="header",
     *         type="string",
     *         description="公共参数,区分前后台，frontend为前台,backend为后台,默认为前台",
     *     ),
     *     @SWG\Parameter(
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="所要删除的消息唯一标识id",
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
     * @throws HttpException
     */
    public function actionDelete($id)
    {
        $message = new MessageForm();
        if ($result = $message->deleteMessage($id)) {
            return '';
        }
        throw new HttpException(500, $message->getStringErrors(), Code::SERVER_FAILED);
    }
}