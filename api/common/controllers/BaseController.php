<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;

use common\extension\Code;
use common\models\Member;
use Yii;
use common\components\vendor\RestController;
use yii\web\ForbiddenHttpException;

class BaseController extends RestController
{

    /**
     * @param $action
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        \Yii::$app->user->login(Member::findIdentity(1));
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('没有权限', Code::SERVER_NOT_PERMISSION);
        }

        return true;
    }

}