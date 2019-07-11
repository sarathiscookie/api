<?php


use App\Shopname;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShopnamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
        	['name' => 'rakuten', 'created_at' => Carbon::now()],
        	['name' => 'amazone', 'created_at' => Carbon::now()],
        	['name' => 'ebay', 'created_at' => Carbon::now()]
        ];

        Shopname::insert($data);
    }
}
