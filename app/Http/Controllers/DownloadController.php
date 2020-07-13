<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\CurlTrait;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    use CurlTrait;
    /**
     * Download invoice.
     *
     * @param  string  $api_key
     * @param  string  $orderNo
     * @return \Illuminate\Http\Response
     */
    public function download($api_key, $order_no)
    {
        try {
            // Sending request to API. Passing api key, and order number to get order delivery note.
            $getOrderDeliveryNote = 'http://webservice.rakuten.de/merchants/orders/getOrderDeliveryNote?key=' . $api_key . '&format=json&order_no=' . $order_no;

            // Get order invoice
            if (!empty($getOrderDeliveryNote)) {
                // Fetching data from API
                $jsonDecodedResults = $this->curl($getOrderDeliveryNote);
            }

            // Checking the API response is success or failure.
            if ($jsonDecodedResults['result']['success'] === '1') {

                // URL src from API response
                // URL src doesn't have trasfer protocol. So added trasfer protocol in environment file manually.
                $fileSource = env('API_URL_TRANSFER_PROTOCOL') . $jsonDecodedResults['result']['ticket']['src'];

                $fileName = $jsonDecodedResults['result']['ticket']['filename']; // Here we get th Filename from API response.

                $headers = ['Content-Type: application/pdf'];

                $file_get_contents = file_get_contents($fileSource);

                // Path of directory and file
                $pathToDirectory = 'getOrderDeliveryNote';

                $pathToFile = $pathToDirectory . '/' . $fileName;

                // If directory doesn't exists create a new one.
                if (!Storage::exists($pathToDirectory)) {
                    $createDirectory = Storage::makeDirectory($pathToDirectory, 0775);
                }

                // Checking data already exist or not
                if (Storage::exists($pathToFile)) {

                    Storage::delete($pathToFile); // Delete files from directory

                    file_put_contents(storage_path('app/' . $pathToFile), $file_get_contents); // Store content in to a file
                } 
                else {

                    file_put_contents(storage_path('app/' . $pathToFile), $file_get_contents); // Store content in to a file
                }

                return Storage::download($pathToFile, $fileName, $headers);
            }
        } catch (\Exception $e) {
            return response()->json(['getOrderDeliveryNote' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
