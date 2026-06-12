<?php

namespace Tests\Unit\Services;

use App\Services\VariableResolver;
use App\Services\VariableValidator;
use Tests\TestCase;

class VariableValidatorTest extends TestCase
{
    private VariableValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $resolver = new VariableResolver();
        $this->validator = new VariableValidator($resolver);
    }

    /**
     * @test
     */
    public function testValidateValidVariables(): void
    {
        $text = 'Olá {{nome}}, seu telefone é {{telefone}}';
        $result = $this->validator->validate($text);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['warnings']);
        $this->assertCount(2, $result['variables_found']);
        $this->assertContains('nome', $result['variables_found']);
        $this->assertContains('telefone', $result['variables_found']);
    }

    /**
     * @test
     */
    public function testValidateInvalidVariables(): void
    {
        $text = 'Olá {{nome}}, seu departamento é {{departamento}}';
        $result = $this->validator->validate($text);

        $this->assertFalse($result['valid']);
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('departamento', $result['warnings'][0]);
    }

    /**
     * @test
     */
    public function testValidateMultipleVariablesInText(): void
    {
        $text = 'Bem-vindo {{nome}}! Seu telefone é {{telefone}} e você está no setor {{setor}}.';
        $result = $this->validator->validate($text);

        $this->assertTrue($result['valid']);
        $this->assertCount(3, $result['variables_found']);
    }

    /**
     * @test
     */
    public function testValidateNoVariables(): void
    {
        $text = 'Bem-vindo ao nosso sistema!';
        $result = $this->validator->validate($text);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['warnings']);
        $this->assertEmpty($result['variables_found']);
    }

    /**
     * @test
     */
    public function testValidateMultipleInvalidVariables(): void
    {
        $text = 'Olá {{nome}}, seu {{cargo}} está no {{departamento}}';
        $result = $this->validator->validate($text);

        $this->assertFalse($result['valid']);
        $this->assertCount(2, $result['warnings']);

        $warnings = implode(' ', $result['warnings']);
        $this->assertStringContainsString('cargo', $warnings);
        $this->assertStringContainsString('departamento', $warnings);
    }
}
