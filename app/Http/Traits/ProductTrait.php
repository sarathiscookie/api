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
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /** 
     * Get supplier details related to product and company.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getSupplierDetails($id)
    {
        try {
            $supplier = Product::select('users.id AS supplierId', 'users.name AS supplierName', 'users.email AS supplierEmail')
                ->join('user_companies', 'products.company_id', '=', 'user_companies.company_id')
                ->join('companies', 'user_companies.company_id', '=', 'companies.id')
                ->join('users', 'user_companies.user_id', '=', 'users.id')
                ->where('companies.active', 'yes')
                ->where('users.active', 'yes')
                ->where('users.role', 'supplier')
                ->where('api_product_id', $id)
                ->get();

            return $supplier;
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
