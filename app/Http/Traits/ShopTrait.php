<?php

namespace App\Http\Traits;

use App\Shop;

trait ShopTrait {
    /**
     * Get shop matching with id
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function fetchShop($id)
    {
        try {
            $shop = Shop::select('shopname_id')
            ->active()
            ->find($id);

            return $shop;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

    /**
     * Find shops of parent company
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function getShops($id)
    {
        try {
            $shop = Shop::join('shopnames', 'shops.shopname_id', '=', 'shopnames.id')
            ->select('shops.id', 'shopnames.name AS shop')
            ->where('shops.company_id', $id)
            ->joinactive()
            ->get();

            return $shop;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

    /**
     * Find shop name for container
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function getShopsName($id)
    {
        try {
            $shops = Shop::join('key_shops', 'shops.id', '=', 'key_shops.shop_id')
            ->select('shops.shopname_id')
            ->where('key_shops.key_container_id', $id)
            ->joinactive()
            ->get();

            return $shops;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

    /**
     * Find shop name for container
     * @param  string $shopId
     * @param  string $companyId
     * @return \Illuminate\Http\Response
     */
    public function getApiKey($shopId, $companyId)
    {
        try {
            $api_key = Shop::select('api_key')
            ->where('shopname_id', $shopId)
            ->where('company_id', $companyId)
            ->first();

            return $api_key;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

    /**
     * Generating dynamic email configuration.
     * @param object $shop
     * @return \Illuminate\Http\Response
     */
    public function dynamicEmailConfig($shop)
    {
        $configuration = [
            'smtp_host'       => $shop->mail_host,
            'smtp_port'       => $shop->mail_port,
            'smtp_username'   => $shop->mail_username,
            'smtp_password'   => $this->passwordExtract($shop->mail_password),
            'smtp_encryption' => $shop->mail_encryption,
            'from_email'      => $shop->mail_from_address,
            'from_name'       => $shop->mail_from_name,
        ];

        return $configuration;
    }

    /**
     * Generating password.
     * @param string $password
     * @return \Illuminate\Http\Response
     */
    public function passwordGenerate($password)
    {
        return random_int(111111, 999999).'%_%'.$password.'%_%'.uniqid();
    }

    /**
     * Extracting password.
     * @param string $password
     * @return \Illuminate\Http\Response
     */
    public function passwordExtract($password)
    {
        $extract = explode('%_%', $password);

        // If user added new password, there is not symbol %_% to extract.
        // Added condition to identify new password or old password. 
        return (count($extract) === 3) ? $extract[1] : $extract[0];
    }

}

?>