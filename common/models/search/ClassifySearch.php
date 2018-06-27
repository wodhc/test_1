<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\models\search;


use common\models\Classify;
use common\models\Tag;
use phpDocumentor\Reflection\Types\Object_;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use common\components\traits\ModelErrorTrait;

;

class ClassifySearch extends Model
{
    use ModelErrorTrait;
    /** @var string 前台查询 */
    const SCENARIO_FRONTEND = 'frontend';
    /** @var string 后台查询 */
    const SCENARIO_BACKEND = 'backend';

    public $category;
    public $status;
    public $classify;

    public function rules()
    {
        return [
            [['category', 'status', 'classify'], 'integer'],
        ];
    }

    /**
     * 查询官方模板分类表（带分页）
     * @param $params
     * @return ActiveDataProvider
     * @author thanatos <thanatos915@163.com>
     */
    public function search($params)
    {
        $this->load($params, '');

        $query = Classify::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $query->andFilterWhere([
            'category' => $this->category,
            'status' => $this->status,
        ]);

        return $dataProvider;
    }

    /**
     * @author thanatos <thanatos915@163.com>
     * @return array|bool 获取小分类或标签
     */
    public function classifyTag()
    {
        if ($this->classify) {
            $classifyModel = Classify::findById($this->classify);
            if ($classifyModel)
                $tags = $this->searchTag($classifyModel);
        }
        $category = $this->category ?: $classifyModel->category_id;
        $classify = Classify::online()->andWhere(['category_id' => $category])->all();

        return ArrayHelper::merge(['classify' => $classify], $tags);
    }

    /**
     * @param Classify $classify
     * @return array|bool
     */
    public function searchTag(Classify $classify)
    {
        //关联表查询标签数据
        $tags_data = $classify->tags;
        $tags['style'] = [];
        $tags['industry'] = [];
        foreach ($tags_data as $value) {
            if ($value->type == Tag::TYPE_STYLE) {
                $tags['style'][] = $value;
            } elseif ($value->type == Tag::TYPE_INDUSTRY) {
                $tags['industry'][] = $value;
            } elseif ($value->type == Tag::TYPE_FUNCTION) {
                $tags['function'][] = $value;
            }
        }
        return $tags;
    }
}