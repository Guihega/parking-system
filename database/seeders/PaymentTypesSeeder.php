<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTypesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $types = [
            ['code' => 'cash',     'name' => 'Efectivo',      'is_active' => 1],
            ['code' => 'card',     'name' => 'Tarjeta',       'is_active' => 1],
            ['code' => 'transfer', 'name' => 'Transferencia', 'is_active' => 1],
        ];

        foreach ($types as $t) {
            DB::table('payment_types')->updateOrInsert(
                ['code' => $t['code']],
                [
                    'name' => $t['name'],
                    'is_active' => $t['is_active'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
