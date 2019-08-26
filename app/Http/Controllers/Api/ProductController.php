<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPoster;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::with('tags')->get();

        return $this->success($products);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $product->content; // 这里不能直接with出来

        return $this->success($product);
    }

    public function posters($id)
    {
        $posters = ProductPoster::where('product_id', $id)->get();

        return $this->success($posters);
    }
}
