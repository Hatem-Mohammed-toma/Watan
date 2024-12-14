<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('categories')->insert([
        //     [
        //         'name' => 'Electronics',
        //         'desc' => 'Electronic items like TVs, mobiles, and computers.',
        //         'code' => 'ELEC',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'name' => 'Furniture',
        //         'desc' => 'Household furniture including chairs, tables, and beds.',
        //         'code' => 'FURN',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        // ]);
    }
}
