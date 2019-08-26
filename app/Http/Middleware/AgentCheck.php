<?php

namespace App\Http\Middleware;

use Closure;

class AgentCheck
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
        if (!$request->member->agent) {
            // 如果不是代理人，则接口报错，不让用户进入相应的页面

            return response()->json([
                'code'    => 999,
                'message' => '您还不是代理人，请加入代理人后进行查看!'
            ]);
        }

        return $next($request);
    }
}
