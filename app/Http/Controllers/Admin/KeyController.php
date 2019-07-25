<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\KeyContainerRequest;
use App\Http\Traits\KeyContainerTrait;
use App\Http\Traits\KeyTypeTrait;
use App\Http\Traits\ShopTrait;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\KeyShopTrait;
use App\Http\Traits\CountryTrait;
use App\Key;
use App\KeyContainer;
use App\KeyInstruction;
use App\KeyShop;
use App\Shop;
use DB;

class KeyController extends Controller
{
    use KeyTypeTrait, ShopTrait, CompanyTrait, KeyContainerTrait, KeyShopTrait, CountryTrait;
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
        ->join('shopnames', 'shops.shopname_id', '=', 'shopnames.id')
        ->select('key_containers.id', 'key_containers.name', 'key_containers.container', 'companies.company', DB::raw('group_concat(distinct shopnames.name separator ", ") as shopName'), 'key_containers.active')
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
                $nestedData['name']     = $keyList->name.'<hr><div>Container: <span class="badge badge-info badge-pill">'.$keyList->container.'</span></div> <div>Company: <span class="badge badge-info badge-pill text-capitalize">'.$keyList->company.'</span></div> <div>Shops: '.$htmlBadgeShopName.'</div>';
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
            ->orWhere('shopnames.name', 'like', "%{$searchData}%");
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
            ->orWhere('shopnames.name', 'like', "%{$searchData}%");
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

            // Getting key types
            $keyTypeOptions = '';
            foreach($this->keytypes() as $keyValue => $keyType) {
                $keyTypeSelected = ($keyContainer->type === $keyValue) ? 'selected' : '';
                $keyTypeOptions .= '<option value="'.$keyValue.'" '.$keyTypeSelected.'>'.$keyType.'</option>';
            }

            // Getting companies
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

            // Getting Countries
            $countryOptions = '';
            foreach($this->country() as $country) {
                $countryOptions .= '<option value="'.$country->id.'">'.$country->code.'</option>';
            }

            $html        = '<a class="btn btn-secondary btn-sm editKey" data-keycontainerid="'.$keyContainer->id.'" data-keycontainercompanyid="'.$keyContainer->company->id.'"  data-toggle="modal" data-target="#editKeyModal_'.$keyContainer->id.'"><i class="fas fa-cog"></i></a> <a class="btn btn-secondary btn-sm createKeyInstruction" data-keyinstructioncontainerid="'.$keyContainer->id.'" data-toggle="modal" data-target="#keyInstructionModal_'.$keyContainer->id.'" data-toggle="tooltip" data-placement="top" title="Create Key Instructions"><i class="fas fa-folder-plus"></i></a>

            <div class="modal fade" id="keyInstructionModal_'.$keyContainer->id.'" tabindex="-1" role="dialog" aria-labelledby="createKeyInstructionLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="createKeyInstructionLabel">Create Key Instruction</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="keyInstructionValidationAlert"></div>
            <div class="form-group col-md-6">
            <label for="key_instruction_language">Language <span class="required">*</span></label>
            <select id="key_instruction_language_'.$keyContainer->id.'" class="form-control" name="key_instruction_language">
            <option value="">Choose Language</option>
            '.$countryOptions.'
            </select>
            </div>

            <div class="form-group col-md-12">
            <label for="key_instruction_file">Key Instruction File <span class="required">*</span></label>
            <input type="file" id="key_instruction_file_'.$keyContainer->id.'" class="form-control-file" name="key_instruction_file">
            </div>
            <button type="button" class="btn btn-primary btn-lg btn-block createKeyInstruction_'.$keyContainer->id.'"><i class="fas fa-plus"></i> Create Instruction </button>
            </div>

            </div>
            </div>
            </div>

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
            <label for="key_company_name">Company <span class="required">*</span></label>
            <select id="key_company_name_'.$keyContainer->id.'" class="form-control" name="key_company_name">
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
            $countOfKeys                     = $this->countKeys($request->keys); //Count of array

            $keyContainer                    = new KeyContainer;
            $keyContainer->name              = $request->key_name;
            $keyContainer->container         = $this->generateContainer($request->key_type);
            $keyContainer->company_id        = $request->key_company;
            $keyContainer->type              = $request->key_type;
            $keyContainer->activation_number = $request->key_activation_number;
            $keyContainer->count             = $countOfKeys;
            $keyContainer->total_activation  = $request->key_activation_number * $countOfKeys;
            $keyContainer->active            = 'no';
            $keyContainer->save();

            // Storing shops id in to key shop table
            foreach($request->key_shops as $shop) {
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
            $countOfKeys                     = $this->countKeys($request->keys); // Count of array

            // Deleting shops from key shop table
            KeyShop::where('key_container_id', $request->key_container_id)->delete();

            // Deleting keys from key table
            Key::where('key_container_id', $request->key_container_id)->delete();

            $keyContainer                    = KeyContainer::find($request->key_container_id);
            $keyContainer->name              = $request->key_name; 
            $keyContainer->company_id        = $request->key_company;
            $keyContainer->activation_number = $request->key_activation_number;
            $keyContainer->count             = $countOfKeys;
            $keyContainer->total_activation  = $request->key_activation_number * $countOfKeys;
            $keyContainer->save();

            // Storing shops id in to key shop table
            foreach($request->key_shop as $shop) {
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
            $key          = Key::where('key_container_id', $id)->delete();
            $keyShop      = KeyShop::where('key_container_id', $id)->delete();
            $keyContainer = KeyContainer::destroy($id); 
            
            DB::commit();
            return response()->json(['deletedKeyStatus' => 'success', 'message' => 'Key details deleted successfully'], 201);
        }   
        catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['deletedKeyStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
