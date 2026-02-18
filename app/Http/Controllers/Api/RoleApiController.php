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
        $roles = DB::table('roles')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
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

        $before = DB::table('roles')->where('id', $id)->first();

        DB::table('roles')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'code' => $request->code,
                'updated_at' => now()
            ]);

        $after = DB::table('roles')->where('id', $id)->first();

        AuditService::updateRole(
            auth()->id(),
            $id,
            $before,
            $after
        );

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

    public function permissions(int $id)
    {
        $rows = DB::select('CALL sp_role_get_permissions(?)', [$id]);

        $grouped = [];

        foreach ($rows as $perm) {
            $module = $perm->module ?? 'general';

            $grouped[$module][] = [
                'id'       => $perm->id,
                'code'     => $perm->code,
                'name'     => $perm->name,
                'assigned' => (bool) $perm->assigned,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => $grouped,
        ]);
    }



    public function assignPermissions(Request $request, int $id)
    {
        $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'integer'
        ]);

        try {
            // Convertimos array a JSON para el SP
            $permissionsJson = json_encode($request->permissions);

            DB::statement(
                'CALL sp_role_assign_permissions(?, ?)',
                [$id, $permissionsJson]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Permisos asignados correctamente'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al asignar permisos',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function permissionsByModule(int $roleId)
    {
        $all = DB::table('permissions')
            ->select('id', 'code', 'name', 'module')
            ->where('is_active', 1)
            ->orderBy('module')
            ->orderBy('code')
            ->get();

        $assigned = DB::table('permission_role')
            ->where('role_id', $roleId)
            ->pluck('permission_id')
            ->toArray();

        $grouped = [];

        foreach ($all as $perm) {
            $grouped[$perm->module][] = [
                'id'       => $perm->id,
                'code'     => $perm->code,
                'name'     => $perm->name,
                'assigned' => in_array($perm->id, $assigned)
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => $grouped
        ]);
    }

    public function audit(int $id)
    {
        $logs = DB::table('role_permission_audit as a')
            ->join('users as u', 'u.id', '=', 'a.actor_user_id')
            ->where('a.role_id', $id)
            ->orderByDesc('a.created_at')
            ->select([
                'a.id',
                'a.permissions_before',
                'a.permissions_after',
                'a.created_at',
                'u.name as actor_name',
                'u.email as actor_email',
            ])
            ->limit(50)
            ->get();

        return response()->json([
            'status' => 'success',
            'audit' => $logs
        ]);
    }

}
