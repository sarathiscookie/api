<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Set the module name.
     *
     * @param  string  $value
     * @return void
     */
    public function setModuleAttribute($value)
    {
        $this->attributes['module'] = strtolower($value);
    }

    /**
    * Get the module name.
    *
    * @param  string  $value
    * @return string
    */
    public function getModuleAttribute($value)
    {
        return ucwords($value);
    }

    /**
    * Scope for module status.
    *
    * @param  object  $query
    * @return string
    */
    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }
}
