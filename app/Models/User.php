<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $guarded = [];

    protected $appends = ['role_label'];

    protected $hidden = [
        'role',
        'password',
        'remember_token',
        'updated_at'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdministrator()
    {
        $flag = $this->id == 1 ? true : false;

        return $flag;
    }

    public function getRoleLabelAttribute()
    {
        if ($this->role) {
            return $this->role->name;
        }
        
        return '';
    }

    public function getIsAdminAttribute()
    {
        return $this->attributes['admin'] === 'yes';
    }
}
