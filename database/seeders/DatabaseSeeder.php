<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        DB::table('vehicle_types')->insert([
            ['name' => 'Auto', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Motocicleta', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Camioneta', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('parking_space_statuses')->insert([
            ['code' => 'available', 'name' => 'Disponible', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'occupied', 'name' => 'Ocupado', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'maintenance', 'name' => 'Mantenimiento', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('ticket_statuses')->insert([
            ['code' => 'open', 'name' => 'Abierto', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'closed', 'name' => 'Cerrado', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'cancelled', 'name' => 'Cancelado', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
