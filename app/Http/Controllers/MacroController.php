<?php

namespace App\Http\Controllers;

use App\Models\Macro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MacroController extends Controller
{
    public function index()
    {
        $macros = Macro::where('user_id', Auth::id())
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('macros.index', compact('macros'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'shortcut' => 'nullable|string|max:50|unique:macros,shortcut',
            'category' => 'required|string|max:100',
        ]);

        Macro::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('macros.index')->with('success', 'Macro criada com sucesso!');
    }

    public function update(Request $request, Macro $macro)
    {
        $this->authorizeMacro($macro);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'shortcut' => 'nullable|string|max:50|unique:macros,shortcut,' . $macro->id,
            'category' => 'required|string|max:100',
        ]);

        $macro->update($validated);

        return redirect()->route('macros.index')->with('success', 'Macro atualizada!');
    }

    public function destroy(Macro $macro)
    {
        $this->authorizeMacro($macro);
        $macro->delete();
        return redirect()->route('macros.index')->with('success', 'Macro removida!');
    }

    private function authorizeMacro(Macro $macro): void
    {
        if ($macro->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
