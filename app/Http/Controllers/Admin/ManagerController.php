<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\User;
use App\Http\Controllers\Controller;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.manager');
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
            2 => 'email',
            3 => 'created_at',
            4 => 'active',
        );

        $totalData = User::select('name', 'email', 'phone', 'street', 'postal', 'city', 'country', 'active', 'role', 'created_at')
        ->where('role', 'manager')
        ->count();

        $q         = User::select('id', 'name', 'email', 'phone', 'street', 'postal', 'city', 'country', 'active', 'role', 'created_at')
        ->where('role', 'manager');

        $totalFiltered = $totalData;
        $limit         = (int)$request->input('length');
        $start         = (int)$request->input('start');
        $order         = $columns[$params['order'][0]['column']]; //contains column index
        $dir           = $params['order'][0]['dir']; //contains order such as asc/desc

        // Search query for email
        if(!empty($request->input('search.value'))) {
            $search   = $request->input('search.value');
            $q->where(function($query) use ($search) {
                $query->where('email', 'like', "%{$search}%");
            });

           $totalFiltered = $q->where(function($query) use ($search) {
            $query->where('email', 'like', "%{$search}%");
           })
           ->count();
        }

        // tfoot search query for email
        if( !empty($params['columns'][2]['search']['value']) ) {
            $q->where(function($query) use ($params) {
                $query->where('email', 'like', "%{$params['columns'][2]['search']['value']}%");
            });

            $totalFiltered = $q->where(function($query) use ($params) {
                $query->where('email', 'like', "%{$params['columns'][2]['search']['value']}%");
            })
            ->count();
        }

        // tfoot search query for user status
        if( !empty($params['columns'][4]['search']['value']) ) {
            $q->where(function($query) use ($params) {
                $query->where('active', "{$params['columns'][4]['search']['value']}");
            });

            $totalFiltered = $q->where(function($query) use ($params) {
                $query->where('active', "{$params['columns'][4]['search']['value']}");
            })
            ->count();
        }

        $managerLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if(!empty($managerLists)) {
            foreach ($managerLists as $key=> $managerList) {
                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$managerList->id.'" />';
                $nestedData['name']       = $managerList->name;
                $nestedData['email']      = $managerList->email;
                $nestedData['created_at'] = date('d.m.y', strtotime($managerList->created_at));
                $nestedData['active']     = $managerList->active;
                $nestedData['actions']    = '<a type="button" class="btn btn-secondary btn-sm"><i class="fas fa-user-edit"></i></a> <a type="button" class="btn btn-secondary btn-sm deleteEvent" data-id="'.$managerList->id.'"><i class="fas fa-trash-alt"></i></a>';
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
        $user            = User::destroy($id);
        return response()->json(['message' => 'User deleted successfully!'], 201);
    }
}
