<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{

    public function index()
    {
        $ret['banners'] = Banner::latest()->paginate();
        $ret['options'] = [
            'status' => Banner::STATUS_MAP
        ];

        return $this->success($ret);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'cover' => 'required|url',
            'link'  => 'required|url',
        ]);

        try {
            $banner = Banner::create($request->all());

            return $this->success($banner);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_DUPLICATE, $e->getMessage());
        }
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);

        return $this->success($banner);
    }

    public function update(Request $request, $id)
    {
        try {
            $banner = Banner::where('id', $id)->first();
            $banner->update($request->all()); // 涉及到修改器，必须要先查出来再update

            return $this->success($banner);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, '更新失败！');
        }
    }
}
