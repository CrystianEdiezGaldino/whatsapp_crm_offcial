<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * WhatsApp User Blocking Service
 *
 * Block or unblock users from contacting the business.
 * Per Meta: Can only block contacts you received message from in last 24 hours
 *
 * When a user is blocked:
 * - User cannot message the business
 * - User cannot see business is online
 * - Business cannot send messages to the user
 */
class WhatsAppBlockingService
{
    private Client $client;
    private string $phoneNumberId;
    private string $accessToken;
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->accessToken = config('services.whatsapp.access_token');
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

    /**
     * Block a user from contacting the business
     *
     * Requirements:
     * - Must have received a message from this user in the last 24 hours
     * - Phone number must be normalized (with country code)
     */
    public function blockUser(string $phoneNumber): bool
    {
        // Normalize phone number
        $normalizedPhone = PhoneNormalizer::digits($phoneNumber);
        if (!$normalizedPhone) {
            Log::error('[WhatsApp Block] Invalid phone number', [
                'phone' => $phoneNumber,
            ]);
            return false;
        }

        try {
            // Call block endpoint
            $response = $this->client->post("{$this->phoneNumberId}/contacts", [
                'json' => [
                    'blocking' => 'block',
                    'contact_phone_number' => $normalizedPhone,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Update contact record
            $contact = Contact::where('phone', $normalizedPhone)->first();
            if ($contact) {
                $contact->update([
                    'is_blocked' => true,
                    'blocked_at' => now(),
                ]);
            }

            Log::info('[WhatsApp Block] User blocked successfully', [
                'phone' => $normalizedPhone,
                'response' => $data,
            ]);

            return true;
        } catch (ClientException $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true);

            // Check for specific error: "Must have recent message"
            if (isset($error['error']['code']) && $error['error']['code'] === 551) {
                Log::warning('[WhatsApp Block] Cannot block - no recent message from user', [
                    'phone' => $normalizedPhone,
                    'message' => $error['error']['message'] ?? 'No recent message',
                ]);
                return false;
            }

            Log::error('[WhatsApp Block] Failed to block user', [
                'phone' => $normalizedPhone,
                'error' => $error['error']['message'] ?? 'Unknown error',
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('[WhatsApp Block] Exception occurred', [
                'phone' => $normalizedPhone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Unblock a user
     *
     * Allows the user to message the business again and see online status
     */
    public function unblockUser(string $phoneNumber): bool
    {
        // Normalize phone number
        $normalizedPhone = PhoneNormalizer::digits($phoneNumber);
        if (!$normalizedPhone) {
            Log::error('[WhatsApp Unblock] Invalid phone number', [
                'phone' => $phoneNumber,
            ]);
            return false;
        }

        try {
            // Call unblock endpoint
            $response = $this->client->post("{$this->phoneNumberId}/contacts", [
                'json' => [
                    'blocking' => 'unblock',
                    'contact_phone_number' => $normalizedPhone,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Update contact record
            $contact = Contact::where('phone', $normalizedPhone)->first();
            if ($contact) {
                $contact->update([
                    'is_blocked' => false,
                    'blocked_at' => null,
                ]);
            }

            Log::info('[WhatsApp Unblock] User unblocked successfully', [
                'phone' => $normalizedPhone,
                'response' => $data,
            ]);

            return true;
        } catch (ClientException $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true);

            Log::error('[WhatsApp Unblock] Failed to unblock user', [
                'phone' => $normalizedPhone,
                'error' => $error['error']['message'] ?? 'Unknown error',
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('[WhatsApp Unblock] Exception occurred', [
                'phone' => $normalizedPhone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if a user is blocked
     *
     * Returns: true if blocked, false otherwise
     */
    public function isBlocked(string $phoneNumber): bool
    {
        $normalizedPhone = PhoneNormalizer::digits($phoneNumber);
        if (!$normalizedPhone) {
            return false;
        }

        $contact = Contact::where('phone', $normalizedPhone)->first();
        return $contact ? (bool)$contact->is_blocked : false;
    }

    /**
     * Get all blocked contacts
     */
    public static function getBlockedContacts()
    {
        return Contact::where('is_blocked', true)
            ->orderBy('blocked_at', 'desc')
            ->get();
    }

    /**
     * Block contact with reason (for internal tracking)
     */
    public function blockUserWithReason(string $phoneNumber, string $reason = 'Manual block'): bool
    {
        $normalizedPhone = PhoneNormalizer::digits($phoneNumber);
        if (!$normalizedPhone) {
            return false;
        }

        // Block via API
        if (!$this->blockUser($phoneNumber)) {
            return false;
        }

        // Update contact with reason
        $contact = Contact::where('phone', $normalizedPhone)->first();
        if ($contact) {
            $contact->update([
                'block_reason' => $reason,
            ]);
        }

        Log::info('[WhatsApp Block] User blocked with reason', [
            'phone' => $normalizedPhone,
            'reason' => $reason,
        ]);

        return true;
    }

    /**
     * Bulk block multiple users
     */
    public function blockMultipleUsers(array $phoneNumbers): array
    {
        $results = [];

        foreach ($phoneNumbers as $phone) {
            $results[$phone] = $this->blockUser($phone);
        }

        $blocked = count(array_filter($results, fn($r) => $r === true));
        Log::info('[WhatsApp Block] Bulk block completed', [
            'total' => count($phoneNumbers),
            'blocked' => $blocked,
            'failed' => count($phoneNumbers) - $blocked,
        ]);

        return $results;
    }

    /**
     * Bulk unblock multiple users
     */
    public function unblockMultipleUsers(array $phoneNumbers): array
    {
        $results = [];

        foreach ($phoneNumbers as $phone) {
            $results[$phone] = $this->unblockUser($phone);
        }

        $unblocked = count(array_filter($results, fn($r) => $r === true));
        Log::info('[WhatsApp Unblock] Bulk unblock completed', [
            'total' => count($phoneNumbers),
            'unblocked' => $unblocked,
            'failed' => count($phoneNumbers) - $unblocked,
        ]);

        return $results;
    }
}
