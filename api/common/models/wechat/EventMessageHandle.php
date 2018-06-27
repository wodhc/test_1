<?php

namespace api\common\models\wechat;

use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use Yii;
use common\models\MemberOauth;
use common\models\forms\RegisterForm;
use yii\web\BadRequestHttpException;

class EventMessageHandle extends BaseMessageHandle
{
    const SCENE_LOGIN = 'login';
    /** @var int 登录缓存时间 */
    const LOGIN_DURATION = 1800;

    /** @var array 二维码参数数组 */
    private $_scene;

    /**
     * 用户关注事件
     * @return string
     */
    public function eventSubscribe()
    {
        $subscribe_string = 'hi,等你好久啦！'. "\n".'认识帮主，设计不用愁。'. "\r\n" .'为您提供名片、宣传单、PPT、新媒体、电商等5大类40多个应用场景的精美模板。'. "\r\n" .'拖拉拽，秒出图。'. "\n" .'5分钟搞定设计'. "\r\n" .'登录图帮主电脑网页版极速体验吧~'."\r\n".'欢迎加入图帮主服务群：188123416，一起来玩转设计吧~'. "\r\n\r\n". '点击→“<a href="http://mp.weixin.qq.com/s/4uSPVB6vVwotywkpzQuBNA">这里</a>”挑选符合自己的女神定义，昭告世界你的“女神态度”';

        // 发送关注消息
        try {
            $this->app->customer_service->message(new Text($subscribe_string))->to($this->fromUserName)->send();
        } catch (\Exception $e) {}

        // 扫描带参数二维码关注
        if ($this->isSceneLogin()) {
            $this->autoLogin();
        }
        return '';
    }

    /**
     * 处理扫码动作
     * @return string
     */
    public function eventScan()
    {
        if ($this->isSceneLogin()) {
            $this->autoLogin();
        }
        return '';
    }


    /**
     * 判断是否是扫码登录
     * @return bool
     */
    public function isSceneLogin()
    {
        return in_array(static::SCENE_LOGIN, $this->parseScene());
    }

    /**
     * 扫码自动注册
     * @return bool|\common\models\Member
     */
    private function autoLogin()
    {
        // 如果没有登录的话，就自动注册账号
        if (Yii::$app->user->isGuest) {
            // 用户注册
            $model = new RegisterForm(['scenario' => RegisterForm::SCENARIO_OAUTH]);
            if (!($member = $model->register([
                'username' => $this->wechatInfo->nickname,
                'sex' => (int)$this->wechatInfo->sex,
                'oauth_name' => MemberOauth::OAUTH_WECHAT,
                'oauth_key' => $this->wechatInfo->unionid,
                'headimgurl' => $this->wechatInfo->headimgurl,
            ]))) {
                return false;
            }
        }


        // 确认已经登录，记录缓存
        if (!Yii::$app->user->isGuest) {
            $cache = Yii::$app->cache;
            $cacheKey = [
                $this->ticket,
            ];
            if (!$cache->set($cacheKey, $this->wechatInfo->unionid, static::LOGIN_DURATION)) {
                return false;
            }
        }

        // 发送登录成功消息
        $description = "您已成功使用微信登录图帮主\r\n登录账号：". $this->wechatInfo->nickname ."\r\n登录时间：". date('Y-m-d H:i:s');
        $message = new NewsItem(['title'=>'登录提醒', 'description'=> $description, 'url'=>'']);
        try {
            $this->app->customer_service->message(new News([$message]))->to($this->fromUserName)->send();
        } catch (\Exception $exception) {
            $exception->getMessage();
        }
        return true;
    }

    /**
     * 处理二维码参数
     * @return array
     * @internal
     */
    private function parseScene()
    {
        if ($this->_scene === null) {
            $tmp = explode('_', $this->eventKey);
            $this->_scene = $tmp;
            unset($tmp);
        }
        return $this->_scene;
    }


}