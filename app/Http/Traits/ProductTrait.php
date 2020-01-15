<?php

namespace App\Http\Traits;

use App\Product;

trait ProductTrait
{

    /**
     * Get product
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function productExist($id)
    {
        try {
            $product = Product::select('id', 'api_product_id')
                ->where('api_product_id', $id)
                ->first();

            return $product; 
        } 
        catch (\Exception $e) {
            abort(404);
        }
    }
}
