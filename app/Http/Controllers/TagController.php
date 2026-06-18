<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        $query = Tag::query();

        if ($category) {
            $query->where('category', $category);
        }

        $tags = $query->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'tags' => $tags,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:tags',
            'color' => 'required|string',
            'category' => 'required|in:priority,status,outcome,custom',
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'success' => true,
            'tag' => $tag,
        ]);
    }

    public function conversationTags($conversationId)
    {
        $conversation = \App\Models\Conversation::with('tags')->findOrFail($conversationId);

        return response()->json([
            'success' => true,
            'tag_ids' => $conversation->tags->pluck('id')->map(fn ($id) => (int) $id)->values(),
            'tags' => $conversation->tags,
        ]);
    }

    public function attachToConversation(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'tag_ids' => 'present|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ]);

        $conversation = \App\Models\Conversation::findOrFail($conversationId);
        $conversation->tags()->sync($validated['tag_ids']);
        $conversation->load('tags');

        return response()->json([
            'success' => true,
            'message' => 'Etiquetas atualizadas.',
            'tags' => $conversation->tags,
        ]);
    }

    public function detachFromConversation(Request $request, $conversationId, $tagId)
    {
        $conversation = \App\Models\Conversation::findOrFail($conversationId);
        $conversation->tags()->detach($tagId);
        $conversation->load('tags');

        return response()->json([
            'success' => true,
            'message' => 'Etiqueta removida.',
            'tags' => $conversation->tags,
        ]);
    }
}
