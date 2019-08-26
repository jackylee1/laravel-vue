<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        $ret['products'] = Product::latest()->paginate();
        $ret['options']  = [
            'category' => Product::CATEGORY_MAP,
            'status'   => Product::STATUS_MAP
        ];

        return $this->success($ret);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'            => 'required',
            'cover'           => 'required|url',
            'price'           => 'required|numeric',
            'commission_rate' => 'required|numeric',
            'agent_rate'      => 'required|array',
            'status'          => 'required',
            'share_title'     => 'required',
            'share_desc'      => 'required',
            'share_img'       => 'required|url',
            'inventory'       => 'required|integer',
            'category'        => 'required|in:' . implode(',', array_keys(Product::CATEGORY_MAP))
        ]);

        try {
            $paramData = $request->all();
            //$paramData['agent_rate'] = json_encode(array_get($paramData, 'agent_rate', []));
            $product = Product::create($paramData);
            $tags    = Tag::whereIn('id', $request->tag_id)->get();
            $product->tags()->saveMany($tags);

            return $this->success($product);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_DUPLICATE, $e->getMessage());
        }
    }

    public function show($id)
    {
        $product = Product::with('tags')->findOrFail($id);

        return $this->success($product);
    }

    public function update(Request $request, $id)
    {
        try {
            $product   = Product::where('id', $id)->first();
            $paramData = $request->all();
            //$paramData['agent_rate'] = json_encode(array_get($paramData, 'agent_rate', []));
            $product->update($paramData); // 涉及到修改器，必须要先查出来再update
            $tags = Tag::whereIn('id', $request->tag_id)->get();
            $product->tags()->detach();
            $product->tags()->saveMany($tags);

            return $this->success($product);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, '更新失败！');
        }
    }

    public function category()
    {
        return $this->success(Product::CATEGORY_MAP);
    }

    public function pes($id)
    {
        $product = Product::findOrFail($id);

        return $this->success($product->pes);
    }
}
