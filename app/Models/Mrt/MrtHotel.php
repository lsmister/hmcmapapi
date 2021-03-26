<?php

namespace App\Models\Mrt;

use Illuminate\Database\Eloquent\Model;

class MrtHotel extends Model
{

    protected $guarded = [];
    

    public function hotelGroup()
    {
        return $this->belongsTo(HotelGroup::class, 'group_id');
    }
    
    public function basicRooms()
    {
        return $this->hasMany(MrtBasicRoom::class, 'hotel_code', 'code');
    }
}
