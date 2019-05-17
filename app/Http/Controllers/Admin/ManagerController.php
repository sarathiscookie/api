<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagerRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            2 => 'active',
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

        // Search query for name
        if(!empty($request->input('search.value'))) {
            $this->searchUserName($q, $request->input('search.value'));
        }

        // tfoot search query for name
        if( !empty($params['columns'][1]['search']['value']) ) {
            $this->tfootUserName($q, $params['columns'][1]['search']['value']);
        }

        // tfoot search query for user status
        if( !empty($params['columns'][2]['search']['value']) ) {
            $this->tfootUserStatus($q, $params['columns'][2]['search']['value']);
        }

        $managerLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if(!empty($managerLists)) {
            foreach ($managerLists as $key=> $managerList) {
                if($managerList->active === 'yes') {
                    $yesStatus    = 'btn-success';
                    $noStatus     = 'btn-secondary';
                }
                else {
                    $yesStatus    = 'btn-secondary';
                    $noStatus     = 'btn-danger';
                }

                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$managerList->id.'" />';
                $nestedData['name']       = $this->bootstrapModal($managerList->id, $managerList->name, $managerList->email, $managerList->phone, $managerList->street, $managerList->postal, $managerList->city, $managerList->country, $managerList->created_at);
                $nestedData['active']     = $this->userStatusHtml($managerList->id, $managerList->active, $yesStatus, $noStatus);
                $nestedData['actions']    = $this->editMangerModel($managerList->id);
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
     * Search query for user name
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchUserName($q, $searchData)
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
     * tfoot search query for user name
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootUserName($q, $searchData)
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
    public function bootstrapModal($userId, $name, $email, $phone, $street, $postal, $city, $country, $created_at)
    {
        $html = '<a href="" data-toggle="modal" data-target="#userModal_'.$userId.'">
        '.$name.'
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
        <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">Created On</h5>
        </div>
        <p class="mb-1">'.date('d.m.y', strtotime($created_at)).'</p>
        </a>
        <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">Email</h5>
        </div>
        <p class="mb-1">'.$email.'</p>
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
     * html group button to change manager status 
     * @param  int $id
     * @param  string $oldStatus 
     * @param  string $yesStatus
     * @param  string $noStatus  
     * @return \Illuminate\Http\Response
     */
    public function userStatusHtml($id, $oldStatus, $yesStatus, $noStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";
        $html    = '<label class="switch" data-userid="'.$id.'">
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

            $user         = User::findOrFail($request->userId);

            $user->active = $request->newStatus;

            $user->save();

            return response()->json(['status' => 'success', 'message' => 'User status updated successfully'], 201);
        } 
        catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * Model to edit manager data
     * @param  integer $mangerId
     * @return \Illuminate\Http\Response
     */
    public function editMangerModel($mangerId)
    {
        try {
            $user     = $this->edit($mangerId);
            $company  = ($user->company === 'tcs') ? 'selected' : '';
            $country  = ($user->country === 'de') ? 'selected' : '';
            $html     = '<a href="" type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#editManagerModal_'.$user->id.'"><i class="fas fa-cog"></i></a>
            <div class="modal fade" id="editManagerModal_'.$user->id.'" tabindex="-1" role="dialog" aria-labelledby="editManagerModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editManagerModalLabel">Edit Manager</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="text-right">
            <a href="" type="button" class="btn btn-danger btn-sm deleteEvent" data-id="'.$user->id.'"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <form method="POST" action="">
            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="name">Name</label>
            <input id="name" type="text" class="form-control" name="name" value="'.$user->name.'" autocomplete="name" maxlength="255" autofocus>

            </div>
            <div class="form-group col-md-6">

            <label for="phone">Phone</label>
            <input id="phone" type="text" class="form-control" name="phone" value="'.$user->phone.'" maxlength="20" autocomplete="phone">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="company">Company</label>
            <select id="company" class="form-control" name="company">
            <option value="">Choose Company</option>
            <option value="tcs" '.$company.'>TCS</option>
            </select>

            </div>
            <div class="form-group col-md-6">

            <label for="street">Street</label>
            <input id="street" type="text" class="form-control" name="street" value="'.$user->street.'" maxlength="255" autocomplete="street">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="city">City</label>
            <input id="city" type="text" class="form-control" name="city" value="'.$user->city.'" maxlength="255" autocomplete="city">

            </div>
            <div class="form-group col-md-4">

            <label for="country">Country</label>
            <select id="country" class="form-control" name="country">
            <option value="">Choose Country</option>
            <option value="de" '.$country.'>Germany</option>
            </select>

            </div>
            <div class="form-group col-md-2">

            <label for="zip">Zip</label>
            <input id="zip" type="text" class="form-control" name="zip" value="'.$user->postal.'" maxlength="20" autocomplete="zip">

            </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-user-edit"></i> Update Manager</button>
            </form>

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
     * Show the form to create a new resource.
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
     * @param  \App\Http\Requests\Admin\ManagerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ManagerRequest $request)
    {
        try {
            $user           = new User;
            $user->name     = $request->name;
            $user->password = Hash::make($request->password);
            $user->email    = $request->email;
            $user->phone    = $request->phone;
            $user->street   = $request->street;
            $user->city     = $request->city;
            $user->country  = $request->country;
            $user->postal   = $request->zip;
            $user->company  = $request->company;
            $user->active   = 'no';
            $user->role     = 'manager';
            $user->save();

            return response()->json(['successStatusManager' => 'success', 'message' => 'Well done! User created successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['failedStatusManager' => 'success', 'message' => 'Whoops! Something went wrong'], 404);
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
        $user = User::where('role', 'manager')->findOrFail($id);
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            User::where('id', session()->get('manager'))
            ->where('role', 'manager')
            ->update([
                'name'      => $request->name,  
                'phone'     => $request->phone, 
                'street'    => $request->street, 
                'city'      => $request->city, 
                'country'   => $request->country,
                'zip'       => $request->zip,
                'company'   => $request->company,
            ]);

            session()->forget('manager');

            return redirect('/admin/dashboard/manager/list')->with('successUpdateManager', 'Well done! User details updated successfully');
        } 
        catch(\Exception $e){
            return redirect()->back()->with('failureUpdateManager', 'Whoops! Something went wrong');
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
        $user            = User::destroy($id);
        return response()->json(['message' => 'User deleted successfully!'], 201);
    }
}
