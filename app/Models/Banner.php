<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{

    protected $fillable = ['title', 'introduction', 'link', 'cover', 'status', 'begin_at', 'end_at'];
    const STATUS_VALID   = 1;
    const STATUS_INVALID = 2;
    const STATUS_MAP     = [
        self::STATUS_VALID   => '启用',
        self::STATUS_INVALID => '不启用'
    ];

    public function scopeValid()
    {
        return $this->where('status', self::STATUS_VALID);
    }
}
