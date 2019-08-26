<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{

    /**
     * 获取用户所有的地址
     * @param Request $request
     * @return MemberController
     */
    public function index(Request $request)
    {
        $addresses = $request->member->addresses;

        return $this->success($addresses);
    }

    public function show(Request $request, $id)
    {
        $ret = Address::where('member_id', $request->member->id)
            ->where('id', $id)
            ->first();

        return $this->success($ret);
    }

    /**
     * 添加地址
     * @param Request $request
     * @return MemberController
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'address' => 'required'
        ]);

        $address = Address::create(array_merge(['member_id' => $request->member->id], $request->all()));

        return $this->success($address);
    }

    public function update(Request $request, $id)
    {
        $ret = Address::where('member_id', $request->member->id)
            ->where('id', $id)
            ->update($request->all());

        return $this->success($ret);
    }

    public function destroy(Request $request, $id)
    {
        $ret = Address::where('member_id', $request->member->id)
            ->where('id', $id)->delete();

        return $this->success($ret);
    }
}
