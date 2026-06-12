<?php

namespace App\Services;

class VariableValidator
{
    public function __construct(private VariableResolver $resolver) {}

    /**
     * Valida variáveis em um texto
     * Retorna array com warnings e status de validade
     */
    public function validate(string $text): array
    {
        $availableVars = $this->resolver->getAvailableVariables();
        $matches = [];

        preg_match_all('/\{\{(\w+)\}\}/', $text, $matches);

        $warnings = [];
        foreach ($matches[1] as $varName) {
            if (!isset($availableVars[$varName])) {
                $warnings[] = "Variável desconhecida: {{$varName}}";
            }
        }

        return [
            'valid' => count($warnings) === 0,
            'warnings' => $warnings,
            'variables_found' => $matches[1] ?? []
        ];
    }
}
