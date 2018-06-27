
<?php
/**
 * Created by PhpStorm.
 * User: thanatos
 * Date: 2018/4/23
 * Time: 下午3:48
 */


class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication the application instance
     */
    public static $app;
}

/**
 * Class BaseApplication
 * Used for properties that are identical for both WebApplication and ConsoleApplication
 * @property \common\components\vendor\RestController $controller
 * @property \thanatos\wechat\Wechat $wechat 微信类
 * @property \common\components\vendor\DataCache $dataCache 缓存组件
 * @property \common\components\vendor\Sms $sms 验证码类
 * @property \thanatos\oss\Oss $oss Oss
 * @property \common\extension\User $user
 * @property \common\components\vendor\Alipay $alipay
 * @property \common\components\vendor\Request $request
 * @property \yii\web\Response|\yii\console\Response|\common\components\vendor\Response $response The response component. This property is
 * read-only.
 */
abstract class BaseApplication extends yii\base\Application
{
}

/**
 * Class WebApplication
 * Include only Web application related components here
 */
abstract class WebApplication extends  yii\web\Application
{
}


/**
 * Class ConsoleApplication
 * @property \yii\db\Connection $dbMigrateDdy
 * @property \yii\db\Connection $dbMigrateTbz
 * Include only Console application related components here
 */
abstract class ConsoleApplication extends yii\console\Application
{
}