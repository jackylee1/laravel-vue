<?php
/**
 * @author   caojinliang@fosun.com
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{

    public function index()
    {
        $types = Permission::select(\DB::raw('id,name,id as value, parent_id, display_name as label'))->get()->toArray();
        $data  = getTree(0, $types);
        $ret   = [
            [
                'value'    => 0,
                'id'       => 0,
                'label'    => '根目录',
                'children' => $data,
            ],
        ];

        return $this->success($ret);
    }

    public function show($id)
    {
        $ret = Permission::find($id)->toArray();

        return $this->success($ret);
    }

    public function store()
    {
        try {
            $return['data'] = Permission::create(request()->all());

            return $this->success($return);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_DUPLICATE, '权限已存在');
        }
    }

    public function update(Request $request, $id)
    {
        if (Permission::where('id', $id)->update($request->all())) {
            return $this->success();
        } else {
            return $this->error(ErrorCode::ERR_SYSTEM, '更新失败');
        }
    }
}
