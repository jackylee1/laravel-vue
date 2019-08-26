<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhysicalExaminationQualification;
use App\Services\Providers\ShanZhenService;
use Cache;

class ShanZhenController extends Controller
{

    public function reserve($id)
    {
        $qualification = PhysicalExaminationQualification::findOrFail($id);
        $ret['url']    = (new ShanZhenService())->getReservationUrl($qualification);

        return $this->success($ret);
    }

    public function report($id)
    {
        // 目前善诊提供的地址是既可以预约又可以查看报告
        return $this->reserve($id);
    }

    public function addresses()
    {
        $ret = Cache::remember('shanzhen_address' . config('app.env'), 60 * 24 * 30, function () {
            $addresses = \DB::table('shanzhen')->select('province', 'org_address', 'city', 'org_name')->groupBy('org_address')->get();
            $ret       = [];
            foreach ($addresses as $address) {
                $ret[$address->province][$address->city][] = [
                    'name'    => $address->org_name,
                    'address' => $address->org_address
                ];
            }

            return $ret;
        });

        return $this->success($ret);
    }
}
