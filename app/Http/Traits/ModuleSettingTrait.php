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
    public function moduleName($productApiId)
    {
        try {
            $moduleName = '';

            $moduleSettings = ModuleSetting::join('modules', 'module_settings.module_id', '=', 'modules.id')
                ->join('products', 'module_settings.product_id', '=', 'products.id')
                ->select('module_settings.id AS moduleSettingsId', 'module_settings.module_id', 'modules.module AS moduleName', 'module_settings.product_id')
                ->joinActive()
                ->where('products.api_product_id', $productApiId)
                ->get();
    
            if($moduleSettings->count() > 0) { // Checking count
                foreach($moduleSettings as $moduleSetting) {
                    $moduleName .= '<span class="badge badge-info badge-pill">' . ucwords($moduleSetting->moduleName) . '&nbsp<i class="fas fa-trash-alt module_settings" data-modulesettingsid='.$moduleSetting->moduleSettingsId.' style="color:#9e004f; cursor:pointer;"></i></span></<span>&nbsp<span class="module_settings_spinner_'.$moduleSetting->moduleSettingsId.'"></span>';
                }
            }
            else {
                $moduleName = '<span class="badge badge-secondary badge-pill"> No Modules </span>';
            }   
                
            return $moduleName; 
        } 
        catch (\Exception $e) {
            abort(404);
        }
    }
}


