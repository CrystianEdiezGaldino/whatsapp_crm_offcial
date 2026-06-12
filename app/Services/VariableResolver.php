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
