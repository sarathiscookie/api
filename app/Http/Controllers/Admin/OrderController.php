<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\ShopTrait;
use App\Http\Traits\CurlTrait;
use App\Order;
use App\Shop;
use Illuminate\Http\Request;
use DateTime;
use Carbon\Carbon;

class OrderController extends Controller
{
    use CompanyTrait, ShopTrait, CurlTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = $this->fetchCompanyMatchingWithShop();
        return view('admin.order', ['companies' => $companies]);
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

            if( !empty($request->orderListDateRange) && !empty($request->orderCompany) ) {

                $dateRange  = explode("-", $request->orderListDateRange);

                // Search query for order number
                if( !empty($request->input('search.value')) ) {
                    $search = "&search=".urlencode($request->input('search.value'))."&search_field=order_no";
                }

                // Get api key from shops
                // 1 = Rakuten: Other shops like Amazone and ebay send invoices automatically. For rakuten we need to send invoices. So invoice send module is only for rakuten.
                $api_key    = $this->getApiKey(1, $request->orderCompany);

                // Passing api and from to date in url and list orders.
                $urlGetOrders = 'http://webservice.rakuten.de/merchants/orders/getOrders?key='.$api_key->api_key.'&format=json&page='.$request->pageActive.'&created_from='.$dateRange[0].'&created_to='.$dateRange[1].$search;

                // Get order details
                if( !empty($urlGetOrders) ) {
                    $orderDetails = $this->getUrlOrders($urlGetOrders);
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
     * @return \Illuminate\Http\Response
     */
    public function getUrlOrders($urlGetOrders)
    {
        $columns = [ 1 => 'name', 2 => 'active' ];

        // Fetching data from API
        $jsonDecodedResults = $this->curl($urlGetOrders);

        if( ($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['orders']['paging'][0]['total'] != '0') ) {

            $totalData       = $jsonDecodedResults['result']['orders']['paging'][0]['total'];
            $totalFiltered   = $totalData;

            foreach($jsonDecodedResults['result']['orders']['order'] as $key => $orderList) {

                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$orderList['order_no'].'" />';
                $nestedData['name']       = '<h6>'.$orderList['order_no'].'</h6><div>Invoice no: <span class="badge badge-info badge-pill">'.$orderList['invoice_no'].'</span></div><div>Created on: <span class="badge badge-info badge-pill">'.date("d.m.y H:i:s", strtotime($orderList['created'])).'</span></div>';
                $nestedData['active']     = ucwords($orderList['status']);
                $nestedData['actions']    = '<i class="fas fa-download"></i>';
                $data[]                   = $nestedData;
            }

            return compact('data', 'totalData', 'totalFiltered');
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
