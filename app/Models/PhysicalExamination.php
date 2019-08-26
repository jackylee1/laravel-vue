<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalExamination extends Model
{

    protected $fillable = ['title', 'price', 'type', 'extra_no', 'protocol_name', 'protocol_url'];
    const TYPE_BASIC = 1;
    const TYPE_EXTRA = 2;
    const TYPE_MAP   = [
        self::TYPE_BASIC => '基础包',
        self::TYPE_EXTRA => '叠加包',
    ];

    public function product()
    {
        return $this->morphToMany(Product::class, 'related', 'product_items');
    }

    //public function Order()
    //{
    //    return $this->morphToMany(Order::class, 'related', 'product_items');
    //}

    public function setPriceAttribute($price)
    {
        $this->attributes['price'] = $price * 100;
    }
}
