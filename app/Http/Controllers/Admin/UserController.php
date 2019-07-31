<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\CountryTrait;
use App\User;
use App\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use CompanyTrait, CountryTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = $this->company();
        $countries = $this->country();

        return view('admin.user', ['companies' => $companies, 'countries' => $countries]);
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

        $totalData = User::select('id', 'name', 'active')
        ->employee()
        ->count();

        $q         = User::select('id', 'name', 'active')
        ->employee();

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

        $userLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if(!empty($userLists)) {
            foreach ($userLists as $key=> $userList) {
                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$userList->id.'" />';
                $nestedData['name']       = $userList->name;
                $nestedData['active']     = $this->userStatusHtml($userList->id, $userList->active);
                $nestedData['actions']    = $this->editMangerModel($userList->id);
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
     * html group button to change user status 
     * @param  int $id
     * @param  string $oldStatus  
     * @return \Illuminate\Http\Response
     */
    public function userStatusHtml($id, $oldStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";
        $html    = '<label class="switch" data-userid="'.$id.'">
        <input type="checkbox" class="buttonStatus" '.$checked.'>
        <span class="slider round"></span>
        </label>';

        return $html;
    }

    /**
     * Model to edit user data
     * @param  integer $userId
     * @return \Illuminate\Http\Response
     */
    public function editMangerModel($userId)
    {
        try {
            $user                = $this->edit($userId);

            $countryOptions      = '';
            foreach($this->country() as $country) {
                $countrySelected = ($user->country->id === $country->id) ? 'selected' : '';
                $countryOptions .= '<option value="'.$country->id.'" '.$countrySelected.'>'.$country->name.'</option>';
            }

            //Getting companies and selecting matching companies
            
            $userCompanyId = [];
            foreach($user->userCompanies as $userCompany) {
                $userCompanyId[] = $userCompany->company_id;
            }

            $companyOptions = '';
            foreach($this->company() as $company) {
                $companySelected = ( in_array($company->id, $userCompanyId) ) ? 'selected' : '';
                $companyOptions .= '<option value="'.$company->id.'" '.$companySelected.'>'.$company->company.'</option>';
            }

            $country  = ($user->country === 'de') ? 'selected' : '';
            $html     = '<a class="btn btn-secondary btn-sm editUser cursor" data-userid="'.$user->id.'" data-toggle="modal" data-target="#editUserModal_'.$user->id.'"><i class="fas fa-cog"></i></a>
            <div class="modal fade" id="editUserModal_'.$user->id.'" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editUserModalLabel">Edit User Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="userUpdateValidationAlert"></div>
            <div class="text-right">
            <a href="" class="btn btn-danger btn-sm deleteEvent" data-id="'.$user->id.'"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="email">Email:</label>
            <div class="badge badge-secondary text-wrap" style="width: 6rem;">
            '.$user->email.'
            </div>
            

            </div>
            <div class="form-group col-md-6">

            <label for="createdon">Created On:</label>
            <div class="badge badge-secondary" style="width: 6rem;">
            '.date('d.m.y', strtotime($user->created_at)).'
            </div>
            

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="name">Name <span class="required">*</span></label>
            <input id="name_'.$user->id.'" type="text" class="form-control" name="name" value="'.$user->name.'" autocomplete="name" maxlength="255">

            </div>
            <div class="form-group col-md-6">

            <label for="phone">Phone <span class="required">*</span></label>
            <input id="phone_'.$user->id.'" type="text" class="form-control" name="phone" value="'.$user->phone.'" maxlength="20" autocomplete="phone">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="user_company">Company <span class="required">*</span></label>
            <select id="user_company_'.$user->id.'" class="form-control" name="user_company">
            <option value="">Choose Company</option>
            '.$companyOptions.'
            </select>

            </div>
            <div class="form-group col-md-6">

            <label for="street">Street <span class="required">*</span></label>
            <input id="street_'.$user->id.'" type="text" class="form-control" name="street" value="'.$user->street.'" maxlength="255" autocomplete="street">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="city">City <span class="required">*</span></label>
            <input id="city_'.$user->id.'" type="text" class="form-control" name="city" value="'.$user->city.'" maxlength="255" autocomplete="city">

            </div>
            <div class="form-group col-md-4">

            <label for="country">Country <span class="required">*</span></label>
            <select id="country_'.$user->id.'" class="form-control" name="country">
            <option value="">Choose Country</option>
            '.$countryOptions.'
            </select>

            </div>
            <div class="form-group col-md-2">

            <label for="zip">Zip <span class="required">*</span></label>
            <input id="zip_'.$user->id.'" type="text" class="form-control" name="zip" value="'.$user->postal.'" maxlength="20" autocomplete="zip">

            </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateUser_'.$user->id.'"><i class="fas fa-user-edit"></i> Update User</button>

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

            return response()->json(['userStatusChange' => 'success', 'message' => 'User status updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['userStatusChange' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
     * @param  \App\Http\Requests\Admin\UserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        try {
            $user               = new User;
            $user->name         = $request->name;
            $user->password     = Hash::make($request->password);
            $user->email        = $request->email;
            $user->username     = $request->username;
            $user->phone        = $request->phone;
            $user->street       = $request->street;
            $user->city         = $request->city;
            $user->country_id   = $request->country;
            $user->postal       = $request->zip;
            $user->active       = 'no';
            $user->role         = 'employee';
            $user->save();

            $userCompany             = new UserCompany;
            $userCompany->user_id    = $user->id;
            $userCompany->company_id = $request->user_company;
            $userCompany->save();

            return response()->json(['userStatus' => 'success', 'message' => 'Well done! User created successfully'], 201);
        } 
        catch(\Exception $e) {
            return response()->json(['userStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
        $user = User::employee()->findOrFail($id);
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Admin\UserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request)
    {
        try {
            //Delete ansd store company id in user companies table
            UserCompany::where('user_id', $request->userid)->delete();
            
            $user             = User::find($request->userid);
            $user->name       = $request->name; 
            $user->phone      = $request->phone; 
            $user->street     = $request->street; 
            $user->city       = $request->city; 
            $user->country_id = $request->country;
            $user->postal     = $request->zip;
            $user->save();

            $userCompany             = new UserCompany;
            $userCompany->user_id    = $user->id;
            $userCompany->company_id = $request->user_company;
            $userCompany->save();

            return response()->json(['userStatusUpdate' => 'success', 'message' => 'Well done! User details updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['userStatusUpdate' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
        try {
            $userCompany = UserCompany::where('user_id', $id)->delete();
            $user        = User::destroy($id);

            return response()->json(['deletedUserStatus' => 'success', 'message' => 'User details deleted successfully'], 201);
        }   
        catch(\Exception $e) {
            return response()->json(['deletedUserStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
