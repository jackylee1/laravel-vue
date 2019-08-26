<?php
/**
 * @author   caojinliang@fosun.com
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;

class RoleController extends Controller
{

    public function index()
    {
        $return = Role::get();

        return $this->success($return);
    }

    public function show($id)
    {
        $ret = PermissionRole::where('role_id', $id)->pluck('permission_id')->toArray();

        return $this->success($ret);
    }

    public function update($id)
    {
        try {
            Role::where('id', $id)->update(request()->all());

            return $this->success();
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, '更新失败！');
        }
    }

    public function store()
    {
        try {
            $return['data'] = Role::create(request()->all());

            return $this->success($return);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, '角色已存在');
        }
    }

    public function permission($id)
    {
        try {
            PermissionRole::where('role_id', $id)->delete();// 先清空权限
            $perms = Permission::whereIn('id', request()->get('perms'))->get();
            $role  = Role::find($id);
            $role->attachPermissions($perms);
            \Cache::tags(config('entrust.permission_role_table'))->flush();// 手动清空缓存

            return $this->success();
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, '分配失败！');
        }
    }
}
