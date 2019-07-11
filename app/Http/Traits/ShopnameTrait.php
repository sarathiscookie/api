<?php

namespace App\Http\Traits;

use App\Shopname;

trait ShopnameTrait {

    /**
     * Get all shop names
     * @return \Illuminate\Http\Response
     */
	public function shopNames()
	{
		try {
            $shopNames = Shopname::get();
            return $shopNames;
        }
        catch(\Exception $e) {
            abort(404);
        } 
	}
}

?>