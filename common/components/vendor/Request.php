<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\vendor;

use common\models\OauthPublicKeys;
use common\models\Team;


/**
 * Class Request
 * @property OauthPublicKeys|false|null $client
 * @property string $handle
 * @package common\components\vendor
 * @author thanatos <thanatos915@163.com>
 */
class Request extends \yii\web\Request
{
    use RequestTrait;
}