<?php

namespace App\Http\Traits;

use App\Country;

trait CountryTrait {

    /**
     * Get all country
     * @return \Illuminate\Http\Response
     */
	public function country()
	{
		try {
            $country = Country::select('id', 'name')
            ->active()
            ->orderBy('name')
            ->get();

            return $country;
        }
        catch(\Exception $e) {
            abort(404);
        } 
	}

    /**
     * Get country matching with id
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function fetchCountry($id)
    {
        try {
            $country = Country::select('name')
            ->active()
            ->find($id);

            return $country;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }
}

?>