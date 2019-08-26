<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\Agent;
use App\Models\Member;
use App\Models\Order;
use App\Models\PhysicalExaminationQualification;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $peOrders = PhysicalExaminationQualification::with('order.pes')
            ->where('member_id', $request->member->id)
            ->latest()
            ->get();
        $data     = [
            'pe_orders' => $peOrders,
            'meta'      => PhysicalExaminationQualification::STATUS_MAP
        ];

        return $this->success($data);
    }

    public function show($id)
    {
        $data = [
            'order' => Order::find($id),
            'meta'  => Order::STATUS_MAP
        ];

        return $this->success($data);
    }

    public function peOrderDetail(Request $request, $id)
    {
        $peOrder = PhysicalExaminationQualification::with('reservation', 'order.pes')->where('member_id', $request->member->id)->find($id);
        $data    = [
            'detail' => $peOrder,
            'meta'   => PhysicalExaminationQualification::STATUS_MAP
        ];

        return $this->success($data);
    }

    /**
     * @param Request $request
     * @return OrderController|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Member::where('id', $request->member->id)->value('mobile')) {
            return $this->error(ErrorCode::ERR_NOT_ALLOWED, '请先绑定手机号');
        }

        $this->validate($request, [
            'product_id'               => 'required|integer',
            'physical_examination_ids' => 'required|array',
        ], [
            'physical_examination_ids.*' => '体检包必须是数组'
        ]);

        $product = Product::findOrFail($request->product_id);
        $agent   = $request->filled('agent_id') ? Agent::findOrFail($request->agent_id) : null;

        try {
            if ($product->category == Product::CATEGORY_PE) {
                // 创建体检类订单
                $order = Order::makePEOrder($request->member, $product, $request->physical_examination_ids, $agent);
            }

            return $this->success($order);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, $e->getMessage());
        }
    }
}
