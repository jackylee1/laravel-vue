<?php

namespace App\Models;

use App\Services\Providers\ShanZhenService;
use Illuminate\Database\Eloquent\Model;

class PhysicalExaminationQualification extends Model
{

    protected $fillable = [
        'member_id',
        'order_id',
        'status',
        'combo_code',
        'physical_examination_id',
        'out_order_no',
        'reason',
        'title'
    ];
    const STATUS_PENDING    = 1;
    const STATUS_ORDERED    = 2;
    const STATUS_RESERVED   = 3;
    const STATUS_REPORTED   = 4;
    const STATUS_CANCELED   = 5;
    const STATUS_PROCESSING = 6;
    const STATUS_FAILED     = 7;
    const STATUS_MAP        = [
        self::STATUS_PENDING    => '待激活',
        self::STATUS_ORDERED    => '已下单',
        self::STATUS_RESERVED   => '已预约',
        self::STATUS_REPORTED   => '已生成报告',
        self::STATUS_CANCELED   => '已取消',
        self::STATUS_PROCESSING => '处理中',
        self::STATUS_FAILED     => '下单失败',
    ];

    public static function createFromOrder(Order $order)
    {
        try {
            $peids = $order->pes->pluck('id')->sort()->implode(',');
            // 根据 Order_item里面的physical_examination_id 去查询这些ids组合码
            $combo = PhysicalExaminationCombo::where('physical_examination_id', $peids)->first();

            $ret = self::create([
                'title'                   => $order->title,
                'status'                  => self::STATUS_PENDING,
                'order_id'                => $order->id,
                'member_id'               => $order->member->id,
                'combo_code'              => $combo->code,
                'physical_examination_id' => $peids
            ]);
            // 目前只有善诊一家，直接调用下单即可
            (new ShanZhenService())->order($ret);
        } catch (\Exception $e) {
            \Log::error('根据订单创建体检资格失败：' . $e->getFile() . '；行数：' . $e->getLine() . '；错误信息：' . $e->getMessage());
        }
    }

    public function pes()
    {
        return PhysicalExamination::whereIn('id', explode(',', $this->physical_examination_id))->get();
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function reservation()
    {
        return $this->hasOne(PhysicalExaminationReservation::class, 'physical_examination_qualification_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
