<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class BasePermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'dashboard.view',
            'branches.manage',
            'parking.manage',
            'tariffs.manage',
            'cash.manage',
            'reports.view',
            'security.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
    }
}
