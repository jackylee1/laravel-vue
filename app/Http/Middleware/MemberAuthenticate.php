<?php
/**
 * @author wangzilong@fosun.com
 */

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Http\Response;

/**
 * Class OAuthAuthenticate.
 */
class MemberAuthenticate
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
        $member = $request->context[Member::getTableName()] ?? null;
        if (empty($member)) {
            return Response::create([])->setStatusCode(Response::HTTP_UNAUTHORIZED, urlencode('尚未登录，请先登录'));
        }
        $request->member = $member;

        return $next($request);
    }
}
