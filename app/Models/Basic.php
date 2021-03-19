<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Basic extends Model
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

}
