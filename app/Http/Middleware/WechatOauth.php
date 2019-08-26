<?php

namespace App\Http\Middleware;

use App\Models\Member;
use App\Models\Wechat;
use App\Services\TokenService;
use Closure;
use Event;
use Overtrue\LaravelWeChat\Events\WeChatUserAuthorized;
use Overtrue\LaravelWeChat\Middleware\OAuthAuthenticate;

class WechatOauth extends OAuthAuthenticate
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $scopes
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $account = 'default', $scopes = null)
    {
        // $account 与 $scopes 写反的情况
        if (is_array($scopes) || (\is_string($account) && str_is('snsapi_*', $account))) {
            list($account, $scopes) = [$scopes, $account];
            $account || $account = 'default';
        }

        $isNewSession    = false;
        $sessionKey      = \sprintf('wechat.oauth_user.%s', $account);
        $config          = config(\sprintf('wechat.official_account.%s', $account), []);
        $officialAccount = app(\sprintf('wechat.official_account.%s', $account));
        $scopes          = $scopes ?: array_get($config, 'oauth.scopes', ['snsapi_base']);

        if (is_string($scopes)) {
            $scopes = array_map('trim', explode(',', $scopes));
        }

        $session = session($sessionKey, []);

        if (!$session) {
            if ($request->has('code')) {
                $wechatUser = $officialAccount->oauth->user();
                session([$sessionKey => $wechatUser ?? []]);
                $isNewSession = true;

                Event::fire(new WeChatUserAuthorized(session($sessionKey), $isNewSession, $account));

                $wechat = Wechat::where('open_id', $wechatUser->getId())->first();
                if (!$wechat) {
                    $original = $wechatUser->getOriginal();

                    $wechat = Wechat::create([
                        'open_id'  => $wechatUser->getId(),
                        'name'     => $wechatUser->getName(),
                        'avatar'   => $wechatUser->getAvatar(),
                        'gender'   => Wechat::WECHAT_GENDER_MAP[$original['sex']] ?? Wechat::GENDER_UNKNOWN,
                        'country'  => $original['country'],
                        'province' => $original['province'],
                        'city'     => $original['city']
                    ]);

                    $member = Member::create([
                        'nick_name'            => $wechat->name,
                        'identify_status' => Member::IDENTIFY_STATUS_PENDING
                    ]);
                    $wechat->update([
                        'member_id' => $member->id
                    ]);
                }

                // 生成 token
                $token = TokenService::generate();
                $wechat->tokenSet($token);
                session([
                    'api.context' => [
                        'token' => $token,
                        'hash'  => TokenService::hash($token),
                    ],
                ]);

                $wechat->member->tokenSet($token);

                return redirect()->to($this->getTargetUrl($request));
            }

            session()->forget($sessionKey);

            $goURL = $request->fullUrl(); //当前请求的完整URL
            //跳转网关适配代码
            $parsedGoURL = parse_url($goURL);

            //if (($host = array_get($parsedGoURL, 'host')) && $host != 'agent.xinglin.ai') {
            //    //当前域名不是agent.xinglin.ai 需要借助wechatJumpGateway二次跳转 注意，wechatJumpGateway 是没有middle ware处理的
            //    $queries = ['returnUrl' => $goURL];
            //    if ($request->has('print_code')) {
            //        $queries['print_code'] = 'AAAAAAAAAAAAAAAAAA';
            //        //                    当不是agent.xinglin.ai域名时候 ，请求微信登录接口只要带任意值的print_code参数
            //        //                    则构建出当微信授权成功后，跳转网关只打印带授权code跳转的连接 不自动跳转的连接
            //        //                    粘贴到微信开发者工具或者微信APP里面访问后，就能拿到此带CODE的连接
            //        //                    你可以把带code的跳转连接复制下来粘贴到postman上 方便调试。 正常testing或者staging的测试不会触发此逻辑
            //    }
            //    $goURL = route('wechatJumpGateway', $queries, false);
            //    $goURL = 'https://agent.xinglin.ai' . $goURL; //强制把跳转链接设置为 agent.xinglin.ai 开头的网址 //@todo 后面增加证书以后这里改成https
            //    //} else {
            //    //    $goURL = route('wechatLoginUserInfo', ['returnUrl' => $goURL], false);
            //    //    //因为$goURL有可能是一个静态内容的页面 所以跳到 wechatLogin 再跳回去
            //}

            return $officialAccount->oauth->scopes($scopes)->redirect($goURL);
        } else {
            // 防止token过期，重新生成
            $wechatUser = session($sessionKey);
            $wechat     = Wechat::where('open_id', $wechatUser->getId())->first();

            $token = TokenService::generate();
            $wechat->tokenSet($token);
            session([
                'api.context' => [
                    'token' => $token,
                    'hash'  => TokenService::hash($token),
                ],
            ]);
            $wechat->member->tokenSet($token);
        }

        Event::fire(new WeChatUserAuthorized(session($sessionKey), $isNewSession, $account));

        return $next($request);
    }
}
