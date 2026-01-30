<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PermissionApiController extends Controller
{
    public function index()
    {
        $permissions = DB::table('permissions')
            ->select('id', 'code', 'name')
            ->orderBy('code')
            ->get();

        return response()->json([
            'status' => 'success',
            'permissions' => $permissions
        ]);
    }
}
