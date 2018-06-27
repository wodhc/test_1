<?php

namespace common\models;

use common\components\traits\ModelErrorTrait;
use common\components\traits\ModelFieldsTrait;
use Yii;
use common\components\traits\TimestampTrait;

/**
 * This is the model class for table "{{%share_template}}".
 * @SWG\Definition(type="object", @SWG\Xml(name="ShareTemplate"))
 *
 * @property int $id @SWG\Property(property="id", type="integer", description="")
 * @property int $template_id 个人模板的template_id @SWG\Property(property="templateId", type="integer", description=" 个人模板的template_id")
 * @property int $shared_person 被分享人的user_id @SWG\Property(property="sharedPerson", type="integer", description=" 被分享人的user_id")
 * @property int $authority 权限，10可同步修改，20修改不同步 @SWG\Property(property="authority", type="integer", description=" 权限，10可同步修改，20修改不同步")
 * @property int $created_at 创建日期 @SWG\Property(property="createdAt", type="integer", description=" 创建日期")
 * @property int $updated_at 修改时间 @SWG\Property(property="updatedAt", type="integer", description=" 修改时间")
 * @property int $sharing_person 分享人的id @SWG\Property(property="sharingPerson", type="integer", description=" 分享人的id")
 */
class ShareTemplate extends \yii\db\ActiveRecord
{

    use TimestampTrait;
    use ModelFieldsTrait;
    use ModelErrorTrait;
    /** @var int 可同步修改权限 */
    const EQUALLY_AUTHORITY = 10;
    /** @var int 不可同步修改权限 */
    const LIMITED_AUTHORITY = 20;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%share_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id', 'shared_person', 'authority', 'created_at', 'updated_at', 'sharing_person'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => '模板id',
            'shared_person' => '被分享人',
            'authority' => '是否同步修改权限',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'sharing_person' => '分享人',
        ];
    }
    /**
     * @return array
     */
    public function frontendFields()
    {
        return [
            'id','template_id','shared_person','sharing_person','authority'
        ];
    }
}
