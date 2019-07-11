<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopRequest;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\ShopnameTrait;
use App\Shop;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    use CompanyTrait, ShopnameTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = $this->company();

        $shopNames = $this->shopNames();

        return view('admin.shop', ['companies' => $companies, 'shopNames' => $shopNames]);
    }

    /**
     * Display a listing of the resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $params = $request->all();

        $columns = array(
            1 => 'shop',
            2 => 'active',
        );

        $totalData = Shop::select('id', 'shopname_id', 'active', 'company_id')
        ->count();

        $q         = Shop::select('id', 'shopname_id', 'active', 'company_id');

        $totalFiltered = $totalData;
        $limit         = (int)$request->input('length');
        $start         = (int)$request->input('start');
        $order         = $columns[$params['order'][0]['column']]; //contains column index
        $dir           = $params['order'][0]['dir']; //contains order such as asc/desc

        // Search query for shop name
        if(!empty($request->input('search.value'))) {
            $this->searchShop($q, $request->input('search.value'));
        }

        // tfoot search query for shop name
        if( !empty($params['columns'][1]['search']['value']) ) {
            $this->tfootShop($q, $params['columns'][1]['search']['value']);
        }

        // tfoot search query for shop status
        if( !empty($params['columns'][2]['search']['value']) ) {
            $this->tfootShopStatus($q, $params['columns'][2]['search']['value']);
        }

        $shopLists = $q->skip($start)
        ->take($limit)
        ->orderBy($order, $dir)
        ->get();

        $data = [];

        if(!empty($shopLists)) {
            foreach ($shopLists as $key=> $shopList) {
                $company_shop_list        = (isset($this->fetchCompany($shopList->company_id)->company)) ? $this->fetchCompany($shopList->company_id)->company : '';
                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$shopList->id.'" />';
                $nestedData['shop']       = $shopList->shop.' <span class="badge badge-secondary">'.$company_shop_list.'</span>';
                $nestedData['active']     = $this->shopStatusHtml($shopList->id, $shopList->active);
                $nestedData['actions']    = $this->editShopModel($shopList->id);
                $data[]                   = $nestedData;
            }
        }

        $json_data = array(
            'draw'            => (int)$params['draw'],
            'recordsTotal'    => (int)$totalData,
            'recordsFiltered' => (int)$totalFiltered,
            'data'            => $data
        );

        return response()->json($json_data);
    }

    /**
     * Search query for shop name
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchShop($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('shop', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('shop', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * tfoot search query for shop name
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootShop($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('shop', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('shop', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * tfoot search query for shop status
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootShopStatus($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('active', "{$searchData}");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('active', "{$searchData}");
        })
        ->count();

        return $this;    
    }

    /**
     * html group button to change shop status 
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
            $shop               = $this->edit($shopId);
            $companyOptions     = '';

            foreach($this->company() as $company) {
                $companySelected = ($shop->company->id === $company->id) ? 'selected' : '';
                $companyOptions .= '<option value="'.$company->id.'" '.$companySelected.'>'.$company->company.'</option>';
            }

            $html               = '<a class="btn btn-secondary btn-sm editShop" data-shopid="'.$shop->id.'" data-toggle="modal" data-target="#editShopModal_'.$shop->id.'"><i class="fas fa-cog"></i></a>
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

            <label for="shop">Shop <span class="required">*</span></label>
            <input id="shop_'.$shop->id.'" type="text" class="form-control" name="shop" value="'.$shop->shop.'" maxlength="150">

            </div>
            <div class="form-group col-md-6">

            <label for="company">Company <span class="required">*</span></label>
            <select id="company_'.$shop->id.'" class="form-control" name="company">
            <option value="">Choose Company</option>
            '.$companyOptions.'
            </select>

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-4">
            <label for="mail_driver">Mail Driver <span class="required">*</span></label>
            <input id="mail_driver_'.$shop->id.'" type="text" class="form-control" name="mail_driver" maxlength="150" value="'.$shop->mail_driver.'">
            </div>

            <div class="form-group col-md-4">
            <label for="mail_port">Mail Port <span class="required">*</span></label>
            <input id="mail_port_'.$shop->id.'" type="text" class="form-control" name="mail_port" maxlength="20" value="'.$shop->mail_port.'">
            </div>

            <div class="form-group col-md-4">
            <label for="mail_encryption">Mail Encryption <span class="required">*</span></label>
            <input id="mail_encryption_'.$shop->id.'" type="text" class="form-control" name="mail_encryption" maxlength="20" value="'.$shop->mail_encryption.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12">
            <label for="mail_host">Mail Host <span class="required">*</span></label>
            <input id="mail_host_'.$shop->id.'" type="text" class="form-control" name="mail_host" maxlength="150" value="'.$shop->mail_host.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="mail_from_address">Mail From Address <span class="required">*</span></label>
            <input id="mail_from_address_'.$shop->id.'" type="text" class="form-control" name="mail_from_address" maxlength="255" value="'.$shop->mail_from_address.'">
            </div>

            <div class="form-group col-md-6">
            <label for="mail_from_name">Mail From Name <span class="required">*</span></label>
            <input id="mail_from_name_'.$shop->id.'" type="text" class="form-control" name="mail_from_name" maxlength="150" value="'.$shop->mail_from_name.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="mail_username">Mail Username <span class="required">*</span></label>
            <input id="mail_username_'.$shop->id.'" type="text" class="form-control" name="mail_username" maxlength="100" value="'.$shop->mail_username.'">
            </div>

            <div class="form-group col-md-6">
            <label for="mail_password">Mail Password <span class="required">*</span></label>
            <input id="mail_password_'.$shop->id.'" type="password" class="form-control" name="mail_password" maxlength="255" value="'.$shop->mail_password.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12">
            <label for="api_key">Api Key</label>
            <input id="api_key_'.$shop->id.'" type="text" class="form-control" name="api_key" maxlength="255" value="'.$shop->api_key.'">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="customer_number">Customer Number</label>
            <input id="customer_number_'.$shop->id.'" type="text" class="form-control" name="customer_number" maxlength="100" value="'.$shop->customer_number.'">
            </div>

            <div class="form-group col-md-6">
            <label for="password">Password</label>
            <input id="password_'.$shop->id.'" type="password" class="form-control" name="password" maxlength="255" value="'.$shop->password.'">
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
     * Store a newly created resource in storage.
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
            $shop->mail_password    = Hash::make($request->shop_mail_password);
            $shop->api_key          = $request->shop_api_key;
            $shop->customer_number  = $request->shop_customer_number;
            $shop->password         = Hash::make($request->shop_password);
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
        $shop = Shop::with('company:id,company')->findOrFail($id);
        return $shop;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Admin\ShopRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(ShopRequest $request)
    {
        try {
            //dd($request->all());
            Shop::where('id', (int)$request->shopid)
            ->update([
                'shop'              => $request->shop,
                'company_id'        => $request->company,
                'mail_driver'       => $request->mail_driver,
                'mail_port'         => $request->mail_port,
                'mail_encryption'   => $request->mail_encryption,
                'mail_host'         => $request->mail_host,
                'mail_from_address' => $request->mail_from_address,
                'mail_from_name'    => $request->mail_from_name,
                'mail_username'     => $request->mail_username,
                'mail_password'     => $request->mail_password,
                'api_key'           => $request->api_key,
                'customer_number'   => $request->customer_number,
                'password'          => $request->password
            ]);

            return response()->json(['shopStatusUpdate' => 'success', 'message' => 'Well done! Shop details updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['shopStatusUpdate' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        } 
    }

    /**
     * Remove the specified resource from storage.
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
