<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Set the shop name.
     *
     * @param  string  $value
     * @return void
     */
    public function setShopAttribute($value)
    {
        $this->attributes['shop'] = strtolower($value);
    }

    /**
    * Get the shop name.
    *
    * @param  string  $value
    * @return string
    */
    public function getShopAttribute($value)
    {
        return ucwords($value);
    }

    /**
     * Set the mail from name.
     *
     * @param  string  $value
     * @return void
     */
    public function setMailFromNameAttribute($value)
    {
        $this->attributes['mail_from_name'] = strtolower($value);
    }

    /**
    * Get the mail from name.
    *
    * @param  string  $value
    * @return string
    */
    public function getMailFromNameAttribute($value)
    {
        return ucwords($value);
    }

    /**
    * Scope for shop status.
    *
    * @param  string  $query
    * @return string
    */
    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }

    /**
    * Get the matching company
    */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}
