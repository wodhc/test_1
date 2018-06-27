<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/17
 * Time: 15:57
 */

namespace common\models\forms;

use common\components\traits\ModelErrorTrait;
use common\models\FileCommon;
use common\models\FolderMaterialMember;
use common\models\FolderTemplateTeam;
use common\models\TemplateMember;
use common\models\TemplateTeam;

class TemplateOperationForm extends \yii\base\Model
{
    use ModelErrorTrait;

    /* @var integer  重命名 */
    const RENAME = 1;
    /* @var integer  移动到文件夹 */
    const MOVE_FOLDER = 2;
    /* @var integer  到回收站(初步删除) */
    const RECYCLE_BIN = 3;
    /* @var integer  删除 */
    const DELETE = 4;
    /* @var integer  还原 */
    const REDUCTION = 5;
    /* @var integer  个人转团队 */
    const PERSONAL_TRANSFER_TEAM = 6;


    /** @var int 到回收站 */
    const STATUS_TRASH = 7;
    /** @var int 删除 */
    const STATUS_DELETE = 3;
    /** @var int 还原 */
    const STATUS_NORMAL = 10;

    public $ids;
    public $name;
    public $folder;
    public $team_id;
    public $type;

    public $_table;
    public $_user;
    public $_condition;
    public $_folderModel;
    public $_tableModel;

    public function rules()
    {
        return [
            [['ids', 'type'], 'required'],
            [['folder', 'team_id'], 'integer'],
            ['name', 'string'],
            ['folder', 'required', 'when' => function ($model) {
                return $model->type == static::MOVE_FOLDER;
            }],
            ['name', 'required', 'when' => function ($model) {
                return $model->type == static::RENAME;
            }],
            ['ids', function () {
                if (!is_integer($this->ids) && !is_numeric($this->ids) && !is_array($this->ids)) {
                    $this->addError('ids', 'ids必须是整数或者数组');
                }
            }]
        ];
    }

