<?php

namespace App\Models;


//角色
class Role extends Basic
{
    protected $guarded = [];

    protected $hidden = [
        'updated_at'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class);
    }
}
