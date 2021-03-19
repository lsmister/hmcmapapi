<?php

namespace App\Models;


//城市
class City extends Basic
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

}
