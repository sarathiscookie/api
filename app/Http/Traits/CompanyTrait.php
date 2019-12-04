<?php

namespace App\Http\Traits;

use App\Company;

trait CompanyTrait {

    /**
     * Get all companies
     * @return \Illuminate\Http\Response
     */
	public function company()
	{
		try {
            $company = Company::select('id', 'company')
            ->active()
            ->get();

            return $company;
        }
        catch(\Exception $e) {
            abort(404);
        } 
	}

    /**
     * Get company matching with id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function fetchCompany($id)
    {
        try {
            $company = Company::select('company')
            ->active()
            ->find($id);

            return $company;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

    /**
     * Get rakuten companies
     * @return \Illuminate\Http\Response
     */
    public function fetchCompanyMatchingWithShop()
    {
        try {
            $company = Company::join('shops', 'companies.id', '=', 'shops.company_id')
            ->join('shopnames', 'shops.shopname_id', '=', 'shopnames.id')
            ->select('companies.id', 'companies.company')
            ->where('shopnames.id', 1) // Rakuten
            ->joinactive()
            ->get();

            return $company;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

}

?>