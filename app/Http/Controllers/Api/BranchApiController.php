<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'status'   => 'success',
            'branches' => Branch::where('tenant_id', app('tenant_id'))
                ->orderBy('name')
                ->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $branch = Branch::create([
            'tenant_id' => app('tenant_id'),
            'name'      => $request->name,
            'is_active' => 1,
        ]);

        return response()->json($branch, 201);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::where('tenant_id', app('tenant_id'))
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $branch->update([
            'name' => $request->name,
        ]);

        return response()->json($branch);
    }

    public function toggle($id)
    {
        $branch = Branch::where('tenant_id', app('tenant_id'))
            ->where('id', $id)
            ->firstOrFail();

        $branch->is_active = !$branch->is_active;
        $branch->save();

        return response()->json($branch);
    }

    public function destroy($id)
    {
        Branch::where('tenant_id', app('tenant_id'))
            ->where('id', $id)
            ->delete();

        return response()->json(['status' => 'ok']);
    }
}
