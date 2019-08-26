<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{

    protected $fillable = ['member_id', 'address', 'contact_name', 'mobile', 'province', 'city', 'district'];
}
