<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminRolesController extends Controller
{
    public function index()
    {
        //dd(session('permissions'));
        return view('admin.roles.index');
    }

    public function assignPermissions(Request $request, int $roleId)
    {
        $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'integer',
        ]);

        DB::statement(
            'CALL sp_role_assign_permissions(?, ?, ?)',
            [
                $roleId,
                json_encode($request->permissions),
                auth()->id()
            ]
        );

        // 🔥 Si el usuario actual tiene ese rol → refrescar sesión
        if (auth()->user()->roles->pluck('id')->contains($roleId)) {
            session()->forget('permissions');
        }

        return response()->json([
            'status' => 'success'
        ]);
    }


}
