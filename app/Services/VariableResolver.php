<?php

namespace App\Services;

use App\Models\Conversation;

class VariableResolver
{
    /**
     * Resolve todas as variáveis disponíveis para uma conversa
     */
    public function resolve(Conversation $conversation): array
    {
        return [
            'nome' => $conversation->contact->name ?? '',
            'telefone' => $conversation->contact->phone ?? '',
            'setor' => $conversation->sector->name ?? ''
        ];
    }

    /**
     * Substitui {nome}, {{nome}}, {telefone}, etc. no texto.
     */
    public function replaceInText(string $text, Conversation $conversation): string
    {
        $conversation->loadMissing(['contact', 'sector']);

        foreach ($this->resolve($conversation) as $key => $value) {
            $text = str_replace(['{{' . $key . '}}', '{' . $key . '}'], $value ?? '', $text);
        }

        return $text;
    }

    /**
     * Lista de variáveis disponíveis (para UI)
     */
    public function getAvailableVariables(): array
    {
        return [
            'nome' => 'Nome do contato',
            'telefone' => 'Telefone do contato',
            'setor' => 'Setor de atendimento'
        ];
    }
}
