<?php

namespace App\Models\Mrt;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Model;

class MrtRoomType extends Model
{
    public $timestamps = false;

    protected $appends = ['hotel_name', 'hotel_ctrip_code'];
    
    
    public function hotel()
    {
        return $this->belongsTo(MrtHotel::class, 'hotel_code', 'code');
    }


    public function gethotelNameAttribute()
    {
        return $this->hotel->en_name;
    }

    public function gethotelCtripCodeAttribute()
    {
        return $this->hotel->ctrip_hotel_code;
    }


}
