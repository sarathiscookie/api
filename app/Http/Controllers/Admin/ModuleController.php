<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ModuleRequest;
use App\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.module');
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
            1 => 'module',
            2 => 'active',
        );

        $totalData     = Module::select('id', 'module', 'active')
        ->count();

        $q             = Module::select('id', 'module', 'active');

        $totalFiltered = $totalData;
        $limit         = (int)$request->input('length');
        $start         = (int)$request->input('start');
        $order         = $columns[$params['order'][0]['column']]; //contains column index
        $dir           = $params['order'][0]['dir']; //contains order such as asc/desc

        // Search query for module name
        if(!empty($request->input('search.value'))) {
            $this->searchModule($q, $request->input('search.value'));
        }

        // tfoot search query for module name
        if( !empty($params['columns'][1]['search']['value']) ) {
            $this->tfootModule($q, $params['columns'][1]['search']['value']);
        }

        // tfoot search query for module status
        if( !empty($params['columns'][2]['search']['value']) ) {
            $this->tfootModuleStatus($q, $params['columns'][2]['search']['value']);
        }

        $moduleLists = $q->skip($start)
        ->take($limit)
        ->orderBy($order, $dir)
        ->get();

        $data = [];

        if(!empty($moduleLists)) {
            foreach ($moduleLists as $key=> $moduleList) {
                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$moduleList->id.'" />';
                $nestedData['module']     = $moduleList->module;
                $nestedData['active']     = $this->moduleStatusHtml($moduleList->id, $moduleList->active);
                $nestedData['actions']    = $this->editModuleModel($moduleList->id);
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
     * Search query for module name
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchModule($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('module', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('module', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * tfoot search query for module name
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootModule($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('module', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('module', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * tfoot search query for module status
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootModuleStatus($q, $searchData)
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
     * html group button to change module status 
     * @param  int $id
     * @param  string $oldStatus
     * @return \Illuminate\Http\Response
     */
    public function moduleStatusHtml($id, $oldStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";
        $html    = '<label class="switch" data-modulestatusid="'.$id.'">
        <input type="checkbox" class="buttonStatus" '.$checked.'>
        <span class="slider round"></span>
        </label>';

        return $html;
    }

    /**
     * Update manager status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        try {

            $module         = Module::findOrFail($request->moduleStatusId);

            $module->active = $request->newStatus;

            $module->save();

            return response()->json(['moduleStatusChange' => 'success', 'message' => 'Module status updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['moduleStatusChange' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Model to edit module data
     * @param  integer $moduleId
     * @return \Illuminate\Http\Response
     */
    public function editModuleModel($moduleId)
    {
        try {
            $module            = $this->edit($moduleId);

            $html              = '<a class="btn btn-secondary btn-sm editModule cursor" data-moduleid="'.$module->id.'" data-toggle="modal" data-target="#editModuleModal_'.$module->id.'"><i class="fas fa-cog"></i></a>
            <div class="modal fade" id="editModuleModal_'.$module->id.'" tabindex="-1" role="dialog" aria-labelledby="editModuleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editModuleModalLabel">Edit Module Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="moduleUpdateValidationAlert"></div>
            <div class="text-right">
            <a href="" class="btn btn-danger btn-sm deleteModule" data-deletemoduleid="'.$module->id.'"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="createdon">Created On:</label>
            <div class="badge badge-secondary" style="width: 6rem;">
            '.date('d.m.y', strtotime($module->created_at)).'
            </div>
            

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12">

            <label for="name">Module <span class="required">*</span></label>
            <input id="module_'.$module->id.'" type="text" class="form-control" name="module" value="'.$module->module.'" autocomplete="module" maxlength="255">

            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateModule_'.$module->id.'"><i class="far fa-edit"></i> Update Module</button>
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
     * @param  \App\Http\Requests\Admin\ModuleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ModuleRequest $request)
    {
        try {
            $module           = new Module;
            $module->module   = $request->module;
            $module->active  = 'yes';
            $module->save();

            return response()->json(['moduleStatus' => 'success', 'message' => 'Well done! Module created successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['moduleStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function show(Module $module)
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
        $module = Module::findOrFail($id);
        return $module;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Admin\ModuleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(ModuleRequest $request)
    {
        try {
            $module            = Module::find($request->moduleid);
            $module->module    = $request->module; 
            $module->save();

            return response()->json(['moduleStatusUpdate' => 'success', 'message' => 'Well done! Module details updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['moduleStatusUpdate' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        } 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Module::destroy($id);

            return response()->json(['deletedModuleStatus' => 'success', 'message' => 'Module details deleted successfully'], 201);
        }
        catch(\Exception $e) {
            return response()->json(['deletedModuleStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
