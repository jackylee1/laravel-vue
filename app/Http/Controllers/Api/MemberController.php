<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\Member;
use App\Services\Aliyun\AliSmsService;
use App\Services\TokenService;
use Illuminate\Http\Request;

class MemberController extends Controller
{

    /*
     * 注册或登录
     *
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|mobile',
            'code'   => 'required'
        ]);

        $sms = AliSmsService::verifySmsCode($request->mobile, $request->code);

        if ($sms['success']) {
            $member = Member::with('wechat', 'agent')->where('mobile', $request->mobile)->first();

            $token = TokenService::generate();
            $hash  = TokenService::hash($token);

            $member->tokenSet($token);

            return $this->success($member, [
                config('api.token.http.token') => $token,
                config('api.token.http.hash')  => $hash
            ]);
        } else {
            return $this->error(ErrorCode::ERR_INVALID_ARGUMENT, $sms['msg']);
        }
    }

    /**
     * 微信授权登录后，绑定手机号
     * @param Request $request
     * @return MemberController|\Illuminate\Http\JsonResponse
     */
    public function bindMobile(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|mobile',
            'code'   => 'required'
        ]);

        $sms = AliSmsService::verifySmsCode($request->mobile, $request->code);

        if ($sms['success']) {
            $data = [
                'mobile' => $request->mobile
            ];

            if ($request->join) {
                // 如果绑定手机号的时候，join 参数为 true ，则表示同时申请加入代理人
                $data['status'] = Member::STATUS_JOIN_AGENT_APPLY;
            }
            Member::where('id', $request->member->id)->update($data);

            return $this->success();
        } else {
            return $this->error(ErrorCode::ERR_INVALID_ARGUMENT, $sms['msg']);
        }
    }

    //public function show(Request $request)
    //{
    //    return $this->success($request->member);
    //}

    /**
     * 更新用户名
     * @param Request $request
     * @return MemberController
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'nick_name' => 'required'
        ]);

        $member = $request->member->update($request->only('nick_name'));

        return $this->success($member);
    }

    /**
     * 身份认证
     * @param Request $request
     * @return MemberController
     */
    public function identify(Request $request)
    {
        $this->validate($request, [
            'identity'       => 'required|identity',
            'identity_files' => 'required|array',
            'valid_date'     => 'required|date',
            'name'      => 'required',
        ]);

        $member = $request->member->update(array_merge($request->all(), ['identify_status' => Member::IDENTIFY_STATUS_APPLY]));

        return $this->success($member);
    }
}
