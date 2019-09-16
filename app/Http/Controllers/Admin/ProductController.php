<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\ShopnameTrait;
use App\Http\Traits\ShopTrait;
use App\Shop;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use CompanyTrait, ShopnameTrait, ShopTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = $this->company();

        $shopNames = $this->shopNames();

        return view('admin.productGet', ['companies' => $companies, 'shopNames' => $shopNames]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $shopId
     * @param  int  $companyId
     * @return \Illuminate\Http\Response
     */
    public function show($shopId, $companyId)
    {
        return view('admin.productList', ['shopId' => $shopId, 'companyId' => $companyId]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        try {
            $params           = $request->all();
            $totalData        = '';
            $totalFiltered    = '';
            $data             = [];
            $category_details = [];

            //If shop is rakuten then below code will execute.
            if($request->productListShopID === '1') {
                //get company api key from shops
                $api_key           = $this->getApiKey($request->productListShopID, $request->productListCompanyId); 

                $urlGetProducts    = 'http://webservice.rakuten.de/merchants/products/getProducts?key='.$api_key->api_key.'&format=json&page='.$request->pageActive;

                $urlShopCategories = 'http://webservice.rakuten.de/merchants/categories/getShopCategories?key='.$api_key->api_key.'&format=json';
            }

            //Getting product details
            if( !empty($urlGetProducts) ) {
                //Fetching data from API
                $jsonDecodedResults = $this->curl($urlGetProducts);

                //If json status is success then value is '1' error value is '-1'
                if($jsonDecodedResults['result']['success'] === '1') {

                    $columns = array(
                        1 => 'name',
                        2 => 'active',
                    );

                    $totalData     = $jsonDecodedResults['result']['products']['paging'][0]['total'];
                    $totalFiltered = $totalData;

                    if($jsonDecodedResults['result']['products']['paging'][0]['total'] != '0') {

                        foreach($jsonDecodedResults['result']['products']['product'] as $key => $productList) {
                            $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$productList['product_id'].'" />';
                            $nestedData['name']       = '<h6>'.$productList['name'].'</h6> <hr><div>Product Id: <span class="badge badge-info badge-pill">'.$productList['product_id'].'</span></div> <div>Producer: <span class="badge badge-info badge-pill text-capitalize">'.$productList['producer'].'</span></div> <div>Art No: <span class="badge badge-info badge-pill text-capitalize">'.$productList['product_art_no'].'</span></div>';
                            $nestedData['active']     = 'active';
                            $nestedData['actions']    = 'actions';
                            $data[]                   = $nestedData;
                        }

                    }

                }

            }

            //Getting shop category details
            if( !empty($urlShopCategories) ) {
                //Fetching data from API
                $jsonDecodedResults = $this->curl($urlShopCategories);

                //If json status is success then value is '1' error value is '-1'
                if($jsonDecodedResults['result']['success'] === '1') {
                    if($jsonDecodedResults['result']['categories']['paging'][0]['total'] != 0) {
                        $category_details[]   = $jsonDecodedResults['result']['categories']['category'];
                    }
                }
            }

            $json_data = array(
                'draw'            => (int)$params['draw'],
                'recordsTotal'    => (int)$totalData,
                'recordsFiltered' => (int)$totalFiltered,
                'data'            => $data,
                'categoryDetails' => $category_details[0]
            );

            return response()->json($json_data);
        } 
        catch(\Exception $e){
            return response()->json(['productListStatusMsg' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $url
     * @return \Illuminate\Http\Response
     */
    public function curl($url) {
        //create a new cURL resource
        $ch = curl_init();

        //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //set url and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //grab url and pass it to the browser
        $result = curl_exec($ch);
        //close cURL resouces, and free up system resources
        curl_close($ch);

        $jsonDecodedResults = json_decode($result, true);

        return $jsonDecodedResults;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
