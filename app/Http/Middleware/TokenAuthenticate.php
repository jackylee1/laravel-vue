<?php
/**
 * @author wangzilong@fosun.com
 */

namespace App\Http\Middleware;

use App\Services\TokenService;
use Closure;
use Illuminate\Http\Response;

/**
 * Class OAuthAuthenticate.
 */
class TokenAuthenticate
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
        $context = TokenService::injectRequest($request, true);
        if (empty($context)) {
            return Response::create([])->setStatusCode(Response::HTTP_UNAUTHORIZED, urlencode('未授权，请先登录'));
        }
        $request->context = $context;

        return $next($request);
    }
}
