<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\tests\unit\models;

use Yii;
use Codeception\Test\Unit;
use common\fixtures\UserFixture;
use common\models\forms\RechargeForm;
use common\models\Member;
use common\models\MemberCoinRecharge;

class RechargeFormTest extends Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ]);
        Yii::$app->user->login(Member::findByMobile('13255553420'));
    }

    /**
     * 测试前端充值
     * @author thanatos <thanatos915@163.com>
     */
    public function testCorrectRecharge()
    {
        Yii::$app->request->handle = 'frontend';
        $model = new RechargeForm();
        $result = $model->submit([
            'money' => '10'
        ]);
        expect($result)->isInstanceOf(MemberCoinRecharge::class);

    }

    /**
     * 测试前端充值
     * @author thanatos <thanatos915@163.com>
     */
    public function testNotCorrectRecharge()
    {
        $model = new RechargeForm();
        $result = $model->submit([]);
        expect($result)->false();

    }

    /**
     * 测试后端充值
     * @author thanatos <thanatos915@163.com>
     */
    public function testCorrectBackendRecharge()
    {
        Yii::$app->request->handle = 'backend';
        $model = new RechargeForm();
        $result = $model->submit([
            'coin' => 1000,
            'user_id' => 1,
        ]);

        expect($result)->isInstanceOf(MemberCoinRecharge::class);
        expect($result->status)->equals(MemberCoinRecharge::STATUS_READY_PAY);
        expect($result->admin_id)->notEmpty();
        expect($result->admin_name)->notEmpty();
        $member = Member::findIdentity($model->user_id);
        expect($member->coin)->equals(1000);
    }

    /**
     * 测试后端充值
     * @author thanatos <thanatos915@163.com>
     */
    public function testNotCorrectBackendRecharge()
    {
        Yii::$app->request->handle = 'backend';
        $model = new RechargeForm();
        $result = $model->submit([
            'coin' => 1000,
        ]);
        expect($result)->false();

        $model = new RechargeForm();
        $result = $model->submit([
            'user_id' => 1
        ]);
        expect($result)->false();
    }

}