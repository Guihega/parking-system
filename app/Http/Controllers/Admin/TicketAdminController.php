<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Branch;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\TicketStatus;
use Illuminate\Support\Facades\Auth;

class TicketAdminController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = app('tenant_id');

        $canSeeAll = (bool) $user->is_superadmin;

        // Base query
        $query = Ticket::query()
            ->where('tenant_id', $tenantId)
            ->with(['branch', 'vehicleType', 'parkingSpace', 'user']);

        // 🔒 Scope por usuario si NO es superadmin
        if (!$canSeeAll) {
            $query->where('created_by', $user->id);
        }

        // 🔎 Filtros
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('user_id') && $canSeeAll) {
            $query->where('created_by', $request->user_id);
        }

        if ($request->filled('vehicle_type_id')) {
            $query->where('vehicle_type_id', $request->vehicle_type_id);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('entry_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('entry_time', '<=', $request->date_to);
        }

        $tickets = $query->orderByDesc('entry_time')->paginate(10)->withQueryString();

        // ✅ Catálogos para filtros (fuente de verdad)
        $branches = Branch::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        // Solo tiene sentido listar users si es superadmin
        $users = $canSeeAll
            ? User::where('tenant_id', $tenantId)->where('is_active', 1)->orderBy('name')->get()
            : collect();

        $vehicleTypes = VehicleType::orderBy('name')->get();

        $statuses = TicketStatus::orderBy('id')->get();

        return view('admin.tickets.index', compact(
            'tickets',
            'branches',
            'users',
            'vehicleTypes',
            'statuses',
            'canSeeAll'
        ));
    }

    public function show($token)
    {
        $tenantId = app('tenant_id');
        $user = Auth::user();

        $ticket = Ticket::where('tenant_id', $tenantId)
            ->where('token', $token)
            ->with(['branch','vehicleType','parkingSpace','status','user'])
            ->firstOrFail();

        if (!$user->is_superadmin && $ticket->created_by !== $user->id) {
            abort(403);
        }

        return view('admin.tickets.partials.detail', compact('ticket'));
    }

}
