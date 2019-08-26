<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\Request;

class AuthenticateController extends Controller
{

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
