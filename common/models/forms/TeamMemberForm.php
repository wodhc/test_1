<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/21
 * Time: 13:30
 */

namespace common\models\forms;
use common\components\traits\ModelAttributeTrait;
use common\models\TeamMember;
use yii\base\Model;
use common\components\traits\ModelErrorTrait;
class TeamMemberForm extends Model
{
    use ModelErrorTrait;
    use ModelAttributeTrait;
    /** @var int 创建者角色 */
    const CREATED_ROLE = 1;
    /** @var int 普通成员角色 */
    const MEMBER_ROLE = 4;
    /** @var int 真删除状态 */
    const DELETE_STATUS = 3;
    public $status;
    public $user_id;
    public $team_id;
    public $role;
    public function rules()
    {
        return [
            [['status', 'team_id', 'user_id', 'role'], 'integer','on'=>['create','update','delete']],
            [['role'],'required','on'=>['update']],
            ['user_id','required','on'=>['create','update','delete']],
            ['status','required','on'=>['delete']],
        ];
    }
    public function scenarios()
    {
        return [
            'create' => ['user_id', 'role','team_id'],
            'update' => ['user_id', 'role','team_id'],
            'delete' => ['user_id', 'status','team_id'],
        ];
    }
    /**
     * 添加成员
     * @return bool|TeamMember
     */
    public function editMember($params)
    {
        $this->load($params,'');
        if (!$this->validate()) {
            return false;
        }
        if (!$team = \Yii::$app->user->identity->team){
            $this->addError('team','团队信息错误');
        }
        $this->team_id = $team->id;
        if ($team->members[0]->role != static::CREATED_ROLE){
            $this->addError('','当前用户无权对成员进行操作');
            return false;
        }
        $model = TeamMember::findOne(['user_id'=>$this->user_id,'team_id'=>$this->team_id]);
        if (!$model){
            $model = new TeamMember();
            //默认新添加成员的角色为普通成员角色
            $this->role = static::MEMBER_ROLE;
            //默认新添加的成员状态为正常状态
            $this->status = TeamMember::NORMAL_STATUS;
        }
        if ($this->status && $this->status == static::DELETE_STATUS){
            /** 删除成员 */
            if ($model->delete()){
                return true;
            }
        }else{
            /** 添加或修改成员信息 */
            $model->load($this->getUpdateAttributes(),'');
            if ($model->validate() && $model->save()){
                return $model;
            }
            $this->addError('',$model->getStringErrors());
        }
        return false;
    }
}