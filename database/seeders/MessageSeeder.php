<?php

namespace Database\Seeders;

use App\Enums\UserAccessLevel;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $threads = Thread::all();

        if ($threads->isEmpty()) {
            $this->command->warn('Nenhum thread encontrado. Execute ThreadSeeder primeiro.');
            return;
        }

        $this->command->info('=== Criando mensagens ===');

        $staffUsers = User::whereIn('access_level', UserAccessLevel::staffPanelValues())->get();
        $encrypt = fn ($v) => Crypt::encryptString($v);

        $clientMessages = [
            'Olá, gostaria de saber o prazo de entrega do meu pedido.',
            'Qual o status atual da minha impressão?',
            'Preciso alterar a cor do filamento, é possível?',
            'Obrigado pelo atendimento!',
            'Tem previsão de quando ficará pronto?',
            'Qual material vocês usam para ABS?',
            'Posso fazer um pedido maior? Preciso de 20 unidades.',
            'A qualidade da impressão está excelente, parabéns!',
        ];

        $staffMessages = [
            'Olá! O prazo de entrega é de até 7 dias úteis.',
            'Seu pedido está em produção, etapa de impressão iniciada.',
            'Sim, podemos alterar a cor. Qual cor você gostaria?',
            'Ficamos felizes em ajudar! Qualquer dúvida estamos à disposição.',
            'A previsão é de 3 dias úteis para finalização.',
            'Utilizamos ABS da marca eSun, de alta qualidade.',
            'Claro! Podemos produzir as 20 unidades sem problemas.',
            'Agradecemos o feedback! Trabalhamos com muito cuidado em cada peça.',
        ];

        $total = 0;

        foreach ($threads as $thread) {
            // 4-8 mensagens por thread
            $numMessages = fake()->numberBetween(4, 8);

            for ($i = 0; $i < $numMessages; $i++) {
                // Alterna entre client e staff
                $isStaff = $i % 2 === 1;
                $senderType = $isStaff ? 'staff' : 'client';

                if ($isStaff && $staffUsers->isNotEmpty()) {
                    $senderId = $staffUsers->random()->id;
                } else {
                    $senderId = $thread->client_id;
                }

                $messageContent = $isStaff
                    ? fake()->randomElement($staffMessages)
                    : fake()->randomElement($clientMessages);

                Message::firstOrCreate(
                    [
                        'thread_id' => $thread->id,
                        'sender_type' => $senderType,
                        'sender_id' => $senderId,
                        'content_encrypted' => $encrypt($messageContent),
                    ]
                );

                $total++;
            }
        }

        $this->command->info("✓ Total: {$total} mensagens criadas com sucesso.");
    }
}