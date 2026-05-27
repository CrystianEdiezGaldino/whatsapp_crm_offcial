<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Macro;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create sectors first
        $supportSector = Sector::firstOrCreate(
            ['name' => 'Suporte'],
            [
                'keyboard_option' => 0,
                'description' => 'Setor de Suporte Técnico',
                'greeting_message' => 'Bem-vindo ao Suporte! Como posso ajudá-lo?',
                'is_active' => true,
                'order' => 1,
            ]
        );

        $salesSector = Sector::firstOrCreate(
            ['name' => 'Vendas'],
            [
                'keyboard_option' => 1,
                'description' => 'Setor de Vendas',
                'greeting_message' => 'Olá! Bem-vindo ao nosso setor de vendas!',
                'is_active' => true,
                'order' => 2,
            ]
        );

        $admin = User::create([
            'name' => 'Ricardo Silva',
            'email' => 'admin@erp.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'online',
            'sector_id' => $supportSector->id,
            'is_active' => true,
        ]);

        $agent = User::create([
            'name' => 'Ana Paula',
            'email' => 'ana@erp.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'status' => 'online',
            'sector_id' => $supportSector->id,
            'is_active' => true,
        ]);

        $contacts = Contact::factory()->count(10)->create()->each(function ($contact) use ($admin, $agent) {
            $assignedTo = rand(0, 1) ? $admin->id : $agent->id;
            $contact->update(['assigned_to' => $assignedTo]);

            $conversation = Conversation::create([
                'contact_id' => $contact->id,
                'assigned_to' => $assignedTo,
                'status' => collect(['open', 'open', 'open', 'closed'])->random(),
                'last_message_at' => now()->subMinutes(rand(1, 1440)),
            ]);

            $messages = rand(2, 8);
            for ($i = 0; $i < $messages; $i++) {
                $direction = $i % 2 === 0 ? 'inbound' : 'outbound';
                Message::create([
                    'conversation_id' => $conversation->id,
                    'direction' => $direction,
                    'type' => 'text',
                    'content' => $direction === 'inbound'
                        ? collect([
                            'Olá, preciso de ajuda com meu pedido',
                            'Qual o prazo de entrega?',
                            'Vocês tem esse produto em estoque?',
                            'Preciso cancelar minha assinatura',
                            'Como faço para trocar o produto?',
                            'Obrigado pelo atendimento!',
                            'Pode me enviar o comprovante?',
                            'Qual o valor do frete?',
                        ])->random()
                        : collect([
                            'Olá! Como posso ajudar?',
                            'Claro, vou verificar para você.',
                            'O prazo é de 3 a 5 dias úteis.',
                            'Sim, temos disponível em estoque.',
                            'Posso ajudar com mais alguma coisa?',
                            'Vou enviar por email agora.',
                            'Qualquer dúvida, estamos à disposição!',
                        ])->random(),
                    'status' => collect(['sent', 'delivered', 'read'])->random(),
                    'sender_id' => $direction === 'outbound' ? $assignedTo : null,
                    'created_at' => now()->subMinutes(($messages - $i) * rand(5, 30)),
                ]);
            }
        });

        Macro::create(['user_id' => $admin->id, 'name' => 'Saudação', 'content' => 'Olá! Como posso ajudar você hoje?', 'shortcut' => '/oi', 'category' => 'saudacao']);
        Macro::create(['user_id' => $admin->id, 'name' => 'Aguarde', 'content' => 'Por favor, aguarde um momento enquanto verifico.', 'shortcut' => '/aguarde', 'category' => 'util']);
        Macro::create(['user_id' => $admin->id, 'name' => 'Encerramento', 'content' => 'Obrigado pelo contato! Se precisar de mais alguma coisa, estamos à disposição. Tenha um ótimo dia!', 'shortcut' => '/tchau', 'category' => 'encerramento']);
        Macro::create(['user_id' => $admin->id, 'name' => 'Link de Pagamento', 'content' => 'Segue o link para pagamento: {link}. Qualquer dúvida, nos avise!', 'shortcut' => '/pag', 'category' => 'financeiro']);
        Macro::create(['user_id' => $admin->id, 'name' => 'Rastreamento', 'content' => 'Seu pedido pode ser rastreado pelo código: {codigo}. Acesse: https://link.correios.com.br', 'shortcut' => '/rastreio', 'category' => 'logistica']);

        // Seed initial data
        $this->call(InitialDataSeeder::class);
    }
}
