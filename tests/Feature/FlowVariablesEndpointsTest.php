<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FlowVariablesEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for testing
        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);
    }

    /**
     * @test
     */
    public function testValidateVariablesEndpoint(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/flows/validate-variables?text=Olá {{nome}}, bem-vindo!');

        $response->assertStatus(200)
            ->assertJsonStructure(['valid', 'warnings', 'variables_found'])
            ->assertJson([
                'valid' => true,
                'warnings' => []
            ])
            ->assertJsonCount(1, 'variables_found');
    }

    /**
     * @test
     */
    public function testValidateVariablesWithInvalidVariables(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/flows/validate-variables?text=Olá {{nome}}, seu {{cargo}} é importante');

        $response->assertStatus(200)
            ->assertJson(['valid' => false])
            ->assertJsonCount(1, 'warnings');
    }

    /**
     * @test
     */
    public function testPreviewVariablesEndpoint(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/flows/preview-variables?text=Olá {{nome}}!');

        $response->assertStatus(200)
            ->assertJsonStructure(['preview']);
    }

    /**
     * @test
     */
    public function testAvailableVariablesEndpoint(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/flows/available-variables');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'variables' => [
                    'nome',
                    'telefone',
                    'setor'
                ]
            ]);
    }

    /**
     * @test
     */
    public function testEndpointsRequireAuthentication(): void
    {
        $this->getJson('/admin/flows/validate-variables?text=test')->assertStatus(401);
        $this->getJson('/admin/flows/preview-variables?text=test')->assertStatus(401);
        $this->getJson('/admin/flows/available-variables')->assertStatus(401);
    }
}
