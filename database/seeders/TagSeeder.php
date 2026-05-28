<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Service Types
            ['name' => 'Venda de Título', 'color' => '#4CAF50', 'category' => 'custom'],
            ['name' => 'Reentrada', 'color' => '#2196F3', 'category' => 'custom'],
            ['name' => 'Locação de Salão', 'color' => '#FF9800', 'category' => 'custom'],
            ['name' => 'Dúvidas', 'color' => '#9C27B0', 'category' => 'custom'],
            ['name' => 'Reclamação', 'color' => '#F44336', 'category' => 'custom'],
            ['name' => 'Suporte Técnico', 'color' => '#00BCD4', 'category' => 'custom'],
            ['name' => 'Renovação', 'color' => '#FFEB3B', 'category' => 'custom'],
            ['name' => 'Cancelamento', 'color' => '#E91E63', 'category' => 'custom'],

            // Priority Tags
            ['name' => 'Urgente', 'color' => '#F44336', 'category' => 'priority'],
            ['name' => 'Alto', 'color' => '#FF9800', 'category' => 'priority'],
            ['name' => 'Normal', 'color' => '#4CAF50', 'category' => 'priority'],
            ['name' => 'Baixo', 'color' => '#9E9E9E', 'category' => 'priority'],

            // Status Tags
            ['name' => 'Aguardando Cliente', 'color' => '#FFC107', 'category' => 'status'],
            ['name' => 'Em Análise', 'color' => '#03A9F4', 'category' => 'status'],
            ['name' => 'Em Progresso', 'color' => '#2196F3', 'category' => 'status'],

            // Outcome Tags
            ['name' => 'Resolvido', 'color' => '#4CAF50', 'category' => 'outcome'],
            ['name' => 'Escalado', 'color' => '#FF9800', 'category' => 'outcome'],
            ['name' => 'Não Resolvido', 'color' => '#F44336', 'category' => 'outcome'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['name' => $tag['name']],
                $tag
            );
        }
    }
}
