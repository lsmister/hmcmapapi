<?php

namespace App\Models;

//菜单
class Menu extends Basic
{
    protected $guarded = [];

    protected $appends = ['parent_name'];

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
    public function getParentNameAttribute()
    {
        if ($this->attributes['parent_id'] != 0) {
            if ($this->parent) {
                return $this->parent->title;
            }else {
                return '';
            }
        }

        return '顶级';
    }

    public function categoryMenu($menus, $parent_id)
    {
        $data = [];
        foreach($menus as $v) {
            if($v['parent_id'] == $parent_id) {
                $data[] = $v;
            }
        }

        if(count($data) == 0) {
            return [];
        }else {
            foreach($data as $key => $val) {
                $info[$key]['id'] = $val['id'];
                $info[$key]['label'] = $val['title'];
                $info[$key]['icon'] = $val['icon'];
                $info[$key]['children'] = $this->categoryMenu($menus, $val['id']);
                if(empty($info[$key]['children'])) {
                    unset($info[$key]['children']);
                }
            }
        }

        return $info;
    }

}
