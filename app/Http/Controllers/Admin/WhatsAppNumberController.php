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
            'access_token' => 'required|string',
            'business_account_id' => 'nullable|string',
        ]);

        // Se o token for "system", usar o token do .env
        $token = $validated['access_token'];
        if ($token === 'system') {
            $token = config('services.whatsapp.access_token') ?? env('WA_ACCESS_TOKEN');
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum token de acesso configurado no .env. Configure WA_ACCESS_TOKEN.',
                ], 422);
            }
        }

        try {
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://graph.facebook.com/v25.0/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
                'verify' => storage_path('cacert.pem'),
            ]);

            $businesses = [];

            // Se WABA ID foi fornecido, usar diretamente
            if (!empty($validated['business_account_id'])) {
                $businesses[] = ['id' => $validated['business_account_id']];
            } else {
                // Tentar obter automaticamente
                try {
                    $meResponse = $client->get('me?fields=id,name');
                    $meData = json_decode((string)$meResponse->getBody(), true);
                    $userId = $meData['id'] ?? null;

                    if ($userId) {
                        try {
                            $businessResponse = $client->get($userId . '/businesses?fields=id,name');
                            $businessData = json_decode((string)$businessResponse->getBody(), true);
                            $businesses = $businessData['data'] ?? [];
                        } catch (\Exception $e) {
                            // Se falhar, tentar com WABA_ID do .env
                            $wabaId = env('WA_WABA_ID');
                            if ($wabaId) {
                                $businesses[] = ['id' => $wabaId];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $wabaId = env('WA_WABA_ID');
                    if ($wabaId) {
                        $businesses[] = ['id' => $wabaId];
                    }
                }
            }

            if (empty($businesses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma Business Account encontrada. Forneça o WABA_ID ou configure WA_WABA_ID no .env.',
                ], 422);
            }

            $imported = 0;
            $errors = [];

            // Buscar números de todas as Business Accounts
            foreach ($businesses as $business) {
                $businessAccountId = $business['id'] ?? null;
                if (!$businessAccountId) continue;

                try {
                    $response = $client->get($businessAccountId . '/phone_numbers');
                    $data = json_decode((string)$response->getBody(), true);
                    $numbers = $data['data'] ?? [];

                    if (empty($numbers)) {
                        $errors[] = "Nenhum número encontrado em $businessAccountId";
                        continue;
                    }

                    foreach ($numbers as $number) {
                        $phoneNumber = $number['phone_number'] ?? null;
                        $displayName = $number['display_name'] ?? $phoneNumber;
                        $phoneNumberId = $number['id'] ?? null;

                        if ($phoneNumber && !WhatsAppNumber::where('phone_number', $phoneNumber)->exists()) {
                            WhatsAppNumber::create([
                                'phone_number' => $phoneNumber,
                                'display_name' => $displayName,
                                'phone_number_id' => $phoneNumberId,
                                'business_account_id' => $businessAccountId,
                                'access_token' => $token,
                            ]);
                            $imported++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Erro ao buscar números de $businessAccountId: " . $e->getMessage();
                }
            }

            if ($imported === 0) {
                $message = count($errors) > 0 ? implode('; ', $errors) : 'Nenhum número encontrado ou já importado.';
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            $successMsg = "Importados {$imported} número(s) da Meta com sucesso!";
            if (count($errors) > 0) {
                $successMsg .= " Avisos: " . implode('; ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $successMsg,
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
