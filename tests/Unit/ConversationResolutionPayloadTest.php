<?php

namespace Tests\Unit;

use App\Http\Controllers\ConversationController;
use App\Models\ConversationResolution;
use Carbon\Carbon;
use ReflectionMethod;
use Tests\TestCase;

class ConversationResolutionPayloadTest extends TestCase
{
    public function test_format_resolution_payload_returns_null_without_record(): void
    {
        $payload = $this->invokeFormatResolutionPayload(null);

        $this->assertNull($payload);
    }

    public function test_format_resolution_payload_maps_saved_fields(): void
    {
        $resolution = new ConversationResolution([
            'resolution_reason' => 'problem_solved',
            'resolution_notes' => 'Cliente orientado sobre reserva.',
            'internal_comments' => 'Verificar retorno amanhã.',
        ]);
        $resolution->created_at = Carbon::parse('2026-06-17 14:30:00');
        $resolution->setRelation('resolvedBy', (object) ['name' => 'Ana Agente']);

        $payload = $this->invokeFormatResolutionPayload($resolution);

        $this->assertSame('problem_solved', $payload['reason']);
        $this->assertSame('Cliente orientado sobre reserva.', $payload['notes']);
        $this->assertSame('Verificar retorno amanhã.', $payload['internal_comments']);
        $this->assertSame('Ana Agente', $payload['resolved_by']);
        $this->assertSame('17/06/2026 14:30', $payload['resolved_at']);
    }

    private function invokeFormatResolutionPayload(?ConversationResolution $resolution): ?array
    {
        $controller = new ConversationController();
        $method = new ReflectionMethod($controller, 'formatResolutionPayload');
        $method->setAccessible(true);

        return $method->invoke($controller, $resolution);
    }
}
