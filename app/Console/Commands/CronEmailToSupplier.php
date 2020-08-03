<?php

namespace App\Console\Commands;

use App\Shop;
use App\User;
use Illuminate\Console\Command;
use App\Mail\SendEmailToSupplier;
use App\Http\Traits\CurlTrait;
use App\Http\Traits\ModuleSettingTrait;
use App\ModuleSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CronEmailToSupplier extends Command
{
    use CurlTrait, ModuleSettingTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:ToSupplier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending email to suppliers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $shops = Shop::active()->get();

        foreach ($shops as $shop) {
            // Sending request to API. Passing api key to get orders list.
            $urlGetOrders = 'http://webservice.rakuten.de/merchants/orders/getOrders?key=' . $shop->api_key . '&format=json&status=editable';

            // Fetching data from API
            $jsonDecodedResults = $this->curl($urlGetOrders);

            // Checking API response is success or failure and count of total data.
            if (($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['orders']['paging'][0]['total'] != '0')) {

                Log::info('////////Success Begin///////////'); // Testing api key.
                Log::info('API Key'); // Testing api key.
                Log::info($shop->api_key); // Testing api key.
                Log::info('--------------'); // Testing api key.

                // Loop for extracting each orders
                foreach ($jsonDecodedResults['result']['orders']['order'] as $key => $orderList) {

                    Log::info('Order List'); // Testing order list.
                    Log::info($orderList['order_no']); // Testing order list.
                    Log::info('-------------'); // Testing order list.

                    // Loop for extracting each items in orders
                    foreach ($orderList['items']['item'] as $item) {

                        Log::info('Product Item'); // Testing product item.
                        Log::info($item); // Testing product item.
                        Log::info('-----------'); // Testing product item.

                        // Fetching module settings matching with product id.
                        $moduleSetting = ModuleSetting::byProductId($item['product_id']);

                        Log::info('Module Setting'); // Testing module setting.
                        Log::info($moduleSetting); // Testing module setting.
                        Log::info('---------------'); // Testing module setting.

                        // Conditions to send cron job email.
                        // 1: Module settins status must be active.
                        // 2: Supplier id must be filled.
                        // 3: Cron status must be not sent.
                        if ((!empty($moduleSetting)) && ($moduleSetting->user_supplier_id !== null) && ($moduleSetting->status === 1)) {

                            // Get the crons that owns the module settings
                            $conStatusCheck = $moduleSetting->crons()
                                ->where('api_order_no', $orderList['order_no'])
                                ->where('cron_status', 1)
                                ->first();

                            if (empty($conStatusCheck)) {

                                Log::info('Success');
                                Log::info($moduleSetting);
                                Log::info('--------------');

                                Log::info('Cron Status Check');
                                Log::info($conStatusCheck);
                                Log::info('--------------');

                                // Fetching supplier details 
                                $supplier = User::supplier()->active()->find($moduleSetting->user_supplier_id);

                                if (!empty($supplier)) {

                                    Log::info('Supplier');
                                    Log::info($supplier->email);
                                    Log::info('Done...');

                                    // Creating corresponding URLs for suppliers.
                                    $apiUrlForEmails = $this->apiUrlForEmail($shop->api_key, $orderList['order_no'], $moduleSetting);

                                    Log::info('URLS & DeliveryNoteAPIURL');
                                    Log::info($apiUrlForEmails);

                                    // Send email to supplier and Update cron status.
                                    Mail::send(new SendEmailToSupplier($supplier, $orderList, $item, $moduleSetting, $apiUrlForEmails));
                                } else {
                                    Log::info('Supplier not active');
                                    Log::info('Supplier Id: ' . $supplier->id . 'Supplier Email: ' . $supplier->email);
                                    Log::info('Done...');
                                }
                            }
                            else {
                                Log::info('Cron Status Check');
                                Log::info($conStatusCheck);
                                Log::info('--------------');
                            }

                        }
                    }
                }

                Log::info('////////Success End///////////'); // Testing api key.

            } else {
                Log::info('===========Failure Begin============');
                Log::info('API Key');
                Log::info($shop->api_key);
                Log::info('getOrders - API URL:');
                Log::info($urlGetOrders);
                Log::info('getOrders - API status:');
                Log::info($jsonDecodedResults['result']['success']);
                Log::info('getOrders - API orders total:');
                Log::info($jsonDecodedResults);
                Log::info('++++++++++++Failure End+++++++++++');
            }
        }
    }
}
