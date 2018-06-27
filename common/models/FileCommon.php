<?php

namespace common\models;

use common\components\traits\ModelFieldsTrait;
use common\components\traits\TimestampTrait;
use Yii;
use yii\helpers\Url;
use yii\validators\UrlValidator;

/**
 * This is the model class for table "{{%file_common}}".
 *
 * @property int $file_id
 * @property string $etag 文件唯一值
 * @property string $path 文件路径
 * @property int $size 文件大小
 * @property int $type 文件类型
 * @property int $width 图片宽度
 * @property int $height 图片高度
 * @property int $sum 文件使用次数
 * @property int $created_at 创建时间
 */
class FileCommon extends \yii\db\ActiveRecord
{
    use TimestampTrait;
    use ModelFieldsTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%file_common}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['width', 'height', 'sum'], 'default', 'value' => 0],
            [['width', 'height', 'size', 'sum'], 'filter', 'filter' => 'intval'],
            [['etag', 'path', 'size', 'type'], 'required'],
            [['size', 'width', 'height', 'sum', 'created_at'], 'integer'],
            [['etag'], 'string', 'max' => 32, 'min' => 32],
            [['path'], 'string', 'max' => 255],
            [['type'], 'integer', 'min' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'File ID',
            'etag' => 'Etag',
            'path' => 'Path',
            'size' => 'Size',
            'type' => 'Type',
            'width' => 'Width',
            'height' => 'Height',
            'created_at' => 'Created At',
        ];
    }

    /**
     * 上传图片
     * @param $params
     * @return bool|FileCommon|null
     * @author thanatos <thanatos915@163.com>
     */
    public function create($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }

        if ($model = static::findByEtag($this->etag)) {
            return $model;
        }
        return $this->save() ? $this : false;
    }

    /**
     * 根据唯一值查询
     * @param $etag
     * @return FileCommon|null
     */
    public static function findByEtag($etag)
    {
        return static::findOne(['etag' => $etag]);
    }

    /**
     * 减少引用次数
     * @param $params
     * @return bool|int
     */
    public static function reduceSum($params)
    {
        if (empty($params) || (!is_array($params) && !is_numeric($params))) {
            return false;
        }
        return static::updateAllCounters(['sum' => -1], ['file_id' => $params]);
    }

    /**
     * 增加文件引用次数
     * @param $params
     * @return bool|int
     */
    public static function increaseSum($params)
    {
        if (empty($params) || (!is_array($params) && !is_numeric($params))) {
            return false;
        }
        return static::updateAllCounters(['sum' => 1], ['file_id' => $params]);
    }

    const EXT_DOC = 1;
    const EXT_DOCX = 2;
    const EXT_XLS = 3;
    const EXT_XLSX = 4;
    const EXT_TXT = 5;
    const EXT_JPG = 6;
    const EXT_JPEG = 7;
    const EXT_BMP = 8;
    const EXT_PNG = 9;
    const EXT_GIF = 10;
    const EXT_RAR = 11;
    const EXT_ZIP = 12;
    const EXT_7Z = 13;
    const EXT_PSD = 14;
    const EXT_PPT = 15;
    const EXT_PPTX = 16;
    const EXT_AI = 17;
    const EXT_PDF = 18;
    const EXT_CDR = 19;
    const EXT_EPS = 20;
    const EXT_TIF = 21;
    const EXT_TIFF = 22;
    const EXT_SVG = 23;
    const EXT_CSS = 24;

    static $extension = [
        ['ext' => 'doc', 'mime' => 'application/msword', 'type' => self::EXT_DOC],
        ['ext' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'type' => self::EXT_DOCX],
        ['ext' => 'xls', 'mime' => 'application/vnd.ms-office', 'type' => self::EXT_XLS],
        ['ext' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'type' => self::EXT_XLSX],
        ['ext' => 'txt', 'mime' => 'text/plain', 'type' => self::EXT_TXT],
        ['ext' => 'css', 'mime' => 'text/css', 'type' => self::EXT_CSS],
        ['ext' => 'jpg', 'mime' => 'image/jpeg', 'type' => self::EXT_JPG],
        ['ext' => 'jpeg', 'mime' => 'image/jpeg', 'type' => self::EXT_JPEG],
        ['ext' => 'bmp', 'mime' => 'image/x-ms-bmp', 'type' => self::EXT_BMP],
        ['ext' => 'png', 'mime' => 'image/png', 'type' => self::EXT_PNG],
        ['ext' => 'gif', 'mime' => 'image/gif', 'type' => self::EXT_GIF],
        ['ext' => 'rar', 'mime' => 'application/x-rar', 'type' => self::EXT_RAR],
        ['ext' => 'zip', 'mime' => 'application/zip', 'type' => self::EXT_ZIP],
        ['ext' => '7z', 'mime' => 'application/x-7z-compressed', 'type' => self::EXT_7Z],
        ['ext' => 'psd', 'mime' => 'image/vnd.adobe.photoshop', 'type' => self::EXT_PSD],
        ['ext' => 'ppt', 'mime' => 'application/vnd.ms-powerpoint', 'type' => self::EXT_PPT],
        ['ext' => 'pptx', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'type' => self::EXT_PPTX],
        ['ext' => 'ai', 'mime' => 'application/pdf', 'type' => self::EXT_AI],
        ['ext' => 'pdf', 'mime' => 'application/pdf', 'type' => self::EXT_PDF],
        ['ext' => 'cdr', 'mime' => 'application/zip', 'type' => self::EXT_CDR],
        ['ext' => 'eps', 'mime' => 'application/octet-stream', 'type' => self::EXT_EPS],
        ['ext' => 'tif', 'mime' => 'image/tiff', 'type' => self::EXT_TIF],
        ['ext' => 'tiff', 'mime' => 'image/tiff', 'type' => self::EXT_TIFF],
        ['ext' => 'svg', 'mime' => 'image/svg+xml', 'type' => self::EXT_SVG],
    ];

}
