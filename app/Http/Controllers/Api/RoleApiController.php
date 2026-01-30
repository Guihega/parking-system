<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;

class RoleApiController extends Controller
{
    public function index()
    {
        $roles = DB::table('roles')->select('id', 'name', 'code')->get();

        return response()->json([
            'status' => 'success',
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:roles,code'
        ]);

        DB::table('roles')->insert([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rol creado correctamente'
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:roles,code,' . $id
        ]);

        // 1. Actualizar rol
        DB::table('roles')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'code' => $request->code,
                'updated_at' => now()
            ]);

        // 2. REGISTRO DE AUDITORÍA (AQUÍ VA)
        AuditService::updateRole(
            auth()->id(),
            $id,
            $before,
            $after
        );

        // 3. Respuesta
        return response()->json([
            'status' => 'success',
            'message' => 'Rol actualizado correctamente'
        ]);
    }

    public function destroy($id)
    {
        DB::table('roles')->where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Rol eliminado'
        ]);
    }

    public function permissions($id)
    {
        $permissions = DB::table('permissions')
            ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permission_role.role_id', $id)
            ->select('permissions.id', 'permissions.code', 'permissions.name')
            ->get();

        return response()->json([
            'status' => 'success',
            'permissions' => $permissions
        ]);
    }

    public function assignPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array'
        ]);

        DB::table('permission_role')->where('role_id', $id)->delete();

        foreach ($request->permissions as $permissionId) {
            DB::table('permission_role')->insert([
                'role_id' => $id,
                'permission_id' => $permissionId
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Permisos asignados correctamente'
        ]);
    }
}
