<?php

namespace App\Http\Controllers\Admin;

use App\Shop;
use App\Product;
use App\ModuleSetting;
use Illuminate\Http\Request;
use App\Http\Traits\CurlTrait;
use App\Http\Traits\ShopTrait;
use App\Http\Traits\ModuleTrait;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\ShopnameTrait;
use App\Http\Traits\ProductTrait;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    use CompanyTrait, ShopnameTrait, ShopTrait, ModuleTrait, CurlTrait, ProductTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = $this->company();

        $shopNames = $this->shopNames();

        return view('admin.productGet', ['companies' => $companies, 'shopNames' => $shopNames]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $shopId
     * @param  int  $companyId
     * @return \Illuminate\Http\Response
     */
    public function show($shopId, $companyId)
    {
        return view('admin.productList', ['shopId' => $shopId, 'companyId' => $companyId]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        try {
            $params           = $request->all();
            $data             = [];
            $totalData        = 0;
            $totalFiltered    = 0;
            $product_details  = '';
            $category_details = '';
            $search           = '';
            $visible          = '';
            $available        = '';

            // Search query for product name
            if (!empty($request->input('search.value'))) {
                $search = "&search=" . urlencode($request->input('search.value')) . "&search_field=name";
            }

            //If shop is rakuten then below code will execute.
            if ($request->productListShopID === '1') {

                // Search query for category
                if (($request->productCategoryId !== 'allCategories') && ($request->productCategoryId !== null)) {
                    $search = "&search=" . urlencode($request->productCategoryId) . "&search_field=shop_category_id";
                }

                //Filter visible: 1 = Visible & 0 = Not visible
                if ($request->visible !== null) {
                    $visible = "&visible=" . $request->visible;
                }

                //Filter available: 1 = Available & 0 = Not available
                if ($request->available !== null) {
                    $available = "&available=" . $request->available;
                }

                //get company api key from shops
                $api_key           = $this->getApiKey($request->productListShopID, $request->productListCompanyId);

                $urlGetProducts    = 'http://webservice.rakuten.de/merchants/products/getProducts?key=' . $api_key->api_key . '&format=json&page=' . $request->pageActive . $visible . $available . $search;

                $urlShopCategories = 'http://webservice.rakuten.de/merchants/categories/getShopCategories?key=' . $api_key->api_key . '&format=json';
            }

            // Get product details
            if (!empty($urlGetProducts)) {
                $product_details  = $this->getUrlProducts($urlGetProducts);
            }

            // Get shop categories
            if (!empty($urlShopCategories)) {
                $category_details = $this->getUrlShopCategories($urlShopCategories);
            }

            // Checking product details is empty or not
            if (!empty($product_details)) {
                $data = $product_details['data'];
                $totalData = (int) $product_details['totalData'];
                $totalFiltered = (int) $product_details['totalFiltered'];
            }

            $json_data = array(
                'draw'            => (int) $params['draw'],
                'recordsTotal'    => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data'            => $data,
                'categoryDetails' => $category_details
            );

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
        $columns = [1 => 'name', 2 => 'active'];

        //Fetching data from API
        $jsonDecodedResults = $this->curl($urlGetProducts);

        //If json status is success then value is '1' error value is '-1'
        if (($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['products']['paging'][0]['total'] != '0')) {

            $totalData       = $jsonDecodedResults['result']['products']['paging'][0]['total'];
            $totalFiltered   = $totalData;

            foreach ($jsonDecodedResults['result']['products']['product'] as $key => $productList) {
                $visibleStatus            = ($productList['visible'] === '1') ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';

                //Some products doesn't have available status in product array. Eg:1918778210,1918779405,1918780015
                if ($productList['has_variants'] === '1' && empty($productList['available'])) {
                    $availableStatus      = '<i class="fas fa-thumbs-down"></i>';
                } else {
                    $availableStatus      = ($productList['available'] === '1') ? '<i class="fas fa-thumbs-up"></i>' : '<i class="fas fa-thumbs-down"></i>';
                }

                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="' . $productList['product_id'] . '" />';
                $nestedData['name']       = '<h6>' . $productList['name'] . '</h6> <hr><div>Product Id: <span class="badge badge-info badge-pill">' . $productList['product_id'] . '</span></div> <div>Producer: <span class="badge badge-info badge-pill text-capitalize">' . $productList['producer'] . '</span></div> <div>Art No: <span class="badge badge-info badge-pill text-capitalize">' . $productList['product_art_no'] . '</span></div> <div>Visible: ' . $visibleStatus . '</div> <div>Available: ' . $availableStatus . '</div>';
                $nestedData['active']     = $this->productStatusHtml($productList['product_id']);
                $nestedData['actions']    = $this->moduleSettingsHtml($productList['product_id']);
                $data[]                   = $nestedData;
            }
            return compact('data', 'totalData', 'totalFiltered');
        }
    }

    /**
     * html for module settings 
     * @param  string $productApiId
     * @return \Illuminate\Http\Response
     */
    public function moduleSettingsHtml($productApiId)
    {
        $moduleOptions = '';

        foreach ($this->fetchModules() as $module) {
            $moduleOptions .= '<option value="' . $module->id . '">' . $module->module . '</option>';
        }

        $html = '<a href="" style="color:black;" class="btn btn-secondary btn-sm moduleSettings_' . $productApiId . ' moduleAtag" data-productid="' . $productApiId . '" data-target="#moduleModal_' . $productApiId . '" title="Add Module"><i class="fas fa-plus"></i></a>
                <a href="" class="btn btn-secondary btn-sm" style="color:black;" title="Manage Module"><i class="fas fa-cog"></i></a>
                <div class="modal fade" id="moduleModal_' . $productApiId . '" tabindex="-1" role="dialog" aria-labelledby="moduleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title">Add Module</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">

                <div class="addModuleSettingsStatus_' . $productApiId . '"></div>
                <form>
                <div class="form-group">
                <label for="module">Module:</label>
                <select class="form-control" id="module_id_' . $productApiId . '">
                <option>Choose Module</option>
                ' . $moduleOptions . '
                </select>
                </div>
                </form>

                </div>
                <div class="modal-footer"><button type="button" class="btn btn-primary saveModuleDetails" data-addmoduleproductid="' . $productApiId . '">Add</button></div>
                </div>
                </div>
                </div>';

        return $html;
    }

    /**
     * html group button to change product status 
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function productStatusHtml($id)
    {
        //------------ Write query to check default status
        $checked = 'checked';
        $html    = '<label class="switch" data-productstatusid="' . $id . '">
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
        $category_details        = [];
        $category_details_offset = [];

        //Fetching data from API
        $jsonDecodedResults = $this->curl($urlShopCategories);

        //If json status is success then value is '1' error value is '-1'
        if ($jsonDecodedResults['result']['success'] === '1') {
            if ($jsonDecodedResults['result']['categories']['paging'][0]['total'] != 0) {
                $category_details[]        = $jsonDecodedResults['result']['categories']['category'];
                $category_details_offset   = $category_details[0];
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
    public function addModule(Request $request)
    {
        try {
            // Checking product already existing in product table.
            $productExist = $this->productExist($request->product_id); 

            // Checking module already existing in module settings table.
            $moduleSettings = ModuleSetting::where('module_id', $request->module_id)
                ->where('product_id', $productExist->id)
                ->first();

            if (empty($moduleSettings)) {
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
