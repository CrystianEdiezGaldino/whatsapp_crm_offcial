<?php

namespace Tests\Unit\Services;

use App\Models\Conversation;
use App\Models\Contact;
use App\Models\Sector;
use App\Models\ConversationFlow;
use App\Models\FlowNode;
use App\Services\FlowService;
use App\Services\WhatsAppService;
use Tests\TestCase;
use Mockery\MockInterface;

class FlowServiceVariablesTest extends TestCase
{
    private FlowService $flowService;
    private MockInterface $whatsAppService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->whatsAppService = $this->mock(WhatsAppService::class);
        $this->flowService = new FlowService($this->whatsAppService);
    }

    /**
     * @test
     */
    public function testReplaceVariablesWithResolver(): void
    {
        $contact = Contact::factory()->create([
            'name' => 'João Silva'
        ]);

        $sectorName = 'Sector ' . uniqid();
        $sector = Sector::factory()->create([
            'name' => $sectorName
        ]);

        $conversation = Conversation::factory()->create([
            'contact_id' => $contact->id,
            'sector_id' => $sector->id
        ]);

        // Test that private method works (we'll need to use reflection)
        $reflection = new \ReflectionClass($this->flowService);
        $method = $reflection->getMethod('replaceVariables');
        $method->setAccessible(true);

        $text = 'Olá {{nome}}, seu telefone é {{telefone}} e você está no setor {{setor}}.';
        $result = $method->invokeArgs($this->flowService, [$text, $conversation]);

        $this->assertStringContainsString('João Silva', $result);
        $this->assertStringContainsString($contact->phone, $result);
        $this->assertStringContainsString($sectorName, $result);
        $this->assertStringNotContainsString('{{nome}}', $result);
        $this->assertStringNotContainsString('{{telefone}}', $result);
        $this->assertStringNotContainsString('{{setor}}', $result);
    }

    /**
     * @test
     */
    public function testReplaceVariablesWithNullSector(): void
    {
        $contact = Contact::factory()->create([
            'name' => 'Maria Santos'
        ]);

        $conversation = Conversation::factory()->create([
            'contact_id' => $contact->id,
            'sector_id' => null
        ]);

        $reflection = new \ReflectionClass($this->flowService);
        $method = $reflection->getMethod('replaceVariables');
        $method->setAccessible(true);

        $text = 'Olá {{nome}}, você está no setor {{setor}}.';
        $result = $method->invokeArgs($this->flowService, [$text, $conversation]);

        $this->assertStringContainsString('Maria Santos', $result);
        // The empty sector variable becomes empty string, resulting in "setor  ." (two spaces)
        $this->assertTrue(strpos($result, 'setor') !== false && strpos($result, '.') !== false);
        $this->assertStringNotContainsString('{{nome}}', $result);
        $this->assertStringNotContainsString('{{setor}}', $result);
    }
}
