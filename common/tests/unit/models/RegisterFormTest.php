<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\tests\unit\models;

use common\models\forms\RegisterForm;
use common\models\Member;
use Yii;
use Codeception\Test\Unit;
use common\fixtures\UserFixture;
use common\models\forms\LoginForm;

class RegisterFormTest extends Unit
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
    }

    public function testCorrectBindModel()
    {
        Yii::$app->user->login(Member::findByMobile('13255553420'));
        $smsModel = Yii::$app->sms;
        $result = $smsModel->send('13255553420', 'bind-mobile');
        expect($result)->true();

        $model = new RegisterForm(['scenario' => RegisterForm::SCENARIO_BIND]);
        $result = $model->bind([
            'mobile' => '13255553420',
            'password' => '12345678',
            'sms_code' => '111111'
        ]);
        expect($result)->isInstanceOf(Member::class);
    }

    public function testNotCorrectBindModel()
    {
        Yii::$app->user->login(Member::findByMobile('13255553420'));
        $smsModel = Yii::$app->sms;
        $result = $smsModel->send('13255553420', 'bind-mobile');
        expect($result)->true();

        $model = new RegisterForm(['scenario' => RegisterForm::SCENARIO_BIND]);
        $result = $model->bind([
            'mobile' => '13255553420',
            'password' => '12345678',
            'sms_code' => '11111'
        ]);
        expect($result)->false();

        $model = new RegisterForm(['scenario' => RegisterForm::SCENARIO_BIND]);
        $result = $model->bind([
            'mobile' => '13255553421',
            'password' => '12345678',
            'sms_code' => '111111'
        ]);
        expect($result)->false();

    }


}