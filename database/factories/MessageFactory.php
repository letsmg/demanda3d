<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'thread_id' => Thread::factory(),
            'sender_type' => fake()->randomElement(['staff', 'client']),
            'sender_id' => fake()->randomNumber(2),
            'content_encrypted' => Crypt::encryptString(fake()->sentence()),
        ];
    }
}