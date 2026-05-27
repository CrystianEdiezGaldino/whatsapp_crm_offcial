<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Sector;
use Illuminate\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTags();
        $this->seedSectorConfigurations();
    }

    private function seedTags(): void
    {
        $tags = [
            // Priority tags
            ['name' => 'VIP', 'color' => '#FF6B6B', 'category' => 'priority', 'is_active' => true],
            ['name' => 'Importante', 'color' => '#FFA500', 'category' => 'priority', 'is_active' => true],
            ['name' => 'Normal', 'color' => '#4ECDC4', 'category' => 'priority', 'is_active' => true],

            // Status tags
            ['name' => 'Aguardando Resposta', 'color' => '#3498DB', 'category' => 'status', 'is_active' => true],
            ['name' => 'Em Análise', 'color' => '#9B59B6', 'category' => 'status', 'is_active' => true],
            ['name' => 'Bloqueado', 'color' => '#E74C3C', 'category' => 'status', 'is_active' => true],

            // Outcome tags
            ['name' => 'Resolvido', 'color' => '#27AE60', 'category' => 'outcome', 'is_active' => true],
            ['name' => 'Escalado', 'color' => '#F39C12', 'category' => 'outcome', 'is_active' => true],
            ['name' => 'Duplicado', 'color' => '#95A5A6', 'category' => 'outcome', 'is_active' => true],

            // Custom tags
            ['name' => 'Reclamação', 'color' => '#E91E63', 'category' => 'custom', 'is_active' => true],
            ['name' => 'Sugestão', 'color' => '#2196F3', 'category' => 'custom', 'is_active' => true],
            ['name' => 'Bug Reportado', 'color' => '#D32F2F', 'category' => 'custom', 'is_active' => true],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['name' => $tag['name']],
                $tag
            );
        }
    }

    private function seedSectorConfigurations(): void
    {
        $sectors = Sector::all();

        foreach ($sectors as $sector) {
            if (!$sector->sla_first_response_minutes) {
                $sector->update([
                    'sla_first_response_minutes' => 15,
                    'sla_resolution_hours' => 24,
                    'working_hours_start' => '09:00',
                    'working_hours_end' => '18:00',
                    'working_days' => json_encode([1, 2, 3, 4, 5]),
                ]);
            }

            if (!$sector->priority_rules) {
                $sector->update([
                    'priority_rules' => json_encode([
                        [
                            'condition' => 'keyword',
                            'value' => 'urgente',
                            'priority' => 'urgent',
                        ],
                        [
                            'condition' => 'keyword',
                            'value' => 'crítico',
                            'priority' => 'high',
                        ],
                        [
                            'condition' => 'wait_time_minutes',
                            'value' => 30,
                            'priority' => 'high',
                        ],
                    ]),
                ]);
            }
        }
    }
}
