<?php
/**
 * Created by PhpStorm.
 * User: swz
 * Date: 2018/5/11
 * Time: 11:34
 */

namespace api\common\controllers;

use common\models\forms\FolderTemplateForm;
use common\models\search\FolderTemplateSearch;
use common\models\TbzLetter;
use yii\web\NotFoundHttpException;
use common\extension\Code;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\helpers\ArrayHelper;

class FolderTemplateController extends BaseController
{
    /**
     * @SWG\Get(
     *     path="/folder-template",
     *     operationId="getFolderTemplate",
     *     schemes={"http"},
     *     tags={"文件夹接口"},
     *     summary="获取模板文件夹信息",
     *     description="此接口用于获取个人或团队模板文件夹信息，前台成功返回当前用户或团队下的正常状态模板文件夹信息，后台根据查询状态值返回所有个人或团队的模板文件夹信息",
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
     *         description="团队的唯一标识team_id,当为团队模板文件夹的操作时，此项必传，否则为操作当前用户的个人模板文件夹",
     *     ),
     *      @SWG\Parameter(
     *          in="query",
     *          name="status",
     *          type="integer",
     *          description="文件夹状态(后台可以根据状态查询,10为正常，7为回收站，3为删除)",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/FolderTemplateMember")
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
        $folder = new FolderTemplateSearch();
        $result = $folder->search(\Yii::$app->request->get());
        if ($result) {
            return $result;
        }
        return '';
    }

    /**
     * @SWG\Post(
     *     path="/folder-template",
     *     operationId="addFolderTemplate",
     *     schemes={"http"},
     *     tags={"文件夹接口"},
     *     summary="创建模板文件夹",
     *     description="此接口用于创建个人或团队的模板文件夹，成功返回新增的模板文件夹信息",
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
     *         description="团队的唯一标识team_id,当为团队模板文件夹的操作时，此项必传，否则为操作当前用户的个人模板文件夹",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="name",
     *          type="string",
     *          description="文件夹名称",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="color",
     *          type="string",
     *          description="文件夹颜色",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/FolderTemplateMember"
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
        $message = new FolderTemplateForm();
        if ($message->load(\Yii::$app->request->post(), '') && ($result = $message->editFolder())) {
            return $result;
        }
        throw new BadRequestHttpException($message->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Put(
     *     path="/folder-template/{folder_id}",
     *     operationId="updateFolderTemplate",
     *     schemes={"http"},
     *     tags={"文件夹接口"},
     *     summary="修改模板文件夹信息",
     *     description="此接口用于编辑个人或团队的模板文件夹，成功返回所编辑的模板文件夹信息",
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
     *         description="团队的唯一标识team_id,当为团队模板文件夹的操作时，此项必传，否则为操作当前用户的个人模板文件夹",
     *     ),
     *     @SWG\Parameter(
     *          in="path",
     *          name="folder_id",
     *          type="integer",
     *          description="文件夹唯一标识folder_id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="name",
     *          type="string",
     *          description="文件夹名称",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="color",
     *          type="string",
     *          description="文件夹颜色",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/FolderTemplateMember"
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
        $update_data = ArrayHelper::merge(\Yii::$app->request->post(), ['id' => $id]);
        $folder = new FolderTemplateForm();
        if ($folder->load($update_data, '') && ($result = $folder->editFolder())) {
            return $result;
        }
        throw new BadRequestHttpException($folder->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Delete(
     *     path="/folder-template/{folder_id}",
     *     operationId="deleteFolderTemplate",
     *     schemes={"http"},
     *     tags={"文件夹接口"},
     *     summary="模板文件夹到回收站",
     *     description="此接口用于删除个人或团队的模板文件夹，如果该文件夹下还有模板信息，默认会把所有该文件夹下的模板移动到默认文件夹，成功返回空字符串",
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
     *         description="团队的唯一标识team_id,当为团队模板文件夹的操作时，此项必传，否则为操作当前用户的个人模板文件夹",
     *     ),
     *     @SWG\Parameter(
     *          in="path",
     *          name="folder_id",
     *          type="integer",
     *          description="文件夹id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="true",
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
        $folder = new FolderTemplateForm();
        if ($folder->load(['id' => $id], '') && $folder->deleteFolder()) {
            return '';
        }
        throw new HttpException(500, $folder->getStringErrors(), Code::SERVER_FAILED);
    }
}