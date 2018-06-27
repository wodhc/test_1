<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/19
 * Time: 10:06
 */

namespace common\models\forms;

use common\models\MyFavoriteMember;
use common\models\MyFavoriteTeam;
use common\models\TemplateOfficial;
use yii\base\Model;
use common\components\traits\ModelErrorTrait;
use common\components\traits\ModelAttributeTrait;

class MyFavoriteForm extends Model
{
    use ModelErrorTrait;
    use ModelAttributeTrait;

    /** @var int 到回收站状态 */
    const RECYCLE_BIN_STATUS = 7;
    /** @var int 删除状态 */
    const DELETE_STATUS = 3;


    public $template_id;
    public $user_id;
    public $team_id;
    public $id;


    private $_tableModel;
    private $_condition;

    public function rules()
    {
        return [
            [['template_id'], 'required', 'on' =>'create'],
            [['template_id','id'], 'integer','on'=>['create','delete']],
            ['id','required','on'=>'delete']
        ];
    }
    public function scenarios(){
        return [
            'create' => ['template'],
            'delete' => ['id'],
        ];
    }
    /** 添加收藏
     * @return bool
     * @throws \yii\db\Exception
     */
    public function addMyFavorite()
    {
        if (!$this->validate()) {
            return false;
        }
        if (!$model = $this->tableModel) {
            return false;
        }
        $template_model = TemplateOfficial::findOne(['template_id' => $this->template_id, 'status' => TemplateOfficial::STATUS_ONLINE]);
        if (!$template_model) {
            $this->addError('', '收藏的模板不存在');
            return false;
        }
        $model->load($this->getUpdateAttributes(), '');
        $model->user_id = \Yii::$app->user->id;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //保存收藏
            $model->save();
            //增加模板的收藏量
            $template_model->updateCounters(['amount_favorite' => 1]);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('', '收藏失败');
            return false;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->addError('', '收藏失败');
            return false;
        }
        return true;
    }

    /**
     * 删除收藏
     * @param $id
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteMyFavorite()
    {
        if (!$model = $this->tableModel) {
            $this->addError('id','该收藏不存在');
            return false;
        }
        if ($model->delete()) {
            return true;
        }
        $this->addError('', '删除失败');
        return false;
    }

    /**
     * 获取模型
     * @return bool|MyFavoriteMember|MyFavoriteTeam
     */
    public function getTableModel()
    {
        if ($this->_tableModel === null) {
            $user = \Yii::$app->user->identity;
            if ($user->team) {
                //团队
                $this->team_id = $user->team->id;
                $tableModel = MyFavoriteTeam::class;
                $this->_condition = ['team_id' => $user->team->id];
            } else {
                //个人
                $tableModel = MyFavoriteMember::class;
                $this->_condition = ['user_id' => \Yii::$app->user->id];
            }
            /** @var MyFavoriteTeam|MyFavoriteMember $tableModel */
            if ($this->id) {
                $tableModel = $tableModel::findOne(array_merge(['id' => $this->id], $this->_condition)) ?: false;
            } else {
                $is_favorite = $tableModel::findOne(array_merge(['template_id' => $this->template_id], $this->_condition));
                if ($is_favorite) {
                    $this->addError('template', '收藏已存在，无需重复收藏');
                    $tableModel = false;
                } else {
                    $tableModel = new $tableModel;
                }

            }
            $this->_tableModel = $tableModel;
        }
        return $this->_tableModel;
    }
}