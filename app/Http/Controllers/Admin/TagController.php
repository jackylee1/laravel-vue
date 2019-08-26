<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\RoleTag;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{

    public function index()
    {
        $tags = Tag::all();

        return $this->success($tags->makeVisible(['id', 'description']));
    }

    public function store(Request $request)
    {
        try {
            $return['data'] = Tag::create($request->all());

            return $this->success($return);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_DUPLICATE, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Tag::where('id', $id)->update($request->all());

            return $this->success();
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, '更新失败！');
        }
    }
}
