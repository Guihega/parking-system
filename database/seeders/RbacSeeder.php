<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['code' => 'admin', 'name' => 'Administrador'],
            ['code' => 'supervisor', 'name' => 'Supervisor'],
            ['code' => 'cashier', 'name' => 'Cajero'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['code' => $r['code']], $r);
        }

        $permissions = [
            // Tariffs
            ['code' => 'tariffs.view', 'name' => 'Ver tarifas', 'module' => 'tariffs'],
            ['code' => 'tariffs.create', 'name' => 'Crear tarifas', 'module' => 'tariffs'],
            ['code' => 'tariffs.update', 'name' => 'Actualizar tarifas', 'module' => 'tariffs'],
            ['code' => 'tariffs.delete', 'name' => 'Desactivar tarifas', 'module' => 'tariffs'],

            // Cash Sessions
            ['code' => 'cash.open', 'name' => 'Abrir caja', 'module' => 'cash_sessions'],
            ['code' => 'cash.close', 'name' => 'Cerrar caja', 'module' => 'cash_sessions'],
            ['code' => 'cash.audit', 'name' => 'Ver auditoría de caja', 'module' => 'cash_sessions'],

            // Reports
            ['code' => 'reports.view', 'name' => 'Ver reportes', 'module' => 'reports'],
        ];

        $permIds = [];
        foreach ($permissions as $p) {
            $perm = Permission::firstOrCreate(['code' => $p['code']], $p);
            $permIds[$p['code']] = $perm->id;
        }

        $admin = Role::where('code', 'admin')->first();
        $admin->permissions()->sync(array_values($permIds));

        $supervisor = Role::where('code', 'supervisor')->first();
        $supervisor->permissions()->sync([
            $permIds['tariffs.view'],
            $permIds['cash.audit'],
            $permIds['reports.view'],
        ]);

        $cashier = Role::where('code', 'cashier')->first();
        $cashier->permissions()->sync([
            $permIds['cash.open'],
            $permIds['cash.close'],
            $permIds['tariffs.view'],
        ]);
    }
}
