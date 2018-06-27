<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\models\forms;

use common\components\traits\ModelErrorTrait;
use Yii;
use common\components\traits\FuncTrait;
use common\components\validators\FileUploadValidator;
use common\components\validators\PathValidator;
use common\components\vendor\OriginFIle;
use common\extension\Code;
use common\models\FileCommon;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class FrontendUploadForm
 * @property OriginFIle|bool $fileData
 * @package common\models\forms
 * @author thanatos <thanatos915@163.com>
 */
class FileUpload extends Model
{
    use ModelErrorTrait;
    /** @var string 用户自助上传文件 */
    const SCENARIO_FRONTEND = 'frontend';
    /** @var string 系统上传文件 */
    const SCENARIO_INTERNAL = 'internal';
    /** @var string 系统上传本地文件 */
    const SCENARIO_INTERNAL_LOCAl = 'internal_local';
    /** @var string 系统上传文件替换掉之前的文件，并删除闲现有文件 */
    const SCENARIO_INTERNAL_REPLACE = 'internal_replace';

    const TEMP_DIR = 'temporary';
    /** @var string 存放模板缩略图 */
    const DIR_TEMPLATE = 'template';
    /** @var string 存放官方素材 */
    const DIR_MATERIAL = 'material';
    /** @var string 存放用户素材 */
    const DIR_ELEMENT = 'element';
    /** @var string 其它文件 */
    const DIR_OTHER = 'other';

    // 用户上传素材
    const METHOD_MEMBER_MATERIAL = 'member_material';
    // 正常的上传文件
    const METHOD_NORMAL = 'normal';

    public $filename;
    public $etag;
    public $mimeType;
    public $size;
    public $folder_id;
    public $width;
    public $height;
    public $method;
    public $format;
    public $user_id;

    public $file_url;
    public $dir;
    public $replace;
    public $content;


    private $_fileData;

    public function rules()
    {
        return [
            [['filename', 'etag', 'mimeType', 'size', 'method', 'format', 'file_url', 'dir', 'replace', 'content'], 'required'],
            [['user_id', 'width', 'height', 'folder_id'], 'default', 'value' => 0],
            [['user_id', 'width', 'height', 'folder_id'], 'filter', 'filter' => 'intval'],
            [['filename', 'etag', 'mimeType', 'method', 'format', 'file_url', 'dir', 'replace'], 'string'],
            [['size', 'user_id', 'folder_id', 'width', 'height'], 'integer'],
//            ['user_id', 'exist', 'targetAttribute' => 'id', 'targetClass' => Member::class],
            // 验证文件上传方式
            ['method', 'in', 'range' => [static::METHOD_MEMBER_MATERIAL]],
            // 验证文件大小
            ['size', FileUploadValidator::class, 'method' => FileUploadValidator::METHOD_FILE_SIZE],
            // 验证文件上传类型
            ['mimeType', FileUploadValidator::class, 'method' => FileUploadValidator::METHOD_MIME_TYPE],
            ['dir', function () {
                if (!in_array($this->dir, [static::DIR_ELEMENT, static::DIR_MATERIAL, static::DIR_OTHER, static::DIR_TEMPLATE]))
                    // 目录不存在
                    $this->addError('dir', Code::DIR_NOT_EXIST);
            }],
            ['file_url', 'validateFileUrl'],
            // 验证路径格式
            ['replace', PathValidator::class],
            // 验证文件是否存在
            ['filename', 'validateFilename'],
            [['content'], 'string']
        ];
    }

    public function validateFilename()
    {
        if (!Yii::$app->oss->doesObjectExist($this->filename)) {
            $this->addError('filename', '文件不存在');
        }
    }

    /**
     * 验证文件Url
     * @author thanatos <thanatos915@163.com>
     */
    public function validateFileUrl()
    {
        $validator = new FileUploadValidator(['method' => FileUploadValidator::METHOD_FILE_SIZE]);
        // 文件大小
        if (!$validator->validate($this->fileData->length, $error)) {
            return $this->addError('file_url', $error);
        }
        // 文件格式
        $validator->method = FileUploadValidator::METHOD_MIME_TYPE;
        if (!$validator->validate($this->fileData->type, $error)) {
            return $this->addError('file_url', $error);
        }
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $data = [
            static::SCENARIO_FRONTEND => ['filename', 'etag', 'user_id', 'mimeType', 'size', 'method', 'format', 'folder_id', 'width', 'height'],
            static::SCENARIO_INTERNAL => ['file_url', 'dir'],
            static::SCENARIO_INTERNAL_LOCAl => ['content', 'dir'],
            static::SCENARIO_INTERNAL_REPLACE => ['file_url', 'dir', 'replace'],
        ];
        return ArrayHelper::merge($scenarios, $data);
    }

