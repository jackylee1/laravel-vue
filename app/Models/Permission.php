<?php

namespace App\Models;

/**
 * @author: caojinliang@fosun.com
 * Date: 17-9-20
 * Time: 下午3:33
 */
use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{

    protected $fillable = ['id', 'name', 'parent_id', 'display_name', 'description'];
}
