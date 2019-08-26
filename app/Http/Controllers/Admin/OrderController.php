<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $ret['orders'] = Order::with(['member', 'agent.member', 'product', 'payment'])
            ->when($request->filled('member_id'), function ($q) use ($request) {
                $q->whereHas('member', function ($qq) use ($request) {
                    $qq->where('id', '=', $request->get('member_id'));
                });
            })
            ->when($request->filled('user'), function ($q) use ($request) {
                $q->whereHas('member', function ($qq) use ($request) {
                    $qq->where('nick_name', 'like', '%' . $request->get('user') . '%');
                });
            })
            ->when($request->filled('agent'), function ($q) use ($request) {
                $q->whereHas('agent', function ($qq) use ($request) {
                    $qq->whereHas('member', function ($qqq) use ($request) {
                        $qqq->where('name', 'like', '%' . $request->agent . '%');
                    });
                });
            })
            ->latest()
            ->where('status', '<>', Order::STATUS_PAY_OVERTIME)
            ->paginate(10);

        $ret['options'] = [
            'category' => Product::CATEGORY_MAP,
            'status'   => Order::STATUS_MAP
        ];

        return $this->success($ret);
    }

    public function show($id)
    {
        $data = [
            'order'   => Order::with('member', 'agent.member', 'payment')->findOrFail($id),
            'options' => Order::STATUS_MAP
        ];

        return $this->success($data);
    }
}
