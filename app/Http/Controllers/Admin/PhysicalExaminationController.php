<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExaminationCombo;
use App\Models\Product;
use Illuminate\Http\Request;

class PhysicalExaminationController extends Controller
{

    public function store(Request $request, $productId)
    {
        $this->validate($request, [
            'title'         => 'required',
            'price'         => 'required|numeric',
            'type'          => 'required|in:' . implode(',', array_keys(PhysicalExamination::TYPE_MAP))
        ]);

        $product = Product::findOrFail($productId);

        if ($request->type == PhysicalExamination::TYPE_BASIC && $product->pes()->where('type', PhysicalExamination::TYPE_BASIC)->first()) {
            return $this->error(ErrorCode::ERR_DUPLICATE, '只能存在一个基础包');
        }

        try {
            $pe = PhysicalExamination::create($request->all());
            $pe->product()->save($product);

            return $this->success($pe);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_DUPLICATE, $e->getMessage());
        }
    }

    public function update(Request $request, $productId, $peId)
    {
        $this->validate($request, [
            'title'         => 'required',
            'price'         => 'required|numeric',
            //'protocol_name' => 'required',
            //'protocol_url'  => 'required|url',
            'type'          => 'required|in:' . implode(',', array_keys(PhysicalExamination::TYPE_MAP))
        ]);

        $product = Product::findOrFail($productId);

        $basic = $product->pes()->where('type', PhysicalExamination::TYPE_BASIC)->first();
        if ($request->type == PhysicalExamination::TYPE_BASIC && $basic && $basic->id != $peId) {
            return $this->error(ErrorCode::ERR_DUPLICATE, '只能存在一个基础包');
        }

        try {
            $pe = PhysicalExamination::findOrFail($peId);
            $pe->update($request->all());

            return $this->success($pe);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_DUPLICATE, $e->getMessage());
        }
    }

    public function combo($productId)
    {
        $data = PhysicalExaminationCombo::where('product_id', $productId)
            ->select('physical_examination_id', 'code', 'id')
            ->get();

        return $this->success($data);
    }

    public function comboStore(Request $request, $productId)
    {
        $data = $request->all();
        array_push($data['extra_ids'], $data['basic_id']);
        sort($data['extra_ids']);

        $basic = [
            'product_id'              => $productId,
            'physical_examination_id' => $data['extra_ids'],
            'code'                    => $data['code']
        ];

        try {
            $combo = PhysicalExaminationCombo::create($basic);

            return $this->success($combo);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_DUPLICATE, $e->getMessage());
        }
    }

    public function comboDestroy($productId, $comboId)
    {
        try {
            $ret = PhysicalExaminationCombo::where('product_id', $productId)
                ->where('id', $comboId)
                ->delete();

            return $this->success($ret);
        } catch (\Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, $e->getMessage());
        }
    }
}
