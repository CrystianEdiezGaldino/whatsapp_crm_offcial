<?php

namespace App\Helpers;

use App\Models\Message;

class ChatInboxHelper
{
    /** Chave única para o front não renderizar a mesma mensagem 2x (AJAX + poll). */
    public static function dedupeKey(Message $message): string
    {
        if ($message->wa_message_id) {
            return 'wa:' . $message->wa_message_id;
        }

        return 'id:' . $message->id;
    }

    /** Payload JSON padronizado para envio AJAX e poll. */
    public static function toClientArray(Message $message): array
    {
        return [
            'id' => $message->id,
            'wa_message_id' => $message->wa_message_id,
            'dedupe_key' => self::dedupeKey($message),
            'direction' => $message->direction,
            'type' => $message->type,
            'content' => $message->content,
            'media_url' => $message->media_url,
            'media_filename' => $message->media_filename,
            'mime_type' => $message->mime_type,
            'status' => $message->status,
            'created_at' => $message->created_at?->toIso8601String(),
            'sender' => $message->relationLoaded('sender') && $message->sender
                ? ['id' => $message->sender->id, 'name' => $message->sender->name]
                : null,
        ];
    }

    /** @param iterable<Message> $messages */
    public static function mapMessagesForClient(iterable $messages): array
    {
        $out = [];
        foreach ($messages as $message) {
            $out[] = self::toClientArray($message);
        }

        return $out;
    }
}
