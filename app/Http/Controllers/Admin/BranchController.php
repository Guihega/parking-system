<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::where('tenant_id', app('tenant_id'))
            ->orderBy('name')
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create(Request $request)
    {
        $branch = new Branch();

        if ($request->ajax()) {
            return view('admin.branches.form', compact('branch'));
        }

        return view('admin.branches.form', compact('branch'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120|unique:branches,name,NULL,id,tenant_id,' . app('tenant_id')
        ]);

        Branch::create([
            'tenant_id' => app('tenant_id'),
            'name' => $request->name,
            'is_active' => 1
        ]);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal creada correctamente');
    }

    public function edit(Request $request, $id)
    {
        $branch = Branch::where('tenant_id', app('tenant_id'))
            ->where('id', $id)
            ->firstOrFail();

        if ($request->ajax()) {
            return view('admin.branches.form', compact('branch'));
        }

        return view('admin.branches.form', compact('branch'));
    }


    public function update(Request $request, $id)
    {
        $branch = Branch::where('tenant_id', app('tenant_id'))
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:120|unique:branches,name,' . $branch->id . ',id,tenant_id,' . app('tenant_id')
        ]);

        $branch->update([
            'name' => $request->name
        ]);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Sucursal actualizada');
    }
}
