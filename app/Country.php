<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $guarded = ['id'];

    /**
    * Scope for country status.
    *
    * @param  string  $query
    * @return string
    */
    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }
}
