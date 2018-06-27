<?php

namespace console\models;

use common\models\Member;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * 官方模版模型类
 *
 * @property integer $id
 * @property integer $uid
 * @property string $title
 * @property string $seo_title
 * @property integer $coop_id
 * @property string $product
 * @property string $keywords
 * @property string $description
 * @property string $style_ids
 * @property string $industry_ids
 * @property string $function_ids
 * @property integer $layout_mode
 * @property string $edit_config
 * @property integer $price_coin
 * @property string $thumbnail
 * @property string $thumbnail_back
 * @property string $thumbnail_help
 * @property integer $save_from_tpl
 * @property integer $num_edit
 * @property integer $num_edit_virtual
 * @property integer $num_edit_total
 * @property integer $num_view
 * @property integer $num_view_virtual
 * @property integer $num_view_total
 * @property integer $num_fav
 * @property integer $num_fav_virtual
 * @property integer $num_fav_total
 * @property integer $num_buy
 * @property string $relate_subject
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $sort
 * @property integer $status
 * @property integer $thumbnail_updated_at
 * @property integer $thumbnail_back_updated_at
 * @property integer $recommend_at
 * @property TemplatePage[] $pages
 */
class OfficialTemplate extends \yii\db\ActiveRecord
{
    /** 状态: 下线*/
    const STATUS_OFFLINE = -1;
    /** 状态: 编辑中*/
    const STATUS_EDITING = 0;
    /** 状态: 上线*/
    const STATUS_ONLINE = 1;
    /** 状态: 删除*/
    const STATUS_DELETE = -2;
    
    //缓存的key
    const CACHE_KEY = 'updated_at';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%official_template}}';
    }

    public static function getDb()
    {
        return Yii::$app->dbMigrateTbz;
    }

    public function behaviors(){
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ]
            ]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'uid', 'coop_id', 'layout_mode', 'price_coin', 'save_from_tpl', 'num_edit', 'num_edit_virtual', 'num_edit_total', 'num_view', 'num_view_virtual', 'num_view_total', 'num_fav', 'num_fav_virtual', 'num_fav_total', 'num_buy', 'created_at', 'updated_at', 'sort', 'status', 'thumbnail_updated_at', 'thumbnail_back_updated_at', 'recommend_at'], 'integer'],
            [['edit_config'], 'string'],
            [['title', 'seo_title'], 'string', 'max' => 50],
            [['product'], 'string', 'max' => 60],
            [['keywords', 'description', 'style_ids', 'industry_ids', 'function_ids'], 'string', 'max' => 150],
            [['thumbnail', 'thumbnail_back', 'thumbnail_help', 'relate_subject'], 'string', 'max' => 255],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'title' => 'Title',
            'seo_title' => 'Seo Title',
            'coop_id' => 'Coop ID',
            'product' => 'Product',
            'keywords' => 'Keywords',
            'description' => 'Description',
            'style_ids' => 'Style Ids',
            'industry_ids' => 'Industry Ids',
            'function_ids' => 'Function Ids',
            'layout_mode' => 'Layout Mode',
            'edit_config' => 'Edit Config',
            'price_coin' => 'Price Coin',
            'thumbnail' => 'Thumbnail',
            'thumbnail_back' => 'Thumbnail Back',
            'thumbnail_help' => 'Thumbnail Help',
            'save_from_tpl' => 'Save From Tpl',
            'num_edit' => 'Num Edit',
            'num_edit_virtual' => 'Num Edit Virtual',
            'num_edit_total' => 'Num Edit Total',
            'num_view' => 'Num View',
            'num_view_virtual' => 'Num View Virtual',
            'num_view_total' => 'Num View Total',
            'num_fav' => 'Num Fav',
            'num_fav_virtual' => 'Num Fav Virtual',
            'num_fav_total' => 'Num Fav Total',
            'num_buy' => 'Num Buy',
            'relate_subject' => 'Relate Subject',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'sort' => 'Sort',
            'status' => 'Status',
            'thumbnail_updated_at' => 'Thumbnail Updated At',
            'thumbnail_back_updated_at' => 'Thumbnail Back Updated At',
            'recommend_at' => 'Recommend At',
        ];
    }
    
    public function fields(){
        return [
            'id', 'uid', 'title' => 'seo_title', 'product', 'num_edit_total', 'num_fav_total', 'num_view_total', 'price_coin',
            'edit_config' => function(){
                return json_decode($this->edit_config, true);
            },
            'thumbnail' => function(){
                return Url::to('@oss') . $this->thumbnail;
            },
        ];
    }

    /**
     * @return yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['id' => 'uid' ]);
    }
    /**
     * @param     $product
     * @param int $coopId
     * @return static[]
     */
    public static function findByProduct($product,$coopId=0){
        return static::findAll(['product'=>$product,'coop_id'=>$coopId,'status'=>static::STATUS_ONLINE]);
    }
    
    /**
     * 通过关键词关联模板风格、行业查找模板
     * @param $tagName
     * @param $product
     * @return array
     */
    public static function getOfficalTemplateByTagName($tagName,$product){
        $rows = static::findByProduct($product);
        $rows = ArrayHelper::toArray($rows);
        $tplOffical = array();
        $i=0;
        foreach ($rows as $row) {
            //匹配模板风格
            if ($row['style_ids']) {
                $styleArray =  explode(',', $row['style_ids']);
                $styleData = TemplateStyle::findOne(['name'=>$tagName]);
                if (in_array($styleData->id,$styleArray)) {
                    $tplOffical[] = $row;
                }
            }
            //匹配模板行业
            if ($row['industry_ids']) {
                $industryArray =  explode(',', $row['industry_ids']);
                $industryData = TemplateIndustry::findOne(['name'=>$tagName]);
                if (in_array($industryData->id,$industryArray)) {
                    $tplOffical[] = $row;
                }
            }
        }
        return  $tplOffical;
    }
    
    /**
     *
     * @param $ids
     * @return array
     */
    public static function getOfficalDetailDataByIds($ids,$product=''){
        $tplData = static::find()->where(['and',['in','id',$ids],'status=:status'])->params([':status'=>static::STATUS_ONLINE])->andFilterWhere(['product'=>$product])->all();
        $rows = [];
        if(!empty($tplData)){
            foreach($tplData as $object){
                $data = ArrayHelper::toArray($object);
                $thumb = ImageHelper::makeImageThumbnail($data['thumbnail']);
                $data['imgNail'] = $thumb['file'];
                $data['width'] = $thumb['w'] ? $thumb['w'] : 228;
                $data['height'] = $thumb['h']? $thumb['h'] : 486;
                $data['price'] = $object->price_coin;
                $data['uid'] = $object->uid;
                $data['name'] = $object->member->getUsername();
                $data['created'] = date('Y-m-d',$object->created_at);
                $rows[] = $data;
            }
        }
        return $rows;
    }


    public function getPages()
    {
        return $this->hasMany(TemplatePage::className(), ['tpl_id' => 'id'])->onCondition(['status' => TemplatePage::STATUS_NORMAL]);
    }
    
}
