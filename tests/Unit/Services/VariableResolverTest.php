<?php

namespace Tests\Unit\Services;

use App\Models\Conversation;
use App\Models\Contact;
use App\Models\Sector;
use App\Services\VariableResolver;
use Tests\TestCase;

class VariableResolverTest extends TestCase
{
    private VariableResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new VariableResolver();
    }

    /**
     * @test
     */
    public function testResolveWithAllVariables(): void
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

        $variables = $this->resolver->resolve($conversation);

        $this->assertEquals('João Silva', $variables['nome']);
        $this->assertEquals($contact->phone, $variables['telefone']);
        $this->assertEquals($sectorName, $variables['setor']);
    }

    /**
     * @test
     */
    public function testResolveWithNullSector(): void
    {
        $contact = Contact::factory()->create([
            'name' => 'John Doe'
        ]);

        $conversation = Conversation::factory()->create([
            'contact_id' => $contact->id,
            'sector_id' => null
        ]);

        $variables = $this->resolver->resolve($conversation);

        $this->assertEquals('John Doe', $variables['nome']);
        $this->assertEquals($contact->phone, $variables['telefone']);
        $this->assertEquals('', $variables['setor']);
    }

    /**
     * @test
     */
    public function testGetAvailableVariables(): void
    {
        $available = $this->resolver->getAvailableVariables();

        $this->assertIsArray($available);
        $this->assertArrayHasKey('nome', $available);
        $this->assertArrayHasKey('telefone', $available);
        $this->assertArrayHasKey('setor', $available);
        $this->assertEquals('Nome do contato', $available['nome']);
        $this->assertEquals('Telefone do contato', $available['telefone']);
        $this->assertEquals('Setor de atendimento', $available['setor']);
    }
}
