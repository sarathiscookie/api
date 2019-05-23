<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Set the user's name.
     *
     * @param  string  $value
     * @return void
     */
    public function setCompanyAttribute($value)
    {
        $this->attributes['company'] = strtolower($value);
    }

    /**
    * Get the user's first name.
    *
    * @param  string  $value
    * @return string
    */
    public function getCompanyAttribute($value)
    {
        return ucwords($value);
    }

    /**
    * Scope for manager role.
    *
    * @param  string  $query
    * @return string
    */
    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }
}
