<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopRequest;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\ShopnameTrait;
use App\Http\Traits\ShopTrait;
use App\Shop;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    use CompanyTrait, ShopnameTrait, ShopTrait;

    /**
     * Show the shops view page. Passing all active companies and shop names into the shops view page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Companies trait to fetch the active companies.
        $companies = $this->company();

        // Shop names trait to fetch the active shop names.
        $shopNames = $this->shopNames();

        return view('admin.shop', ['companies' => $companies, 'shopNames' => $shopNames]);
    }

    /**
     * Show the shops data into the view page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        // Getting all the http request.
        $params = $request->all();

        $columns = array(
            1 => 'shop',
            2 => 'active',
        );

         // Getting count of shops.
        $totalData      = Shop::join('shopnames AS sn', 'shops.shopname_id', '=', 'sn.id')
            ->join('companies AS cm', 'shops.company_id', '=', 'cm.id')
            ->select('shops.id', 'sn.name AS shop', 'shops.active', 'cm.company')
            ->count();

        // Query to select shops.
        $q              = Shop::join('shopnames AS sn', 'shops.shopname_id', '=', 'sn.id')
            ->join('companies AS cm', 'shops.company_id', '=', 'cm.id')
            ->select('shops.id', 'sn.name AS shop', 'shops.active', 'cm.company');

        $totalFiltered = $totalData;
        $limit         = (int)$request->input('length');
        $start         = (int)$request->input('start');
        $order         = $columns[$params['order'][0]['column']]; //contains column index
        $dir           = $params['order'][0]['dir']; //contains order such as asc/desc

        // If the request has a search value (shop name), this query will execute and fetch the results.
        if(!empty($request->input('search.value'))) {
            $this->searchShop($q, $request->input('search.value'));
        }

        // If the table has footer column value (shop name), this query will execute and fetch the results based on the shop name.
        if( !empty($params['columns'][1]['search']['value']) ) {
            $this->tfootShop($q, $params['columns'][1]['search']['value']);
        }

        // If the table has footer column value (shop status), this query will execute and fetch the results based on the shop status.
        if( !empty($params['columns'][2]['search']['value']) ) {
            $this->tfootShopStatus($q, $params['columns'][2]['search']['value']);
        }

        // Query scripts ends here.
        $shopLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if(!empty($shopLists)) {

            foreach ($shopLists as $key=> $shopList) {
                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$shopList->id.'" />';
                $nestedData['shop']       = ucwords($shopList->company).' <span class="badge badge-secondary text-capitalize">'.$shopList->shop.'</span>';
                $nestedData['active']     = $this->shopStatusHtml($shopList->id, $shopList->active);
                $nestedData['actions']    = $this->editShopModel($shopList->id);
                $data[]                   = $nestedData;
            }

        }

        // Preparing array to send the response in JSON format to draw the data on datatable.
        $json_data = array(
            'draw'            => (int)$params['draw'],
            'recordsTotal'    => (int)$totalData,
            'recordsFiltered' => (int)$totalFiltered,
            'data'            => $data
        );

        return response()->json($json_data);
    }

    /**
     * Function to search shops based on the shop name.
     * @param  object $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchShop($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('sn.name', 'like', "%{$searchData}%")
            ->orWhere('cm.company', 'like', "%{$searchData}%");
        });

        // Total filtered count
        $totalFiltered = $q->count();

        return $this;    
    }

    /**
     * Function to filter shops based on the shop name.
     * @param  object $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootShop($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('sn.name', 'like', "%{$searchData}%")
            ->orWhere('cm.company', 'like', "%{$searchData}%");
        });

        // Total filtered count
        $totalFiltered = $q->count();

        return $this;    
    }

    /**
     * Function to filter shops based on the shop status.
     * @param  object $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootShopStatus($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('shops.active', "{$searchData}");
        });

        // Total filtered count
        $totalFiltered = $q->count();

        return $this;    
    }

    /**
     * HTML group button to change shop status 
     * @param  int $id
     * @param  string $oldStatus   
     * @return \Illuminate\Http\Response
     */
    public function shopStatusHtml($id, $oldStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";

        $html    = '<label class="switch" data-shopstatusid="'.$id.'">
            <input type="checkbox" class="buttonStatus" '.$checked.'>
            <span class="slider round"></span>
            </label>';

        return $html;
    }

    /**
    * Update shop status.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function updateStatus(Request $request)
    {
        try {
            // Fetching shops details based on requested shop id.
            $shop         = Shop::findOrFail($request->shopStatusId);

            $shop->active = $request->newStatus;

            $shop->save();

            return response()->json(['shopStatusChange' => 'success', 'message' => 'Shop status updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['shopStatusChange' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Model to edit shop data
     * @param  integer $shopId
     * @return \Illuminate\Http\Response
     */
    public function editShopModel($shopId)
    {
        try {
            // Fetching shops details based on shop id.
            $shop               = $this->edit($shopId);

            // Company select box
            $companyOptions     = '';
            foreach($this->company() as $company) {
                $companySelected = ($shop->company_id === $company->id) ? 'selected' : '';
                $companyOptions .= '<option value="'.$company->id.'" '.$companySelected.'>'.$company->company.'</option>';
            }

            // Shop name select box
            $shopNameOptions    = '';
            foreach($this->shopNames() as $shopName) {
                $shopNameSelected = ($shop->shopname_id === $shopName->id) ? 'selected' : '';
                $shopNameOptions .= '<option value="'.$shopName->id.'" '.$shopNameSelected.'>'.$shopName->name.'</option>';
            }

            // Getting shop token
            $shopToken = ( ($shop->shopname->name === env('SHOP_NAME_AMAZONE')) || ($shop->shopname->name === env('SHOP_NAME_EBAY')) ) ? $shop->token : '';

            $html               = '<a class="btn btn-secondary btn-sm editShop cursor" data-shoptoken="'.$shopToken.'" data-shopid="'.$shop->id.'" data-toggle="modal" data-target="#editShopModal_'.$shop->id.'"><i class="fas fa-cog"></i></a>
            <div class="modal fade" id="editShopModal_'.$shop->id.'" tabindex="-1" role="dialog" aria-labelledby="editShopModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editShopModalLabel">Edit Shop Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="shopUpdateValidationAlert"></div>
            <div class="text-right">
            <a href="" class="btn btn-danger btn-sm deleteShop" data-deleteshopid="'.$shop->id.'"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="createdon">Created On:</label>
            <div class="badge badge-secondary" style="width: 6rem;">
            '.date('d.m.y', strtotime($shop->created_at)).'
            </div>
            

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="shop_name">Shop <span class="required">*</span></label>
            <select id="shop_name_'.$shop->id.'" class="form-control" name="shop_name">
            <option value="">Choose Shop</option>
            '.$shopNameOptions.'
            </select>

            </div>
            <div class="form-group col-md-6">

            <label for="shop_company">Company <span class="required">*</span></label>
            <select id="shop_company_'.$shop->id.'" class="form-control" name="shop_company">
            <option value="">Choose Company</option>
            '.$companyOptions.'
            </select>

            </div>
            </div>

            <div class="form-row shop_token_div_'.$shop->id.'">
            </div>

            <div class="form-row">
            <div class="form-group col-md-4">
            <label for="shop_mail_driver">Mail Driver <span class="required">*</span></label>
            <input id="shop_mail_driver_'.$shop->id.'" type="text" class="form-control" name="shop_mail_driver" maxlength="150" value="'.$shop->mail_driver.'">
            </div>

            <div class="form-group col-md-4">
            <label for="shop_mail_port">Mail Port <span class="required">*</span></label>
            <input id="shop_mail_port_'.$shop->id.'" type="text" class="form-control" name="shop_mail_port" maxlength="20" value="'.$shop->mail_port.'">
            </div>

            <div class="form-group col-md-4">
            <label for="shop_mail_encryption">Mail Encryption <span class="required">*</span></label>
            <input id="shop_mail_encryption_'.$shop->id.'" type="text" class="form-control" name="shop_mail_encryption" maxlength="20" value="'.$shop->mail_encryption.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12">
            <label for="shop_mail_host">Mail Host <span class="required">*</span></label>
            <input id="shop_mail_host_'.$shop->id.'" type="text" class="form-control" name="shop_mail_host" maxlength="150" value="'.$shop->mail_host.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="shop_mail_from_address">Mail From Address <span class="required">*</span></label>
            <input id="shop_mail_from_address_'.$shop->id.'" type="text" class="form-control" name="shop_mail_from_address" maxlength="255" value="'.$shop->mail_from_address.'">
            </div>

            <div class="form-group col-md-6">
            <label for="shop_mail_from_name">Mail From Name <span class="required">*</span></label>
            <input id="shop_mail_from_name_'.$shop->id.'" type="text" class="form-control" name="shop_mail_from_name" maxlength="150" value="'.$shop->mail_from_name.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="shop_mail_username">Mail Username <span class="required">*</span></label>
            <input id="shop_mail_username_'.$shop->id.'" type="text" class="form-control" name="shop_mail_username" maxlength="100" value="'.$shop->mail_username.'">
            </div>

            <div class="form-group col-md-6">
            <label for="shop_mail_password">Mail Password <span class="required">*</span></label>
            <input id="shop_mail_password_'.$shop->id.'" type="password" class="form-control" name="shop_mail_password" maxlength="255" value="'.$shop->mail_password.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12">
            <label for="shop_api_key">Api Key</label>
            <input id="shop_api_key_'.$shop->id.'" type="text" class="form-control" name="shop_api_key" maxlength="255" value="'.$shop->api_key.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="shop_customer_number">Customer Number</label>
            <input id="shop_customer_number_'.$shop->id.'" type="text" class="form-control" name="shop_customer_number" maxlength="100" value="'.$shop->customer_number.'">
            </div>

            <div class="form-group col-md-6">
            <label for="shop_password">Password</label>
            <input id="shop_password_'.$shop->id.'" type="password" class="form-control" name="shop_password" maxlength="255" value="'.$shop->password.'">
            </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateShop_'.$shop->id.'"><i class="far fa-edit"></i> Update Shop</button>

            </div>

            </div>
            </div>
            </div>';

            return $html;
            
        } 
        catch(\Exception $e){
            abort(404);
        }
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
     * Store a newly created shops in storage.
     *
     * @param  \App\Http\Requests\Admin\ShopRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShopRequest $request)
    {
        try {
            $shop                   = new Shop;
            $shop->shopname_id      = $request->shop_name;
            $shop->company_id       = $request->shop_company;
            $shop->mail_driver      = $request->shop_mail_driver;
            $shop->mail_port        = $request->shop_mail_port;
            $shop->mail_encryption  = $request->shop_mail_encryption;
            $shop->mail_host        = $request->shop_mail_host;
            $shop->mail_from_address= $request->shop_mail_from_address;
            $shop->mail_from_name   = $request->shop_mail_from_name;
            $shop->mail_username    = $request->shop_mail_username;
            $shop->mail_password    = $this->passwordGenerate($request->shop_mail_password);
            $shop->api_key          = $request->shop_api_key;
            $shop->token            = $request->shop_token;
            $shop->customer_number  = $request->shop_customer_number;
            $shop->password         = $this->passwordGenerate($request->shop_password);
            $shop->active           = 'no';
            $shop->save();

            return response()->json(['shopStatus' => 'success', 'message' => 'Well done! Shop created successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['shopStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
        // Fetching shops details based on shop id.
        $shop = Shop::with('shopname:id,name')->findOrFail($id);

        return $shop;
    }

    /**
     * Update the specified ships in storage.
     *
     * @param  \App\Http\Requests\Admin\ShopRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(ShopRequest $request)
    {
        try {
            // Fetching shops details based on requested shop id.
            $shop                    = Shop::find($request->shopid);
            $shop->shopname_id       = $request->shop_name;
            $shop->company_id        = $request->shop_company;
            $shop->token             = $request->shop_token;
            $shop->mail_driver       = $request->shop_mail_driver;
            $shop->mail_port         = $request->shop_mail_port;
            $shop->mail_encryption   = $request->shop_mail_encryption;
            $shop->mail_host         = $request->shop_mail_host;
            $shop->mail_from_address = $request->shop_mail_from_address;
            $shop->mail_from_name    = $request->shop_mail_from_name;
            $shop->mail_username     = $request->shop_mail_username;
            $shop->mail_password     = $this->passwordGenerate($this->passwordExtract($request->shop_mail_password));
            $shop->api_key           = $request->shop_api_key;
            $shop->customer_number   = $request->shop_customer_number;
            $shop->password          = $this->passwordGenerate($this->passwordExtract($request->shop_password));
            $shop->save();

            return response()->json(['shopStatusUpdate' => 'success', 'message' => 'Well done! Shop details updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['shopStatusUpdate' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        } 
    }

    /**
     * Remove the specified shops from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Shop::destroy($id);

            return response()->json(['deletedShopStatus' => 'success', 'message' => 'Shop details deleted successfully'], 201);
        }
        catch(\Exception $e) {
            return response()->json(['deletedShopStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
