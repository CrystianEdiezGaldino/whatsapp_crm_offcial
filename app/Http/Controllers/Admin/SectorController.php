<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class SectorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('ensure_is_admin');
    }

    public function index()
    {
        $sectors = Sector::orderBy('keyboard_option')->paginate(15);
        return view('admin.sectors.index', compact('sectors'));
    }

    public function create()
    {
        $nextOption = Sector::max('keyboard_option') + 1 ?? 1;
        return view('admin.sectors.create', compact('nextOption'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sectors,name',
            'description' => 'nullable|string|max:500',
            'keyboard_option' => 'required|integer|unique:sectors,keyboard_option|min:0|max:9',
            'greeting_message' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        try {
            $sector = Sector::create($validated);

            Log::info('[Admin] Sector created', [
                'sector_id' => $sector->id,
                'name' => $sector->name,
                'keyboard_option' => $sector->keyboard_option,
                'created_by' => auth()->user()->email,
            ]);

            return redirect()
                ->route('admin.sectors.show', $sector)
                ->with('success', "Setor '{$sector->name}' criado com sucesso!");
        } catch (\Exception $e) {
            Log::error('[Admin] Error creating sector', ['error' => $e->getMessage()]);
            return back()->withInput()->withError('Erro ao criar setor');
        }
    }

    public function show(Sector $sector)
    {
        $sector->load('agents');
        return view('admin.sectors.show', compact('sector'));
    }

    public function edit(Sector $sector)
    {
        return view('admin.sectors.edit', compact('sector'));
    }

    public function update(Request $request, Sector $sector)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sectors,name,' . $sector->id,
            'description' => 'nullable|string|max:500',
            'keyboard_option' => 'required|integer|unique:sectors,keyboard_option,' . $sector->id . '|min:0|max:9',
            'greeting_message' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        try {
            $sector->update($validated);

            Log::info('[Admin] Sector updated', [
                'sector_id' => $sector->id,
                'updated_by' => auth()->user()->email,
            ]);

            return redirect()
                ->route('admin.sectors.show', $sector)
                ->with('success', "Setor '{$sector->name}' atualizado!");
        } catch (\Exception $e) {
            Log::error('[Admin] Error updating sector', ['error' => $e->getMessage()]);
            return back()->withInput()->withError('Erro ao atualizar setor');
        }
    }

    public function destroy(Sector $sector)
    {
        try {
            if ($sector->agents()->count() > 0) {
                return back()->withError('Não pode deletar setor com atendentes. Reassigne os atendentes primeiro.');
            }

            $sectorName = $sector->name;
            $sector->delete();

            Log::warning('[Admin] Sector deleted', [
                'sector_id' => $sector->id,
                'name' => $sectorName,
                'deleted_by' => auth()->user()->email,
            ]);

            return redirect()
                ->route('admin.sectors.index')
                ->with('success', "Setor '{$sectorName}' deletado!");
        } catch (\Exception $e) {
            Log::error('[Admin] Error deleting sector', ['error' => $e->getMessage()]);
            return back()->withError('Erro ao deletar setor');
        }
    }

    public function toggleActive(Sector $sector)
    {
        try {
            $sector->update(['is_active' => !$sector->is_active]);

            Log::info('[Admin] Sector toggled', [
                'sector_id' => $sector->id,
                'is_active' => $sector->is_active,
            ]);

            return response()->json([
                'success' => true,
                'is_active' => $sector->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao alterar status'], 500);
        }
    }
}
