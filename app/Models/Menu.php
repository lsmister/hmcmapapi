<?php

namespace App\Models;

//菜单
class Menu extends Basic
{
    protected $guarded = [];

    protected $appends = ['parent_label'];

    protected $hidden = ['parent', 'updated_at'];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class);
    }

    //上级菜单title
    public function getParentLabelAttribute()
    {
        if (isset($this->attributes['parent_id'])) {
            if ($this->parent) {
                return $this->parent->title;
            }
        }

        return '';
    }

}
