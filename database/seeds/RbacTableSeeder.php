<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class RbacTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $routes = Route::getRoutes()->getRoutes();
        foreach ($routes as $route) {
            try {
                $tmpAction = $route->action['controller']; // 获取系统所有controller
            } catch (Exception $e) {
                continue;
            }
            $str = "App\\Http\\Controllers\\Admin\\";// 过滤后台需要控制的模块
            if (strpos($tmpAction, $str) === false || strpos($tmpAction, 'create') || strpos($tmpAction, 'edit')) {
                // create/store只保留store, edit/update只保留update
                continue;
            }
            $tmpAction       = str_replace($str, '', $tmpAction);
            $parent          = substr($tmpAction, 0, strpos($tmpAction, '@'));// 初始化层级关系
            $data[$parent][] = [
                'name'         => $tmpAction,
                'display_name' => $this->getNameByAction($tmpAction)
            ];
        }
        foreach ($data as $parent => $item) {
            $tmp  = [
                'name'         => $parent,
                'display_name' => $parent
            ];
            $perm = Permission::where('name', $parent)->first();
            if (!$perm) {
                $perm = Permission::create($tmp);
            }
            foreach ($item as $val) {
                $val['parent_id'] = $perm->id;
                if (!Permission::where('name', $val['name'])->first()) {
                    Permission::create($val);
                }
            }
        }
        $this->init();
    }

    private function getNameByAction($action)
    {
        $action = substr($action, strpos($action, '@') + 1);
        switch ($action) {
            case 'index':
                $name = '列表';
                break;
            case 'update':
                $name = '更新';
                break;
            case 'store':
                $name = '添加';
                break;
            case 'show':
                $name = '详情';
                break;
            case 'destroy':
                $name = '删除';
                break;
            default:
                $name = $action;
                break;
        }

        return $name;
    }

    /**
     * 初始化超级管理员(id为1)权限
     */
    private function init()
    {
        if (RoleUser::first()) {
            // 如果有数据，则不填充
            return;
        }
        $role  = [
            'name'         => 'super_admin',
            'display_name' => '超级管理员',
            'description'  => '拥有系统所有权限，并且可以分配其他管理员权限'
        ];
        $role  = Role::create($role);
        $perms = Permission::all();
        $role->attachPermissions($perms);

        if (!$user = User::find(1)) {
            $user = User::create([
                'name'     => 'admin',
                'password' => bcrypt('admin'),
                'email'    => 'admin@admin.com',
            ]);
        }
        $user->attachRole($role);
    }
}

