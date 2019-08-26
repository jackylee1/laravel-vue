<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalExaminationReservation extends Model
{

    protected $fillable = ['physical_examination_qualification_id', 'name', 'mobile', 'identity', 'date', 'org_id', 'org_name', 'org_address'];
}
