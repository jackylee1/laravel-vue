<?php

namespace App\Http\Middleware;

use Closure;
use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{

    /**
     * The trusted proxies for this application.
     *
     * @var array
     */
    protected $proxies;
    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;

    public function handle(Request $request, Closure $next)
    {
        $headers         = $request->headers;
        $dynamicTrustIPs = [];
        $slbID           = $headers->get('SLB-ID');
        $slbIP           = $headers->get('SLB-IP');
        //阿里云来的请求都有这两个头，理论上虽然直接由客户端也能发，但是我们的机器都在VPC内网，这两个头不会被客户端发的头覆盖，所以简单的进行信任
        //按理说，应该建立白名单的
        //客户的真实IP藏在 RemoteIp 头，由SLB带来
        if ($slbID) {
            //当使用SLB的时候 开启realIP会造成REMOTE_ADDR已经是客户IP 这样会造成对REMOTE_ADDR的不恰当信任，我们应该相信的是SLB IP
            //提前约定 把 NGINX 的 $realip_remote_addr 变量放到fastcgi参数中 名字为 REALIP_REMOTE_ADDR
            if ($realipRemoteAddr = $request->server->get('REALIP_REMOTE_ADDR')) {
                //当启用了Nginx的RealIP模块 和ECS接触的那个SLB实例是可信的 其IP放置在 REALIP_REMOTE_ADDR，REMOTE_ADDR是用户的IP
                $dynamicTrustIPs[] = $realipRemoteAddr;
                $request->server->set('REMOTE_ADDR', $realipRemoteAddr);//纠正设置
            } else {
                //当关闭Nginx的RealIP模块 REMOTE_ADDR 是SLB的IP，用户IP需要从X forward for 取 请确保这时候RealIP是关闭的
                $dynamicTrustIPs[] = $request->server->get('REMOTE_ADDR');
            }
            $slbIPs = explode(',', $slbIP);
            foreach ($slbIPs as $IP) {
                $IP = trim($IP);
                if (ip2long($IP)) {
                    $dynamicTrustIPs[] = $IP;
                }
            }
        }

        //        $aliYunCDN = [
        //            '111.202.96.192/26',
        //            '36.110.143.0/26',
        //            '111.13.31.128/27',
        //            '111.202.96.64/26',
        //            '111.13.32.192/26',
        //            '111.202.96.128/27',
        //            '111.13.31.160/27',
        //            '140.205.31.128/25',
        //            '140.205.16.128/25',
        //            '140.205.127.0/25',
        //            '101.200.101.128/25',
        //            '139.196.128.0/25',
        //            '101.200.101.0/25',
        //            '123.57.117.64/26',
        //            '120.27.173.192/26',
        //            '106.11.144.128/25',
        //            '106.11.37.128/25',
        //            '140.205.253.128/25',
        //        ];

        $privateAddress = [
            '10.0.0.0/8',//#私有地址
            '127.0.0.0/8', //LoopBack
            '172.16.0.0/12',//#私有地址
            '192.168.0.0/16',//#私有地址
            '100.64.0.0/10',// #运营商NAT 一般阿里云的SLB
        ];

        $request::setTrustedProxies(array_merge($dynamicTrustIPs, $privateAddress), 1);

        return $next($request);
    }
}
