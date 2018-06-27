<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/21
 * Time: 18:11
 */

namespace common\models\forms;

use common\components\traits\ModelAttributeTrait;
use common\models\FileCommon;
use common\models\FolderMaterialMember;
use common\models\FolderMaterialTeam;
use Yii;
use common\models\MaterialMember;
use common\components\traits\ModelErrorTrait;
use common\models\MaterialTeam;
use yii\helpers\Json;

/**
 * Class MaterialForm
 * @property MaterialTeam|MaterialMember|null $activeModel
 * @package common\models\forms
 * @author thanatos <thanatos915@163.com>
 */
class MaterialForm extends \yii\base\Model
{
    use ModelErrorTrait;
    use ModelAttributeTrait;

    /** @var int 到回收站状态 */
    const RECYCLE_BIN_STATUS = 7;

    public $file_id;
    public $thumbnail;
    public $team_id;
    public $user_id;
    public $mode;
    public $file_name;
    public $folder_id;

    public $id;
    public $status;

    private $_activeModel;

    public function rules()
    {
        return [
            [['thumbnail', 'file_id'], 'required', 'when' => function ($model) {
                return empty($model->id);
            }],
            [['folder_id', 'file_id', 'team_id', 'id'], 'integer'],
            [['file_name', 'thumbnail'], 'string', 'max' => 255],
            ['id', function () {
                if (empty($this->activeModel)) {
                    $this->addError('id', '请求资源不存在');
                }
            }],
            ['status', 'compare', 'compareValue' => static::RECYCLE_BIN_STATUS, 'operator' => '=']
        ];
    }

    /**
     * 用户素材处理函数
     * @param $params
     * @return bool|MaterialMember|MaterialTeam|null
     * @author thanatos <thanatos915@163.com>
     */
    public function submit($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }
        $model = $this->activeModel;
        if (!$model) {
            return false;
        }
        $model->load($this->getUpdateAttributes(), '');
        $model->user_id = Yii::$app->user->id;
        // 添加Team信息
        if ($model instanceof MaterialTeam) {
            $model->team_id = Yii::$app->user->identity->team->id;
        }
        //新增素材时，只添加素材引用记录
        if ($model->isNewRecord) {
            $create_file = $model->file_id;
        }
        //修改素材，且文件有变化时，删除原来文件引用记录，然后增加文件引用记录
        if ($model->isAttributeChanged('thumbnail') && $model->isAttributeChanged('file_id')) {
            $drop_file = $model->getOldAttribute('file_id');
            $create_file = $model->file_id;
        }
        if ($model->isAttributeChanged('status') && $model->status == static::RECYCLE_BIN_STATUS){
            $drop_file = $model->getOldAttribute('file_id');
        }
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            //素材的添加或修改操作
            if (!($model->validate() && $model->save())) {
                throw new \Exception('素材操作失败' . $model->getStringErrors());
            }
            //素材文件变化，删除原来的文件引用信息
            if ($drop_file) {
                $drop_result = FileCommon::reduceSum($drop_file);
                if (!$drop_result) {
                    throw new \Exception('原文件引用记录删除失败');
                }
            }
            // 添加素材文件引用类型
            if ($create_file) {
                $create_result = FileCommon::increaseSum($create_file);
                if (!$create_result) {
                    throw new \Exception('新文件引用记录添加失败');
                }
            }
            $transaction->commit();
            return $model;
        } catch (\Throwable $e) {
            try {
                $transaction->rollBack();
            } catch (\Throwable $e) {
            }
            $message = $e->getMessage();
            // 添加错误信息
            if (strpos($message, '=') === false)
                $this->addError('', $message);
            else
                $this->addErrors(Json::decode(explode(':', $message)[1]));
            return false;
        }

    }
    /**
     * @return array|null|\yii\db\ActiveRecord
     * @author thanatos <thanatos915@163.com>
     */
    public function getActiveModel()
    {
        if ($this->_activeModel === null) {
            $user = Yii::$app->user->identity;
            /** @var MaterialMember|MaterialTeam $modelClass */
            $modelClass = '';
            if ($user->team) {
                $modelClass = MaterialTeam::class;
                $folderClass = FolderMaterialTeam::class;
                $condition = ['team_id' => $user->team->id];
            } else {
                $modelClass = MaterialMember::class;
                $folderClass = FolderMaterialMember::class;
                $condition = ['user_id' => \Yii::$app->user->id];
            }
            if ($this->id) {
                $model = $modelClass::findById($this->id);
            } else {
                $model = new $modelClass();
            }
            /** @var FolderMaterialMember|FolderMaterialTeam $folderClass */
            if ($this->folder_id) {
                //验证目标文件夹是否存在
                $folder = $folderClass::find()->where(['id' => $this->folder_id])->andWhere($condition)->one();
                if (!$folder) {
                    $this->addError('', '目标文件夹不存在');
                    $model = false;
                }
            }
            $this->_activeModel = $model;
        }
        return $this->_activeModel;
    }
}