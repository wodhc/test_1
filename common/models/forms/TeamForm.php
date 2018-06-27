<?php

namespace common\models\forms;

use common\components\traits\ModelAttributeTrait;
use common\models\FileCommon;
use common\models\Team;
use common\models\TeamMember;
use yii\base\Model;
use common\components\traits\ModelErrorTrait;

class TeamForm extends Model
{
    use ModelErrorTrait;
    use ModelAttributeTrait;
    /** @var int 允许最大创建数量 */
    const MAX_TEAM_NUMBER = 5;
    /** @var int 创建者角色 */
    const CREATED_ROLE = 1;
    /** @var int 普通成员角色 */
    const MEMBER_ROLE = 4;
    /** @var string 默认头像 */
    const DEFAULT_MARK = 'dXBsb2Fkcw==/other/201805/C4U24nXDmTJjLL9mfFFB.jpeg';
    /** @var int 默认头像的文件id */
    const DEFAULT_FILE = 1;

    public $id;
    public $team_name;
    public $founder_id;
    public $team_mark;
    public $file_id;
    public $status;

    public $color;
    public $font;
    public $operation_type;

    public function rules()
    {
        return [
            [['id', 'operation_type', 'file_id', 'status'], 'integer', 'on' => ['create', 'update', 'delete', 'operation']],
            [['team_name'], 'string', 'max' => 100, 'on' => ['create', 'update']],
            [['team_mark'], 'string', 'max' => 200, 'on' => ['create', 'update']],
            [['color', 'font'], 'string', 'on' => ['operation']],
            [['team_name'], 'required', 'on' => ['create']],
            ['id', 'required', 'on' => ['update', 'delete', 'operation']],
            ['status', 'required', 'on' => 'delete']
        ];
    }

    public function scenarios()
    {
        return [
            'create' => ['team_name', 'team_mark', 'file_id'],
            'update' => ['team_name', 'team_mark', 'file_id', 'id'],
            'delete' => ['id', 'status'],
            'operation' => ['id', 'color', 'font']
        ];
    }

    /**
     * 创建团队
     * @return bool|Team
     */
    public function editTeam($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }
        if ($this->id) {
            $team_model = Team::findOne(['id' => $this->id]);
            if ($team_model->founder_id != \Yii::$app->user->id) {
                $this->addError('', '非团队创建者无权操作团队信息');
            }
        } else {
            if (!$this->isBeyondLimit()) {
                $this->addError('', '同一用户只能创建最多5个团队');
                return false;
            }
            $team_model = new Team();
            if (!$this->team_mark) {
                //团队头像为默认头像
                $this->team_mark = static::DEFAULT_MARK;
                $this->file_id = static::DEFAULT_FILE;
            }
            $this->founder_id = \Yii::$app->user->id;
        }
        $team_model->load($this->getUpdateAttributes(), '');
        //新创建团队只添加文件引用记录
        if ($is_new = $team_model->isNewRecord) {
            $create_file = $team_model->file_id;
        }
        //修改团队头像先删除文件引用记录，再添加文件引用记录
        if ($team_model->isAttributeChanged('team_mark') && $team_model->isAttributeChanged('file_id')) {
            $drop_file = $team_model->getOldAttribute('file_id');
            $create_file = $team_model->file_id;
        }
        //删除团队时，只删除文件引用记录
        if ($team_model->isAttributeChanged('status') && $team_model->status == Team::RECYCLE_BIN_STATUS) {
            $drop_file = $team_model->getOldAttribute('file_id');
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            if (!($team_model->validate() && $team_model->save())) {
                throw new \Exception('团队操作失败' . $team_model->getStringErrors());
            }
            //删除文件记录
            if ($drop_file) {
                $drop_result = FileCommon::reduceSum($drop_file);
                if (!$drop_result) {
                    throw new \Exception('原文件引用记录删除失败');
                }
            }
            //创建文件记录
            if ($create_file) {
                $create_result = FileCommon::increaseSum($create_file);
                if (!$create_result) {
                    throw new \Exception('新文件引用记录添加失败');
                }
            }
            //新创建团队把创建者信息存入团队会员表
            if ($is_new) {
                $team_member_model = new TeamMember();
                $team_member_model->user_id = \Yii::$app->user->id;
                $team_member_model->team_id = $team_model->id;
                $team_member_model->role = static::CREATED_ROLE;         //创建者角色
                if (!($team_member_model->validate() &&$team_member_model->save())){
                    throw new \Exception('新文件引用记录添加失败'. $team_member_model->getStringErrors());
                }
            }
            $transaction->commit();
            return $team_model;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->addError('', $e->getMessage());
            return false;
        }
    }

    /**
     * 添加、剔除颜色或字体
     * @return bool|Team|null
     */
    public function operation()
    {
        if (!$this->validate()) {
            return false;
        }
        $team_model = Team::findOne(['id' => $this->id, 'status' => Team::NORMAL_STATUS]);
        if (!$team_model) {
            $this->addError('', '当前团队不存在');
            return false;
        }
        if ($this->color) {
            $colors = explode(',', $team_model->colors);
            if ($this->operation_type == 1) {
                //剔除团队颜色
                $colors = array_diff($colors, [$this->color]);
            } else {
                //添加颜色
                array_push($colors, $this->color);
            }
            $team_model->colors = trim(implode(',', $colors), ',');
        }
        if ($this->font) {
            $fonts = explode(',', $team_model->fonts);
            if ($this->operation_type == 1) {
                //剔除团队字体
                $fonts = array_diff($fonts, [$this->font]);
            } else {
                //添加团队字体
                array_push($fonts, $this->font);
            }
            $team_model->fonts = trim(implode(',', $fonts), ',');
        }
        if ($team_model->save()) {
            return $team_model;
        }
        return false;
    }

    /**
     * 判断是否超出最大创建团队的数量
     * @return bool
     */
    public function isBeyondLimit()
    {
        $number = $count = (new \yii\db\Query())
            ->from(Team::tableName())
            ->where(['founder_id' => \Yii::$app->user->id, 'status' => Team::NORMAL_STATUS])
            ->count();
        if ($number >= static::MAX_TEAM_NUMBER) {
            return false;
        }
        return true;
    }

    public function getUserAuthority()
    {

    }
}