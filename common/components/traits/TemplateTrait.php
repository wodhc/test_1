<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\components\traits;


trait TemplateTrait
{
    /**
     * 处理页面内容
     * @author thanatos <thanatos915@163.com>
     */
    public function prepareContent()
    {
        var_dump(11);exit;
        // 替换资源
//        $content = preg_replace_callback('/"source":(\d+)/', function ($matches) {
//            return '"source":'. $this->getTables()[$matches[1]]['id'];
//        }, $this->content);
        return '';
    }
}