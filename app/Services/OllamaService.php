<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    private const OLLAMA_URL = 'https://ollama.com/api/generate';
    private const MODEL = 'gemma4:31b';
    private const TIMEOUT = 30;

    /**
     * Improve text grammar and spelling
     */
    public static function improveGrammar(string $text): string
    {
        if (empty(trim($text))) {
            throw new \InvalidArgumentException('Text cannot be empty');
        }

        $prompt = "You are a Portuguese grammar assistant. Correct the text for spelling, grammar, and punctuation. Keep the original meaning and tone. Return ONLY the corrected text without any explanation.\n\nText: $text";

        return self::callOllama($prompt);
    }

    /**
     * Improve text for professional tone
     */
    public static function improveProfessionalTone(string $text): string
    {
        if (empty(trim($text))) {
            throw new \InvalidArgumentException('Text cannot be empty');
        }

        $prompt = "You are a professional communication expert. Reformulate the text for professional context (business communication). Make it more formal, courteous, and clear while maintaining the message intent. Return ONLY the reformulated text without any explanation.\n\nText: $text";

        return self::callOllama($prompt);
    }

    /**
     * Improve text for both grammar and professional tone
     */
    public static function improveBoth(string $text): string
    {
        if (empty(trim($text))) {
            throw new \InvalidArgumentException('Text cannot be empty');
        }

        $prompt = "You are a professional Portuguese communication expert. 1. Correct spelling, grammar, and punctuation 2. Reformulate for professional context (formal, courteous, clear). Keep the original meaning. Return ONLY the final professional text without any explanation.\n\nText: $text";

        return self::callOllama($prompt);
    }

    /**
     * Call Ollama Cloud API
     */
    private static function callOllama(string $prompt): string
    {
        $apiKey = env('OLLMA_KEY_CODE');

        if (!$apiKey) {
            throw new \Exception('Ollama API key not configured. Set OLLMA_KEY_CODE in .env');
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post(self::OLLAMA_URL, [
                    'model' => self::MODEL,
                    'prompt' => $prompt,
                    'stream' => false,
                ])
                ->throwIfServerError()
                ->throwIfClientError();

            if (!$response->successful()) {
                throw new \Exception("Ollama API error: " . $response->body());
            }

            $data = $response->json();

            if (!isset($data['response']) || empty($data['response'])) {
                throw new \Exception('Invalid response from Ollama API');
            }

            return trim($data['response']);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error('[Ollama] API request failed', [
                'error' => $e->getMessage(),
                'status' => $e->response?->status(),
            ]);
            throw new \Exception('Serviço de IA indisponível. Tente novamente.');
        }
    }
}
