<?php

namespace Wunsun\Tools\Mount\Models;

use Illuminate\Database\Eloquent\Model;

class MountRecord extends Model
{
    protected $fillable = [
        'name',
        'filename',
        'status',
        'origin_data',
        'mounted_data',
        'index'
    ];
}
