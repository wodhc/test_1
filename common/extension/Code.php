<?php
/**
 * @user: thanatos
 */
namespace common\extension;

use yii\helpers\ArrayHelper;

class Code
{
    /** @var int 用户已经存在 */
    const USER_EXIST = 50001;
    /** @var int 用户不存在 */
    const USER_NOT_FOUND = 50002;
    /** @var int 密码不正确 */
    const USER_WRONG_PASSWORD = 50003;
    /** @var string refreshToken不正确  */
    const USER_TOKEN_FAILED = '50004';
    /** @var string 密码必须在8-16位之间 */
    const USER_PASSWORD_LENGTH_FAILED = '50005';
    const USER_MOBILE_EXIST = '50006';


    /** @var int 文件不存在 */
    const FILE_NOT_EXIST = 30001;
    /** @var int 不允许上传 */
    const FILE_EXTENSION_NOT_ALLOW = 30002;
    /** @var int 文件大小超出限制 */
    const FILE_SIZE_NOT_ALLOW = 30002;
    /** @var int 目录不存在 */
    const DIR_NOT_EXIST = 30003;

    const SERVER_FAILED = -1;
    const SERVER_SUCCESS = 0;
    /** @var int 验证失败 */
    const SERVER_UNAUTHORIZED = 10001;
    /** @var int 没有权限 */
    const SERVER_NOT_PERMISSION = 10002;
    /** @var int 资源不存在 */
    const SOURCE_NOT_FOUND = 10004;

    const TEMPLATE_FORMAT_ERROR = '60001';

    /** @var array common return code */
    public $common = [
        self::SERVER_SUCCESS => '请求成功',
        self::SERVER_FAILED => '系统繁忙, 请稍候再试',
        self::SERVER_UNAUTHORIZED => '验证失败',
        self::SOURCE_NOT_FOUND => '资源不存在',
        self::SERVER_NOT_PERMISSION => '没有权限'
    ];

    /** @var array system return code */
    public $system = [
        self::FILE_NOT_EXIST => '文件不存在',
        self::FILE_EXTENSION_NOT_ALLOW => '不允许的文件类型',
        self::FILE_SIZE_NOT_ALLOW => '文件大小超出限制',
        self::DIR_NOT_EXIST => '目录不存在',
    ];

    /** @var array user return code */
    public $user = [
        self::USER_EXIST => '用户已经存在',
        self::USER_NOT_FOUND => '用户不存在',
        self::USER_WRONG_PASSWORD => '密码不正确',
        self::USER_TOKEN_FAILED => 'refreshToken不正确',
        self::USER_PASSWORD_LENGTH_FAILED => '密码必须在8-16位之间',
        self::USER_MOBILE_EXIST => '手机号已被使用，请联系客服',
    ];

    public $template = [
        self::TEMPLATE_FORMAT_ERROR => '模板数据格式错误',
    ];

    /**
     * 获取平台错误信息
     * @return array
     * @author thanatos <thanatos915@163.com>
     */
    public function getErrors()
    {
        $data = ArrayHelper::merge($this->common, $this->system, $this->user, $this->template);
        return $data;
    }

}