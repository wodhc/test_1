<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\tests\unit\models;


use Codeception\Test\Unit;
use common\fixtures\UserFixture;
use common\models\forms\LoginForm;

class LoginFormTest extends Unit
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

    public function testCorrectLogin()
    {
        $model = new LoginForm(['scenario' => LoginForm::SCENARIO_MOBILE]);
        $result = $model->submit([
            'mobile' => '13255553420',
            'password' => '12345678',
        ]);

        expect($result)->notNull();
        expect($result['accessToken'])->notNull();
        expect($result['mobile'])->equals('13255553420');
    }

    public function testNotCorrectLogin()
    {
        $model = new LoginForm(['scenario' => LoginForm::SCENARIO_MOBILE]);
        $result = $model->submit([
            'mobile' => 'test',
            'password' => 'test',
        ]);
        expect($result)->false();
    }

}