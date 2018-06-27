<?php

/**
 * Created by PhpStorm.
 * User: IT07
 * Date: 2018/6/4
 * Time: 8:50
 */

namespace common\models\forms;

use common\components\traits\ModelErrorTrait;
use common\models\FileCommon;
use common\models\Member;
use common\models\ShareTemplate;
use common\models\TemplateMember;
use Monolog\Handler\IFTTTHandler;

class ShareTemplateForm extends \yii\base\Model
{
    use ModelErrorTrait;
    /** @var int 默认文件夹 */
    const DEFAULT_FOLDER = 0;

    public $shared_person;
    public $authority;
    public $template_id;
    public $sharing_person;

    public function rules()
    {
        return [
            [['shared_person', 'authority', 'template_id'], 'required'],
            [['shared_person', 'template_id'], 'integer'],
            ['authority', 'in', 'range' => [ShareTemplate::EQUALLY_AUTHORITY, ShareTemplate::LIMITED_AUTHORITY]]
        ];
    }

    /**
     * 添加分享
     * @param $params
     * @return bool|TemplateMember
     */
    public function addShare($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }
        $this->sharing_person = \Yii::$app->user->id;
        $template_data = TemplateMember::findOne(['template_id' => $this->template_id, 'user_id' => $this->sharing_person, 'status' => TemplateMember::STATUS_NORMAL]);
        if (!$template_data) {
            $this->addError('template_id', '分享的模板不存在');
            return false;
        }
        $shared_person = Member::findOne(['id'=>$this->shared_person,'status'=>Member::STATUS_NORMAL]);
        if (!$shared_person){
            $this->addError('shared_person','被分享人不存在');
            return false;
        }
        $model = new ShareTemplate();
        $model->load($this->attributes, '');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //当为不可同步修改权限时,复制新的模板信息
            if ($this->authority == 20) {
                $share_model = new TemplateMember();
                $share_model->classify_id = $template_data->classify_id;
                $share_model->open_id = $template_data->open_id;
                $share_model->user_id = $this->shared_person;
                $share_model->folder_id = static::DEFAULT_FOLDER; //分享的模板，放入默认文件夹
                $share_model->cooperation_id = $template_data->cooperation_id;
                $share_model->title = $template_data->title;
                $share_model->thumbnail_id = $template_data->thumbnail_id;
                $share_model->thumbnail_url = $template_data->thumbnail_url;
                $share_model->status = $template_data->status;
                $share_model->is_diy = $template_data->is_diy;
                $share_model->edit_from = $template_data->edit_from;
                $share_model->amount_print = 0; //印刷次数归0
                if (!($share_model->validate() && $share_model->save())) {
                    throw new \Exception('分享失败' . $share_model->getStringErrors());
                }
                //创建文件引用记录
                $create_result = FileCommon::increaseSum($share_model->thumbnail_id);
                if (!$create_result) {
                    throw new \Exception('新文件引用记录添加失败');
                }
                $model->template_id = $share_model->template_id;
            }
            if (!($model->validate() && $model->save())) {
                throw new \Exception('分享失败' . $model->getStringErrors());
            }
            $transaction->commit();
            return $template_data;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('', $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->addError('', $e->getMessage());
            return false;
        }
    }
}