<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller as BaseController;
use App\Services\Providers\ShanZhenService;
use Illuminate\Http\Request;

class ShanzhenController extends BaseController
{

    /**
     * 善诊订单数据通知
     * @param Request $request
     */
    public function orderNotify(Request $request)
    {
        $service = new ShanZhenService();
        $ret     = $service->orderNotify($request->all());

        return response()->json($ret);
    }

    public function reportNotify(Request $request)
    {
        $service = new ShanZhenService();
        $ret     = $service->reportNotify($request->all());

        return response()->json($ret);
    }
}
