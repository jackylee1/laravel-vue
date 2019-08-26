<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;

class BannerController extends Controller
{

    public function index()
    {
        $now     = date('Y-m-d H:i:s');
        $banners = Banner::valid()->get()->reject(function ($item) use ($now) {
            if ($item->begin_at && $item->begin_at > $now) {
                return true;
            }
            if ($item->end_at && $item->end_at < $now) {
                return true;
            }
        })->values();

        return $this->success($banners);
    }
}
