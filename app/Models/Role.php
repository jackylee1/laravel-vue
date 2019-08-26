<?php

namespace App\Models;

/**
 * @author: caojinliang@fosun.com
 * Date: 17-9-20
 * Time: 下午3:33
 */
use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{

    protected $fillable = ['name', 'display_name', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }
}
