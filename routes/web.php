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
		/* Delete bookings */
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
		/* Delete bookings */
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
		/* Delete bookings */
		Route::delete('/dashboard/shop/delete/{id}', 'Admin\ShopController@destroy')->name('admin.dashboard.shop.delete');
		/* Update shop status */
		Route::post('/dashboard/shop/status/update', 'Admin\ShopController@updateStatus')->name('admin.dashboard.v.status.update');
		/* Store shop */
		Route::post('/dashboard/shop/store', 'Admin\ShopController@store')->name('admin.dashboard.shop.store');
		/* Update shop */
		Route::put('/dashboard/shop/update', 'Admin\ShopController@update')->name('admin.dashboard.shop.update');
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
