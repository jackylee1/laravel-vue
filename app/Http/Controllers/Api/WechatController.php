<?php
/**
 * @author ihipop@gmail.com @ 17-7-31 上午10:58 For xinglin-server.
 */

namespace App\Http\Controllers\Api;

use App\Helper\UrlHelper;
use App\Http\Controllers\Controller as Base;
use EasyWeChat\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WechatController extends Base
{

    /**
     * 微信Oauth授权登录后的跳转处理
     *
     * @param Request $request
     */
    public function redirect()
    {
        //不用做什么代码，因为中间件都做了，如果是JSON请求，重定向的会发在redirect里面 这里处理好$returnUrl就可以了
        $returnUrl = UrlHelper::previous('/app/', true);
        $context   = session('api.context');

        if (parse_url($returnUrl, PHP_URL_QUERY)) {
            $returnUrl = $returnUrl . '&token=' . $context['token'] . '&hash=' . $context['hash'];
        } else {
            $returnUrl = $returnUrl . '?token=' . $context['token'] . '&hash=' . $context['hash'];
        }

        return view('wechat.redirect', [
            'url' => $returnUrl,
        ]);
    }

    /**
     * Oauth有域名限制 这里弄这个二次的跳转，避开自己中间件的处理进行域名跳转 作为到测试环境的桥梁
     * @param Request $request
     * @return RedirectResponse|string
     */
    public function jumpGateway(Request $request)
    {
        $returnUrl = $request->input('returnUrl');
        if ($request->has('code') && $returnUrl) {
            //微信过来了的
            //如下代码只会在 wx.xinglin.ai 执行
            //确保不是本机发起的 因为代码一样的
            if (array_get(parse_url($returnUrl), 'host') == 'agent.xinglin.ai') {
                return '循环跳转链接';
            }
            if (!UrlHelper::isJumpSafeURL($returnUrl)) {
                return '非法跳转链接';
            }

            $newQuery = $request->query();
            array_forget($newQuery, 'returnUrl');
            array_forget($newQuery, 'print_code');

            if ($request->has('print_code')) {
                return UrlHelper::appendQuery($returnUrl, $newQuery);
            }

            return RedirectResponse::create(UrlHelper::appendQuery($returnUrl, $newQuery));
        } else {
            return '非正确请求';
        }
    }

    /**
     * 获取微信配置
     * @param Request $request
     * @return array|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function config(Request $request)
    {
        $config = config('wechat.official_account.default');
        $wechat = Factory::officialAccount($config);
        $apis   = $request->input('api', []);

        // 前端支付的url需要参与签名
        return $wechat->jssdk->setUrl($request->header('referer'))->buildConfig($apis);
    }
}
