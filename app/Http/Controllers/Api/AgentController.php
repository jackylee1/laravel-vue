<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\Agent;
use App\Models\Member;
use App\Services\Aliyun\AliSmsService;
use Illuminate\Http\Request;

class AgentController extends Controller
{

    /**
     * 获取某个代理人的信息
     * @param $id
     * @return AgentController
     */
    public function detail($id)
    {
        $agent = Agent::with('member.wechat')->findOrFail($id)->member->only('nick_name', 'wechat');

        return $this->success($agent);
    }

    /**
     * 获取代理人个人(自身)信息
     * @param Request $request
     * @return AgentController
     */
    public function show(Request $request)
    {
        $agent = Member::where('id', $request->member->id)->with('agent', 'wechat')->first();

        return $this->success($agent);
    }

    /**
     * 代理人招募：加入
     * @param Request $request
     * @return AgentController|\Illuminate\Http\JsonResponse
     */
    public function join(Request $request)
    {
        $this->validate($request, [
            'mobile'   => 'required|mobile',
            'code'     => 'required',
            'agent_id' => 'required|integer',
        ]);
        $sms = AliSmsService::verifySmsCode($request->mobile, $request->code);

        if ($sms['success']) {
            if ($request->member->agent) {
                // 如果已经是代理人了，则不会走加入团队逻辑
                // 可能是发起邀请的自己点击，或者其他代理人
                return $this->error(ErrorCode::ERR_NOT_ALLOWED, '您已经是代理人，不可以重复加入！');
            }

            try {
                \DB::transaction(function () use ($request) {
                    // 如果不是本人点击进入(因为走了微信授权中间件，所以member已经创建好了)
                    $request->member->update([
                        'mobile' => $request->mobile, // 首先绑定手机号
                        'status' => Member::STATUS_JOIN_AGENT_JOINED // 标记成为代理人
                    ]);
                    // 再创建代理人信息
                    Agent::create([
                        'member_id' => $request->member->id,
                        'parent_id' => $request->agent_id
                    ]);
                });

                return $this->success();
            } catch (\Throwable $e) {
                \Log::error('代理人招募：加入失败：' . $e->getMessage());

                return $this->error(ErrorCode::ERR_SYSTEM, '系统错误：加入失败！');
            }
        } else {
            return $this->error(ErrorCode::ERR_INVALID_ARGUMENT, $sms['msg']);
        }
    }

    public function team(Request $request)
    {
        $team = Agent::with([
            'team' => function ($q) {
                $q->with('team')->orderBy('created_at', 'desc');
            }
        ])->where('member_id', $request->member->id)->first();

        return $this->success($team);
    }
}