    /**
     * 上传远程文件
     * @param string $url 文件URl
     * @param string $dir 存放位置
     * @param null $replace 想要替换的原始Object
     * @return bool|FileCommon|null
     * @author thanatos <thanatos915@163.com>
     */
    public static function upload($url, $dir = self::DIR_OTHER, $replace = null)
    {
        $scenario = static::SCENARIO_INTERNAL;
        if ($replace) {
            $scenario = static::SCENARIO_INTERNAL_REPLACE;
        }
        $model = new static(['scenario' => $scenario]);
        if ($result = $model->submit(['file_url' => $url, 'dir' => $dir, 'replace' => $replace])) {
            return $result;
        } else {
            $model->addErrors($model->getErrors());
            return false;
        }
    }

    /**
     * 上传本地文件
     * @param string $content 文件内容
     * @param string $dir 存放位置
     * @return bool|FileCommon|null
     * @author thanatos <thanatos915@163.com>
     */
    public static function uploadLocal($content, $dir)
    {
        $model = new static(['scenario' => static::SCENARIO_INTERNAL_LOCAl]);
        if ($result = $model->submit(['content' => $content, 'dir' => $dir])) {
            return $result;
        } else {
            $model->addErrors($model->getErrors());
            return false;
        }
    }

    /**
     * 上传文件
     * @param $params
     * @return bool|FileCommon|null
     * @author thanatos <thanatos915@163.com>
     */
    public function submit($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }

