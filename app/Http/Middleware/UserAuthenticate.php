<?php
/**
 * @author wangzilong@fosun.com
 */

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Response;

/**
 * Class OAuthAuthenticate.
 */
class UserAuthenticate
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $scopes
     * @return mixed
     */
    public function handle($request, Closure $next, $scopes = null)
    {
        $user = $request->context[User::getTableName()] ?? null;
        if (empty($user)) {
            return Response::create([])->setStatusCode(Response::HTTP_UNAUTHORIZED, urlencode('尚未登录，请先登录'));
        }
        $request->user = $user;

        return $next($request);
    }
}
