<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController
{
    public function index()
    {
        $tags = Tag::query()
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {
        $categories = ['priority', 'status', 'outcome', 'custom'];

        return view('admin.tags.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'category' => 'required|in:priority,status,outcome,custom',
            'is_active' => 'boolean',
        ]);

        Tag::create($validated);

        return redirect()->route('admin.tags.index')->with('success', 'Tag criada com sucesso');
    }

    public function edit(Tag $tag)
    {
        $categories = ['priority', 'status', 'outcome', 'custom'];

        return view('admin.tags.edit', compact('tag', 'categories'));
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $tag->id,
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'category' => 'required|in:priority,status,outcome,custom',
            'is_active' => 'boolean',
        ]);

        $tag->update($validated);

        return redirect()->route('admin.tags.index')->with('success', 'Tag atualizada com sucesso');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'Tag deletada com sucesso');
    }

    public function toggleActive(Tag $tag)
    {
        $tag->update(['is_active' => !$tag->is_active]);

        return response()->json(['is_active' => $tag->is_active]);
    }
}
