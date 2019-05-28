<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Shop;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.shop');
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

        $totalData = Shop::select('id', 'shop', 'active')
        ->count();

        $q         = Shop::select('id', 'shop', 'active');

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
                if($shopList->active === 'yes') {
                    $yesStatus    = 'btn-success';
                    $noStatus     = 'btn-secondary';
                }
                else {
                    $yesStatus    = 'btn-secondary';
                    $noStatus     = 'btn-danger';
                }

                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$shopList->id.'" />';
                $nestedData['shop']       = $shopList->shop;
                $nestedData['active']     = $this->shopStatusHtml($shopList->id, $shopList->active, $yesStatus, $noStatus);
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
     * @param  string $yesStatus
     * @param  string $noStatus  
     * @return \Illuminate\Http\Response
     */
    public function shopStatusHtml($id, $oldStatus, $yesStatus, $noStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";
        $html    = '<label class="switch" data-shopstatusid="'.$id.'">
        <input type="checkbox" class="buttonStatus" '.$checked.'>
        <span class="slider round"></span>
        </label>';

        return $html;
    }

    /**
     * Model to edit shop data
     * @param  integer $shopId
     * @return \Illuminate\Http\Response
     */
    public function editShopModel($shopId)
    {
        try {
            $html               = '<a class="btn btn-secondary btn-sm editShop" data-shopid="'.$shop->id.'" data-toggle="modal" data-target="#editShopModal_'.$shop->id.'"><i class="fas fa-cog"></i></a>';
            /*$shop            = $this->edit($shopId);
            $countrySelected    = ($shop->country === 'de') ? 'selected' : '';
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

            <label for="phone">Created On:</label>
            <div class="badge badge-secondary" style="width: 6rem;">
            '.date('d.m.y', strtotime($shop->created_at)).'
            </div>
            

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="name">Shop <span class="required">*</span></label>
            <input id="shop_'.$shop->id.'" type="text" class="form-control" name="shop" value="'.$shop->shop.'" autocomplete="shop" maxlength="255">

            </div>
            <div class="form-group col-md-6">

            <label for="phone">Phone <span class="required">*</span></label>
            <input id="phone_'.$shop->id.'" type="text" class="form-control" name="phone" value="'.$shop->phone.'" maxlength="20" autocomplete="phone">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="street">Street <span class="required">*</span></label>
            <input id="street_'.$shop->id.'" type="text" class="form-control" name="street" value="'.$shop->street.'" maxlength="255" autocomplete="street">

            </div>
            <div class="form-group col-md-6">

            <label for="city">City <span class="required">*</span></label>
            <input id="city_'.$shop->id.'" type="text" class="form-control" name="city" value="'.$shop->city.'" maxlength="255" autocomplete="city">

            </div>
            </div>

            <div class="form-row">
            
            <div class="form-group col-md-6">

            <label for="country">Country <span class="required">*</span></label>
            <select id="country_'.$shop->id.'" class="form-control" name="country">
            <option value="">Choose Country</option>
            <option value="de" '.$countrySelected.'>Germany</option>
            </select>

            </div>
            <div class="form-group col-md-6">

            <label for="zip">Zip <span class="required">*</span></label>
            <input id="zip_'.$shop->id.'" type="text" class="form-control" name="zip" value="'.$shop->postal.'" maxlength="20" autocomplete="zip">

            </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateShop_'.$shop->id.'"><i class="far fa-edit"></i> Update Shop</button>

            </div>

            </div>
            </div>
            </div>';*/

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
