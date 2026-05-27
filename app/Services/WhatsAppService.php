<?php

namespace App\Services;

use App\Events\MessageReceived;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Support\PhoneNormalizer;
use App\Support\WhatsAppApiError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppService
{
    private Client $client;
    private string $phoneNumberId;
    private string $accessToken;
    private string $apiVersion;
    private string $baseUrl;

    private ?array $lastError = null;

    public function __construct()
    {
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        // Usar token do banco de dados se disponível, caso contrário usar .env
        $this->accessToken = $this->getValidAccessToken();
        $this->apiVersion = config('services.whatsapp.api_version', 'v23.0');
        $this->baseUrl = config('services.whatsapp.base_url', 'https://graph.facebook.com');

        $this->client = new Client([
            'base_uri' => "{$this->baseUrl}/{$this->apiVersion}/",
            'headers' => [
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
            'verify' => storage_path('cacert.pem'),
        ]);
    }

    private function getValidAccessToken(): string
    {
        try {
            $token = \App\Models\WhatsAppToken::where('token_type', 'access')->first();
            if ($token && !$token->isExpired()) {
                return $token->token_value;
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao buscar token do BD, usando .env', ['error' => $e->getMessage()]);
        }
        return config('services.whatsapp.access_token');
    }

    public function getLastError(): ?array
    {
        return $this->lastError;
    }

    public function getUserFacingError(): string
    {
        return WhatsAppApiError::userMessage($this->lastError);
    }

    private function parseApiError(\Throwable $e): array
    {
        if ($e instanceof ClientException && $e->hasResponse()) {
            $body = json_decode((string) $e->getResponse()->getBody(), true);
            if (!empty($body['error'])) {
                return $body['error'];
            }
        }

        return ['message' => $e->getMessage(), 'code' => 0];
    }

    /** Tenta variantes BR (com/sem 9) quando a Meta rejeita o formato. */
    private function postToRecipients(string $to, array $messageBody): ?array
    {
        $this->lastError = null;

        foreach (PhoneNormalizer::variants($to) as $recipient) {
            try {
                $response = $this->client->post("{$this->phoneNumberId}/messages", [
                    'json' => array_merge([
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $recipient,
                    ], $messageBody),
                ]);

                return json_decode($response->getBody()->getContents(), true);
            } catch (ClientException $e) {
                $this->lastError = $this->parseApiError($e);
                Log::error('WhatsApp API error', ['to' => $recipient, 'error' => $this->lastError]);

                if (in_array($this->lastError['code'] ?? 0, [131030, 100], true)) {
                    continue;
                }

                return null;
            } catch (\Exception $e) {
                $this->lastError = $this->parseApiError($e);
                Log::error('WhatsApp send failed', ['to' => $recipient, 'error' => $e->getMessage()]);
                return null;
            }
        }

        return null;
    }

    public function sendText(string $to, string $text): ?array
    {
        // Validar número de telefone
        $normalizedPhone = PhoneValidationService::normalize($to);
        if (!$normalizedPhone) {
            $this->lastError = ['message' => 'Invalid phone number format', 'code' => 400];
            Log::error('[WhatsApp] Invalid phone number rejected', ['phone' => $to]);
            return null;
        }

        return $this->postToRecipients($normalizedPhone, [
            'type' => 'text',
            'text' => ['body' => $text, 'preview_url' => false],
        ]);
    }

    public function sendMedia(string $to, string $type, string $mediaId, ?string $caption = null): ?array
    {
        // Validar número de telefone
        $normalizedPhone = PhoneValidationService::normalize($to);
        if (!$normalizedPhone) {
            $this->lastError = ['message' => 'Invalid phone number format', 'code' => 400];
            Log::error('[WhatsApp] Invalid phone number rejected', ['phone' => $to]);
            return null;
        }

        if ($type === 'audio') {
            return $this->sendAudio($normalizedPhone, $mediaId, false);
        }

        $mediaPayload = ['id' => $mediaId];

        if ($caption && in_array($type, ['image', 'document', 'video'], true)) {
            $mediaPayload['caption'] = $caption;
        }

        if ($type === 'document') {
            $mediaPayload['filename'] = $caption ?? 'document';
        }

        return $this->postToRecipients($normalizedPhone, [
            'type' => $type,
            $type => $mediaPayload,
        ]);
    }

    /** Envio de áudio via media_id (upload prévio na Meta). */
    public function sendAudio(string $to, string $mediaId, bool $asVoice = false): ?array
    {
        // Validar número de telefone
        $normalizedPhone = PhoneValidationService::normalize($to);
        if (!$normalizedPhone) {
            $this->lastError = ['message' => 'Invalid phone number format', 'code' => 400];
            Log::error('[WhatsApp] Invalid phone number rejected', ['phone' => $to]);
            return null;
        }

        $audio = ['id' => $mediaId];
        if ($asVoice) {
            $audio['voice'] = true;
        }

        return $this->postToRecipients($normalizedPhone, [
            'type' => 'audio',
            'audio' => $audio,
        ]);
    }

    public function uploadMedia(string $filePath, string $mimeType, ?string $filename = null): ?string
    {
        $filename = $filename ?: basename($filePath);
        $this->lastError = null;

        try {
            $client = new Client([
                'base_uri' => "{$this->baseUrl}/{$this->apiVersion}/",
                'timeout' => 120,
                'verify' => storage_path('cacert.pem'),
            ]);

            $response = $client->post("{$this->phoneNumberId}/media", [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => $filename,
                        'headers' => ['Content-Type' => $mimeType],
                    ],
                    [
                        'name' => 'type',
                        'contents' => $mimeType,
                    ],
                    [
                        'name' => 'messaging_product',
                        'contents' => 'whatsapp',
                    ],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['id'] ?? null;
        } catch (ClientException $e) {
            $this->lastError = $this->parseApiError($e);
            Log::error('WhatsApp media upload failed', ['file' => $filename, 'mime' => $mimeType, 'error' => $this->lastError]);
        } catch (\Exception $e) {
            $this->lastError = $this->parseApiError($e);
            Log::error('WhatsApp media upload failed', ['file' => $filename, 'error' => $e->getMessage()]);
        }

        return null;
    }

    public function downloadMedia(string $mediaId): ?string
    {
        try {
            $response = $this->client->get("{$mediaId}");
            $data = json_decode($response->getBody()->getContents(), true);
            $url = $data['url'] ?? null;

            if (!$url) return null;

            $mediaResponse = $this->client->get($url);
            $filename = 'media/' . $mediaId . '_' . time();
            Storage::put("public/{$filename}", $mediaResponse->getBody()->getContents());

            return $filename;
        } catch (\Exception $e) {
            Log::error('WhatsApp media download failed', ['media_id' => $mediaId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function getContactProfilePhoto(string $phone): ?string
    {
        try {
            $normalizedPhone = PhoneNormalizer::forApi($phone);
            $response = $this->client->get("{$normalizedPhone}/profile_photo", [
                'query' => ['format' => 'url'],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $photoUrl = $data['profile_photo_url'] ?? null;

            if (!$photoUrl) {
                Log::info('WhatsApp no profile photo found', ['phone' => $phone]);
                return null;
            }

            Log::info('WhatsApp profile photo fetched', ['phone' => $phone, 'url' => $photoUrl]);
            return $photoUrl;
        } catch (ClientException $e) {
            $error = $this->parseApiError($e);
            Log::warning('WhatsApp profile photo not available', ['phone' => $phone, 'error' => $error]);
            return null;
        } catch (\Exception $e) {
            Log::error('WhatsApp profile photo fetch failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function sendTemplate(string $to, string $templateName, string $language = 'pt_BR', array $components = []): ?array
    {
        // Validar número de telefone
        $normalizedPhone = PhoneValidationService::normalize($to);
        if (!$normalizedPhone) {
            $this->lastError = ['message' => 'Invalid phone number format', 'code' => 400];
            Log::error('[WhatsApp] Invalid phone number rejected', ['phone' => $to]);
            return null;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => PhoneNormalizer::forApi($normalizedPhone),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        try {
            $response = $this->client->post("{$this->phoneNumberId}/messages", [
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->lastError = $this->parseApiError($e);
            Log::error('WhatsApp send template failed', ['to' => $to, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function markAsRead(string $messageId): bool
    {
        try {
            $this->client->post("{$this->phoneNumberId}/messages", [
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $messageId,
                ],
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp mark as read failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send contact information (vCard)
     */
    public function sendContact(string $to, array $contact): ?array
    {
        // Validar número de telefone
        $normalizedPhone = PhoneValidationService::normalize($to);
        if (!$normalizedPhone) {
            $this->lastError = ['message' => 'Invalid phone number format', 'code' => 400];
            Log::error('[WhatsApp] Invalid phone number rejected', ['phone' => $to]);
            return null;
        }

        try {
            $response = $this->client->post("{$this->phoneNumberId}/messages", [
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $normalizedPhone,
                    'type' => 'contacts',
                    'contacts' => [$contact],
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->lastError = $this->parseApiError($e);
            Log::error('WhatsApp send contact failed', ['to' => $to, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send emoji reaction to a message
     * Only can react to messages you received from the user
     */
    public function sendReaction(string $messageId, string $emoji): ?array
    {
        try {
            // Validate emoji is single character
            if (strlen($emoji) > 4) {  // emojis can be up to 4 bytes UTF-8
                Log::warning('[WhatsApp] Emoji reaction invalid', ['emoji' => $emoji]);
                return null;
            }

            $response = $this->client->post("{$this->phoneNumberId}/messages", [
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'type' => 'reaction',
                    'message_id' => $messageId,
                    'emoji' => $emoji,
                ],
            ]);

            Log::info('[WhatsApp] Reaction sent', [
                'message_id' => $messageId,
                'emoji' => $emoji,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->lastError = $this->parseApiError($e);
            Log::error('WhatsApp send reaction failed', ['message_id' => $messageId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send OTP via authentication template
     * CRITICAL: Must use authentication template, not freeform message
     * Using this properly prevents account suspension
     */
    public function sendOTP(string $to, string $code, int $expirationMinutes = 10): ?array
    {
        // Validar número de telefone
        $normalizedPhone = PhoneValidationService::normalize($to);
        if (!$normalizedPhone) {
            $this->lastError = ['message' => 'Invalid phone number format', 'code' => 400];
            Log::error('[WhatsApp] Invalid phone number rejected', ['phone' => $to]);
            return null;
        }

        // Build OTP template
        $otpBuilder = WhatsAppOTPBuilder::create()
            ->code($code)
            ->expiresIn($expirationMinutes)
            ->oneTabAutofill();

        $template = $otpBuilder->build();

        if (!$template) {
            $this->lastError = ['message' => implode('; ', $otpBuilder->getErrors()), 'code' => 400];
            Log::error('[WhatsApp] OTP template validation failed', [
                'errors' => $otpBuilder->getErrors(),
            ]);
            return null;
        }

        // Log for audit (code length only, not actual code)
        $otpBuilder->logAudit(auth()->id() ?? 'system', $normalizedPhone, 'sending');

        try {
            $response = $this->client->post("{$this->phoneNumberId}/messages", [
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => $normalizedPhone,
                    'type' => 'template',
                    'template' => [
                        'name' => $otpBuilder->getTemplateName(),
                        'language' => ['code' => 'pt_BR'],
                        'components' => $template['components'],
                    ],
                ],
            ]);

            $otpBuilder->logAudit(auth()->id() ?? 'system', $normalizedPhone, 'sent');

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->lastError = $this->parseApiError($e);
            Log::error('WhatsApp OTP send failed', [
                'to' => $normalizedPhone,
                'error' => $e->getMessage(),
                'code' => '*** (hidden for security)',
            ]);
            return null;
        }
    }

    public static function processWebhook(array $payload): void
    {
        $entry = $payload['entry'][0]['changes'][0]['value'] ?? null;
        if (!$entry) return;

        $messages = $entry['messages'] ?? [];
        $statuses = $entry['statuses'] ?? [];

        foreach ($messages as $waMessage) {
            self::handleInboundMessage($waMessage, $entry['contacts'][0] ?? []);
        }

        foreach ($statuses as $status) {
            self::handleStatusUpdate($status);
        }
    }

    private static function handleInboundMessage(array $waMessage, array $contactInfo): void
    {
        $phone = PhoneNormalizer::digits($waMessage['from']);
        $waMsgId = $waMessage['id'];
        $type = $waMessage['type'];

        if (Message::where('wa_message_id', $waMsgId)->exists()) return;

        $contact = Contact::findOrCreateByPhone($phone, [
            'name' => $contactInfo['profile']['name'] ?? $phone,
        ]);

        // Fetch and store profile photo if not already present
        if (!$contact->profile_photo_url) {
            $whatsapp = new self();
            $profilePhotoUrl = $whatsapp->getContactProfilePhoto($phone);
            if ($profilePhotoUrl) {
                $contact->update(['profile_photo_url' => $profilePhotoUrl]);
            }
        }

        $conversation = Conversation::firstOrCreate(
            ['contact_id' => $contact->id, 'status' => 'new'],
            ['last_message_at' => now()]
        );

        // Apply automatic distribution if enabled
        \App\Services\DistributionService::assign($conversation);

        $content = match ($type) {
            'text' => $waMessage['text']['body'] ?? null,
            'image' => $waMessage['image']['caption'] ?? null,
            'document' => $waMessage['document']['caption'] ?? $waMessage['document']['filename'] ?? null,
            'video' => $waMessage['video']['caption'] ?? null,
            'audio' => null,
            'sticker' => null,
            'location' => ($waMessage['location']['latitude'] ?? '') . ', ' . ($waMessage['location']['longitude'] ?? ''),
            'contacts' => json_encode($waMessage['contacts'] ?? []),
            default => null,
        };

        $mediaId = match ($type) {
            'image' => $waMessage['image']['id'] ?? null,
            'document' => $waMessage['document']['id'] ?? null,
            'video' => $waMessage['video']['id'] ?? null,
            'audio' => $waMessage['audio']['id'] ?? null,
            'sticker' => $waMessage['sticker']['id'] ?? null,
            default => null,
        };

        $mimeType = match ($type) {
            'image' => $waMessage['image']['mime_type'] ?? 'image/jpeg',
            'document' => $waMessage['document']['mime_type'] ?? 'application/octet-stream',
            'video' => $waMessage['video']['mime_type'] ?? 'video/mp4',
            'audio' => $waMessage['audio']['mime_type'] ?? 'audio/ogg',
            default => null,
        };

        $mediaFilename = match ($type) {
            'document' => $waMessage['document']['filename'] ?? null,
            default => $type . '_' . ($waMsgId ?? time()),
        };

        $message = $conversation->messages()->create([
            'wa_message_id' => $waMsgId,
            'direction' => 'inbound',
            'type' => $type,
            'content' => $content,
            'media_id' => $mediaId,
            'media_filename' => $mediaFilename,
            'mime_type' => $mimeType,
            'status' => 'delivered',
        ]);

        if ($mediaId && in_array($type, ['image', 'document', 'video', 'audio'])) {
            $whatsapp = new self();
            $localPath = $whatsapp->downloadMedia($mediaId);
            if ($localPath) {
                $message->update(['media_url' => $localPath]);
            }
        }

        $contact->update(['last_message_at' => now()]);
        $conversation->update(['last_message_at' => now()]);

        // Dispara notificação em tempo real via broadcast
        event(new MessageReceived($message, $conversation->id, $conversation->assigned_to));
        Log::info('[MessageReceived Event] Dispatched', [
            'message_id' => $message->id,
            'conversation_id' => $conversation->id,
        ]);

        // Invalida cache de relatórios
        Cache::flush(); // Simplificado: limpa todo o cache de relatórios
    }

    private static function handleStatusUpdate(array $status): void
    {
        $waMsgId = $status['id'] ?? null;
        $newStatus = $status['status'] ?? null;

        if (!$waMsgId || !$newStatus) return;

        $message = Message::where('wa_message_id', $waMsgId)->first();
        if (!$message) return;

        $oldStatus = $message->status;
        $message->update(['status' => $newStatus]);

        // Broadcast status change via Redis/SSE
        event(new \App\Events\MessageStatusChanged($message));

        Log::info('[MessageStatusChanged Event] Dispatched', [
            'message_id' => $message->id,
            'wa_message_id' => $waMsgId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }
}
