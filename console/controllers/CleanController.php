<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace console\controllers;


use common\models\FileCommon;
use OSS\OssClient;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\Console;

class CleanController extends Controller
{

    private $_count;

    public function options($actionID)
    {
        return [];
    }

    public function optionAliases()
    {
        return [];
    }

    /**
     * 清理文件
     * @author thanatos <thanatos915@163.com>
     */
    public function actionCleanFile()
    {
        $prefix = 'updata/';
        $start = time();
        $this->stdout('开始清除文件' . "\n", Console::FG_YELLOW);
        $this->cleanDir($prefix);
        $end = time();
        $this->stdout('清除完成'. $this->_count . '个' . "\n", Console::FG_GREEN);
        $this->stdout('总耗时 '. $end - $start . "\n", Console::FG_GREEN);
    }

    private function cleanDir($dir)
    {
        $nextMarker = '';

        while (true) {
            $options = [
                OssClient::OSS_PREFIX => $dir,
                OssClient::OSS_MAX_KEYS => 1000,
                OssClient::OSS_MARKER => $nextMarker,
            ];

            try  {
                $listObjectInfo = Yii::$app->oss->listObjects($options);
            } catch (Exception $e) {
                print_r($e->getMessage());
                return;
            }
            $listPrefix = $listObjectInfo->getPrefixList();
            // 遍历子目录
            if ($listPrefix) {
                foreach ($listPrefix as $k => $item) {
                    if ($item->getPrefix() !== 'updata/temporary/')
                        $this->cleanDir($item->getPrefix());
                }
            }
            $listObject = $listObjectInfo->getObjectList();
            $nextMarker = $listObjectInfo->getNextMarker();
            // 删除当前文件
            foreach ($listObject as $k => $object) {
                if ($object->getSize() !== 0) {
                    if (!FileCommon::findByEtag(trim($object->getETag(), '"'))) {
                        Yii::$app->oss->deleteObject(trim($object->getKey()));
                        $this->_count ++;
                    }
                }
            }
            if ($this->_count > 0)
                $this->stdout('成功删除'. $this->_count . '个文件' . "\n", Console::FG_GREEN);
            // 处理文件删除
            if ($nextMarker === '') {
                break;
            }
        }


    }

}