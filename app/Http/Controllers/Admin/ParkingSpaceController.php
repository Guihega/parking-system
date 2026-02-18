<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpace;
use App\Models\Branch;
use App\Models\VehicleType;
use App\Models\ParkingSpaceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParkingSpaceController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->get('branch_id');

        $branches = Branch::where('tenant_id', app('tenant_id'))
            ->where('is_active',1)
            ->orderBy('name')
            ->get();

        $spaces = ParkingSpace::with(['status', 'branch', 'vehicleType'])
        ->where('tenant_id', app('tenant_id'))
        ->when($branchId, fn($q) => $q->where('branch_id',$branchId))
        ->orderBy('code')
        ->get();

        return view('admin.parking_spaces.index', compact('spaces','branches','branchId'));
    }

    public function create()
    {
        return view('admin.parking_spaces.form', [
            'space'        => new ParkingSpace(), // 👈 MODELO VACÍO (CLAVE)
            'branches'     => Branch::where('tenant_id', app('tenant_id'))->get(),
            'vehicleTypes' => VehicleType::orderBy('name')->get(),
            'statuses'     => ParkingSpaceStatus::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'code' => 'required|string|max:50',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
        ]);

        // 👉 status "available" desde catálogo
        $statusId = ParkingSpaceStatus::where('code', 'available')->value('id');

        ParkingSpace::create([
            'tenant_id'       => app('tenant_id'),
            'branch_id'       => $request->branch_id,
            'vehicle_type_id' => $request->vehicle_type_id,
            'code'            => $request->code,
            'status_id'       => $statusId,
        ]);


        return redirect()
            ->route('admin.parking-spaces.index')
            ->with('success','Cajón creado');
    }


    public function edit($id)
    {
        $space = ParkingSpace::where('tenant_id', app('tenant_id'))
            ->where('id', $id)
            ->firstOrFail();

        $branches = Branch::where('tenant_id', app('tenant_id'))->get();
        $vehicleTypes = VehicleType::orderBy('name')->get();
        $statuses = ParkingSpaceStatus::orderBy('name')->get();

        return view(
            'admin.parking_spaces.form',
            compact('space', 'branches', 'vehicleTypes', 'statuses')
        );
    }

    public function update(Request $request, $id)
    {
        $space = ParkingSpace::where('tenant_id', app('tenant_id'))
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'branch_id'        => 'required|exists:branches,id',
            'code'             => 'required|string|max:50',
            'vehicle_type_id'  => 'required|exists:vehicle_types,id',
            'status_id'        => 'required|exists:parking_space_statuses,id',
        ]);

        $space->update([
            'branch_id'        => $request->branch_id,
            'code'             => $request->code,
            'vehicle_type_id'  => $request->vehicle_type_id,
            'status_id'        => $request->status_id,
        ]);

        return redirect()
            ->route('admin.parking-spaces.index')
            ->with('success', 'Cajón actualizado');
    }

}
