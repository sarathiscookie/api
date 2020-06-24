<?php

use App\Module;
use Illuminate\Database\Seeder;

class ModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Module::updateOrCreate(
            ['id' => 1],
            [
                'module' => 'rakuten lieferanten email',
                'active' => 'yes'
            ]
        );

        Module::updateOrCreate(
            ['id' => 2],
            [
                'module' => 'rakuten hardware otto',
                'active' => 'yes'
            ]
        );

        Module::updateOrCreate(
            ['id' => 3],
            [
                'module' => 'rakuten hardware bork',
                'active' => 'yes'
            ]
        );
    }
}
