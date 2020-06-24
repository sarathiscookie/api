<?php

namespace App\Console\Commands;

use App\Shop;
use App\User;
use Illuminate\Console\Command;
use App\Mail\SendEmailToSupplier;
use App\Http\Traits\CurlTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CronEmailToSupplier extends Command
{
    use CurlTrait;
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
    protected $description = 'Send email to supplier';

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
        $testOrders = [];
        $testApi = [];

        // Fetching supplier details 
        $suppliers = User::supplier()->active()->get();

        $shops = Shop::active()->get();

        foreach ($shops as $shop) {
            // Sending request to API. Passing api key to get orders list.
            $urlGetOrders = 'http://webservice.rakuten.de/merchants/orders/getOrders?key=' . $shop->api_key . '&format=json&format=json&status=editable';

            // Fetching data from API
            $jsonDecodedResults = $this->curl($urlGetOrders);

            // Checking API response is success or failure and count of total data.
            if (($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['orders']['paging'][0]['total'] != '0')) {

                $testApi[] = $shop->api_key;

                foreach ($jsonDecodedResults['result']['orders']['order'] as $key => $orderList) {
                    $testOrders[] = $orderList['order_no'];

                    // TODO: In module settings, We need to check product have module settings? And we need to check already sent cron or not? We need to check email subject and content filled? If module "rakuten lieferanten email" then sent email otherwise not need to send cron email.

                    // TODO: Must check product id and module settings id is active.

                    // If it's not active send info in to the Log file.

                    // TODO: If condition is okay, then send cron to module settings supplier id where supplier is active.
                    
                    // TODO: If supplier is not active info should store in to the Log.
                }
                
            }
        }

        Log::info($testApi);
        Log::info($testOrders);
        


        if( !empty($suppliers) ) {

            foreach($suppliers as $supplier) {
                Mail::send(new SendEmailToSupplier($supplier));
            }

        }
    
    }
}
