<?php
/**
 * Token 相关接口
 *
 * Date: 17-11-07
 * Time: 上午10:37
 *
 * @author wangzilong@fosun.com
 */

namespace App\Services;

use Hash;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

class TokenService
{

    protected static $connection = null;

    protected static function connect()
    {
        if (!self::$connection) {
            self::$connection = Redis::connection('token');
        }

        return self::$connection;
    }

    /**
     * @return [string]
     */
    public static function generate()
    {
        return str_replace('.', '', uniqid("", true) . uniqid("", true));
    }

    /**
     * @return [string]
     */
    public static function hash($token)
    {
        return Hash::make($token);
    }

    /**
     * @return [string]
     */
    public static function verify($token, $hash)
    {
        return Hash::check($token, $hash);
    }

    /**
     * 在指定的 token 内写入数据
     * @param [type] $token [description]
     * @param [type] $field [description]
     * @param [type] $mixed [description]
     */
    public static function set($token, $field, $mixed)
    {
        $redis = self::connect();
        $ret   = $redis->hset($token, $field, serialize($mixed));
        $redis->expire($token, config('api.token.expire'));

        return $ret;
    }

    /**
     * @param  [type]    $token [description]
     * @param  [type]    $field [description]
     * @return [mixed]
     */
    public static function get($token, $field)
    {
        $redis = self::connect();

        return unserialize($redis->hget($token, $field));
    }

    public static function getAll($token)
    {
        $redis = self::connect();
        $mixed = $redis->hgetall($token);
        if (empty($mixed)) {
            return null;
        }
        $ret = [];
        foreach ($mixed as $key => $value) {
            $ret[$key] = unserialize($value);
        }

        return $ret;
    }

    public static function exists($token, $field = null)
    {
        $redis = self::connect();
        if (empty($field)) {
            return $redis->exists($token);
        } else {
            return $redis->hexists($token, $field);
        }
    }

    public static function delete($token, $field = null)
    {
        $redis = self::connect();
        if (empty($field)) {
            return $redis->del($token);
        } else {
            return $redis->hdel($token, $field);
        }
    }

    public static function injectRequest($request, $throw = false)
    {
        $token   = $request->header(config('api.token.http.token'));
        $hash    = $request->header(config('api.token.http.hash'));
        $context = [];

        $passed         = (!empty($token) && !empty($hash)) ? TokenService::verify($token, $hash) : false;
        $request->token = $request->context = null;

        if ($passed && ($context = TokenService::getAll($token))) {
            $request->token   = $token;
            $request->context = $context;
        } elseif ($throw) {
            throw new HttpResponseException(Response::create([])->setStatusCode(Response::HTTP_UNAUTHORIZED, urlencode('Token校验失败.')));
        }

        return $context;
    }
}
