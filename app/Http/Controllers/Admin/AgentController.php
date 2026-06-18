<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('ensure_is_admin');
    }

    public function index()
    {
        $agents = User::whereIn('role', ['agent', 'supervisor', 'admin'])
            ->with('sector')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        $sectors = Sector::active()->byOption()->get();
        $roles = [
            'agent' => 'Atendente',
            'supervisor' => 'Supervisor',
            'admin' => 'Administrador',
        ];

        return view('admin.agents.create', compact('sectors', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:agent,supervisor,admin',
            'sector_id' => 'required_if:role,agent,supervisor|exists:sectors,id|nullable',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'sector_id' => $validated['sector_id'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'notes' => $validated['notes'] ?? null,
                'status' => 'offline',
            ]);

            if (in_array($validated['role'], ['agent', 'supervisor'])) {
                $user->agentCapacity()->create([
                    'max_conversations' => 10,
                    'is_active' => true,
                ]);
            }

            Log::info('[Admin] Agent created', [
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'sector_id' => $user->sector_id,
                'created_by' => auth()->user()->email,
            ]);

            return redirect()
                ->route('admin.agents.show', $user)
                ->with('success', "Atendente '{$user->name}' cadastrado com sucesso!");
        } catch (\Exception $e) {
            Log::error('[Admin] Error creating agent', ['error' => $e->getMessage()]);
            return back()->withInput()->withError('Erro ao cadastrar atendente');
        }
    }

    public function show(User $user)
    {
        $user->load('sector', 'agentCapacity', 'conversations');
        return view('admin.agents.show', compact('user'));
    }

    public function edit(User $user)
    {
        $user->load('sector', 'agentCapacity');
        $sectors = Sector::active()->byOption()->get();
        $roles = [
            'agent' => 'Atendente',
            'supervisor' => 'Supervisor',
            'admin' => 'Administrador',
        ];

        return view('admin.agents.edit', compact('user', 'sectors', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:agent,supervisor,admin',
            'sector_id' => 'required_if:role,agent,supervisor|exists:sectors,id|nullable',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'sector_id' => $validated['sector_id'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'notes' => $validated['notes'] ?? null,
            ]);

            if (in_array($validated['role'], ['agent', 'supervisor']) && !$user->agentCapacity) {
                $user->agentCapacity()->create([
                    'max_conversations' => 10,
                    'is_active' => true,
                ]);
            }

            Log::info('[Admin] Agent updated', [
                'user_id' => $user->id,
                'updated_by' => auth()->user()->email,
            ]);

            return redirect()
                ->route('admin.agents.show', $user)
                ->with('success', "Atendente '{$user->name}' atualizado!");
        } catch (\Exception $e) {
            Log::error('[Admin] Error updating agent', ['error' => $e->getMessage()]);
            return back()->withInput()->withError('Erro ao atualizar atendente');
        }
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user->update(['password' => Hash::make($validated['password'])]);

            Log::warning('[Admin] Password reset', [
                'user_id' => $user->id,
                'reset_by' => auth()->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Senha redefinida com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao redefinir senha'], 500);
        }
    }

    public function toggleActive(User $user)
    {
        try {
            $user->update(['is_active' => !$user->is_active]);

            Log::info('[Admin] Agent toggled', [
                'user_id' => $user->id,
                'is_active' => $user->is_active,
            ]);

            return response()->json([
                'success' => true,
                'is_active' => $user->is_active,
                'message' => $user->is_active ? 'Ativado' : 'Desativado',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao alterar status'], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            if ($user->isAdmin()) {
                return back()->withError('Não pode deletar administrador');
            }

            $userName = $user->name;
            Log::warning('[Admin] Agent deleted', [
                'user_id' => $user->id,
                'name' => $userName,
            ]);

            $user->delete();

            return redirect()
                ->route('admin.agents.index')
                ->with('success', "Atendente '{$userName}' deletado!");
        } catch (\Exception $e) {
            Log::error('[Admin] Error deleting agent', ['error' => $e->getMessage()]);
            return back()->withError('Erro ao deletar atendente');
        }
    }

    public function export()
    {
        $agents = User::whereIn('role', ['agent', 'supervisor', 'admin'])
            ->with('sector')
            ->orderBy('name')
            ->get();

        $fileName = 'agents_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->stream(function () use ($agents) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Nome', 'Email', 'Cargo', 'Setor', 'Status', 'Ativo', 'Criado']);

            foreach ($agents as $agent) {
                fputcsv($handle, [
                    $agent->name,
                    $agent->email,
                    $agent->getRoleLabel(),
                    $agent->getSectorName(),
                    $agent->status,
                    $agent->is_active ? 'Sim' : 'Não',
                    $agent->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}
