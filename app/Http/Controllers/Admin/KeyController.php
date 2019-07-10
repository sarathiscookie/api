<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\KeyContainerRequest;
use App\Http\Traits\KeyContainerTrait;
use App\Http\Traits\KeyTypeTrait;
use App\Http\Traits\ShopTrait;
use App\Http\Traits\companyTrait;
use App\Http\Traits\KeyShopTrait;
use App\Key;
use App\KeyContainer;
use App\KeyInstruction;
use App\KeyShop;
use App\Shop;
use DB;

class KeyController extends Controller
{
    use KeyTypeTrait, ShopTrait, CompanyTrait, KeyContainerTrait, KeyShopTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $keyTypes    = $this->keytypes(); // Getting key types
        $companies   = $this->company();

        return view('admin.key', [ 'keyTypes' => $keyTypes, 'companies' => $companies ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  int  $companyId
     * @return \Illuminate\Http\Response
     */
    public function findShops($companyId)
    {
        try {
            $shops = $this->getShops($companyId);

            return response()->json(['shopAvailableStatus' => 'success', 'shops' => $shops], 200);
        } 
        catch(\Exception $e){
            return response()->json(['shopAvailableStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
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
            1 => 'name',
            2 => 'active',
        );

        $q             = KeyContainer::join('key_shops', 'key_containers.id', '=', 'key_shops.key_container_id')
        ->join('shops', 'key_shops.shop_id', '=', 'shops.id')
        ->join('companies', 'key_containers.company_id', '=', 'companies.id')
        ->select('key_containers.id', 'key_containers.name', 'key_containers.container', 'companies.company', DB::raw('group_concat(distinct shops.shop separator ", ") as shopName'), 'key_containers.active')
        ->groupBy('key_containers.id');

        $totalData     = $q->count();

        $totalFiltered = $totalData;
        $limit         = (int)$request->input('length');
        $start         = (int)$request->input('start');
        $order         = $columns[$params['order'][0]['column']]; //contains column index
        $dir           = $params['order'][0]['dir']; //contains order such as asc/desc

        // Search query for key
        if(!empty($request->input('search.value'))) {
            $this->searchKey($q, $request->input('search.value'));
        }

        // tfoot search query for key
        if( !empty($params['columns'][1]['search']['value']) ) {
            $this->tfootKey($q, $params['columns'][1]['search']['value']);
        }

        // tfoot search query for key status
        if( !empty($params['columns'][2]['search']['value']) ) {
            $this->tfootKeyStatus($q, $params['columns'][2]['search']['value']);
        }

        $keyLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if( !empty($keyLists) ) {
            foreach ($keyLists as $key => $keyList) {

                $htmlBadgeShopName      = '';
                foreach(explode(', ', $keyList->shopName) as $shopKey => $shopName) {
                    $htmlBadgeShopName .= '<span class="badge badge-info badge-pill">'.$shopName.'</span>';
                }

                $nestedData['hash']     = '<input class="checked" type="checkbox" name="id[]" value="'.$keyList->id.'" />';
                $nestedData['name']     = $keyList->name.'<hr><div>Container: <span class="badge badge-info badge-pill">'.$keyList->container.'</span></div> <div>Company: <span class="badge badge-info badge-pill">'.$keyList->company.'</span></div> <div>Shops: '.$htmlBadgeShopName.'</div>';
                $nestedData['active']   = $this->keyStatusHtml($keyList->id, $keyList->active);
                $nestedData['actions']  = $this->editKeyContainerModel($keyList->id);
                $data[]                 = $nestedData;
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
     * Search query for key name, shops and country
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchKey($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('key_containers.name', 'like', "%{$searchData}%")
            ->orWhere('companies.company', 'like', "%{$searchData}%")
            ->orWhere('shops.shop', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->count();

        return $this;    
    }

    /**
     * tfoot search query for key name, shop and country
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootKey($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('key_containers.name', 'like', "%{$searchData}%")
            ->orWhere('companies.company', 'like', "%{$searchData}%")
            ->orWhere('shops.shop', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->count();

        return $this;    
    }

    /**
     * tfoot search query for key status
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootKeyStatus($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('key_containers.active', "{$searchData}");
        });

        $totalFiltered = $q->count();

        return $this;    
    }

    /**
     * html group button to change key status 
     * @param  int $id
     * @param  string $oldStatus  
     * @return \Illuminate\Http\Response
     */
    public function keyStatusHtml($id, $oldStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";
        $html    = '<label class="switch" data-keycontid="'.$id.'">
        <input type="checkbox" class="buttonStatus" '.$checked.'>
        <span class="slider round"></span>
        </label>';

        return $html;
    }

    /**
     * Update key status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        try {

            $keyContainer         = KeyContainer::findOrFail($request->keycontid);

            $keyContainer->active = $request->newStatus;

            $keyContainer->save();

            return response()->json(['keyContainerStatusChange' => 'success', 'message' => 'Key status updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['keyContainerStatusChange' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Model to edit key container data
     * @param  integer $keyContainerId
     * @return \Illuminate\Http\Response
     */
    public function editKeyContainerModel($keyContainerId)
    {
        try {
            $keyContainer   = $this->edit($keyContainerId);
            $keyTypes       = $this->keytypes();
            $companies      = $this->company();

            $keyTypeOptions = '';

            foreach($this->keytypes() as $keyValue => $keyType) {
                $keyTypeSelected = ($keyContainer->type === $keyValue) ? 'selected' : '';
                $keyTypeOptions .= '<option value="'.$keyValue.'" '.$keyTypeSelected.'>'.$keyType.'</option>';
            }

            $companyOptions = '';

            foreach($this->company() as $company) {
                $companySelected = ($keyContainer->company->id === $company->id) ? 'selected' : '';
                $companyOptions .= '<option value="'.$company->id.'" '.$companySelected.'>'.$company->company.'</option>';
            }

            $keys = '';
            foreach($keyContainer->keys as $keyDetail) {
                $keys .= $keyDetail->key.',';
            }

            // Getting shops
            $shopOptions = '';
            foreach($this->getShops($keyContainer->company->id) as $shop) {
                $shopSelected = ($this->getKeyShop($keyContainer->id, $shop->id) !== null) ? 'selected' : '';
                $shopOptions .= '<option value="'.$shop->id.'" '.$shopSelected.'>'.$shop->shop.'</option>';
            }

            /*<a class="btn btn-secondary btn-sm" data-toggle="modal"><i class="fas fa-pen"></i></a>*/
            $html         = '<a class="btn btn-secondary btn-sm editKey" data-keycontainerid="'.$keyContainer->id.'" data-keycontainercompanyid="'.$keyContainer->company->id.'"  data-toggle="modal" data-target="#editKeyModal_'.$keyContainer->id.'"><i class="fas fa-cog"></i></a>
            <div class="modal fade" id="editKeyModal_'.$keyContainer->id.'" tabindex="-1" role="dialog" aria-labelledby="editKeyModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editKeyModalLabel">Edit Key Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="keyUpdateValidationAlert_'.$keyContainer->id.'"></div>
            <div class="text-right">
            <a href="" class="btn btn-danger btn-sm deleteEvent" data-id="'.$keyContainer->id.'"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="key_name_edit">Key Name <span class="required">*</span></label>
            <input type="text" name="key_name_edit" id="key_name_edit_'.$keyContainer->id.'" class="form-control"  maxlength="100" value="'.$keyContainer->name.'">
            </div>
            <div class="form-group col-md-6">
            <label for="company_edit">Company <span class="required">*</span></label>
            <select id="company_edit_'.$keyContainer->id.'" class="form-control" name="company_edit">
            <option value="">Choose Company</option>
            '.$companyOptions.'
            </select>
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12" id="div_shop_edit_'.$keyContainer->id.'">
            <div class="no_shop_alert_'.$keyContainer->id.'"></div>

            <div class="div_shop_edit_'.$keyContainer->id.'">
            <label for="shop_edit">Shops <span class="required">*</span></label>
            <div id="shop_edits_first_div_'.$keyContainer->id.'">
            <select id="shop_edits_'.$keyContainer->id.'" class="form-control shop_edits_'.$keyContainer->id.'" name="shop_edit[]" multiple="multiple">
            <option class="first_option_shop_edit_'.$keyContainer->id.'" value="" disabled="disabled">Choose Shop</option>
            '.$shopOptions.'
            </select>
            </div>

            </div>
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12">
            <label for="key_edit">Key <i class="far fa-question-circle" data-toggle="tooltip" data-placement="right" title="You can separated keys with commas, space and new line. But dont mix with these."></i><span class="required">*</span></label>
            <textarea class="form-control" name="keys_edit" id="keys_edit_'.$keyContainer->id.'" rows="3">'.trim($keys, ",").'</textarea>
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="activation_number_edit">Activation Number <span class="required">*</span></label>
            <input type="number" id="activation_number_edit_'.$keyContainer->id.'" class="form-control" name="activation_number_edit" maxlength="10" value="'.$keyContainer->activation_number.'">
            </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateKeyContainer_'.$keyContainer->id.'"><i class="far fa-edit"></i> Update </button>

            </div>
            </div>
            </div>';

            return $html;
        } 
        catch(\Exception $e) {
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
     * @param  \App\Http\Requests\Admin\KeyContainerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(KeyContainerRequest $request)
    {
        DB::beginTransaction();
        try {
            $keyContainer                    = new KeyContainer;
            $keyContainer->name              = $request->key_name;
            $keyContainer->container         = $this->generateContainer($request->key_type);
            $keyContainer->company_id        = $request->company;
            $keyContainer->type              = $request->key_type;
            $keyContainer->activation_number = $request->act_number;
            $keyContainer->count             = $this->countKeys($request->keys);
            $keyContainer->total_activation  = $request->act_number * $this->countKeys($request->keys);
            $keyContainer->active            = 'no';
            $keyContainer->save();

            // Storing shops id in to key shop table
            foreach($request->shops as $shop) {
                $keyShops                   = new KeyShop;
                $keyShops->key_container_id = $keyContainer->id;
                $keyShops->shop_id          = $shop;
                $keyShops->save();
            }
            
            // Storing keys in to key table
            foreach($request->keys as $key) {
                $keyDetails                   = new Key;
                $keyDetails->key_container_id = $keyContainer->id;
                $keyDetails->key              = preg_replace('/[ ,]+/', '', $key);
                $keyDetails->available        = 1;
                $keyDetails->save();
            }

            DB::commit();
            return response()->json(['keyStatus' => 'success', 'message' => 'Well done! Key created successfully'], 201);
        } 
        catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['keyStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
        $keyContainer = KeyContainer::findOrFail($id);
        return $keyContainer;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Admin\KeyContainerRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(KeyContainerRequest $request)
    {
        DB::beginTransaction();
        try {
            $keyContainer                    = KeyContainer::find($request->key_container_id);
            $keyContainer->name              = $request->key_name_edit; 
            $keyContainer->company_id        = $request->company_edit;
            $keyContainer->activation_number = $request->activation_number_edit;
            $keyContainer->count             = $this->countKeys($request->keys_edit);
            $keyContainer->total_activation  = $request->activation_number_edit * $this->countKeys($request->keys_edit);
            $keyContainer->save();

            // Deleting and Storing shops id in to key shop table
            KeyShop::where('key_container_id', $keyContainer->id)->delete();
            foreach($request->shop_edit as $shop) {
                $keyShops                   = new KeyShop;
                $keyShops->key_container_id = $keyContainer->id;
                $keyShops->shop_id          = $shop;
                $keyShops->save();
            }
            
            // Deleting and Storing keys in to key table
            Key::where('key_container_id', $keyContainer->id)->delete();
            foreach($request->keys_edit as $key) {
                $keyDetails                   = new Key;
                $keyDetails->key_container_id = $keyContainer->id;
                $keyDetails->key              = preg_replace('/[ ,]+/', '', $key);
                $keyDetails->available        = 1;
                $keyDetails->save();
            }

            DB::commit();
            return response()->json(['keyUpdatedStatus' => 'success', 'message' => 'Well done! Key details updated successfully'], 201);
        } 
        catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['keyUpdatedStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
        DB::beginTransaction();
        try {
            $keyInstruction  = KeyInstruction::where('key_id', $id)->first();
            $keyInstruction->delete();

            $key             = Key::destroy($id);
            
            DB::commit();
            return response()->json(['deletedKeyStatus' => 'success', 'message' => 'Key details deleted successfully'], 201);
        }   
        catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['deletedKeyStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
