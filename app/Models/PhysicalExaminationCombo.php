<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalExaminationCombo extends Model
{

    protected $fillable = ['product_id', 'physical_examination_id', 'code'];

    public function setPhysicalExaminationIdAttribute($val)
    {
        $this->attributes['physical_examination_id'] = is_array($val) ? implode(',', $val) : $val;
    }
}
