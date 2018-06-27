<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\validators;


use common\extension\Code;
use common\models\FileCommon;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

class FileUploadValidator extends Validator
{

    /** @var string 验证文件类型 */
    const METHOD_MIME_TYPE = 'mime_type';
    /** @var string 验证文件大小 */
    const METHOD_FILE_SIZE = 'size';

    /** @var int 允许最大文件上传值 */
    const MAX_UPLOAD_FILE_SIZE = 20 * 1024 * 1024;

    public $method;

    /**
     * @throws Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function init()
    {
        parent::init();
        if ($this->method === null){
            throw new Exception('method can not be null');
        }
    }

    /**
     * 上传文件类型验证
     * @param mixed $value
     * @return array|null
     * @author thanatos <thanatos915@163.com>
     */
    public function validateValue($value)
    {
        switch ($this->method) {
            // 验证文件大小
            case static::METHOD_FILE_SIZE:
                $valid = intval($value) > static::MAX_UPLOAD_FILE_SIZE || intval($value) <= 0;
                return $valid ? [Code::FILE_SIZE_NOT_ALLOW, []] : null;
                break;
            // 验证文件类型
            case static::METHOD_MIME_TYPE:
                $valid = in_array($value, ArrayHelper::getColumn(FileCommon::$extension, 'mime'));
                return $valid ? null : [Code::FILE_EXTENSION_NOT_ALLOW, []];
        }
    }

}