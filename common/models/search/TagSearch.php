<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/11
 * Time: 17:18
 */
namespace common\models\search;
use common\models\Tag;
use yii\base\Model;
use yii\data\ActiveDataProvider;
class TagSearch extends Model
{
    /**
     * @return array|bool
     * 查询所有的tag，按热度排序
     */
    public function search()
    {
        $tag = Tag::find();
        $provider = new ActiveDataProvider([
            'query' => $tag,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'sort' => SORT_DESC,
                ]
            ],
        ]);
        $result_data = $provider->getModels();
        if ($result_data) {
            return $result_data;
        }
        return false;
    }
}