        // 区分不同的上传逻辑
        switch ($this->scenario) {
            case static::SCENARIO_FRONTEND:
                return $this->uploadFrontend();
                break;
            case static::SCENARIO_INTERNAL_REPLACE:
            case static::SCENARIO_INTERNAL:
            case static::SCENARIO_INTERNAL_LOCAl:
                return $this->uploadInternal();
                break;
        }

    }

    /**
     *  系统自动上传处理
     * @return bool|FileCommon|null
     * @author thanatos <thanatos915@163.com>
     */
    private function uploadInternal()
    {
        $filename = $this->generateFileName();
        $oldPath = $this->file_url;
        $fullFilename = UPLOAD_BASE_DIR . DIRECTORY_SEPARATOR . $filename;
        $width = $height = 0;
        // 上传本地文件
        if ($this->scenario == static::SCENARIO_INTERNAL_LOCAl) {
            $result = Yii::$app->oss->putObject($fullFilename, $this->content);
            $width = $this->fileData->width;
            $height = $this->fileData->height;
        }
        // 上传远程图片
        else {
            if ($this->fileData->extType == FileCommon::EXT_SVG) {
                // SVG 替换后上传
                $content = static::repairSvgTag($this->fileData->content);
                $result = Yii::$app->oss->putObject($fullFilename, $content);
                $width = $this->fileData->width;
                $height = $this->fileData->height;
            } else {
                // 其他文件直接上传
                $result = Yii::$app->oss->putObjectOrigin($fullFilename, $oldPath);
                // 设置图片的宽高信息
                if ($this->scenario == static::SCENARIO_FRONTEND) {
                    $width = $this->width;
                    $height = $this->height;
                } else {
                    list('height' => $height, 'width' => $width) = Yii::$app->oss->getObjectSize($fullFilename);
                }
            }
        }

        if (empty($result)) {
            $this->addError('url', Code::SERVER_FAILED);
            return false;
        }

        // 检查文件唯一性
        if ($file = FileCommon::findByEtag($result->etag)) {
            // 删除图片
            $this->rollbackFile($fullFilename);
            return $file;
        }

        // 文件信息
        $params = [
            'etag' => $result->etag,
            'path' => $filename,
            'size' => $result->size_upload,
            'type' => $this->fileData->extType,
            'width' => $width,
            'height' => $height,
        ];

        // 添加记录
        $model = new FileCommon();
        if (!($fileModel = $model->create($params))) {
            $this->addErrors($model->getErrors());
            $this->rollbackFile($fullFilename);
            return false;
        }
        return $fileModel;

    }

    /**
     * 前台上传文件处理
     * @return bool|FileCommon|\common\models\MaterialMember|\common\models\MaterialTeam|null
     * @author thanatos <thanatos915@163.com>
     */
    private function uploadFrontend()
    {
        // 把文件信息写入fileData里
        $fileData = new OriginFIle([
            'content' => '',
            'type' => $this->mimeType,
            'length' => $this->size,
        ]);
        $this->fileData = $fileData;
        // 处理不同的上传方式
        switch ($this->method) {
            case static::METHOD_MEMBER_MATERIAL:
                $this->dir = static::DIR_MATERIAL;
        }
        // 生成新的文件名
        $filename = $this->generateFileName();
        // OSS Object 路径
        $fullFile = UPLOAD_BASE_DIR . DIRECTORY_SEPARATOR . $filename;

        // 检查文件唯一性
        if ($file = FileCommon::findByEtag($this->etag)) {
            // 删除图片
            $this->rollbackFile($this->filename);
            return $file;
        }

        $width = 0;
        $height = 0;
        $tmp = ArrayHelper::map(FileCommon::$extension, 'mime', 'type');
        // 处理上传的文件
        if ($tmp[$this->mimeType] == FileCommon::EXT_SVG) {
            // 替换Svg标签
            $content = static::repairSvgTag($this->fileData->content);
            if (!Yii::$app->oss->putObject($fullFile, $content)) {
                $this->addError('', Code::SERVER_FAILED);
                return false;
            }
            list('height' => $height, 'width' => $width) = FuncTrait::getSvgObjectSize($content);
        } else {
            if (!Yii::$app->oss->copyObject($this->filename, $fullFile)) {
                $this->addError('', Code::SERVER_FAILED);
                return false;
            }
            $width = $this->width;
            $height = $this->height;
        }
        // 文件信息
        $params = [
            'etag' => $this->etag,
            'path' => $filename,
            'size' => $this->size,
            'type' => $this->fileData->extType,
            'width' => $width,
            'height' => $height,
        ];

        // 添加记录
        $model = new FileCommon();
        if (!($fileModel = $model->create($params))) {
            $this->addErrors($model->getErrors());
            $this->rollbackFile($fullFile);
            return false;
        }

        // 前端上传的话处理后续步骤
        switch ($this->method) {
            case static::METHOD_MEMBER_MATERIAL:
                $model = new MaterialForm();
                if ($result = $model->submit([
                    'file_id' => $fileModel->file_id,
                    'thumbnail' => $fileModel->path,
                    'folder_id' => $this->folder_id,
                ])) {
                    return $result;
                } else {
                    $this->addErrors($model->getErrors());
                    return false;
                }
        }
    }

    /**
     * 生成唯一的文件路径
     * @return string
     * @author thanatos <thanatos915@163.com>
     */
    public function generateFileName()
    {
        switch ($this->scenario) {
            // 替换原来文件，直接返回要替换的文件名
            case static::SCENARIO_INTERNAL_REPLACE:
                return $this->replace;
            default:
                try {
                    $filename = Yii::$app->security->generateRandomString(20);
                } catch (\Throwable $throwable) {
                    $filename = md5(uniqid());
                }
                $extension = $this->fileData->extString;
                return $this->dir . DIRECTORY_SEPARATOR . date('Ym') . DIRECTORY_SEPARATOR . $filename . '.' . $extension ?: 'png';
        }
    }

    /**
     * 获取文件Header信息
     * @return array|bool
     * @author thanatos <thanatos915@163.com>
     */
    public function getFileData()
    {
        if ($this->_fileData === null) {
            if ($this->scenario == static::SCENARIO_INTERNAL_LOCAl) {
                $model = new OriginFIle();
                $model->content = $this->content;
                $model->length = strlen($this->content);
                $this->fileData = $model;
            } else {
                $this->_fileData = FuncTrait::getSourceOrigin($this->file_url);
            }
        }
        return $this->_fileData;
    }

    public function setFileData($value)
    {
        $this->_fileData = $value;
    }


    /**
     * 回滚 删除文件
     * @param $filename
     * @return bool
     */
    private function rollbackFile($filename)
    {
        if (empty(Yii::$app->oss->deleteObject($filename))) {
            $this->addError('url', Code::SERVER_FAILED);
            Yii::error(['name' => 'deleteFile', 'params' => [$filename]]);
            return false;
        }
        return true;
    }

    /**
     * @param $content
     * @return mixed
     * @author thanatos <thanatos915@163.com>
     */
    public static function repairSvgTag($content)
    {
        //定义待检测替换标签数组
        $svgTagArr = array(
            //标签
            'altglyph' => 'altGlyph',
            'altglyphdef' => 'altGlyphDef',
            'altglyphitem' => 'altGlyphItem',
            'animatecolor' => 'animateColor',
            'animatemotion' => 'animateMotion',
            'animatetransform' => 'animateTransform',
            'clippath' => 'clipPath',
            'feblend' => 'feBlend',
            'fecolormatrix' => 'feColorMatrix',
            'fecomponenttransfer' => 'feComponentTransfer',
            'fecomposite' => 'feComposite',
            'feconvolvematrix' => 'feConvolveMatrix',
            'fediffuselighting' => 'feDiffuseLighting',
            'fedisplacementmap' => 'feDisplacementMap',
            'fedistantlight' => 'feDistantLight',
            'feflood' => 'feFlood',
            'fefunca' => 'feFuncA',
            'fefuncb' => 'feFuncB',
            'fefuncg' => 'feFuncG',
            'fefuncr' => 'feFuncR',
            'fegaussianblur' => 'feGaussianBlur',
            'feimage' => 'feImage',
            'femerge' => 'feMerge',
            'femergenode' => 'feMergeNode',
            'femorphology' => 'feMorphology',
            'feoffset' => 'feOffset',
            'fepointlight' => 'fePointLight',
            'fespecularlighting' => 'feSpecularLighting',
            'fespotlight' => 'feSpotLight',
            'fetile' => 'feTile',
            'feturbulence' => 'feTurbulence',
            'foreignobject' => 'foreignObject',
            'glyphref' => 'glyphRef',
            'lineargradient' => 'linearGradient',
            'radialgradient' => 'radialGradient',
            'textpath' => 'textPath',
            //属性
            'viewbox' => 'viewBox',
        );
        $findArr = array_keys($svgTagArr);
        return str_ireplace($findArr, $svgTagArr, $content);
    }

}