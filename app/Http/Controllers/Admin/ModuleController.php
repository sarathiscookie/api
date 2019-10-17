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
                $nestedData['active']     = 'status';//$this->moduleStatusHtml($moduleList->id, $moduleList->active);
                $nestedData['actions']    = 'eidt';//$this->editModuleModel($moduleList->id);
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
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function edit(Module $module)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Module $module)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function destroy(Module $module)
    {
        //
    }
}
