<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPoster extends Model
{

    protected $fillable = ['product_id', 'src', 'x-axis', 'y-axis', 'width'];
    protected $hidden   = ['created_at', 'updated_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
