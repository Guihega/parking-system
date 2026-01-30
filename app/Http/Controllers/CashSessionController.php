<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashSessionController extends Controller
{
    public function openForm()
    {
        // opcional: validar si ya tiene caja abierta y redirigir
        $hasOpen = DB::table('cash_sessions')
            ->where('user_id', auth()->id())
            ->where('is_open', 1)
            ->exists();

/*         if ($hasOpen) {
            return redirect()->route('dashboard'); // o donde operes tickets
        }
 */
        $branches = DB::table('branches')->get();

        return view('cash.open', compact('branches'));
    }

    public function open(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'opening_amount' => 'nullable|numeric|min:0'
        ]);

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("CALL sp_open_cash_session(?,?,?)");
            $stmt->execute([
                $request->branch_id,
                auth()->id(),
                $request->opening_amount ?? 0
            ]);

            return response()->json([
                'status' => 'success'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
