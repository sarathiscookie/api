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
            $this->searchUserEmail($q, $request->input('search.value'));
        }

        // tfoot search query for email
        if( !empty($params['columns'][2]['search']['value']) ) {
            $this->tfootUserEmail($q, $params['columns'][2]['search']['value']);
        }

        // tfoot search query for user status
        if( !empty($params['columns'][4]['search']['value']) ) {
            $this->tfootUserStatus($q, $params['columns'][4]['search']['value']);
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
                $nestedData['email']      = $this->bootstrapModal($managerList->id, $managerList->email, $managerList->phone, $managerList->street, $managerList->postal, $managerList->city, $managerList->country);
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
     * Search query for user email
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchUserEmail($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('email', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('email', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * tfoot search query for user email
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootUserEmail($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('email', 'like', "%{$searchData}%");
        });

        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('email', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * tfoot search query for user status
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootUserStatus($q, $searchData)
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
     * Bootstrap model for datatable
     * @param  string $userId
     * @return \Illuminate\Http\Response
     */
    public function bootstrapModal($userId, $email, $phone, $street, $postal, $city, $country)
    {
        $html = '<a href="" data-toggle="modal" data-target="#userModal_'.$userId.'">
        '.$email.'
        </a>
        <div class="modal fade" id="userModal_'.$userId.'" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Manager Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <div class="modal-body">
        <div class="list-group">
        <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">Phone</h5>
        </div>
        <p class="mb-1">'.$phone.'</p>
        </a>
        <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">Street</h5>
        </div>
        <p class="mb-1">'.$street.'</p>
        </a>
        <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">Postal</h5>
        </div>
        <p class="mb-1">'.$postal.'</p>
        </a>
        <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">City</h5>
        </div>
        <p class="mb-1">'.$city.'</p>
        </a>
        <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">Country</h5>
        </div>
        <p class="mb-1">'.$country.'</p>
        </a>
        </div>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
        </div>
        </div>
        </div>';

        return $html;
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
