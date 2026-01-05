<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $fillable = [
        'upa_head_name',
        'upa_head_nip',
        'upa_head_position',
    ];
}
