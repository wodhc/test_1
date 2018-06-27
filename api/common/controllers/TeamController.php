<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/19
 * Time: 13:20
 */

namespace api\common\controllers;

use common\models\search\TeamSearch;
use common\models\Team;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use common\extension\Code;
use common\models\forms\TeamForm;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class TeamController extends BaseController
{
    /**
     * @SWG\Get(
     *     path="/team",
     *     operationId="getTeam",
     *     schemes={"http"},
     *     tags={"团队接口"},
     *     summary="获取团队信息",
     *     description="此接口用来前台获取团队信息，成功返回对应团队的信息，后台会根据状态值返回所有符合条件的团队信息",
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
     *         description="前台参数(必传)，团队的唯一标识team_id",
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="query",
     *         type="integer",
     *         required=true,
     *         description="后台参数,团队状态",
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/Team")
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
        $model = new TeamSearch();
        $result = $model->search(\Yii::$app->request->get());
        if ($result) {
            return $result;
        }
        throw new NotFoundHttpException('', Code::SOURCE_NOT_FOUND);
    }
    /**
     * @SWG\Post(
     *     path="/team",
     *     operationId="addTeam",
     *     schemes={"http"},
     *     tags={"团队接口"},
     *     summary="添加团队",
     *     description="此接口用来前创建新的团队，成功返回新增的团队信息",
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
     *          name="team_name",
     *          type="string",
     *          description="团队名称",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="team_mark",
     *          type="string",
     *          description="团队头像，默认为创建者头像",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="file_id",
     *          type="integer",
     *          description="团队头像的文件id",
     *     ),
     *      @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Team"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     * @return bool|\common\models\Team
     * @throws BadRequestHttpException
     * 创建团队
     */
    public function actionCreate()
    {
        $model = new TeamForm();
        $model->scenario = 'create';
        if ($result = $model->editTeam(\Yii::$app->request->post())) {
            return $result;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Put(
     *     path="/team/{id}",
     *     operationId="updateTeam",
     *     schemes={"http"},
     *     tags={"团队接口"},
     *     summary="编辑团队",
     *     description="此接口用来编辑团队信息，成功返回编辑后的团队信息",
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
     *         required=true,
     *         description="团队的唯一标识team_id",
     *     ),
     *     @SWG\Parameter(
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="团队唯一标识team_id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="team_name",
     *          type="string",
     *          description="团队名称,如果填写将修改团队名称",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="team_mark",
     *          type="string",
     *          description="团队头像，如果填写将修改团队头像",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="file_id",
     *          type="integer",
     *          description="团队头像的文件id，此项在team_mark有值时必传",
     *     ),
     *      @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Team"
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
     * @return mixed
     * @throws BadRequestHttpException
     * 编辑团队信息
     */
    public function actionUpdate($id)
    {
        $update_data = ArrayHelper::merge(['id'=>$id],\Yii::$app->request->post());
        $model = new TeamForm();
        $model->scenario = 'update';
        if ($result = $model->editTeam($update_data)) {
            return $result;
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }

    /**
     * @SWG\Delete(
     *     path="/team/{id}",
     *     operationId="deleteTeam",
     *     schemes={"http"},
     *     tags={"团队接口"},
     *     summary="删除团队",
     *     description="此接口用来删除团队，成功返回空字符串",
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
     *         required=true,
     *         description="团队的唯一标识team_id",
     *     ),
     *     @SWG\Parameter(
     *          in="path",
     *          name="id",
     *          type="integer",
     *          description="团队唯一标识team_id",
     *          required=true,
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
     * @param $id
     * @return bool
     * @throws HttpException
     * 删除团队
     */
    public function actionDelete($id)
    {
        $model = new TeamForm();
        $model->scenario = 'delete';
        if ($model->editTeam(['id'=>$id,'status'=>Team::RECYCLE_BIN_STATUS])) {
            return '';
        }
        throw new HttpException(500, $model->getStringErrors(), Code::SERVER_FAILED);
    }

    /**
     * @SWG\Post(
     *     path="/team/team-operation",
     *     operationId="operationTeam",
     *     schemes={"http"},
     *     tags={"团队接口"},
     *     summary="添加或剔除团队颜色或字体",
     *     description="此接口用来添加、剔除团队的颜色或字体，成功返回空字符串",
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
     *         required=true,
     *         description="团队的唯一标识team_id",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="operation_type",
     *          type="integer",
     *          description="添加或剔除操作类型，1为剔除，不传时默认为添加",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="color",
     *          type="string",
     *          description="所要添加或删除的颜色，传值时，颜色将被添加或删除",
     *     ),
     *     @SWG\Parameter(
     *          in="formData",
     *          name="font",
     *          type="string",
     *          description="所要添加或删除的字体，传值时，字体将被添加或删除",
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
     * @return bool|\common\models\Team|null
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionTeamOperation(){
        if (!$team = \Yii::$app->user->identity->team){
            throw new NotFoundHttpException('团队未找到', Code::SOURCE_NOT_FOUND);
        }
        $data = ArrayHelper::merge(\Yii::$app->request->post(),['id'=>$team->id]);
        $model = new TeamForm();
        $model->scenario = 'operation';
        if ($model->load($data, '') && ($result = $model->operation())) {
            return '';
        }
        throw new BadRequestHttpException($model->getStringErrors(), Code::SERVER_UNAUTHORIZED);
    }
}