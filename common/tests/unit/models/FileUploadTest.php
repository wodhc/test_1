<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\FileCommon;
use common\models\forms\FileUpload;

class FileUploadTest extends Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;


    public function testCorrectUpload()
    {
        $model = FileUpload::upload('http://cdn.tubangzhu.net/static/tbz-main/images/tbz_logo_white_4899104.png');
        $this->tester->seeRecord(FileCommon::class, ['file_id' => $model->file_id]);
        expect($model)->isInstanceOf(FileCommon::class);
    }

    public function testNotCorrectUpload()
    {
        $model = FileUpload::upload('12312');
        expect($model)->false();
    }

}