<?php

namespace App\Models\Mrt;

use App\Models\AdminUser;
use App\Models\MrtHotel;
use Illuminate\Database\Eloquent\Model;

class MrtSubRoom extends Model
{
    public $timestamps = false;
    
    
    public function hotel()
    {
        return $this->belongsTo(MrtHotel::class, 'hotel_code', 'code');
    }

}
