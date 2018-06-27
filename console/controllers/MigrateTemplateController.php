<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace console\controllers;


use console\models\OfficialTemplate;
use PHPHtmlParser\Dom;
use yii\console\Controller;
use yii\helpers\Json;

class MigrateTemplateController extends Controller
{
    public function actionIndex()
    {
        $templates = $this->getTemplateData(100227);
        foreach ($templates->pages as $pages) {
            foreach ($pages->elements as $elments) {
                $editData = Json::decode($elments->edit_data);
                var_dump($editData['svg']);exit;
                $dom = new Dom();
                $dom->load($editData['svg']);
                /** @var Dom\HtmlNode $viewBox */
                $viewBox = $dom->find('svg')[0];
            }
        }
    }

    /**
     * @param $id
     * @return array|null|OfficialTemplate
     * @author thanatos <thanatos915@163.com>
     */
    private function getTemplateData($id)
    {
        return OfficialTemplate::find()->where(['id' => $id])->with('pages.elements')->one();
    }
}