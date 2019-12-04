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
     * Set the company name.
     *
     * @param  string  $value
     * @return void
     */
    public function setCompanyAttribute($value)
    {
        $this->attributes['company'] = strtolower($value);
    }

    /**
    * Get the company name.
    *
    * @param  string  $value
    * @return string
    */
    public function getCompanyAttribute($value)
    {
        return ucwords($value);
    }

    /**
     * Set the company street.
     *
     * @param  string  $value
     * @return void
     */
    public function setStreetAttribute($value)
    {
        $this->attributes['street'] = strtolower($value);
    }

    /**
    * Get the company street.
    *
    * @param  string  $value
    * @return string
    */
    public function getStreetAttribute($value)
    {
        return ucwords($value);
    }

    /**
     * Set the company city.
     *
     * @param  string  $value
     * @return void
     */
    public function setCityAttribute($value)
    {
        $this->attributes['city'] = strtolower($value);
    }

    /**
    * Get the company city.
    *
    * @param  string  $value
    * @return string
    */
    public function getCityAttribute($value)
    {
        return ucwords($value);
    }

    /**
    * Scope for company status.
    *
    * @param  string  $query
    * @return string
    */
    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }

    /**
    * Get the matching shops
    */
    public function shops()
    {
        return $this->hasMany(Shops::class);
    }

    /**
    * Get the matching country
    */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
    * Scope for shop status and company status.
    *
    * @param  string  $query
    * @return string
    */
    public function scopeJoinActive($query)
    {
        return $query->where('companies.active', 'yes')->where('shops.active', 'yes');
    }
}
