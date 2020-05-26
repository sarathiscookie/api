<?php

namespace App\Http\Controllers\Admin;

use App\Shop;
use App\Product;
use App\ModuleSetting;
use App\Module;
use Illuminate\Http\Request;
use App\Http\Traits\CurlTrait;
use App\Http\Traits\ShopTrait;
use App\Http\Traits\ModuleTrait;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\ShopnameTrait;
use App\Http\Traits\ProductTrait;
use App\Http\Traits\ModuleSettingTrait;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    use CompanyTrait, ShopnameTrait, ShopTrait, ModuleTrait, CurlTrait, ProductTrait, ModuleSettingTrait;

    /**
     * Show the products view page. Passing all active companies and shops into the products view page.
     *
     *  @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetching companies from company trait
        $companies = $this->company();

        // Fetching shopnames from shopname trait
        $shopNames = $this->shopNames();

        return view('admin.productGet', [ 'companies' => $companies, 'shopNames' => $shopNames ]);
    }

    /**
     * Show the product lists view page. Passing shop id and companhy id into the product lists view page.
     *
     * @param  int  $shopId
     * @param  int  $companyId
     *  @return \Illuminate\View\View
     */
    public function show($shopId, $companyId)
    {
        return view('admin.productList', ['shopId' => $shopId, 'companyId' => $companyId]);
    }

    /**
     *  Show the products into the view page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        try {
            // Getting all the http request.
            $params = $request->all();
            
            $data = [];
            $totalData = 0;
            $totalFiltered = 0;
            $product_details = '';
            $category_details = '';
            $search = '';
            $visible = '';
            $available = '';

            // If the request has a search value (product name), this query will execute and fetch the results.
            if (!empty($request->input('search.value'))) {
                $search = "&search=" . urlencode($request->input('search.value')) . "&search_field=name";
            }

            // If shop is Rakuten then this will execute.
            if ( $request->productListShopID === '1' ) {

                // Checking http request has product categories
                if ( ($request->productCategoryId !== 'allCategories') && ($request->productCategoryId !== null) ) {
                    $search = "&search=" . urlencode($request->productCategoryId) . "&search_field=shop_category_id";
                }

                // Checking http request has filter visible status. Filter visible: 1 = Visible & 0 = Not visible.
                if ( $request->visible !== null ) {
                    $visible = "&visible=" . $request->visible;
                }

                // Checking http request has filter available status. Filter available: 1 = Available & 0 = Not available.
                if ( $request->available !== null ) {
                    $available = "&available=" . $request->available;
                }

                // Get company API key from shops.
                $api_key = $this->getApiKey($request->productListShopID, $request->productListCompanyId);

                $urlGetProducts = 'http://webservice.rakuten.de/merchants/products/getProducts?key=' . $api_key->api_key . '&format=json&page=' . $request->pageActive . $visible . $available . $search;

                $urlShopCategories = 'http://webservice.rakuten.de/merchants/categories/getShopCategories?key=' . $api_key->api_key . '&format=json';
            }

            // Get product details
            if ( !empty($urlGetProducts) ) {
                $product_details  = $this->getUrlProducts($urlGetProducts);
            }

            // Get shop categories
            if ( !empty($urlShopCategories) ) {
                $category_details = $this->getUrlShopCategories($urlShopCategories);
            }

            // Checking product details is empty or not
            if ( !empty($product_details) ) {
                $data = $product_details['data'];
                $totalData = (int) $product_details['totalData'];
                $totalFiltered = (int) $product_details['totalFiltered'];
            }

            // Preparing array to send the response in JSON format to draw the data on datatable.
            $json_data = [
                'draw' => (int) $params['draw'],
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
                'categoryDetails' => $category_details
            ];

            return response()->json($json_data);
        } catch (\Exception $e) {
            return response()->json(['productListStatusMsg' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Get shop products details.
     *
     * @param  string  $urlShopCategories
     * @return \Illuminate\Http\Response
     */
    public function getUrlProducts($urlGetProducts)
    {
        $columns = [
            1 => 'name', 
            2 => 'active'
        ];

        // Fetching data from API
        $jsonDecodedResults = $this->curl($urlGetProducts);

        // If json status is success then value is '1' error value is '-1'
        if (($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['products']['paging'][0]['total'] != '0')) {

            $totalData = $jsonDecodedResults['result']['products']['paging'][0]['total'];
            $totalFiltered = $totalData;

            foreach ($jsonDecodedResults['result']['products']['product'] as $key => $productList) {
                $visibleStatus = ($productList['visible'] === '1') ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';

                // Some products doesn't have available status in product array. Eg:1918778210,1918779405,1918780015
                if ($productList['has_variants'] === '1' && empty($productList['available'])) {
                    $availableStatus = '<i class="fas fa-thumbs-down"></i>';
                } 
                else {
                    $availableStatus = ($productList['available'] === '1') ? '<i class="fas fa-thumbs-up"></i>' : '<i class="fas fa-thumbs-down"></i>';
                }

                // Getting module name matching with module settings
                $moduleName[$key] = '';
                $moduleSettings = $this->moduleName($productList['product_id']);

                // Fetching supplier details which is related to particular product and company.
                $supplierDetails = $this->getSupplierDetails($productList['product_id']);

                // Delivery status arrays
                $deliveryStatus = $this->deliveryStatus();

                if($moduleSettings->count() > 0) {

                    foreach($moduleSettings as $moduleSetting) {
                        $productModuleSettingsModal = view('admin.productModuleSettingsModal', [
                            'moduleSettingsId' => $moduleSetting->moduleSettingsId, 
                            'suppliers' => $supplierDetails,
                            'deliveryStatus' => $deliveryStatus
                        ]);

                        /* $productModuleSettingsViewModal = view('admin.productModuleSettingsViewModal', [
                            'moduleSettingsId' => $moduleSetting->moduleSettingsId
                        ]); */

                        $moduleName[$key] .= '
                        <span class="badge badge-info badge-pill">' . ucwords($moduleSetting->moduleName) . 
                        '&nbsp
                        <i class="fas fa-eye fa-lg module_settings_view" data-modulesettingsviewid='.$moduleSetting->moduleSettingsId.' data-toggle="modal" data-target="#moduleSettingsViewModal_'.$moduleSetting->moduleSettingsId.'" style="cursor:pointer;"></i>
                        &nbsp
                        <i class="fas fa-cog fa-lg module_settings_update" data-modulesettingsupdateid='.$moduleSetting->moduleSettingsId.' data-toggle="modal" data-target="#moduleSettingsModal_'.$moduleSetting->moduleSettingsId.'" style="cursor:pointer;"></i>
                        &nbsp
                        <i class="far fa-trash-alt fa-lg module_settings" data-modulesettingsid='.$moduleSetting->moduleSettingsId.' style="color:#9e004f; cursor:pointer;"></i>
                        </span>
                        &nbsp
                        <span class="module_settings_spinner_'.$moduleSetting->moduleSettingsId.'"></span>'.$productModuleSettingsModal/* .$productModuleSettingsViewModal */;
                    }

                }
                else {
                    $moduleName[$key] = '<span class="badge badge-secondary badge-pill"> No Modules </span>';
                }

                // Datatable filling
                $nestedData['hash'] = '<input class="checked" type="checkbox" name="id[]" value="' . $productList['product_id'] . '" />';
                $nestedData['name'] = '<h6>' . $productList['name'] . '</h6> <hr><div>Product Id: <span class="badge badge-info badge-pill">' . $productList['product_id'] . '</span></div> <div>Producer: <span class="badge badge-info badge-pill text-capitalize">' . $productList['producer'] . '</span></div> <div>Art No: <span class="badge badge-info badge-pill text-capitalize">' . $productList['product_art_no'] . '</span></div> <div>Visible: ' . $visibleStatus . '</div> <div>Available: ' . $availableStatus . '</div><div>Modules: '.$moduleName[$key].'</div>';
                $nestedData['active'] = $this->productStatusHtml($productList['product_id']);
                $nestedData['actions'] = $this->moduleSettingsHtml($productList['product_id']);
                $data[] = $nestedData;
            }
            return compact('data', 'totalData', 'totalFiltered');
        }
    }

    /**
     * HTML for module settings 
     * @param  string $productApiId
     * @return \Illuminate\Http\Response
     */
    public function moduleSettingsHtml($productApiId)
    {
        $moduleOptions = '';

        foreach ($this->fetchModules() as $module) {
            $moduleOptions .= '<option value="' . $module->id . '">' . $module->module . '</option>';
        }

        $productModuleAddModal = view('admin.productModuleAddModal', ['productApiId' => $productApiId, 'moduleOptions' => $moduleOptions]);

        $html = '<a href="" style="color:black;" class="btn btn-secondary btn-sm moduleAddClear moduleSettings_' . $productApiId . ' moduleAtag" data-productid="' . $productApiId . '" data-target="#moduleModal_' . $productApiId . '" title="Add Module"><i class="fas fa-plus"></i></a>'.$productModuleAddModal;

        return $html;
    }

    /**
     * HTML group button to change product status 
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function productStatusHtml($id)
    {
        //------------ Write query to check default status
        $checked = 'checked';
        $html = '<label class="switch" data-productstatusid="' . $id . '">
        <input type="checkbox" class="buttonStatus" ' . $checked . '>
        <span class="slider round"></span>
        </label>';

        return $html;
    }


    /**
     * Get shop categories details.
     *
     * @param  string  $urlShopCategories
     * @return \Illuminate\Http\Response
     */
    public function getUrlShopCategories($urlShopCategories)
    {
        $category_details = [];
        $category_details_offset = [];

        // Fetching data from API
        $jsonDecodedResults = $this->curl($urlShopCategories);

        //If json status is success then value is '1' error value is '-1'
        if ($jsonDecodedResults['result']['success'] === '1') {
            if ($jsonDecodedResults['result']['categories']['paging'][0]['total'] != 0) {
                $category_details[] = $jsonDecodedResults['result']['categories']['category'];
                $category_details_offset = $category_details[0];
            }
        }

        return $category_details_offset;
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
        try {
            $product = Product::updateOrCreate(
                ['api_product_id' => $request->productApiId],
                ['shopname_id' => $request->productListShopId, 'company_id' => $request->productListCompanyId]
            );

            return response()->json(['productStatus' => 'success', 'message' => 'Well done! Product saved successfully'], 201);
        } 
        catch (\Exception $e) {
            return response()->json(['productStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Add modules in to product table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addProductModule(Request $request)
    {
        try {
            // Checking product already existing in product table.
            $productExist = $this->productExist($request->product_id); 

            // Checking module already existing in module settings table.
            $moduleSettings = ModuleSetting::where('module_id', $request->module_id)
                ->where('product_id', $productExist->id)
                ->first();

            if ( empty($moduleSettings) ) {

                $createModuleSetting = new ModuleSetting;
                $createModuleSetting->module_id = $request->module_id;
                $createModuleSetting->product_id = $productExist->id;
                $createModuleSetting->save();

                return response()->json(['moduleSettingStatus' => 'success', 'message' => 'Well done! Module added successfully.'], 201);
            }
            else {
                return response()->json(['moduleSettingStatus' => 'success', 'message' => 'Already exist! Module already exist.'], 201);
            }
            
        } 
        catch (\Exception $e) {
            return response()->json(['moduleSettingStatus' => 'failure', 'message' => 'Whoops! Something went wrong.'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteProductModule($id)
    {
        try {
            ModuleSetting::destroy($id);

            return response()->json(['deletedModuleSettingStatus' => 'success', 'message' => 'Module deleted successfully'], 201);
        }
        catch(\Exception $e) {
            return response()->json(['deletedModuleSettingStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
