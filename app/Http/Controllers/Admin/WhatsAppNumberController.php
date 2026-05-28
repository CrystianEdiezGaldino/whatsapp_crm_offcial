<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppNumber;
use Illuminate\Http\Request;

class WhatsAppNumberController extends Controller
{
    public function index()
    {
        $numbers = WhatsAppNumber::orderBy('created_at', 'desc')->get();
        $activeNumber = WhatsAppNumber::active();

        return view('admin.whatsapp.numbers.index', compact('numbers', 'activeNumber'));
    }

    public function create()
    {
        return view('admin.whatsapp.numbers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|unique:whatsapp_numbers',
            'display_name' => 'required|string',
            'access_token' => 'required|string',
            'business_account_id' => 'nullable|string',
            'phone_number_id' => 'nullable|string',
        ]);

        $number = WhatsAppNumber::create($validated);

        return redirect()->route('admin.whatsapp.numbers.index')
            ->with('success', 'Número WhatsApp adicionado com sucesso!');
    }

    public function setActive(Request $request, WhatsAppNumber $number)
    {
        $number->setActive();

        return response()->json([
            'success' => true,
            'message' => 'Número ' . $number->phone_number . ' definido como ativo!',
        ]);
    }

    public function verify(Request $request, WhatsAppNumber $number)
    {
        // Aqui você pode adicionar lógica para verificar o número com a API do WhatsApp
        $number->update([
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Número verificado com sucesso!',
        ]);
    }

    public function destroy(WhatsAppNumber $number)
    {
        if ($number->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível deletar o número ativo. Defina outro número como ativo primeiro.',
            ], 422);
        }

        $number->delete();

        return response()->json([
            'success' => true,
            'message' => 'Número removido com sucesso!',
        ]);
    }

    public function syncFromMeta(Request $request)
    {
        $validated = $request->validate([
            'business_account_id' => 'required|string',
            'access_token' => 'required|string',
        ]);

        try {
            $whatsapp = new \App\Services\WhatsAppService();

            // Usar token fornecido para fazer a chamada
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://graph.facebook.com/v23.0/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $validated['access_token'],
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
                'verify' => storage_path('cacert.pem'),
            ]);

            $response = $client->get($validated['business_account_id'] . '/phone_numbers');
            $data = json_decode((string)$response->getBody(), true);
            $numbers = $data['data'] ?? [];

            if (empty($numbers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum número encontrado na conta Meta.',
                ], 422);
            }

            $imported = 0;
            foreach ($numbers as $number) {
                $phoneNumber = $number['phone_number'] ?? null;
                $displayName = $number['display_name'] ?? $phoneNumber;
                $phoneNumberId = $number['id'] ?? null;

                if ($phoneNumber && !WhatsAppNumber::where('phone_number', $phoneNumber)->exists()) {
                    WhatsAppNumber::create([
                        'phone_number' => $phoneNumber,
                        'display_name' => $displayName,
                        'phone_number_id' => $phoneNumberId,
                        'business_account_id' => $validated['business_account_id'],
                        'access_token' => $validated['access_token'],
                    ]);
                    $imported++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Importados {$imported} número(s) da Meta com sucesso!",
                'imported_count' => $imported,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar números: ' . $e->getMessage(),
            ], 422);
        }
    }
}
