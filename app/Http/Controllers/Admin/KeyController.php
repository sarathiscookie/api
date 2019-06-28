<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\KeyRequest;
use App\Http\Traits\KeyContainerTrait;
use App\Http\Traits\KeyTypeTrait;
use App\Http\Traits\ShopTrait;
use App\Http\Traits\companyTrait;
use App\Key;
use App\KeyContainer;
use App\KeyInstruction;
use App\KeyShop;
use App\Shop;
use DB;
use Illuminate\Http\Request;

class KeyController extends Controller
{
    use KeyTypeTrait, ShopTrait, CompanyTrait, KeyContainerTrait;
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function findShops($id)
    {
        try {
            $shops = $this->getShops($id);

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

        $totalData     = KeyContainer::select('id', 'name', 'container', 'company_id', 'active')
        ->count();

        $q             = KeyContainer::select('id', 'name', 'container', 'company_id', 'active');

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
                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$keyList->id.'" />';
                $nestedData['name']       = $keyList->name.'<hr><div>Container: <span class="badge badge-info badge-pill">'.$keyList->container.'</span></div> <div>Company: <span class="badge badge-info badge-pill">'.$this->fetchCompany($keyList->company_id)->company.'</span></div> <div>Shops: '.$this->getShopsName($keyList->id).'</div>';
                $nestedData['active']     = '<label class="switch"><input type="checkbox" class="buttonStatus"><span class="slider round"></span></label>';
                $nestedData['actions']    = '<a class="btn btn-secondary btn-sm" data-toggle="modal"><i class="fas fa-cog"></i></a> <a class="btn btn-secondary btn-sm" data-toggle="modal"><i class="fas fa-pen"></i></a>';
                //$nestedData['active']     = $this->keyStatusHtml($keyList->id, $keyList->active);
                //$nestedData['actions']    = $this->editKeyModel($keyList->id);
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
     * Search query for key
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchKey($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * tfoot search query for key
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootKey($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        })
        ->count();

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
            $query->where('name', "{$searchData}");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('name', "{$searchData}");
        })
        ->count();

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
        $html    = '<label class="switch" data-keyid="'.$id.'">
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

            $key         = Key::findOrFail($request->keyId);

            $key->active = $request->newStatus;

            $key->save();

            return response()->json(['keyStatusChange' => 'success', 'message' => 'Key status updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['keyStatusChange' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Model to edit key data
     * @param  integer $keyId
     * @return \Illuminate\Http\Response
     */
    public function editKeyModel($keyId)
    {
        try {
            $key        = $this->edit($keyId);

            //shop = <option value="{{$shopDetail->id}}">{{$shopDetail->shop}}</option>
            //keytype = <option value="{{$keyValue}}">{{$keyType}}</option>
            //language = <option value="{{$language->id}}">{{$language->code}}</option>
            $html     = '<a class="btn btn-secondary btn-sm editKey" data-keyid="'.$key->id.'" data-toggle="modal" data-target="#editKeyModal_'.$key->id.'"><i class="fas fa-cog"></i></a>
            <div class="modal fade" id="editKeyModal_'.$key->id.'" tabindex="-1" role="dialog" aria-labelledby="editKeyModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editKeyModalLabel">Edit Key Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="keyUpdateValidationAlert"></div>
            <div class="text-right">
            <a href="" class="btn btn-danger btn-sm deleteEvent" data-id="'.$key->id.'"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="shop">Shop <span class="required">*</span></label>
            <select id="shop" class="form-control" name="shop">
            <option value="">Choose Shop</option>            
            </select>
            </div>

            <div class="form-group col-md-6">
            <label for="key_type">Key Type <span class="required">*</span></label>
            <select id="key_type" class="form-control" name="key_type">
            <option value="">Choose Type</option>            
            </select>
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12">
            <label for="key">Key <span class="required">*</span></label>
            <input id="key_'.$key->id.'" type="text" class="form-control" name="key" value="'.$key->key.'" autocomplete="key" maxlength="255">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="category">Category <span class="required">*</span></label>
            <input id="category_'.$key->id.'" type="category" class="form-control" name="category" value="'.$key->category.'" maxlength="150" autocomplete="category">
            </div>

            <div class="form-group col-md-6">
            <label for="language">Key Instruction Language <span class="required">*</span></label>
            <select id="language" class="form-control" name="language">
            <option value="">Choose Language</option>
            </select>
            </div>
            </div>

            <div class="form-row ">
            <div class="form-group col-md-12">
            <label for="instruction">Key Instructions</label>
            <textarea class="form-control" name="instruction" id="instruction" rows="3"></textarea>
            </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateKey_'.$key->id.'"><i class="fas fa-user-edit"></i> Update Key</button>

            </div>

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
     * @param  \App\Http\Requests\Admin\KeyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(KeyRequest $request)
    {
        DB::beginTransaction();
        try {
            $keyContainer                    = new KeyContainer;
            $keyContainer->name              = $request->key_name;
            $keyContainer->container         = $this->generateContainer($request->key_type);
            $keyContainer->company_id        = $request->company;
            $keyContainer->type              = $request->key_type;
            $keyContainer->activation_number = $request->act_number;
            $keyContainer->count             = $request->count;
            $keyContainer->total_activation  = $request->act_number * $request->count;
            $keyContainer->total_available   = $request->act_number * $request->count;
            $keyContainer->active            = 'no';
            $keyContainer->save();

            // Storing shops id in to key shop table
            foreach($request->shops as $shop) {
                $keyShops                   = new KeyShop;
                $keyShops->key_container_id = $keyContainer->id;
                $keyShops->shop_id          = $shop;
                $keyShops->save();
            }
            
            //Storing keys in to key table
            foreach($request->keys as $key) {
                $keyDetails                   = new Key;
                $keyDetails->key_container_id = $keyContainer->id;
                $keyDetails->key              = $key;
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
        $key = Key::findOrFail($id);
        return $key;
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
