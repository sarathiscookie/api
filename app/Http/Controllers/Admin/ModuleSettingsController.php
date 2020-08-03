<?php

namespace App\Http\Controllers\admin;

use App\ModuleSetting;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ModuleSettingsRequest;
use App\Http\Controllers\Controller;

class ModuleSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\Admin\ModuleSettingsRequest
     * @return \Illuminate\Http\Response
     */
    public function store(ModuleSettingsRequest $request)
    {
        $moduleSetting = ModuleSetting::findOrFail($request->module_settings_id);
        $moduleSetting->user_supplier_id = $request->supplier;
        $moduleSetting->mail_bcc_name = $request->bcc_name;
        $moduleSetting->mail_bcc = $request->bcc_email;
        $moduleSetting->mail_subject = $request->email_subject;
        $moduleSetting->mail_body = $request->email_body;
        $moduleSetting->setOrderShipped = $request->activate_delivery_note_shipping;
        $moduleSetting->setOrderLogistic = $request->activate_customer_data_sending;
        $moduleSetting->getOrderDeliveryNote = $request->enable_delivery_address_data_shipping;
        $moduleSetting->order_in_logistics = $request->order_in_logistics;
        $moduleSetting->order_shipped = $request->order_shipped;
        $moduleSetting->status = 1;
        $moduleSetting->save();

        return response()->json(['managerSettingsStatus' => 'success', 'message' => 'Well done! Settings updated successfully'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ModuleSetting  $moduleSetting
     * @return \Illuminate\Http\Response
     */
    public function show(ModuleSetting $moduleSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ModuleSetting  $moduleSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(ModuleSetting $moduleSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModuleSetting  $moduleSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModuleSetting $moduleSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ModuleSetting  $moduleSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModuleSetting $moduleSetting)
    {
        //
    }
}
