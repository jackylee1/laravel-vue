<?php

namespace App\Http\Middleware;

use Closure;

class Rbac
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!config('app.rbac_enabled')) {
            return $next($request);
        }
        $actionName = $request->route()->getActionName();
        $action     = str_replace("App\\Http\\Controllers\\Admin\\", '', $actionName);
        $action     = str_replace("@create", '@store', $action);
        $action     = str_replace("@edit", '@update', $action);
        $user       = $request->user;
        if ($user) {
            // 如果用户已登录
            if (!$user->can($action)) {
                return response()->json(['error' => true, 'message' => '权限不足,请联系管理员！'])->setStatusCode(403);
            }
        }

        return $next($request);
    }
}
