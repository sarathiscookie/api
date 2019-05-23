<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Set the user's name.
     *
     * @param  string  $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    /**
    * Get the user's first name.
    *
    * @param  string  $value
    * @return string
    */
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }

    /**
    * Scope for manager role.
    *
    * @param  string  $query
    * @return string
    */
    public function scopeManager($query)
    {
        return $query->where('role', 'manager');
    }

    /**
    * Scope for admin role.
    *
    * @param  string  $query
    * @return string
    */
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    /**
    * Scope for employee role.
    *
    * @param  string  $query
    * @return string
    */
    public function scopeEmployee($query)
    {
        return $query->where('role', 'employee');
    }

}
