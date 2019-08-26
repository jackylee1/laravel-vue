<?php
/**
 * @author ihipop@gmail.com @ 17-8-16 下午3:14 For xinglin-server.
 */

namespace App\Helper;

use Illuminate\Support\Facades\Request;

class UrlHelper
{

    /**
     * 判断域名是否可以在业务上安全的用于页面跳转 （只有可信域名才能进行跳转，否则会被人当跳转跳板用）
     * 注意：本方法 ***不会*** 使用 `htmlspecialchars` 处理文本，请自行确认文本是否可以输出到页面
     *
     * @param  $url
     *
     * @return bool|int|mixed|string
     */
    public static function isJumpSafeURL($url)
    {
        $url = parse_url(strtolower($url)); //PHP7 开始parse_url支持相对协议了(裸协议 也就是 //开头的情况) 所以不需要考虑老版本的情况了
        if (empty($url['host'])) {
            return $url; //是相对路径 肯定是可以安全跳转的
        } else {
            $appDomain = strtolower(array_get(parse_url(config('app.url')), 'host', ''));
            if (strtolower(parse_url(url()->current())['host']) == $url['host']
                || strripos($url['host'], '.' . $appDomain) !== false
            ) {
                return $appDomain;
            }
            if (filter_var($url['host'], FILTER_VALIDATE_IP)) {//如果是IP访问
                if (!filter_var(
                    $url['host'],
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                )
                ) {
                    // 但是IP又是保留地址IP 这种一般是内部测试的访问 给放行
                    // FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE 表示非私有|非保留地址 取反就是保留地址
                    return $url['host'];
                }
            }
            //最后判断性能一般的正则
            $whiteDomain = '/(^(.*\.)*xinglin\.ai|\.dev|\.test|\.local|^homestead\.app|localhost)$/i';

            return preg_match($whiteDomain, $url['host']);
        }
    }

    /**
     * 返回前一个网址 与url()->previous()的区别是，
     * 1. 如果参数里面有 returnUrl，则 returnUrl 优先 否则走 url()->previous 的逻辑()
     * 2. 如果返回的网址和当前页面一样，强制跳 $fallback 防止重复重定向
     *
     * @param  bool $fallback
     *
     * @return string
     */
    public static function previous($fallback = false, $jumpCheck = true)
    {
        $returnUrl = request()->get('returnUrl');
        if ($jumpCheck && !self::isJumpSafeURL($returnUrl)) {
            $returnUrl = null;
        }
        $previous = $returnUrl ? $returnUrl : url()->previous($fallback);
        if (url()->current() == $previous) {
            return $fallback ? url()->to($fallback) : url()->to('/');
        }

        return $previous;
    }

    /**
     * 做和php内置函数parse_url的反向操作
     *
     * @param  $parsed_url
     *
     * @return string
     */
    public static function unParseUrl($parsed_url)
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * 往一个不确定是不是？结尾的URL后面附加查询
     *
     * @param  string       $url
     * @param  string|array $append
     *
     * @return string
     */
    public static function appendQuery($url, $append)
    {
        if (empty($append)) {
            return $url;
        }

        if (is_array($append)) {
            $append = http_build_query($append);
        }

        return !$append ? $url : $url . ((strstr($url, '?')) ? '&' : '?') . $append;
    }
}