    /**
     * @param $params
     * @return bool|null
     * @throws \yii\db\Exception
     */
    public function operation($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }
        switch ($this->type) {
            case static::RENAME:
                return $this->rename();
            case static::MOVE_FOLDER:
                return $this->moveFolder();
            case static::RECYCLE_BIN:
                return $this->recycleBin();
            case static::DELETE :
                return $this->deleteTemplate();
            case static::REDUCTION :
                return $this->reduction();
            case static:: PERSONAL_TRANSFER_TEAM:
                return $this->transferTeam();
            default:
                return null;
        }
    }

    /**
     * 重命名
     * @return bool
     * @throws \yii\db\Exception
     */
    public function rename()
    {
        if (is_array($this->ids)) {
            $this->addError('', '不支持多个重命名');
            return false;
        }
        return $this->batchProcessing('title', $this->name);
    }

    /**
     * 移动到文件夹
     * @return bool
     * @throws \yii\db\Exception
     */
    public function moveFolder()
    {
        return $this->batchProcessing('folder_id', $this->folder);
    }

    /**
     * 到回收站
     * @return bool
     * @throws \yii\db\Exception
     */
    public function recycleBin()
    {
        return $this->batchProcessing('status', static::STATUS_TRASH);
    }

    /**
     * 删除
     * @return bool
     * @throws \yii\db\Exception
     */
    public function deleteTemplate()
    {
        return $this->batchProcessing('status', static::STATUS_DELETE);
    }

    /**
     * 还原
     * @return bool
     * @throws \yii\db\Exception
     */
    public function reduction()
    {
        return $this->batchProcessing('status', static::STATUS_NORMAL);
    }

    /**
     * 个人转团队
     * @return bool
     * @throws \yii\db\Exception
     */
    public function transferTeam()
    {
        $team = \Yii::$app->user->identity->team;
        if (!$team) {
            $this->addError('', '个人转团队，团队唯一标识不能为空不能为空');
            return false;
        }
        //查询将要复制的模板
        $template_data = TemplateMember::find()
            ->where(['template_id' => $this->ids])
            ->andWhere(['user_id' => \Yii::$app->user->id])
            ->andWhere(['status' => TemplateMember::STATUS_NORMAL])
            ->all();
        $data = [];
        $file_data = [];
        foreach ($template_data as $key => $value) {
            $data[] = [
                'classify_id' => $value->classify_id,
                'open_id' => $value->open_id,
                'user_id' => \Yii::$app->user->id,
                'team_id' => $team->id,
                'folder_id' => $this->folder ? $this->folder : 0,
                'cooperation_id' => $value->cooperation_id,
                'title' => $value->title,
                'thumbnail_url' => $value->thumbnail_url,
                'thumbnail_id' => $value->thumbnail_id,
                'status' => 10,
                'is_diy' => $value->is_diy,
                'edit_from' => $value->edit_from,
                'amount_print' => $value->amount_print,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            $file_data[] = $value->thumbnail_id;
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $template_result = \Yii::$app->db->createCommand()->batchInsert(TemplateTeam::tableName(), ['classify_id', 'open_id', 'user_id', 'team_id', 'folder_id', 'cooperation_id', 'title', 'thumbnail_url', 'thumbnail_id', 'status', 'is_diy', 'edit_from', 'amount_print', 'created_at', 'updated_at'], $data)->execute();//执行批量添加
            if (!$template_result) {
                throw new \Exception('无操作执行');
            }
            //更新缓存
            \Yii::$app->dataCache->updateCache(TemplateTeam::class);
            if (count($file_data) != $template_result) {
                throw new \Exception('系统内部错误');
            }
            $create_result = FileCommon::increaseSum($file_data);
            if (!$create_result) {
                throw new \Exception('文件引用记录添加失败');
            }
            $transaction->commit();
            return true;
        } catch
        (\Throwable $e) {
            $transaction->rollBack();
            $this->addError('', $e->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function batchProcessing($key, $value)
    {
        if ($this->table) {
            $transaction = \Yii::$app->getDb()->beginTransaction();
            try {
                $this->_condition = array_merge($this->_condition, ['template_id' => $this->ids]);
                $result = \Yii::$app->db->createCommand()->update(($this->table)::tableName(), [$key => $value], $this->_condition)
                    ->execute();
                if (!$result) {
                    throw new \Exception('无操作执行');
                }
                //更新缓存
                \Yii::$app->dataCache->updateCache($this->table);
                if ($this->type == static::RECYCLE_BIN || $this->type == static::REDUCTION) {
                    $file_data = ($this->table)::find()->where(['id' => $this->ids])->all();
                    $file = [];
                    foreach ($file_data as $key => $value) {
                        $file[] = $value->file_id;
                    }
                    if (!$file || count($file) != $result) {
                        throw new \Exception('系统内部错误');
                    }
                    //素材到回收站的同时，同时删除对应的文件引用记录,还原时，增加文件引用记录
                    $file_result = $this->type == static::RECYCLE_BIN ? FileCommon::reduceSum($file) : FileCommon::increaseSum($file);
                    if (!$file_result) {
                        throw new \Exception('文件引用记录操作失败');
                    }
                }
                $transaction->commit();
                return true;
            } catch
            (\Throwable $e) {
                $transaction->rollBack();
                $this->addError('', $e->getMessage());
                return false;
            }
        }
        $this->addError('', '操作失败');
        return false;
    }

    /**
     * @return bool|string
     */
    public function getTable()
    {
        if ($this->_table === null) {
            $user = \Yii::$app->user->identity;
            if ($user->team) {
                //团队
                $this->_condition = ['team_id' => $user->team->id];
                $tableModel = TemplateTeam::class;
                $this->_folderModel = FolderTemplateTeam::class;
            } else {
                //个人
                $this->_condition = ['user_id' => \Yii::$app->user->id];
                $tableModel = TemplateMember::class;
                $this->_folderModel = FolderMaterialMember::class;
            }
            if ($this->type == 2) {
                /** @var FolderMaterialMember|FolderTemplateTeam $this ->_folderModel */
                $folder = ($this->_folderModel)::find()->where(['id' => $this->folder, 'status' => static::STATUS_NORMAL])->andWhere($this->_condition)->one();
                if (!$folder) {
                    $this->addError('folder', '目标文件夹不存在');
                    $tableModel = false;
                }
            }
            $this->_table = $tableModel;
        }
        return $this->_table;
    }
}