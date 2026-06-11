<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contoh penanganan race condition (Must Have #4).
 *
 * Dua strategi yang bisa dipakai:
 *   1. Pessimistic Locking  → lockForUpdate()
 *   2. Optimistic Locking   → version column check
 */
class ExampleController extends Controller
{
    /**
     * Pessimistic Locking
     * Cocok untuk operasi yang conflict-nya sering terjadi.
     * Row di-lock sampai transaction selesai.
     */
    public function updateWithPessimisticLock(Request $request, int $id)
    {
        return DB::transaction(function () use ($request, $id) {
            // Lock row — user lain yang coba update akan WAIT
            $record = DB::table('items')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$record) {
                return response()->json(['message' => 'Not found'], 404);
            }

            DB::table('items')
                ->where('id', $id)
                ->update([
                    'name'       => $request->name,
                    'updated_at' => now(),
                ]);

            return response()->json(['message' => 'Updated successfully']);
        });
    }

    /**
     * Optimistic Locking
     * Cocok untuk operasi yang conflict-nya jarang terjadi.
     * Tidak ada lock — cek versi saat update.
     */
    public function updateWithOptimisticLock(Request $request, int $id)
    {
        $request->validate([
            'name'    => 'required|string',
            'version' => 'required|integer',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $affected = DB::table('items')
                ->where('id', $id)
                ->where('version', $request->version) // Versi harus cocok
                ->update([
                    'name'       => $request->name,
                    'version'    => $request->version + 1, // Increment versi
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                // Versi tidak cocok = ada user lain yang sudah update duluan
                return response()->json([
                    'message' => 'Conflict: data was modified by another user. Please refresh and try again.',
                    'code'    => 'OPTIMISTIC_LOCK_CONFLICT',
                ], 409);
            }

            return response()->json(['message' => 'Updated successfully']);
        });
    }
}
