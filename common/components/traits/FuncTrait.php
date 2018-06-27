<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\traits;

use OSS\OssClient;
use Yii;
use common\components\vendor\OriginFIle;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\validators\UrlValidator;

/**
 * Trait FuncTraits
 * @package common\components\traits
 * @author thanatos <thanatos915@163.com>
 */
trait FuncTrait
{

    /**
     * 获取远程图片内容
     * @param string $url 远程文件路径
     * @param bool $head 是否只返回head信息
     * @return bool|OriginFIle
     * @author thanatos <thanatos915@163.com>
     */
    public static function getSourceOrigin($url, $head = true)
    {
        $validator = new UrlValidator();
        if ($validator->validate($url)) {
            $client = new Client(['transport' => CurlTransport::class]);
            $method = $head ? 'HEAD' : 'GET';
            if (preg_match("/\.svg$/", $url))
                $method = 'GET';
            $response = $client->createRequest()->setMethod($method)->setUrl($url)->send();
            // 文件类型
            $type = $response->headers->get('content-type');
            // 文件大小
            $length = $response->headers->get('content-length');
            // 文件内容
            $content = $response->content;
            if (!$response->isOk || empty($type) || empty($length)) {
                return false;
            }
        } else {
            // TODO 开发服务器测试
            if (YII_ENV_DEV) {
                Yii::$app->oss->getObject($url);
            }
            if (!$result = Yii::$app->oss->getObjectMeta($url)) {
                return false;
            }
            if ($head) {
                $content = Yii::$app->oss->getObject($url);
            }
            $type = $result->content_type;
            $length = $result->content_length;
        }

        $model = new OriginFIle();
        $model->type = $type;
        $model->length = $length;
        if ($content)
            $model->content = $content;

        return $model;
    }


    /**
     * 获取svg宽高信息
     * @param string $content
     * @return array
     */
    public static function getSvgObjectSize($content)
    {
        $width = 0;
        $height = 0;
        // 拿出svg标签
        if (preg_match("/\<svg([\s\S]*?)\>/i", $content, $matches)) {
            // width height
            if (preg_match("/(width=\".*?\").*?(height=\".*?\").*?/i", $matches[1], $widthMatches)) {
                $width = trim(str_ireplace('width="', '', $widthMatches[1]), 'px"');
                $height = trim(str_ireplace('height="', '', $widthMatches[2]), 'px"');
            } // style
            elseif (preg_match("/(style=\".*?\").*?/i", $matches[1], $styleMatches)) {
                $str = (trim(str_ireplace(['style="', 'width:', 'height:', 'px'], '', str_replace(' ', '', $styleMatches[1])), '"'));
                list($width, $height) = explode(';', $str);
            } // viewbox
            elseif (preg_match("/(viewbox=\".*?\").*?/i", $matches[1], $viewMatches)) {
                // 去掉 viewbox= 和两端空格
                $str = trim(str_ireplace("viewbox=\"", "", $viewMatches[1]), '"');
                // 取出宽高信息
                list(, , $width, $height) = explode(' ', $str);
            }
        }
        return ['height' => $height, 'width' => $width];
    }



    /**
     * 返回base64后远程图片
     * @param array $content 由 getSourceOrigin 返回的数据
     * @return bool|string
     */
    public static function base64Image($content)
    {
        if (empty($content)) {
            return false;
        }

        return 'data:' . $content['type'] . ';base64,' . base64_encode($content['content']);
    }

}