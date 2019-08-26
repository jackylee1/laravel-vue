<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\ProductPosterController;
use App\Http\Controllers\Controller;
use App\Services\Aliyun\AliSmsService;
use Illuminate\Http\Request;

class CommonController extends Controller
{

    public function sendSms(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|mobile',
        ], [
            'mobile.*' => '手机号码格式不正确',
        ]);

        $sms = new AliSmsService();
        if (($result = $sms->sendVerifySmsWithLimit($request->mobile)) === true) {
            return $this->success([]);
        } else {
            return $this->error($result['code']);
        }
    }

    /**
     * 获取阿里云上传凭证
     * @return CommonController
     */
    public function getUploadPolicy()
    {
        return $this->success(ProductPosterController::getPolicy());
    }
}
