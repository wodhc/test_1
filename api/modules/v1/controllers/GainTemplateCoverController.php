<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\modules\v1\controllers;

use Yii;

class GainTemplateCoverController extends \api\common\controllers\GainTemplateCoverController
{
    /*
     * 迁移老数据
     */
    public function actionMigrateOldData()
    {
        $test_data = \Yii::$app->db_old->createCommand('SELECT * FROM com_tbz_subject ')
            ->queryAll();
        $data = [];
        $i = 0;
        foreach ($test_data as $key => $value) {
            $data[$i][0] = $value['id'];
            $data[$i][1] = $value['title'];
            $data[$i][2] = $value['description'];
            $data[$i][3] = $value['thumbnail'];
            $data[$i][4] = $value['banner'];
            $data[$i][5] = $value['seoTitle'];
            $data[$i][6] = $value['seoKeyword'];
            $data[$i][7] = $value['seoDescription'];
            $data[$i][8] = $value['status'];
            $data[$i][9] = $value['sort'];
            $data[$i][10] = strtotime($value['createdTime']);
            $data[$i][11] = strtotime($value['updatedTime']);
            $i++;
        }
        Yii::$app->db->createCommand()->batchInsert('tbz_subject', ['id', 'title', 'description', 'thumbnail', 'banner', 'seo_title', 'seo_keyword', 'seo_description', 'status', 'sort', 'created_time', 'updated_time'], $data)->execute();//执行批量添加
        return $data;
    }
}