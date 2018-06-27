<?php

namespace common\models\forms;

use common\models\FileCommon;
use common\models\TbzSubject;
use yii\base\Model;
use common\components\traits\ModelErrorTrait;
use common\components\traits\ModelAttributeTrait;

class TbzSubjectForm extends Model
{
    use ModelErrorTrait;
    use ModelAttributeTrait;
    /* @var integer 回收站状态 */
    const DELETE_STATUS = 7;

    public $id;
    public $sort;
    public $title;
    public $description;
    public $seo_keyword;
    public $seo_description;
    public $thumbnail;
    public $seo_title;
    public $banner;
    public $status;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['sort', 'title', 'description', 'seo_keyword', 'seo_description', 'thumbnail', 'seo_title', 'banner'], 'required', 'on' => 'create'],
            [['sort', 'thumbnail', 'banner'], 'integer', 'on' => ['create', 'update']],
            ['title', 'string', 'max' => 150, 'on' => ['create', 'update']],
            [['description', 'seo_keyword', 'seo_description'], 'string', 'max' => 255, 'on' => ['create', 'update']],
            [['seo_title'], 'string', 'max' => 100, 'on' => ['create', 'update']],
            [['id'], 'required', 'on' => ['delete', 'update']],
            ['status', 'required', 'on' => 'delete'],
            ['thumbnail', function () {
                if (!($this->isFile($this->thumbnail))) {
                    $this->addError('thumbnail', '缩略图文件不存在');
                }
            }, 'on' => ['create', 'update']],
            ['banner', function () {
                if (!($this->isFile($this->banner))) {
                    $this->addError('banner', 'banner图文件不存在');
                }
            }, 'on' => ['create', 'update']]
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            'create' => ['sort', 'title', 'description', 'seo_keyword', 'seo_description', 'thumbnail', 'seo_title', 'banner', 'status'],
            'update' => ['sort', 'title', 'description', 'seo_keyword', 'seo_description', 'thumbnail', 'seo_title', 'banner', 'status', 'id'],
            'delete' => ['id', 'status'],
        ];
    }

    /**
     * @param $params
     * @return bool|TbzSubject|null
     */
    public function submit($params)
    {

        if (!$this->load($params, '')) {
            return false;
        }
        if (!$this->validate()) {
            return false;
        }
        if ($this->id) {
            $model = TbzSubject::findOne($this->id);
            if (!$model) {
                $this->addError('id', '资源不存在');
                return false;
            }
        } else {
            $model = new TbzSubject();
        }
        $model->load($this->getUpdateAttributes(), '');
        //保存模板专题数据
        $drop_data = [];
        $create_data = [];
        //创建新的模板专题时的操作
        if ($model->isNewRecord) {
            $create_data[] = $model->banner;
            $create_data[] = $model->thumbnail;
        } else {
            /** 修改和删除时的操作 */
            //缩略图有变化时的操作
            if ($model->isAttributeChanged('thumbnail')) {
                $drop_data[] = $model->getOldAttribute('thumbnail');
                $create_data[] = $model->thumbnail;
            }
            //banner图有变化时的操作
            if ($model->isAttributeChanged('banner')) {
                $drop_data[] = $model->getOldAttribute('banner');
                $create_data[] = $model->banner;
            }
            //删除模板时的操作
            if ($model->isAttributeChanged('status') && $model->status == static::DELETE_STATUS) {
                $drop_data[] = $model->getOldAttribute('thumbnail');
                $drop_data[] = $model->getOldAttribute('banner');
            }
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            if (!($model->validate() && $model->save())) {
                throw new \Exception('模板操作失败' . $model->getStringErrors());
            }
            /** 删除文件引用记录 */
            if ($drop_data) {
                $drop_result = FileCommon::reduceSum($drop_data);
                if (!$drop_result) {
                    throw new \Exception('文件引用记录删除失败');
                }
            }
            /** 创建文件引用记录 */
            if ($create_data) {
                $create_result = FileCommon::increaseSum($create_data);
                if (!$create_result) {
                    throw new \Exception('新文件引用记录添加失败');
                }
            }
            $transaction->commit();
            return $model;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->addError('', $e->getMessage());
            return false;
        }
    }

    /**
     * 判断文件是否存在
     * @param $id
     * @return bool
     */
    public function isFile($id)
    {
        if (!FileCommon::findOne(['file_id' => $id])) {
            return false;
        }
        return true;
    }
}