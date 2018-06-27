<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/16
 * Time: 20:08
 */

namespace common\models\forms;

use common\models\TemplateTeam;
use common\models\FolderTemplateMember;
use common\models\FolderTemplateTeam;
use common\models\TemplateMember;
use yii\base\Model;
use common\components\traits\ModelErrorTrait;
use common\components\traits\ModelAttributeTrait;
class FolderTemplateForm extends Model
{
    use ModelErrorTrait;
    use ModelAttributeTrait;
    /* @var integer 正常状态 */
    const STATUS_NORMAL = '10';
    /* @var integer 假删除 */
    const FALSE_DELETE = '7';
    /* @var integer 真删除 */
    const REALLY_DELETE = '3';
    /* @var integer 默认文件夹 */
    const DEFAULT_FOLDER = '0';

    public $name;
    public $color;
    public $user_id;
    public $team_id;
    public $id;

    private $_tableModel;
    private $_condition;
    private $_cacheModel;

    public function rules()
    {
        return [
            [['name', 'color'], 'required', 'when' => function ($model) {
                return empty($model->id);
            }],
            [['name'], 'string', 'max' => 50],
            [['color'], 'string', 'max' => 200],
            ['id', 'integer']
        ];
    }

    /**
     * 编辑、新增模板文件夹（团队和个人）
     * @return bool|mixed
     */
    public function editFolder()
    {
        //验证信息
        if (!$this->validate()) {
            return false;
        }
        if (!$folder = $this->tableModel) {
            return false;
        }
        $folder->load($this->getUpdateAttributes(), '');
        $folder->user_id = \Yii::$app->user->id;
        if ($folder->validate() && $folder->save()) {
            return $folder;
        }
        $this->addErrors($folder->getErrors());
        return false;
    }

    /**
     * 删除文件夹
     * @param $id
     * @return bool
     */
    public function deleteFolder()
    {
        //验证信息
        if (!$folder = $this->tableModel) {
            return false;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->_condition = array_merge($this->_condition, ['folder_id' => $this->id]);
            //把所删文件夹内的模板文件夹改为默认文件夹
            /** @var TemplateTeam|TemplateMember $this ->_cacheModel */
            \Yii::$app->db->createCommand()->update($this->_cacheModel::tableName(), ['folder_id' => static::DEFAULT_FOLDER], $this->_condition)->execute();
            $folder->status = static::FALSE_DELETE;
            $folder->save(false);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('', '删除失败');
            return false;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->addError('', '删除失败');
            return false;
        }
        //更新缓存
        \Yii::$app->dataCache->updateCache($this->_cacheModel);
        return true;
    }

    /**
     * 获取模型
     * @return bool|string
     */
    public function getTableModel()
    {
        if ($this->_tableModel === null) {
            $user = \Yii::$app->user->identity;
            /** @var FolderTemplateTeam|FolderTemplateMember $tableModel */
            if ($user->team) {
                //团队
                $this->team_id = $user->team->id;
                $tableModel = FolderTemplateTeam::class;
                $this->_condition = ['team_id' => $this->team_id];
                $this->_cacheModel = TemplateTeam::class;
            } else {
                //个人
                $tableModel = FolderTemplateMember::class;
                $this->_condition = ['user_id' => \Yii::$app->user->id];
                $this->_cacheModel = TemplateMember::class;
            }
            if ($this->id) {
                $folder = $tableModel::find()->where(['id' => $this->id])->andWhere($this->_condition)->one();
                if (!$folder) {
                    $this->addError('', '操作的文件夹不存在');
                }
            } else {
                $folder = new $tableModel;
            }
            $this->_tableModel = $folder;
        }
        return $this->_tableModel;
    }
}
