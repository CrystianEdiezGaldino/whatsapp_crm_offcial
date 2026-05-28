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

    public function attachToConversation(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $conversation = \App\Models\Conversation::findOrFail($conversationId);
        $conversation->tags()->sync($validated['tag_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Tags updated successfully',
            'tags' => $conversation->tags()->get(),
        ]);
    }

    public function detachFromConversation(Request $request, $conversationId, $tagId)
    {
        $conversation = \App\Models\Conversation::findOrFail($conversationId);
        $conversation->tags()->detach($tagId);

        return response()->json([
            'success' => true,
            'message' => 'Tag removed successfully',
        ]);
    }
}
