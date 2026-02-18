<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tariff;
use App\Models\Branch;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->get('branch_id');

        $branches = Branch::where('is_active', 1)
            ->orderBy('name')
            ->get();

        $tariffs = Tariff::with(['branch', 'vehicleType'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('priority')
            ->get();

        return view('admin.tariffs.index', compact(
            'tariffs',
            'branches',
            'branchId'
        ));
    }

    public function create()
    {
        $branches = Branch::where('is_active', 1)
            ->orderBy('name')
            ->get();

        $vehicleTypes = VehicleType::orderBy('name')->get();

        return view('admin.tariffs.form', compact(
            'branches',
            'vehicleTypes'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id'        => 'required|exists:branches,id',
            'vehicle_type_id'  => 'required|exists:vehicle_types,id',
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string',
            'calc_type'        => 'required|in:hourly,flat',
            'price_per_hour'   => 'nullable|numeric|min:0',
            'flat_amount'      => 'nullable|numeric|min:0',
            'grace_minutes'    => 'nullable|integer|min:0',
            'priority'         => 'required|integer|min:1',
        ]);

        // Normalización
        if ($data['calc_type'] === 'hourly') {
            $data['flat_amount'] = null;
        } else {
            $data['price_per_hour'] = null;
        }

        $data['is_active'] = 1;

        Tariff::create($data);

        return redirect()
            ->route('admin.tariffs.index')
            ->with('success', 'Tarifa creada correctamente');
    }

    public function edit($id)
    {
        $tariff = Tariff::findOrFail($id);

        $branches = Branch::where('is_active', 1)
            ->orderBy('name')
            ->get();

        $vehicleTypes = VehicleType::orderBy('name')->get();

        return view('admin.tariffs.form', compact(
            'tariff',
            'branches',
            'vehicleTypes'
        ));
    }

    public function update(Request $request, $id)
    {
        $tariff = Tariff::findOrFail($id);

        $data = $request->validate([
            'branch_id'        => 'required|exists:branches,id',
            'vehicle_type_id'  => 'required|exists:vehicle_types,id',
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string',
            'calc_type'        => 'required|in:hourly,flat',
            'price_per_hour'   => 'nullable|numeric|min:0',
            'flat_amount'      => 'nullable|numeric|min:0',
            'grace_minutes'    => 'nullable|integer|min:0',
            'priority'         => 'required|integer|min:1',
            'is_active'        => 'required|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // Normalización
        if ($data['calc_type'] === 'hourly') {
            $data['flat_amount'] = null;
        } else {
            $data['price_per_hour'] = null;
        }

        $tariff->update($data);

        return redirect()
            ->route('admin.tariffs.index')
            ->with('success', 'Tarifa actualizada correctamente');
    }
}
