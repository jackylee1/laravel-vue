<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Services\Aliyun\AliSmsService;
use Illuminate\Http\Request;

class CommonController extends Controller
{

    public function sendSms(Request $request)
    {
        $this->validate($request, [
            'name'    => 'required',
            'date'    => 'required|date|after:now',
            'package' => 'required',
            'org'     => 'required',
            'address' => 'required',
            'mobile'  => 'required|mobile',
            'mobile1' => 'required|mobile',
        ], [
            'mobile.*'  => '下单人手机号不正确',
            'mobile1.*' => '体检人手机号不正确',
            'date.*'    => '预约日期必须大于今天'
        ]);

        $sms = [
            'name'    => $request->name,
            'date'    => $request->date,
            'package' => $request->package,
            'org'     => $request->org,
            'address' => $request->address
        ];

        $smsApi = new AliSmsService();

        $ret = $smsApi->sendSms($request->mobile, 'SMS_150738524', $sms);

        if ($request->mobile != $request->mobile1) {
            $smsApi->sendSms($request->mobile1, 'SMS_150738524', $sms);
        }

        if ($ret) {
            return $this->success([]);
        } else {
            return $this->error(ErrorCode::ERR_SYSTEM, '发送失败');
        }
    }
}
