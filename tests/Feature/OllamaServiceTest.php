<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\OllamaService;

class OllamaServiceTest extends TestCase
{
    public function test_improveGrammar_returns_string()
    {
        $text = "i havv a error in my textt";
        $result = OllamaService::improveGrammar($text);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_improveProfessionalTone_returns_string()
    {
        $text = "hey wassup i need help with stuff lol";
        $result = OllamaService::improveProfessionalTone($text);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_improveBoth_returns_string()
    {
        $text = "i havv a problem wit this lol";
        $result = OllamaService::improveBoth($text);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_improveGrammar_throws_on_empty_text()
    {
        $this->expectException(\InvalidArgumentException::class);
        OllamaService::improveGrammar("");
    }

    public function test_improveGrammar_throws_on_ollama_error()
    {
        config(['services.ollama.key' => 'invalid-key']);

        $this->expectException(\Exception::class);
        OllamaService::improveGrammar("test text");
    }
}
