<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\History;

class HistoryController extends Controller
{
    public function index()
    {
        $histories = History::latest()->get();

        return response()->json($histories);
    }

    public function destroy($id)
    {
        $history = History::find($id);

        if (!$history) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $history->delete();

        return response()->json([
            'message' => 'Riwayat berhasil dihapus'
        ]);
    }
}