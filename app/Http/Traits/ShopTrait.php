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
            $shop = Shop::select('shop')
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
            $shop = Shop::select('id', 'shop')
            ->active()
            ->where('company_id', $id)
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
            ->select('shops.shop')
            ->where('key_shops.key_container_id', $id)
            ->get();

            $html = '';
            foreach($shops as $shop) {
                $html .= '<span class="badge badge-info badge-pill">'.$shop->shop.'</span>';
            }
            return $html;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

    /*SELECT t1.shop, t1.id
  FROM shops AS t1 INNER JOIN key_shops AS t2 ON t1.id = t2.shop_id where t2.key_container_id = 3;*/

}

?>