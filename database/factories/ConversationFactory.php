<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Contact;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'sector_id' => Sector::factory(),
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => $this->faker->dateTime(),
        ];
    }
}
