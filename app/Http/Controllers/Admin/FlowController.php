<?php

namespace App\Http\Controllers\Admin;

use App\Models\ConversationFlow;
use App\Models\Sector;
use App\Services\FlowManagementService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FlowController extends Controller
{
    public function __construct(private FlowManagementService $service)
    {}

    public function index()
    {
        $flows = ConversationFlow::with('nodes', 'createdBy')
            ->orderBy('type', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.flows.index', compact('flows'));
    }

    public function create()
    {
        $sectors = Sector::all();
        return view('admin.flows.create', compact('sectors'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:primary,secondary',
                'trigger_type' => 'required|in:on_new_conversation,on_command,manual',
                'config' => 'required|array',
                'config.initial_message' => 'required|string',
                'config.final_message' => 'required|string',
                'nodes' => 'array'
            ]);

            $flow = $this->service->createFlow($data);

            return redirect()->route('admin.flows.index')->with('success', 'Fluxo criado com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Flow creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar fluxo: ' . $e->getMessage());
        }
    }

    public function edit(ConversationFlow $flow)
    {
        $sectors = Sector::all();
        return view('admin.flows.edit', compact('flow', 'sectors'));
    }

    public function update(ConversationFlow $flow, Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:primary,secondary',
                'trigger_type' => 'required|in:on_new_conversation,on_command,manual',
                'config' => 'required|array',
                'config.initial_message' => 'required|string',
                'config.final_message' => 'required|string',
                'nodes' => 'array'
            ]);

            $this->service->updateFlow($flow, $data);

            return redirect()->route('admin.flows.index')->with('success', 'Fluxo atualizado com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Flow update failed', [
                'error' => $e->getMessage(),
                'flow_id' => $flow->id,
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar fluxo: ' . $e->getMessage());
        }
    }

    public function toggle(ConversationFlow $flow)
    {
        try {
            $this->service->toggleFlow($flow);

            $status = $flow->fresh()->is_active ? 'ativado' : 'desativado';
            return back()->with('success', "Fluxo $status com sucesso!");
        } catch (\Exception $e) {
            \Log::error('Flow toggle failed', [
                'error' => $e->getMessage(),
                'flow_id' => $flow->id
            ]);

            return back()->with('error', 'Erro ao ativar/desativar fluxo: ' . $e->getMessage());
        }
    }

    public function executions(ConversationFlow $flow)
    {
        $executions = $flow->executions()
            ->with('conversation.contact', 'resultSector')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.flows.executions', compact('flow', 'executions'));
    }

    public function destroy(ConversationFlow $flow)
    {
        $this->service->deleteFlow($flow);

        return redirect()->route('admin.flows.index')->with('success', 'Fluxo deletado com sucesso!');
    }
}
