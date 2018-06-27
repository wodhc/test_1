<?php
/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/5/11
 * Time: 13:18
 */

namespace common\models\forms;

use common\models\TbzLetter;
use yii\base\Model;
use common\components\traits\ModelErrorTrait;

;

class MessageForm extends Model
{
    use ModelErrorTrait;

    /** @var int 到回收站状态 */
    const RECYCLE_BIN_STATUS = 7;

    public $title;
    public $description;
    public $type;
    public $sort;
    public $status;
    public $user_id;
    public $subtitle;

    public function rules()
    {
        return [
            [['title', 'description', 'type', 'sort', 'status', 'subtitle'], 'required'],
            [['type', 'status', 'sort', 'user_id'], 'integer'],
            ['user_id', 'default', 'value' => 0],
            [['subtitle'], 'string', 'max' => 200],
            [['description'], 'string', 'max' => 500],
        ];
    }

    /**
     * @return bool|TbzLetter
     * 添加新消息
     */
    public function addMessage()
    {
        if (!$this->validate()) {
            return false;
        }
        //个人消息时，用户的user_id 不能为空
        if ($this->type == 3 && !($this->user_id)) {
            $this->addError('user_id', '个人消息,用户user_id不能为空');
            return false;
        }
        $tbz_letter = new TbzLetter();
        if ($tbz_letter->load($this->attributes, '') && $tbz_letter->save(false)) {
            return $tbz_letter;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool|TbzLetter|null
     * 修改消息
     */
    public function updateMessage($id)
    {
        if (!$id) {
            $this->addError('id', '消息唯一标识不能为空');
            return false;
        }
        if (!$this->validate()) {
            return false;
        }
        if ($this->type == 3 && !($this->user_id)) {
            $this->addError('user_id', '个人消息,用户user_id不能为空');
            return false;
        }
        $tbz_letter = TbzLetter::findOne($id);
        if (!$tbz_letter) {
            $this->addError('', '该消息不存在');
            return false;
        }
        if ($tbz_letter->load($this->attributes, '') && $tbz_letter->save(false)) {
            return $tbz_letter;
        }
        $this->addError('', '修改失败');
        return false;
    }

    /**
     * @param $id
     * @return bool
     * 删除消息
     */
    public function deleteMessage($id)
    {
        $message = TbzLetter::findOne($id);
        if (!$message) {
            $this->addError('id', '该消息不存在');
        }
        $message->status = static::RECYCLE_BIN_STATUS;
        if ($message->save(false)) {
            return true;
        }
        $this->addError('', '删除失败');
        return false;
    }
}