<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentCapacity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    public function index()
    {
        $agents = User::where('role', 'agent')
            ->with('agentCapacity')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        return view('admin.agents.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'max_conversations' => 'required|integer|min:1|max:100',
            ]);

            // Create user with agent role
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'agent',
                'status' => 'offline',
            ]);

            // Create capacity record
            AgentCapacity::create([
                'user_id' => $user->id,
                'max_conversations' => $validated['max_conversations'],
                'is_active' => true,
            ]);

            Log::info('[Agent] New agent registered', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

            return redirect()->route('admin.agents.index')
                ->with('success', "Atendente '{$user->name}' cadastrado com sucesso!");
        } catch (\Exception $e) {
            Log::error('[Agent] Error registering agent', ['error' => $e->getMessage()]);
            return redirect()->route('admin.agents.create')
                ->withInput()
                ->with('error', 'Erro ao cadastrar atendente: ' . $e->getMessage());
        }
    }

    public function edit(User $agent)
    {
        if (!$agent->isAgent()) {
            return redirect()->route('admin.agents.index')
                ->with('error', 'Usuário não é um atendente');
        }

        return view('admin.agents.edit', compact('agent'));
    }

    public function update(Request $request, User $agent)
    {
        if (!$agent->isAgent()) {
            return redirect()->route('admin.agents.index')
                ->with('error', 'Usuário não é um atendente');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $agent->id,
                'max_conversations' => 'required|integer|min:1|max:100',
                'status' => 'required|in:online,offline',
            ]);

            // Update user info
            $agent->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ]);

            // Update capacity
            $agent->agentCapacity->update([
                'max_conversations' => $validated['max_conversations'],
            ]);

            Log::info('[Agent] Agent updated', [
                'user_id' => $agent->id,
                'name' => $agent->name,
            ]);

            return redirect()->route('admin.agents.index')
                ->with('success', "Atendente '{$agent->name}' atualizado com sucesso!");
        } catch (\Exception $e) {
            Log::error('[Agent] Error updating agent', ['error' => $e->getMessage()]);
            return redirect()->route('admin.agents.edit', $agent->id)
                ->withInput()
                ->with('error', 'Erro ao atualizar atendente: ' . $e->getMessage());
        }
    }

    public function destroy(User $agent)
    {
        if (!$agent->isAgent()) {
            return redirect()->route('admin.agents.index')
                ->with('error', 'Usuário não é um atendente');
        }

        // Check if agent has active conversations
        $activeConversations = $agent->conversations()
            ->whereIn('status', ['new', 'in_attendance'])
            ->count();

        if ($activeConversations > 0) {
            return redirect()->route('admin.agents.index')
                ->with('error', "Não é possível deletar atendente com {$activeConversations} conversa(s) ativa(s)");
        }

        try {
            $agentName = $agent->name;

            // Delete capacity record
            $agent->agentCapacity?->delete();

            // Delete user
            $agent->delete();

            Log::info('[Agent] Agent deleted', [
                'name' => $agentName,
            ]);

            return redirect()->route('admin.agents.index')
                ->with('success', "Atendente '{$agentName}' deletado com sucesso!");
        } catch (\Exception $e) {
            Log::error('[Agent] Error deleting agent', ['error' => $e->getMessage()]);
            return redirect()->route('admin.agents.index')
                ->with('error', 'Erro ao deletar atendente: ' . $e->getMessage());
        }
    }
}
