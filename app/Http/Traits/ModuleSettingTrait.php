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
                ->select('module_settings.*', 'module_settings.module_id', 'modules.module AS moduleName', 'module_settings.product_id')
                ->where('modules.active', 'yes')
                ->where('products.api_product_id', $productApiId)
                ->get();   
                
            return $moduleSettings; 
        } 
        catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Passing corresponding URL to email.
     * 
     * @param  string  $api_key
     * @param  string  $order_no
     * @param  object  $moduleSetting
     * 
     * @return array
     */
    public function apiUrlForEmail($api_key, $order_no, $moduleSetting)
    {
        $apiUrlForMail = [];

        // If setOrderShipped is active, then url link attach with email.
        // Definition from API: This methods sets an order to shipped.
        if($moduleSetting->setOrderShipped === 1) {
            $apiUrlForMail[0] = env('APP_URL').'/set/order/shipped/'.$api_key.'/'.$order_no;
            $apiUrlForMail['setOrderShipped'] = 'http://webservice.rakuten.de/merchants/orders/setOrderShipped?key=' . $api_key . '&order_no=' . $order_no;
        }
        
        // If setOrderInLogistics is active, then url link attach with email.
        // Definition from API: Use this method to set an order to “in preparation for shipping”. This disables the option for the customer or Rakuten customer service to cancel an order for 48 hours.
        if($moduleSetting->setOrderLogistic === 1) {
            $apiUrlForMail[1] = env('APP_URL').'/set/order/in/logistic/'.$api_key.'/'.$order_no;
            $apiUrlForMail['setOrderInLogistics'] = 'http://webservice.rakuten.de/merchants/orders/setOrderInLogistics?key=' . $api_key . '&order_no=' . $order_no;
        }
        
        // If getOrderDeliveryNote is active, then url link attach with email.
        // Definition from API: This method gives out the delivery note to an order.
        if($moduleSetting->getOrderDeliveryNote === 1) {
            $apiUrlForMail[2] = env('APP_URL').'/download/get/order/delivery/note/'.$api_key.'/'.$order_no;
            $apiUrlForMail['getOrderDeliveryNote'] = 'http://webservice.rakuten.de/merchants/orders/getOrderDeliveryNote?key=' . $api_key . '&format=json&order_no=' . $order_no;
        }

        return $apiUrlForMail;
    }
}


