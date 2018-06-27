<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace console\controllers;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{

    /**
     * 初始化rbac
     * @throws \Exception
     * @author thanatos <thanatos915@163.com>
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        $permission = [
            ['name' => 'createClassify', 'description' => '创建分类'],
            ['name' => 'updateClassify', 'description' => '修改分类'],
            ['name' => 'createOfficialTemplate', 'description' => '创建官方模板'],
            ['name' => 'updateOfficialTemplate', 'description' => '修改官方模板'],
            ['name' => 'createOfficialMaterial', 'description' => '创建官方素材'],
            ['name' => 'updateOfficialMaterial', 'description' => '修改官方素材'],
            ['name' => 'updateMemberTemplate', 'description' => '修改用户模板'],
            ['name' => 'viewOrder', 'description' => '查看订单'],
            ['name' => 'updateOrder', 'description' => '修改订单'],
            ['name' => 'viewMember', 'description' => '查看用户'],
            ['name' => 'updateMember', 'description' => '修改用户'],
            ['name' => 'updateSystem', 'description' => '系统设置'],
            ['name' => 'topicManager', 'description' => '模板专题管理'],
            ['name' => 'createQrcode', 'description' => '创建二维码'],
            ['name' => 'updateQrcode', 'description' => '修改二维码'],
            ['name' => 'viewStatistics', 'description' => '查看统计'],
            ['name' => 'cacheManager', 'description' => '缓存管理'],
        ];

        // 添加权限
        foreach ($permission as $k => $value) {
            ${$value['name']} = $post = $auth->createPermission($value['name']);
            $post->description = $value['description'];
            $auth->add($post);
        }

        // 添加角色
        $role = [
            ['name' => 'systemAdmin', 'description' => '系统管理员'],
            ['name' => 'customerService', 'description' => '客服'],
            ['name' => 'designer', 'description' => '设计师'],
            ['name' => 'finance', 'description' => '财务'],
        ];

        foreach ($role as $k => $value) {
            $role = $auth->createRole($value['name']);
            $role->description = $value['description'];
            $auth->add($role);
            // 分配权限
            switch ($value['name']) {
                case 'systemAdmin':
                    foreach ($permission as $per) {
                        $auth->addChild($role, ${$per['name']});
                    }
                    break;
                case 'customerService':
                    $auth->addChild($role, $viewOrder);
                    $auth->addChild($role, $updateOrder);
                    $auth->addChild($role, $updateMemberTemplate);
                    $auth->addChild($role, $viewMember);
                    $auth->addChild($role, $updateMember);
                    break;
                case 'designer':
                    $auth->addChild($role, $createOfficialTemplate);
                    $auth->addChild($role, $updateOfficialTemplate);
                    $auth->addChild($role, $createOfficialMaterial);
                    $auth->addChild($role, $updateOfficialMaterial);
                    $auth->addChild($role, $topicManager);
                    $auth->addChild($role, $createQrcode);
                    $auth->addChild($role, $updateQrcode);
                    $auth->addChild($role, $createClassify);
                    $auth->addChild($role, $updateClassify);
                    break;
                case 'finance':
                    $auth->addChild($role, $viewOrder);
                    $auth->addChild($role, $updateOrder);
                    $auth->addChild($role, $viewStatistics);
                    break;

            }
        }


    }

}