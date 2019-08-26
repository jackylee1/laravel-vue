<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Response\ErrorCode;

class MemberController extends Controller
{

    /**
     * 用户列表
     * @param Request $request
     * @return MemberController
     */
    public function index(Request $request)
    {
        $paramData = $request->all();
        $keyword = array_get($paramData, 'keyword', '');
        $member = Member::with('wechat', 'agent');
        if ($keyword) {
            $member = $member->where('mobile', $keyword)->orWhere('nick_name', $keyword);
        }
        $ret['member']  = $member->latest()->paginate();

        $ret['options'] = [
            'identify_status' => Member::IDENTIFY_STATUS_MAP
        ];

        return $this->success($ret);
    }
}
