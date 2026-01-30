<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BranchApiController extends Controller
{
    public function index()
    {
        $branches = DB::select('CALL sp_GetBranches()');

        return response()->json([
            'status' => 'success',
            'branches' => $branches
        ]);
    }
}
