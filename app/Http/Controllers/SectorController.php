<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Sector;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    public function index()
    {
        $sectors = Sector::query()
            ->active()
            ->orderBy('order')
            ->orderBy('keyboard_option')
            ->orderBy('name')
            ->get()
            ->map(fn (Sector $sector) => $sector->toUiArray());

        return response()->json([
            'success' => true,
            'sectors' => $sectors,
        ]);
    }

    public function conversationSector(Conversation $conversation)
    {
        $conversation->loadMissing('sector');

        return response()->json([
            'success' => true,
            'sector_id' => $conversation->sector_id,
            'sector' => $conversation->sector?->toUiArray() ?? Sector::defaultUi(),
        ]);
    }

    public function updateConversationSector(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'sector_id' => 'nullable|integer|exists:sectors,id',
        ]);

        $sectorId = $validated['sector_id'] ?? null;
        if ($sectorId) {
            $sector = Sector::query()->active()->find($sectorId);
            if (!$sector) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setor indisponível.',
                ], 422);
            }
        }

        $conversation->update(['sector_id' => $sectorId]);
        $conversation->load('sector');

        return response()->json([
            'success' => true,
            'message' => 'Setor atualizado.',
            'sector_id' => $conversation->sector_id,
            'sector' => $conversation->sector?->toUiArray() ?? Sector::defaultUi(),
        ]);
    }
}
