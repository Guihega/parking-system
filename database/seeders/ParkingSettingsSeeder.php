<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParkingSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('parking_settings')->insert([
            'branch_id' => 1,
            'grace_minutes' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
