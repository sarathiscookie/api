<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [[
        	'name' => 'admin',
        	'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => Hash::make('11111111'),
            'created_at' => Carbon::now(),
            'role' => 'admin',
            'active' => 'yes',
        ],
        [
            'name' => 'marko',
            'email' => 'marko@herm.de',
            'username' => 'marko',
            'password' => Hash::make('www.herm.de'),
            'created_at' => Carbon::now(),
            'role' => 'admin',
            'active' => 'yes',
        ]];

        User::insert($data);
    }
}
