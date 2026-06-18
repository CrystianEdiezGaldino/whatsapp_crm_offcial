<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with(['assignedUser']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($tag = $request->input('tag')) {
            $query->whereJsonContains('tags', $tag);
        }

        if ($agentId = $request->input('agent')) {
            $query->where('assigned_to', $agentId);
        }

        $contacts = $query->orderBy('last_message_at', 'desc')->paginate(25);
        $agents = User::all();

        return view('contacts.index', compact('contacts', 'agents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:contacts,phone',
            'email' => 'nullable|email',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['tags'] = $validated['tags'] ? explode(',', $validated['tags']) : null;
        $validated['assigned_to'] = Auth::id();
        $validated['phone'] = PhoneNormalizer::forApi($validated['phone']);

        if (Contact::whereIn('phone', PhoneNormalizer::variants($validated['phone']))->exists()) {
            return back()->withInput()->withErrors(['phone' => 'Já existe contato com este telefone.']);
        }

        Contact::create($validated);

        return redirect()->route('contacts.index')->with('success', 'Contato criado com sucesso!');
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:contacts,phone,' . $contact->id,
            'email' => 'nullable|email',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['tags'] = $validated['tags'] ? explode(',', $validated['tags']) : null;
        $validated['phone'] = PhoneNormalizer::forApi($validated['phone']);

        if (Contact::whereIn('phone', PhoneNormalizer::variants($validated['phone']))
            ->where('id', '!=', $contact->id)
            ->exists()) {
            return back()->withInput()->withErrors(['phone' => 'Já existe contato com este telefone.']);
        }

        $contact->update($validated);

        return redirect()->route('contacts.index')->with('success', 'Contato atualizado!');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('success', 'Contato removido!');
    }

    public function updateNotes(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:5000',
        ]);

        $contact->update(['notes' => $validated['notes'] ?? null]);

        return response()->json([
            'success' => true,
            'notes' => $contact->notes,
        ]);
    }
}
