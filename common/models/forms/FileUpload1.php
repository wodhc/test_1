<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\models\forms;

use common\components\traits\FuncTrait;
use common\components\validators\FileUploadValidator;
use common\components\validators\PathValidator;
use common\components\vendor\OriginFIle;
use common\models\FileCommon;
use Yii;
use common\extension\Code;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\UrlValidator;


/**
 * 文件上传助手类
 * Class FileUpload
 * @property OriginFIle|bool $fileData
 * @property string $content
 * @package common\models\forms
 * @author thanatos <thanatos915@163.com>
 */
class FileUpload1 extends Model
{
    const TEMP_DIR = 'temporary';
    /** @var string 存放模板缩略图 */
    const DIR_TEMPLATE = 'template';
    /** @var string 存放官方素材 */
    const DIR_MATERIAL = 'material';
    /** @var string 存放用户素材 */
    const DIR_ELEMENT  = 'element';
    /** @var string 其它文件 */
    const DIR_OTHER = 'other';

    /** @var string 正常模式下的上传文件 */
    const SCENARIO_NORMAL = 'normal';
    /** @var string 替换模式下的上传文件 */
    const SCENARIO_REPLACE = 'replace';

    /** @var string*/
    public $url;
    /** @var string */
    public $dir;
    /** @var string 想要替换的原始Object */
    public $replace;

    // 文件信息
    private $_fileData;

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
        $model = new static();
        if ($result = $model->submit(['url' => $url, 'dir' => $dir, 'replace' => $replace])) {
            return $result;
        } else {
            $model->addErrors($model->getErrors());
            return false;
        }
    }

    /**
     * 上传OSS Object文件
     * 从原始路径复制到新路径，并删除原文件
     * @param string $url
     * @param string $replace
     * @param string $dir
     * @return bool
     * @author thanatos <thanatos915@163.com>
     */
    public static function uploadObject(string $url, $dir = self::DIR_TEMPLATE, $replace = '')
    {
        $model = new static(['']);
        if ($result = $model->submit(['url' => $url, 'dir' => $dir, 'replace' => $replace])) {

        } else {
            $model->addErrors($model->getErrors());
            return false;
        }
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $data = [
            static::SCENARIO_NORMAL => ['url', 'dir'],
            static::SCENARIO_REPLACE => ['url', 'dir', 'replace'],
        ];
        return ArrayHelper::merge($scenarios, $data);
    }

    public function rules()
    {
        return [
            [['url', 'dir'], 'required'],
            ['dir', function(){
                if (!in_array($this->dir, [static::DIR_ELEMENT, static::DIR_MATERIAL, static::DIR_OTHER, static::DIR_TEMPLATE]))
                    // 目录不存在
                    $this->addError('dir', Code::DIR_NOT_EXIST);
            }],
            // 文件是否存在
            ['url', function () {
                if (!is_string($this->url) || !(new UrlValidator())->validate($this->url)) {
                    $this->addError('url', Code::FILE_NOT_EXIST);
                }
            }],
            ['url', function () {
                // 文件名是否合法
                if (!$this->getIsAllowByMime()) {
                    return $this->addError('url', Code::FILE_EXTENSION_NOT_ALLOW);
                }
                // 文件大小验证
                if ($this->fileData->length > FileUploadValidator::MAX_UPLOAD_FILE_SIZE)
                    return $this->addError('url', Code::FILE_SIZE_NOT_ALLOW);

            }],
            // 验证路径格式
            ['replace', PathValidator::class]
        ];
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

        // 生成文件名
        if ($this->scenario == static::SCENARIO_REPLACE) {
            $filename = $this->replace ?: $this->generateFileName();
        } else {
            $filename = $this->generateFileName();
        }
        $fullFilename = UPLOAD_BASE_DIR . DIRECTORY_SEPARATOR . $filename;

        $width = $height = 0;
        if ($this->fileData->extType == FileCommon::EXT_SVG) {
            // SVG 替换后上传
            $content = static::repairSvgTag($this->fileData->content);
            $result = Yii::$app->oss->putObject($fullFilename, $content);
            list('height' => $height, 'width' => $width) = $this->fileData->getSvgSize();
        } else {
            // 其他文件直接上传
            $result = Yii::$app->oss->putObjectOrigin($fullFilename, $this->url);
            list('height' => $height, 'width' => $width) = Yii::$app->oss->getObjectSize($fullFilename);
        }

        if (empty($result)) {
            $this->addError('url', Code::SERVER_FAILED);
            return false;
        }

        // 删除原文件
        if ($this->scenario == static::SCENARIO_REPLACE) {
            $this->rollbackFile($this->url);
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
        if (($fileModel = $model->create($params))) {
            return $fileModel;
        } else {
            $this->addErrors($model->getErrors());
            $this->rollbackFile($fullFilename);
            return false;
        }

    }

    /**
     * 生成唯一的文件路径
     * @return string
     */
    public function generateFileName()
    {
        try {
            $filename = Yii::$app->security->generateRandomString(20);
        } catch (\Throwable $throwable) {
            $filename = md5(uniqid());
        }
        $extension = $this->fileData->extString;
        return $this->dir. DIRECTORY_SEPARATOR . date('Ym') . DIRECTORY_SEPARATOR .   $filename . '.' . $extension ?? 'png';
    }

    /**
     * 判断是否是允许的文件
     * @return bool
     */
    public function getIsAllowByMime()
    {
        return in_array($this->fileData['type'], ArrayHelper::getColumn(FileCommon::$extension, 'mime'));
    }


    /**
     * 获取文件Header信息
     * @return array|bool
     * @author thanatos <thanatos915@163.com>
     */
    public function getFileData()
    {
        if ($this->_fileData === null) {
            $this->_fileData = FuncTrait::getSourceOrigin($this->url);
        }
        return $this->_fileData;
    }

    /**
     * @return string
     */
    public static function repairSvgTag($content) {
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

}