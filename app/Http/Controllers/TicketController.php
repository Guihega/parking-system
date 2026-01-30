<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpace;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function create()
    {
        return view('tickets.create', [
            'spaces' => ParkingSpace::where('status', 'available')->get(),
            'vehicleTypes' => VehicleType::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plate' => 'required|string|max:10',
            'vehicle_type_id' => 'required',
            'parking_space_id' => 'required',
        ]);

        // Aquí guardas el ticket (según tu modelo)
        // Ticket::create([...]);

        // Marcar cajón como ocupado
        ParkingSpace::where('id', $request->parking_space_id)
            ->update(['status' => 'occupied']);

        return redirect()->back()->with('success', true);
    }

    public function apiEntry(Request $request)
    {
        $request->validate([
            'plate' => 'required|string|max:10',
            'vehicle_type_id' => 'required',
            'parking_space_id' => 'required',
        ]);

        // Aquí después llamaremos al SP (en el paso 2)

        ParkingSpace::where('id', $request->parking_space_id)
            ->update(['status' => 'occupied']);

        return response()->json([
            'status' => 'success',
            'entry_time' => now()
        ]);
    }

}
