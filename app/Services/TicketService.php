<?php
namespace App\Services;

use App\Models\Ticket;
use App\Models\ParkingSpace;
use App\Models\TicketStatus;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function createEntry(string $plate, int $parkingSpaceId): Ticket
    {
        return DB::transaction(function () use ($plate, $parkingSpaceId) {

            $space = ParkingSpace::with('status')
                ->lockForUpdate()
                ->findOrFail($parkingSpaceId);

            if ($space->status->code !== 'available') {
                throw new \Exception('El cajón no está disponible');
            }

            $ticket = Ticket::create([
                'folio' => Ticket::generateFolio(),
                'plate' => strtoupper($plate),
                'vehicle_type_id' => $space->vehicle_type_id,
                'branch_id' => $space->branch_id,
                'parking_space_id' => $space->id,
                'entry_time' => now(),
                'status_id' => TicketStatus::where('code','open')->value('id'),
            ]);

            $space->update([
                'status_id' => ParkingSpaceStatus::where('code','occupied')->value('id')
            ]);

            return $ticket;
        });
    }
}
