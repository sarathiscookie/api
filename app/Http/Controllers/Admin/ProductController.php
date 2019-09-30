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
            $product_details  = '';
            $category_details = '';
            $search           = '';
            $visible          = '';
            $available        = '';

            // Search query for product name
            if( !empty($request->input('search.value')) ) {
                $search = "&search=".urlencode($request->input('search.value'))."&search_field=name";
            }

            //If shop is rakuten then below code will execute.
            if($request->productListShopID === '1') {

                // Search query for category
                if( ($request->productCategoryId !== 'allCategories') && ($request->productCategoryId !== null) ) {
                    $search = "&search=".urlencode($request->productCategoryId)."&search_field=shop_category_id";
                }

                //Filter visible: 1 = Visible & 0 = Not visible
                if($request->visible !== null) {
                    $visible = "&visible=".$request->visible;
                }

                //Filter available: 1 = Available & 0 = Not available
                if($request->available !== null) {
                    $available = "&available=".$request->available;
                }

                //get company api key from shops
                $api_key           = $this->getApiKey($request->productListShopID, $request->productListCompanyId);

                $urlGetProducts    = 'http://webservice.rakuten.de/merchants/products/getProducts?key='.$api_key->api_key.'&format=json&page='.$request->pageActive.$visible.$available.$search;

                $urlShopCategories = 'http://webservice.rakuten.de/merchants/categories/getShopCategories?key='.$api_key->api_key.'&format=json';
            }

            //Get product details
            if( !empty($urlGetProducts) ) {
                $product_details  = $this->getUrlProducts($urlGetProducts);
            }
            
            //Get shop categories
            if( !empty($urlShopCategories) ) {
                $category_details = $this->getUrlShopCategories($urlShopCategories);
            }

            $json_data = array(
                'draw'            => (int)$params['draw'],
                'recordsTotal'    => (int)$product_details['totalData'],
                'recordsFiltered' => (int)$product_details['totalFiltered'],
                'data'            => $product_details['data'],
                'categoryDetails' => $category_details
            );

            return response()->json($json_data);
        } 
        catch(\Exception $e){
            return response()->json(['productListStatusMsg' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Get shop products details.
     *
     * @param  string  $urlShopCategories
     * @return \Illuminate\Http\Response
     */
    public function getUrlProducts($urlGetProducts)
    {
        $data          = [];
        $totalData     = '';
        $totalFiltered = '';
        $columns       = [ 1 => 'name', 2 => 'active' ];

        //Fetching data from API
        $jsonDecodedResults = $this->curl($urlGetProducts);

        //If json status is success then value is '1' error value is '-1'
        if( ($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['products']['paging'][0]['total'] != '0') ) {

            $totalData       = $jsonDecodedResults['result']['products']['paging'][0]['total'];
            $totalFiltered   = $totalData;

            foreach($jsonDecodedResults['result']['products']['product'] as $key => $productList) {
                $visibleStatus            = ($productList['visible'] === '1') ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';

                //Some products doesn't have available status in product array. Eg:1918778210,1918779405,1918780015
                if( $productList['has_variants'] === '1' && empty($productList['available']) ) {
                    $availableStatus      = '<i class="fas fa-thumbs-down"></i>';
                }
                else {
                    $availableStatus      = ($productList['available'] === '1') ? '<i class="fas fa-thumbs-up"></i>' : '<i class="fas fa-thumbs-down"></i>';
                }

                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$productList['product_id'].'" />';
                $nestedData['name']       = '<h6>'.$productList['name'].'</h6> <hr><div>Product Id: <span class="badge badge-info badge-pill">'.$productList['product_id'].'</span></div> <div>Producer: <span class="badge badge-info badge-pill text-capitalize">'.$productList['producer'].'</span></div> <div>Art No: <span class="badge badge-info badge-pill text-capitalize">'.$productList['product_art_no'].'</span></div> <div>Visible: '.$visibleStatus.'</div> <div>Available: '.$availableStatus.'</div>';
                $nestedData['active']     = '<i class="fas fa-check"></i>';
                $nestedData['actions']    = '<i class="fas fa-cog"></i>';
                $data[]                   = $nestedData;
            }

        }
        return compact('data', 'totalData', 'totalFiltered');
    }

    /**
     * Get shop categories details.
     *
     * @param  string  $urlShopCategories
     * @return \Illuminate\Http\Response
     */
    public function getUrlShopCategories($urlShopCategories) 
    {
        $category_details        = [];
        $category_details_offset = [];

        //Fetching data from API
        $jsonDecodedResults = $this->curl($urlShopCategories);

        //If json status is success then value is '1' error value is '-1'
        if($jsonDecodedResults['result']['success'] === '1') {
            if($jsonDecodedResults['result']['categories']['paging'][0]['total'] != 0) {
                $category_details[]        = $jsonDecodedResults['result']['categories']['category'];
                $category_details_offset   = $category_details[0];
            }
        }

        return $category_details_offset;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $url
     * @return \Illuminate\Http\Response
     */
    public function curl($url) 
    {
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
