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
                $nestedData['active']     = $this->productStatusHtml($productList['product_id']);
                $nestedData['actions']    = $this->moduleSettingsHtml($productList['product_id']);
                $data[]                   = $nestedData;
            }

        }
        return compact('data', 'totalData', 'totalFiltered');
    }

    /**
     * html for module settings 
     * @param  string $productApiId
     * @return \Illuminate\Http\Response
     */
    public function moduleSettingsHtml($productApiId)
    {
       $html = '<a href="" style="color:black;" class="moduleSettings_'.$productApiId.'" data-toggle="modal" data-target="#moduleModal_'.$productApiId.'"><i class="fas fa-cog"></i></a>
                <div class="modal fade" id="moduleModal_'.$productApiId.'" tabindex="-1" role="dialog" aria-labelledby="moduleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title">Module Settings</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">

                <form>
                <div class="form-group">
                <label for="module">Module:</label>
                <select class="form-control" id="delivery_status">
                <option>Choose Module</option>
                <option value="0">Rakuten Lieferanten Email</option>
                <option value="1">Rakuten Hardware Otto</option>
                <option value="2">Rakuten Hardware Bork</option>
                </select>
                </div>

                <div class="card">
                <div class="card-header">
                Email Settings
                </div>
                <div class="card-body">
                <div class="form-row">
                <div class="form-group col-md-6">
                <label for="to_name">To Name(Supplier Name):</label>
                <input type="text" class="form-control" id="to_name" readonly>
                </div>
                <div class="form-group col-md-6">
                <label for="to_email">To Email(Supplier Email):</label>
                <input type="email" class="form-control" id="to_email" readonly>
                </div>
                </div>

                <div class="form-row">
                <div class="form-group col-md-6">
                <label for="bcc_name">Bcc Name:</label>
                <input type="text" class="form-control" id="bcc_name" maxlength="150">
                </div>
                <div class="form-group col-md-6">
                <label for="bcc_email">Bcc Email:</label>
                <input type="email" class="form-control" id="bcc_email">
                </div>
                </div>

                <div class="form-group">
                <label for="email_subject">Email Subject:</label>
                <input type="text" class="form-control" id="email_subject" maxlength="200">
                </div>

                <div class="form-group">
                <label for="email_body">Email Body:</label>
                <textarea class="form-control" id="email_body" rows="3"></textarea>
                </div>

                <div class="form-group">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" id="activate_delivery_note_shipping">
                <label class="form-check-label" for="activate_delivery_note_shipping">
                Activate delivery note shipping
                </label>
                </div>
                </div>

                <div class="form-group">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" id="activate_customer_data_sending">
                <label class="form-check-label" for="activate_customer_data_sending">
                Activate customer data sending
                </label>
                </div>
                </div>

                <div class="form-group">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" id="enable_delivery_address_data_shipping">
                <label class="form-check-label" for="enable_delivery_address_data_shipping">
                Enable delivery address data shipping
                </label>
                </div>
                </div>

                </div>
                </div>

                <div class="card">
                <div class="card-header">
                Cron Settings
                </div>
                <div class="card-body">
                
                <div class="form-row">
                <div class="form-group col-md-4">
                <label for="max_error">Setting maximum error limit:</label>
                <input type="number" class="form-control" id="max_error" maxlength="3">
                </div>
                <div class="form-group col-md-4">
                <label for="call_count">How many times cron job called:</label>
                <input type="text" class="form-control" id="call_count" readonly>
                </div>
                <div class="form-group col-md-4">
                <label for="error_count">How many times cron job failed:</label>
                <input type="number" class="form-control" id="error_count" readonly>
                </div>
                </div>

                <div class="form-row">
                <div class="form-group col-md-6">
                <label for="last_error">When was cron job failed:</label>
                <input type="text" class="form-control" id="last_error" readonly>
                </div>
                <div class="form-group col-md-6">
                <label for="last_call">When was cron job last called:</label>
                <input type="text" class="form-control" id="last_call" readonly>
                </div>
                </div>

                </div>
                </div>

                <div class="card">
                <div class="card-header">
                Order & Delivery Settings
                </div>
                <div class="card-body">
                
                <div class="form-row">
                <div class="form-group col-md-4">
                <label for="delivery_status">Delivery status:</label>
                <select class="form-control" id="delivery_status">
                <option>Choose Status</option>
                <option value="0">Not Active</option>
                <option value="1">Active</option>
                <option value="2">Waiting</option>
                </select>
                </div>

                <div class="form-group col-md-4">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" id="order_in_logistics">
                <label class="form-check-label" for="order_in_logistics">
                Place order as set order in logistics
                </label>
                </div>
                </div>

                <div class="form-group col-md-4">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" id="order_shipped">
                <label class="form-check-label" for="order_shipped">
                Declare order as shipped
                </label>
                </div>
                </div>
                </div>

                </div>
                </div>

                <div class="card">
                <div class="card-header">
                MOD Settings
                </div>
                <div class="card-body">
                <div class="form-row">
                <div class="form-group col-md-6">
                <label for="wait_mod_no">Wait until the MOD pointer number is reached:</label>
                <input type="number" class="form-control" id="wait_mod_no">
                </div>
                <div class="form-group col-md-6">
                <label for="wait_mod_id">Wait until MOD has successfully completed with ID:</label>
                <input type="number" class="form-control" id="wait_mod_id">
                </div>
                </div>
                </div>
                </div>
  
                </form>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-primary saveModuleDetails">Save Changes</button></div>
                </div>
                </div>
                </div>';

        return $html;        
    }

    /**
     * html group button to change product status 
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function productStatusHtml($id)
    {
        //------------ Write query to check default status
        $checked = 'checked';
        $html    = '<label class="switch" data-productstatusid="'.$id.'">
        <input type="checkbox" class="buttonStatus" '.$checked.'>
        <span class="slider round"></span>
        </label>';

        return $html;
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
