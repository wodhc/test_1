<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace common\models\forms;

use common\components\traits\ModelAttributeTrait;
use common\components\vendor\Model;
use Yii;
use common\components\traits\ModelErrorTrait;
use common\extension\Code;
use common\models\TemplateMember;
use common\models\TemplateOfficial;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * 模板保存接口
 * @property TemplateOfficial|TemplateMember $templateModel
 * @package common\models\forms
 * @author thanatos <thanatos915@163.com>
 */
class TemplateForm extends Model
{
    use ModelErrorTrait;
    use ModelAttributeTrait;

    /** @var string 官方模板保存 */
    const METHOD_SAVE_OFFICIAL = 'official';
    /** @var string 用户模板保存 */
    const METHOD_SAVE_MEMBER = 'member';

    /** @var string 保存方式 (用户还是官方) */
    public $method;
    public $template_id;
    public $classify_id;
    public $title;
    public $price;
    public $virtual_edit;
    public $virtual_view;
    public $virtual_favorite;
    public $sort;
    public $is_diy;
    public $edit_from;
    public $content;
    public $folder_id;
    public $team_id;
    public $_templateModel;

    public function rules()
    {
        return [
            [['cooperation_id', 'price', 'virtual_edit', 'virtual_view', 'virtual_favorite', 'sort', 'is_diy', 'edit_from', 'is_team','folder_id','team_id'], 'default', 'value' => 0],
            [['template_id'], 'integer'],
            ['template_id', 'validateTemplateId'],
            // 后台保存验证标题和分类
            [['title', 'classify_id'], 'required', 'on' => static::SCENARIO_BACKEND],
            [['method'], 'required'],
            [['content'], 'default', 'value' => ''],
            ['method', 'default', 'value' => static::METHOD_SAVE_MEMBER],
            [['virtual_edit', 'virtual_view', 'virtual_favorite', 'sort'], 'integer'],
            ['method', 'in', 'range' => [static::METHOD_SAVE_MEMBER, static::METHOD_SAVE_OFFICIAL]],
            ['content', 'validateContent']
        ];
    }

    public function validateTemplateId()
    {
        if (empty($this->templateModel)) {
            $this->addError('template_id', Code::SOURCE_NOT_FOUND);
        }
    }

    /**
     * 验证json格式
     * @author thanatos <thanatos915@163.com>
     */
    public function validateContent()
    {
        if (!$this->hasErrors()) {
            if (empty(Json::decode($this->content))) {
                $this->addError('content', Code::TEMPLATE_FORMAT_ERROR);
                return false;
            }
        }
    }

    public function scenarios()
    {
        return [
            static::SCENARIO_FRONTEND => ['title', 'is_diy', 'edit_from', 'content', 'method', 'template_id','folder_id','team_id'],
            static::SCENARIO_BACKEND => ['method', 'title', 'classify_id', 'price', 'virtual_edit', 'virtual_view', 'virtual_favorite', 'sort', 'template_id','folder_id','team_id'],
        ];
    }


    public function submit($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return false;
        }

        // 保存模板数据
        $data = ArrayHelper::merge($this->getUpdateAttributes(), ['user_id' => Yii::$app->user->id]);
        $this->templateModel->load($data, '');
       if (!$this->templateModel->save()) {
            $this->addErrors($this->templateModel->getErrors());
            return false;
        }

        return $this->templateModel;
    }

    /**
     * @return TemplateMember|TemplateOfficial|string
     * @author thanatos <thanatos915@163.com>
     * @internal
     */
    public function getTemplateModel()
    {
        if ($this->_templateModel === null) {
            /** @var TemplateMember|TemplateOfficial $modelClass */
            $modelClass = '';
            switch ($this->method) {
                case static::METHOD_SAVE_OFFICIAL:
                    $modelClass = TemplateOfficial::class;
                    break;
                case static::METHOD_SAVE_MEMBER:
                    $modelClass = TemplateMember::class;
                    break;
                default:
                    $model = '';
            }
            if ($this->template_id) {
                $model = $modelClass::findById($this->template_id);
            } else {
                $model = new $modelClass;
            }
            $this->_templateModel = $model ?: false;
        }
        return $this->_templateModel;
    }


}