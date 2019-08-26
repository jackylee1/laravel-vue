<?php

namespace App\Services\Aliyun;

use App\Helper\MiscHelper;
use App\Http\Response\ErrorCode;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class AliSmsService
{

    public $errorCode;
    public $errorMessage;
    public $response;
    private $smsClient;

    /**
     * @return mixed
     */
    public static function getSmsCodeKey($fingerprint)
    {
        if (!is_string($fingerprint)) {
            $fingerprint = md5(serialize($fingerprint));
        }

        return config('cache.prefix') . '_' . 'smsCode_' . $fingerprint;
    }

    public static function getErrorCountKey($fingerprint)
    {
        if (!is_string($fingerprint)) {
            $fingerprint = md5(serialize($fingerprint));
        }

        return config('cache.prefix') . '_' . 'smsCodeErrorCount_' . $fingerprint;
    }

    public function __construct()
    {
        $config          = config('easysms');
        $this->smsClient = new EasySms($config);
    }

    /**
     * @param        $mobile
     * @param string $template_id 阿里云短信模板id
     * @param array  $data 模板内容
     * @return bool
     */
    public function sendSms($mobile, $template_id, array $data = [])
    {
        try {
            $this->smsClient->send($mobile, [
                'template' => $template_id,
                'data'     => $data
            ]);

            return true;
        } catch (NoGatewayAvailableException $e) {
            $this->errorCode    = $e->getCode();
            $this->errorMessage = $e->getMessage();

            return false;
        }
    }

    public function sendVerifySms($mobile, $ttl = 5 * 60, $code = null)
    {
        $code   = empty($code) ? mt_rand(1000, 9999) : $code;
        $result = $this->sendSms($mobile, 'SMS_78905539', ['code' => $code]);
        if ($result) {
            return Redis::setex(self::getSmsCodeKey($mobile), $ttl, $code);
        }

        return $result;
    }

    /**
     * @param      $mobile
     * @param int  $ttl
     * @param null $code
     *
     * @return array|bool
     */
    public function sendVerifySmsWithLimit($mobile, $ttl = 5 * 60, $code = null)
    {

        $userIP   = Request::ip();
        $phone    = $mobile;
        $ipKey    = config('cache.prefix') . '_' . 'sms_ip_limit_' . $userIP;
        $phoneKey = config('cache.prefix') . '_' . 'sms_phone_limit_' . $phone;

        //2分钟内IP超过40条，或者 1个手机号码1分钟内超过1条 需要让他等待。
        if ((int)Redis::get($ipKey) >= 40 || Redis::get($phoneKey)) {
            return ['code' => ErrorCode::ERR_FREQUENCY, 'message' => '超过发送限额'];
        }

        //设置IP配额
        if (Redis::incr($ipKey) && Redis::ttl($ipKey) <= 0) {
            Redis::expire($ipKey, 2 * 60);
        }
        //设置号码配额
        Redis::setex($phoneKey, 1 * 60, 1);

        if ($this->sendVerifySms($mobile, $ttl, $code)) {
            return true;
        }

        return ['code' => ErrorCode::ERR_SYSTEM];
    }

    /**
     * @param       $mobile
     * @param       $code
     * @param  bool $clear
     * @return array
     */
    public static function verifySmsCode($mobile, $code, $clear = true)
    {
        $return        = [
            'success' => false,
            'msg'     => '验证码错误',
        ];
        $errorCountKey = self::getErrorCountKey($mobile);
        $smsCodeKey    = self::getSmsCodeKey($mobile);
        if (Redis::get($errorCountKey) > 10) {
            //验证码错误10次销毁验证码
            Redis::del($smsCodeKey);
            Redis::del($errorCountKey);
            $return['msg'] = '验证码已过期'; //已销毁

            return $return;
        }
        $fixCode   = MiscHelper::isProductionEnv() ? "" : 1708; //开发模式的固定验证码
        $redisCode = Redis::get($smsCodeKey);
        if (empty($redisCode)) {
            $return['msg'] = '验证码已过期或者被使用'; //已过期
        }
        $return['success'] = ($redisCode == $code || ($fixCode && ($fixCode == $code)));
        //如果验证码正确， 且需要清除 标记他已经使用了
        if ($return['success'] && $clear) {
            //清除成功才算验证码正确
            $return['success'] = (Redis::del($smsCodeKey) || $fixCode);
        }
        if ($return['success']) {
            $return['msg'] = null;
            Redis::del($errorCountKey);
        } else {
            //记录验证码错误次数
            if (Redis::incr($errorCountKey) && Redis::ttl($errorCountKey) <= 0) {
                Redis::expire($errorCountKey, 20 * 60);
            }
        }

        return $return;
    }
}
