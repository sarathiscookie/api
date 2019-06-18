<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\KeyRequest;
use App\Http\Traits\CountryTrait;
use App\Http\Traits\KeyTypeTrait;
use App\Http\Traits\ShopTrait;
use App\Key;
use App\KeyInstruction;
use App\Shop;
use Illuminate\Http\Request;
use DB;

class KeyController extends Controller
{
    use CountryTrait, KeyTypeTrait, ShopTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $languages   = $this->country();
        $keyTypes    = $this->keytype(); // Getting key types
        $shops       = Shop::select('id', 'shop')->active()->get();
        $shopDetails = (!empty($shops)) ? $shops : '';

        return view('admin.key', ['languages' => $languages, 'shopDetails' => $shopDetails, 'keyTypes' => $keyTypes]);
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
            1 => 'key',
            2 => 'active',
        );

        $totalData = Key::select('id', 'shop_id', 'key', 'key_type', 'category', 'active')
        ->count();

        $q         = Key::select('id', 'shop_id', 'key', 'key_type', 'category', 'active');

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

        // tfoot search query for user status
        if( !empty($params['columns'][2]['search']['value']) ) {
            $this->tfootKeyStatus($q, $params['columns'][2]['search']['value']);
        }

        $keyLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if(!empty($keyLists)) {
            foreach ($keyLists as $key=> $keyList) {
                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$keyList->id.'" />';
                $nestedData['key']        = $keyList->key.'<hr><div><small>Type: <span class="badge badge-info badge-pill">'.$keyList->key_type.'</span></small></div> <div><small>Category: <span class="badge badge-info badge-pill">'.$keyList->category.'</span></small></div> <div><small>Shop: <span class="badge badge-info badge-pill">'.$this->fetchShop($keyList->shop_id)->shop.'</span></small></div>';
                $nestedData['active']     = $this->keyStatusHtml($keyList->id, $keyList->active);
                $nestedData['actions']    = $this->editKeyModel($keyList->id);
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
            $query->where('key', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('key', 'like', "%{$searchData}%");
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
            $query->where('key', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('key', 'like', "%{$searchData}%");
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
            $query->where('active', "{$searchData}");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('active', "{$searchData}");
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
            $key            = new Key;
            $key->shop_id   = $request->shop;
            $key->category  = $request->category;
            $key->key       = $request->key;
            $key->key_type  = $request->key_type;
            $key->allot     = 'no';
            $key->active    = 'no';
            $key->save();

            $instruction                = new KeyInstruction;
            $instruction->key_id        = $key->id;
            $instruction->country_id    = $request->language;
            $instruction->instructions  = $request->instruction;
            $instruction->save();

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
