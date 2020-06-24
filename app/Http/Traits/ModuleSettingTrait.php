<?php

namespace App\Http\Traits;

use App\ModuleSetting;

trait ModuleSettingTrait
{
    /**
     * Get module name
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deliveryStatus()
    {
        return [
            1 => 'Not Active',
            2 => 'Active',
            3 => 'Waiting',
        ];
    }

    /**
     * Get module name
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function moduleName($productApiId)
    {
        try {

            $moduleSettings = ModuleSetting::join('modules', 'module_settings.module_id', '=', 'modules.id')
                ->join('products', 'module_settings.product_id', '=', 'products.api_product_id')
                ->select('module_settings.id AS moduleSettingsId', 'module_settings.module_id', 'modules.module AS moduleName', 'module_settings.product_id')
                ->where('modules.active', 'yes')
                ->where('products.api_product_id', $productApiId)
                ->get();   
                
            return $moduleSettings; 
        } 
        catch (\Exception $e) {
            abort(404);
        }
    }
}


