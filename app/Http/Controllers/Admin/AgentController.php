<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Member;
use Excel;
use Illuminate\Http\Request;

class AgentController extends Controller
{

    public function index()
    {
        $ret['agents']  = Agent::with('member', 'parent.member')
            ->when(request()->filled('nick_name'), function ($q) {
                $q->whereHas('member', function ($query) {
                    $query->where('nick_name', 'like', '%' . request()->nick_name . '%');
                });
            })
            ->latest()->paginate();
        $ret['options'] = [
            'identify_status' => Member::IDENTIFY_STATUS_MAP
        ];

        return $this->success($ret);
    }

    public function tree(Request $request)
    {
        if ($request->filled('nick_name')) {
            $member = Member::with('agent')->where('nick_name', $request->nick_name)->first();
            $agents = Agent::where('id', '>=', $member->agent->id)->get()->toArray(); // 子孙id一定大于自己
            $agents = getTree($member->agent->id, $agents);

            $ret = array_merge($member->agent->toArray(), ['children' => $agents]);
        } else {
            $agents = Agent::get()->toArray();
            $agents = getTree('0', $agents);
            $ret    = [
                'id'          => -1,
                'name'        => '系统',
                'order_count' => -1,
                'children'    => $agents
            ];
        }

        return $this->success($ret);
    }

    public function show($id)
    {
        $agent = Agent::with('member', 'parent.member')->findOrFail($id);
        $agent->member->makeVisible(['identity', 'identify_status', 'identity_files']);

        $ret['agent']   = $agent;
        $ret['options'] = [
            'identify_status' => Member::IDENTIFY_STATUS_MAP
        ];

        return $this->success($ret);
    }

    public function update(Request $request, $id)
    {
        Agent::find($id)->member->update($request->only('identify_status'));

        return $this->success();
    }

    /**
     * 导入代理人
     * @param Request $request
     * @return AgentController
     */
    public function import(Request $request)
    {
        $file = $request->file('file');
        Excel::load($file, function ($reader) {
            collect($reader->getSheet(0)->toArray())->each(function ($item) {
                if (isset($item[0]) && preg_match("/^1[3456789]\d{9}$/", $item[0])) {
                    $member = Member::where('mobile', $item[0])->first();
                    if ($member) {
                        $agent = Agent::where('member_id', $member->id)->first();
                        if (!$agent) {
                            Agent::create([
                                'member_id'   => $member->id,
                                'parent_id'   => 0,
                                'order_count' => 0
                            ]);
                        }
                        $member->update([
                            'status' => Member::STATUS_JOIN_AGENT_JOINED // 标记成为代理人
                        ]);
                    }
                }
            });
        });

        return $this->success();
    }
}
