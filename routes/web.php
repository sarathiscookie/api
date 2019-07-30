<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes([
	'register' => false, //Disable Registration
	'reset' => false //Disable Reset Password
]);

//Route::get('/home', 'HomeController@index')->name('home');

/*
|--------------------------------------------------------------------------
| Web Routes for admin
|--------------------------------------------------------------------------
|
| Here is the web routes for admin.
|
*/
Route::prefix('admin')->group(function(){
	Route::group(['middleware' => ['auth', 'admin']], function () {
		/* Admin dashboard */
		Route::get('/dashboard', 'Admin\DashboardController@index')->name('admin.dashboard');

		/* Show managers listing page */
		Route::get('/dashboard/manager/list', 'Admin\ManagerController@index')->name('admin.dashboard.manager.list');
		/* Listing managers on datatable */
		Route::post('/dashboard/manager/list/datatables', 'Admin\ManagerController@datatable')->name('admin.dashboard.manager.list.datatable');
		/* Delete manager */
		Route::delete('/dashboard/manager/delete/{id}', 'Admin\ManagerController@destroy')->name('admin.dashboard.manager.delete');
		/* Update manager status */
		Route::post('/dashboard/manager/status/update', 'Admin\ManagerController@updateStatus')->name('admin.dashboard.manager.status.update');
		/* Store manager */
		Route::post('/dashboard/manager/store', 'Admin\ManagerController@store')->name('admin.dashboard.manager.store');
		/* Update manager */
		Route::put('/dashboard/manager/update', 'Admin\ManagerController@update')->name('admin.dashboard.manager.update');

		/* Show companies listing page */
		Route::get('/dashboard/company/list', 'Admin\CompaniesController@index')->name('admin.dashboard.company.list');
		/* Listing company on datatable */
		Route::post('/dashboard/company/list/datatables', 'Admin\CompaniesController@datatable')->name('admin.dashboard.company.list.datatable');
		/* Delete company */
		Route::delete('/dashboard/company/delete/{id}', 'Admin\CompaniesController@destroy')->name('admin.dashboard.company.delete');
		/* Update company status */
		Route::post('/dashboard/company/status/update', 'Admin\CompaniesController@updateStatus')->name('admin.dashboard.company.status.update');
		/* Store company */
		Route::post('/dashboard/company/store', 'Admin\CompaniesController@store')->name('admin.dashboard.company.store');
		/* Update company */
		Route::put('/dashboard/company/update', 'Admin\CompaniesController@update')->name('admin.dashboard.company.update');

		/* Show shop listing page */
		Route::get('/dashboard/shop/list', 'Admin\ShopController@index')->name('admin.dashboard.shop.list');
		/* Listing shop on datatable */
		Route::post('/dashboard/shop/list/datatables', 'Admin\ShopController@datatable')->name('admin.dashboard.shop.list.datatable');
		/* Delete shops */
		Route::delete('/dashboard/shop/delete/{id}', 'Admin\ShopController@destroy')->name('admin.dashboard.shop.delete');
		/* Update shop status */
		Route::post('/dashboard/shop/status/update', 'Admin\ShopController@updateStatus')->name('admin.dashboard.shop.status.update');
		/* Store shop */
		Route::post('/dashboard/shop/store', 'Admin\ShopController@store')->name('admin.dashboard.shop.store');
		/* Update shop */
		Route::put('/dashboard/shop/update', 'Admin\ShopController@update')->name('admin.dashboard.shop.update');

		/* Show user listing page */
		Route::get('/dashboard/user/list', 'Admin\UserController@index')->name('admin.dashboard.user.list');
		/* Listing user on datatable */
		Route::post('/dashboard/user/list/datatables', 'Admin\UserController@datatable')->name('admin.dashboard.user.list.datatable');
		/* Delete users */
		Route::delete('/dashboard/user/delete/{id}', 'Admin\UserController@destroy')->name('admin.dashboard.user.delete');
		/* Update user status */
		Route::post('/dashboard/user/status/update', 'Admin\UserController@updateStatus')->name('admin.dashboard.user.status.update');
		/* Store user */
		Route::post('/dashboard/user/store', 'Admin\UserController@store')->name('admin.dashboard.user.store');
		/* Update user */
		Route::put('/dashboard/user/update', 'Admin\UserController@update')->name('admin.dashboard.user.update');

		/* Show key listing page */
		Route::get('/dashboard/key/list', 'Admin\KeyController@index')->name('admin.dashboard.key.list');
		/* Listing key on datatable */
		Route::post('/dashboard/key/list/datatables', 'Admin\KeyController@datatable')->name('admin.dashboard.key.list.datatables');
		/* Delete key */
		Route::delete('/dashboard/key/delete/{id}', 'Admin\KeyController@destroy')->name('admin.dashboard.key.delete');
		/* Update key status */
		Route::post('/dashboard/key/status/update', 'Admin\KeyController@updateStatus')->name('admin.dashboard.key.status.update');
		/* Store key */
		Route::post('/dashboard/key/store', 'Admin\KeyController@store')->name('admin.dashboard.key.store');
		/* Update key */
		Route::put('/dashboard/key/update', 'Admin\KeyController@update')->name('admin.dashboard.key.update');
		/* Shop related to company */
		Route::get('/dashboard/key/get/shops/{id}', 'Admin\KeyController@findShops')->name('admin.dashboard.key.get.shop');
		/* Get key shop id */
		Route::get('/dashboard/key/get/keyshop/id/{keyContainerId}/{keyShopId}', 'Admin\KeyController@findKeyShopId')->name('admin.dashboard.key.get.keyshop.id');

		/* Show key instruction listing page */
		Route::get('/dashboard/key/instruction/list', 'Admin\KeyInstructionController@index')->name('admin.dashboard.key.instruction.list');
		/* Store key instruction*/
		Route::post('/dashboard/key/instruction/store', 'Admin\KeyInstructionController@store')->name('admin.dashboard.key.instruction.store');
		/* Download key instructions file */
		Route::get('/dashboard/key/instruction/download/file/{id}', 'Admin\KeyInstructionController@download')->name('admin.dashboard.key.instruction.download.file');
		/* Delete key */
		Route::delete('/dashboard/key/instruction/delete/{keydeleteinstructionid}', 'Admin\KeyInstructionController@destroy')->name('admin.dashboard.key.instruction.delete');
	});
});

/*
|--------------------------------------------------------------------------
| Web Routes for manager
|--------------------------------------------------------------------------
|
| Here is the web routes for manager.
|
*/
Route::prefix('manager')->group(function(){
	Route::group(['middleware' => ['auth', 'manager']], function () {
		//Manager dashboard
		Route::get('dashboard', 'Manager\DashboardController@index')->name('manager.dashboard');
	});
});

/*
|--------------------------------------------------------------------------
| Web Routes for employee
|--------------------------------------------------------------------------
|
| Here is the web routes for employee.
|
*/
Route::prefix('employee')->group(function(){
	Route::group(['middleware' => ['auth', 'employee']], function () {
		//Employee dashboard
		Route::get('dashboard', 'Employee\DashboardController@index')->name('employee.dasboard');
	});
});
