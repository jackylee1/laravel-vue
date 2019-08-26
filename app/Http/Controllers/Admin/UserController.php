<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
    {
        $users = User::with('roles')->get();

        return $this->success($users);
    }

    public function store()
    {
        $data             = request()->all();
        $data['password'] = bcrypt($data['password']);
        try {
            $return['data'] = User::create($data);

            return $this->success($return);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_DUPLICATE, '用户已存在');
        }
    }

    public function update($id)
    {

        try {
            $data             = request()->all();
            $data['password'] = bcrypt($data['password']);

            User::where('id', $id)->update($data);

            return $this->success();
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, '更新失败！');
        }
    }

    public function show($id)
    {
        $user   = User::find($id);
        $return = $user->roles->pluck('id');

        return $this->success($return);
    }

    public function role($id)
    {
        RoleUser::where('user_id', $id)->delete();// 先清空角色
        $roles = Role::whereIn('id', request()->get('roles'))->get();
        $user  = User::find($id);

        $user->attachRoles($roles);
        \Cache::tags(config('entrust.role_user_table'))->flush();

        return $this->success();
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'email|required',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if (!\Hash::check($request->password, $user->password)) {
            return $this->error(ErrorCode::ERR_VALIDATE, '密码不正确');
        }

        $token = TokenService::generate();
        $hash  = TokenService::hash($token);

        $user->tokenSet($token);

        return $this->success($user, [
            config('api.token.http.token') => $token,
            config('api.token.http.hash')  => $hash
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->header(config('api.token.http.token'));
        TokenService::delete($token);
    }
}
