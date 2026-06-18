<?php

/**
 * Teste rápido: notas do contato e etiquetas da conversa.
 * Uso: php tests/test-contact-notes-tags.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;

$errors = 0;

// 1) Contact notes field exists
$contact = Contact::query()->first();
if (!$contact) {
    echo "SKIP: nenhum contato no banco\n";
} else {
    $controller = new ContactController();
    $request = Request::create('/contacts/' . $contact->id . '/notes', 'PATCH', [
        'notes' => 'Teste nota ' . date('H:i:s'),
    ]);
    $response = $controller->updateNotes($request, $contact);
    $payload = $response->getData(true);

    if (!($payload['success'] ?? false) || !isset($payload['notes'])) {
        echo "FAIL: updateNotes não retornou success\n";
        $errors++;
    } else {
        $contact->refresh();
        if ($contact->notes !== $payload['notes']) {
            echo "FAIL: nota não persistiu no banco\n";
            $errors++;
        } else {
            echo "OK: notas salvas no contato #{$contact->id}\n";
        }
    }
}

// 2) Tags API
$tag = Tag::query()->where('is_active', true)->first();
$conversation = Conversation::query()->first();

if (!$tag || !$conversation) {
    echo "SKIP: tag ou conversa ausente\n";
} else {
    $tagController = new TagController();
    $attachRequest = Request::create(
        '/conversations/' . $conversation->id . '/tags',
        'POST',
        ['tag_ids' => [(int) $tag->id]]
    );
    $attachResponse = $tagController->attachToConversation($attachRequest, $conversation->id);
    $attachPayload = $attachResponse->getData(true);

    if (!($attachPayload['success'] ?? false) || empty($attachPayload['tags'])) {
        echo "FAIL: attachToConversation\n";
        $errors++;
    } else {
        $ids = collect($attachPayload['tags'])->pluck('id')->map(fn ($id) => (int) $id);
        if (!$ids->contains((int) $tag->id)) {
            echo "FAIL: tag não vinculada\n";
            $errors++;
        } else {
            echo "OK: etiqueta vinculada na conversa #{$conversation->id}\n";
        }
    }

    $jsonResponse = $tagController->conversationTags($conversation->id);
    $jsonPayload = $jsonResponse->getData(true);
    if (!($jsonPayload['success'] ?? false) || !is_array($jsonPayload['tag_ids'] ?? null)) {
        echo "FAIL: conversationTags JSON\n";
        $errors++;
    } else {
        echo "OK: conversationTags retorna " . count($jsonPayload['tag_ids']) . " id(s)\n";
    }
}

exit($errors > 0 ? 1 : 0);
