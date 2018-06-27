<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */
namespace common\extension;
use common\models\Member;
use yii\web\IdentityInterface;

/**
 * 扩展官方User类，方便IDE补全
 * @property
 * @property null|Member $identity The identity object associated with the currently logged-in
 * user. `null` is returned if the user is not logged in (not authenticated).
 * @package common\extension
 * @author thanatos <thanatos915@163.com>
 */
class User extends \yii\web\User
{

}