<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\CurlTrait;
use App\Http\Traits\OrderStatusTrait;
use App\Http\Traits\ShopTrait;
use App\Order;
use App\Shop;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class OrderController extends Controller
{
    use CompanyTrait, ShopTrait, CurlTrait, OrderStatusTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = $this->fetchCompanyMatchingWithShop();
        $orderStatuses = $this->orderStatuses();
        return view('admin.order', ['companies' => $companies, 'orderStatuses' => $orderStatuses]);
    }

    /**
     * Display a listing of the resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        try {
            $params        = $request->all();
            $data          = [];
            $totalData     = 0;
            $totalFiltered = 0;
            $search        = '';
            $status        = '';

            if( !empty($request->orderListDateRange) && !empty($request->orderCompany) ) {

                $dateRange  = explode("-", $request->orderListDateRange);

                // Search query for order number
                if( !empty($request->input('search.value')) ) {
                    $search = "&search=".urlencode($request->input('search.value'))."&search_field=order_no";
                }

                // tfoot search functionality for order no & status
                if( !empty($params['columns'][1]['search']['value']) ) {
                    $search = "&search=".urlencode($params['columns'][1]['search']['value'])."&search_field=order_no";
                }

                if( !empty($params['columns'][2]['search']['value']) ) {
                    $status = '&status='.$params['columns'][2]['search']['value'];
                }

                // Get api key from shops
                // 1 = Rakuten: Other shops like Amazone and ebay send invoices automatically. For rakuten we need to send invoices. So invoice send module is only for rakuten.
                $api_key    = $this->getApiKey(1, $request->orderCompany);

                // Passing api and from to date in url and list orders.
                $urlGetOrders = 'http://webservice.rakuten.de/merchants/orders/getOrders?key='.$api_key->api_key.'&format=json&page='.$request->pageActive.'&per_page='.$request->length.'&created_from='.$dateRange[0].'&created_to='.$dateRange[1].$search.$status;

                // Get order details
                if( !empty($urlGetOrders) ) {
                    $orderDetails = $this->getUrlOrders($urlGetOrders, $request->orderCompany);
                }

                // Checking order details is empty or not
                if( !empty($orderDetails) ) {
                    $data = $orderDetails['data'];
                    $totalData = (int)$orderDetails['totalData'];
                    $totalFiltered = (int)$orderDetails['totalFiltered'];
                }
            }

            $json_data = [
                'draw'            => (int)$params['draw'],
                'recordsTotal'    => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data'            => $data
            ];

            return response()->json($json_data);
        }
        catch(\Exception $e) {
            return response()->json(['orderListStatusMsg' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Get shop order details.
     *
     * @param  string  $urlGetOrders
     * @param  int  $companyId
     * @return \Illuminate\Http\Response
     */
    public function getUrlOrders($urlGetOrders, $companyId)
    {
        // Fetching data from API
        $jsonDecodedResults = $this->curl($urlGetOrders);

        if( ($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['orders']['paging'][0]['total'] != '0') ) {

            $totalData     = $jsonDecodedResults['result']['orders']['paging'][0]['total'];
            $totalFiltered = $totalData;

            foreach($jsonDecodedResults['result']['orders']['order'] as $key => $orderList) {

                if( !empty($orderList['invoice_no']) ) {
                    $downloadButton = '<a href="/admin/dashboard/order/list/download/'.$companyId.'/'.$orderList['order_no'].'"><i class="fas fa-download"></i></a>';
                }
                else {
                    $downloadButton = '<span class="badge badge-secondary">No File</span>';
                }

                $nestedData['hash']    = '<input class="checked orderNoInput" type="checkbox" name="id[]" value="'.$orderList['order_no'].'" />';
                $nestedData['order']   = '<h6>'.$orderList['order_no'].'</h6><div>Invoice no: <span class="badge badge-secondary badge-pill">'.$orderList['invoice_no'].'</span></div><div>Created on: <span class="badge badge-secondary badge-pill">'.date("d.m.y H:i:s", strtotime($orderList['created'])).'</span></div>';
                $nestedData['status']  = $this->orderLabels($orderList['status']);
                $nestedData['actions'] = $downloadButton;
                $data[]                = $nestedData;
            }

            return compact('data', 'totalData', 'totalFiltered');
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $companyId
     * @param  string  $orderNo
     * @return \Illuminate\Http\Response
     */
    public function download($companyId, $orderNo)
    {
        try {
            // Get api key from shops
            // 1 = Rakuten: Other shops like Amazone and ebay send invoices automatically. For rakuten we need to send invoices. So invoice send module is only for rakuten.
            $api_key    = $this->getApiKey(1, $companyId);

            // Passing api and from to date in url and list orders.
            $getOrderInvoice = 'http://webservice.rakuten.de/merchants/orders/getOrderInvoice?key='.$api_key->api_key.'&format=json&order_no='.$orderNo;

            // Get order invoice
            if( !empty($getOrderInvoice) ) {
            // Fetching data from API
                $jsonDecodedResults = $this->curl($getOrderInvoice);
            }

            if( $jsonDecodedResults['result']['success'] === '1' ) {

                // URL src from API response
                // URL src doesn't have trasfer protocol. So added trasfer protocol in environment file manually.
                $fileSource = env('API_URL_TRANSFER_PROTOCOL').$jsonDecodedResults['result']['invoice']['src'];
                $fileName = $jsonDecodedResults['result']['invoice']['filename']; // Filename from API response
                $headers = ['Content-Type: application/pdf'];

                $file_get_contents = file_get_contents($fileSource);

                // Path of directory and file
                $pathToDirectory   = 'invoice/'.$companyId;
                $pathToFile = $pathToDirectory.'/'.$fileName;

                // If directory doesn't exists create a new one.
                if( !Storage::exists($pathToDirectory) ) {
                    $createDirectory = Storage::makeDirectory($pathToDirectory, 0775);
                }
            
                // Checking data already exist or not
                if( Storage::exists($pathToFile) ) {

                    Storage::delete($pathToFile); // Delete files from directory

                    file_put_contents( storage_path('app/'.$pathToFile), $file_get_contents ); // Store content in to a file
                }
                else {

                    file_put_contents( storage_path('app/'.$pathToFile), $file_get_contents ); // Store content in to a file
                }

                return Storage::download($pathToFile, $fileName, $headers);
            }
            
        }
        catch(\Exception $e) {
            return response()->json(['orderListStatusMsg' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
 
    } 

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadAllInvoices(Request $request)
    {
        try {
            $orderNoArray = explode(',', $request->inputOrderNoArr);
            $companyId = $request->inputOrderCompanyId;

            // Get api key from shops
            // 1 = Rakuten: Other shops like Amazone and ebay send invoices automatically. For rakuten we need to send invoices. So invoice send module is only for rakuten.
            $api_key = $this->getApiKey(1, $companyId);

            // create new zip object
            $zip = new ZipArchive;

            // Define the file name. Give it a unique name to avoid overriding.
            $zipFileName = 'invoices_'.date("dmyHis").'.zip';

            // Path of directory and file
            $pathToDirectory   = 'invoice/'.$companyId;

            // If directory doesn't exists create a new one.
            if( !Storage::exists($pathToDirectory) ) {
                $createDirectory = Storage::makeDirectory($pathToDirectory, 0775);
            }

            // Create the ZIP file directly inside the desired folder. No need for a temporary file.
            $zip->open(storage_path('app/invoice/'.$companyId.'/'.$zipFileName), ZipArchive::CREATE);

            // Passing api and from to date in url and list orders.
            foreach($orderNoArray as $orderNo) {

                $getOrderInvoice = 'http://webservice.rakuten.de/merchants/orders/getOrderInvoice?key='.$api_key->api_key.'&format=json&order_no='.$orderNo;

                // Fetching data from API
                $jsonDecodedResults = $this->curl($getOrderInvoice);

                if( $jsonDecodedResults['result']['success'] === '1' ) {
                    // URL src from API response
                    // URL src doesn't have trasfer protocol. So added trasfer protocol in environment file manually.
                    $fileSource = env('API_URL_TRANSFER_PROTOCOL').$jsonDecodedResults['result']['invoice']['src'];
                    $fileName = $jsonDecodedResults['result']['invoice']['filename']; // Filename from API response
                    $file_get_contents = file_get_contents($fileSource);

                    // Add it to the zip
                    $zip->addFromString($zipFileName.'/'.$fileName, $file_get_contents);
                }
            }

            // Close zip
            $zip->close();

            $filePath = 'invoice/'.$companyId.'/'.$zipFileName;

            $headers = [
                'Content-Type: application/octet-stream',
                'Content-Disposition: attachment; filename='.$zipFileName,
                'Content-length: '.filesize(storage_path('app/invoice/'.$companyId.'/'.$zipFileName)),
                'Pragma: no-cache',
                'Expires: 0'
            ];

            return Storage::download($filePath, $zipFileName, $headers);
        }
        catch(\Exception $e) {
            return response()->json(['orderListStatusMsg' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

}
