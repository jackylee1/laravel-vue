<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductPoster;
use Illuminate\Http\Request;

class ProductPosterController extends Controller
{

    /**
     * 获取阿里云oss上传凭证
     * @return array
     */
    public static function getPolicy()
    {
        $response = [
            'accessKeyId'     => config('aliyun.accesskey.id'),
            'accessKeySecret' => config('aliyun.accesskey.secret'),
            'bucket'          => config('aliyun.bucket')
        ];

        return $response;
    }

    public function posters($pid)
    {
        $poster = ProductPoster::where('product_id', $pid)->get();

        return $this->success($poster);
    }

    public function store(Request $request)
    {
        $poster = ProductPoster::create($request->all());

        return $this->success($poster);
    }

    public function update(Request $request, $id)
    {
        $poster = ProductPoster::where('id', $id)->update($request->all());

        return $this->success($poster);
    }

    public function destroy($id)
    {
        $poster = ProductPoster::where('id', $id)->delete();

        return $this->success($poster);
    }
}
