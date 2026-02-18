<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TicketAuditApiController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = app('tenant_id');
        $user = $request->user();
        $permissions = $request->attributes->get('jwt_permissions', []);

        $canSeeAll = in_array('tickets.audit', $permissions)
            || in_array('audit.view', $permissions);

        $query = DB::table('tickets as t')
            ->join('branches as b', 't.branch_id', '=', 'b.id')
            ->join('users as u', 't.created_by', '=', 'u.id')
            ->join('ticket_statuses as ts', 't.status_id', '=', 'ts.id')
            ->leftJoin('vehicle_types as vt', 't.vehicle_type_id', '=', 'vt.id')
            ->where('t.tenant_id', $tenantId);

        /*
        |--------------------------------------------------------------------------
        | VISIBILIDAD POR ROL
        |--------------------------------------------------------------------------
        */

        if (!$canSeeAll) {
            $query->where('t.created_by', $user->id);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('branch_id')) {
            $query->where('t.branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('ts.code', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('t.entry_time', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('t.entry_time', '<=', $request->to);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('t.folio', 'like', "%$search%")
                  ->orWhere('t.token', 'like', "%$search%")
                  ->orWhere('t.plate', 'like', "%$search%");
            });
        }

        if ($canSeeAll && $request->filled('user_id')) {
            $query->where('t.created_by', $request->user_id);
        }

        /*
        |--------------------------------------------------------------------------
        | SELECT FINAL
        |--------------------------------------------------------------------------
        */

        $tickets = $query
            ->select(
                't.id',
                't.folio',
                't.token',
                't.plate',
                't.entry_time',
                't.exit_time',
                't.total_amount',
                'ts.code as status',
                'ts.name as status_name',
                'b.name as branch_name',
                'u.name as user_name',
                'vt.name as vehicle_type'
            )
            ->orderByDesc('t.entry_time')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $tickets
        ]);
    }

    public function show(Request $request, $token)
    {
        $tenantId = app('tenant_id');
        $user = $request->user();
        $permissions = $request->attributes->get('jwt_permissions', []);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado'
            ], 401);
        }

        $canSeeAll = in_array('tickets.audit', $permissions)
            || in_array('audit.view', $permissions);

        $ticket = Ticket::where('tenant_id', $tenantId)
            ->where('token', $token)
            ->with([
                'branch',
                'vehicleType',
                'parkingSpace',
                'status',
                'user'
            ])
            ->first();

        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket no encontrado'
            ], 404);
        }

        // 🔒 Restricción por permisos
        if (!$canSeeAll && $ticket->created_by !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'ticket' => [
                'folio'        => $ticket->folio,
                'plate'        => $ticket->plate,
                'branch'       => $ticket->branch?->name,
                'vehicle_type' => $ticket->vehicleType?->name,
                'parking_space'=> $ticket->parkingSpace?->code,
                'status'       => $ticket->status?->name,
                'status_code'  => $ticket->status?->code,
                'entry_time'   => optional($ticket->entry_time)->format('d/m/Y H:i'),
                'exit_time'    => optional($ticket->exit_time)->format('d/m/Y H:i'),
                'total_amount' => number_format($ticket->total_amount, 2),
                'created_by'   => $ticket->user?->name,
            ]
        ]);
    }

}
