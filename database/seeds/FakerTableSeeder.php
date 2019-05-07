<?php

use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FakerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (range(2,20) as $index) {

            $createddate = mt_rand( strtotime("Jul 01 2018"), strtotime("Apr 01 2019") );

            User::insert([
                'name' => $faker->firstNameMale,
                'password' => Hash::make('11111111'),
                'street' => $faker->streetName,
                'postal' => $faker->postcode,
                'city' => $faker->city,
                'country' => $faker->country,
                'phone' => mt_rand(1111111111, 9999999999),
                'email' => $faker->email,
                'active' => 'yes',
                'role' => 'manager',
                'created_at' => date("Y-m-d H:i:s", $createddate),
            ]);
        }

        foreach (range(21,40) as $index) {

            $createddate = mt_rand( strtotime("Jul 01 2018"), strtotime("Apr 01 2019") );

            User::insert([
                'name' => $faker->firstNameMale,
                'password' => Hash::make('11111111'),
                'street' => $faker->streetName,
                'postal' => $faker->postcode,
                'city' => $faker->city,
                'country' => $faker->country,
                'phone' => mt_rand(1111111111, 9999999999),
                'email' => $faker->email,
                'active' => 'yes',
                'role' => 'employee',
                'created_at' => date("Y-m-d H:i:s", $createddate),
            ]);
        }

    }
}
