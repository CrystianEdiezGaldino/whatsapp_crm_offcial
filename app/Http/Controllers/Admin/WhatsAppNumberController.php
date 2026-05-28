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
}
