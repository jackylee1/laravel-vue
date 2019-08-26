<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $fillable = [
        'name',
        'price',
        'commission_rate',
        'agent_rate',
        'status',
        'cover',
        'description',
        'inventory',
        'sale_count',
        'category',
        'share_title',
        'share_desc',
        'share_img',
    ];
    const CATEGORY_PE    = 1;
    const CATEGORY_MAP   = [
        self::CATEGORY_PE => '体检'
    ];
    const STATUS_INVALID = 0;
    const STATUS_VALID   = 1;
    const STATUS_MAP     = [
        self::STATUS_INVALID => '下架',
        self::STATUS_VALID   => '上架',
    ];

    protected $casts = [
        'agent_rate' => 'json'
    ];

    /**
     * 根据产品类别，返回产品具体内容
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function content()
    {
        switch ($this->category) {
            case self::CATEGORY_PE:
                return $this->pes();
        }
    }

    public function posters()
    {
        return $this->hasMany(ProductPoster::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function pes()
    {
        return $this->morphedByMany(PhysicalExamination::class, 'related', 'product_items');
    }

    public function setPriceAttribute($price)
    {
        $this->attributes['price'] = $price * 100;
    }

    public function setCommissionRateAttribute($rate)
    {
        $this->attributes['commission_rate'] = $rate / 100;
    }


    //public function setStatusAttribute($status)
    //{
    //    $this->attributes['status'] = $status ? self::STATUS_VALID : self::STATUS_INVALID;
    //}
}
