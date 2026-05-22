<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => '55' . $this->faker->numerify('119########'),
            'email' => $this->faker->safeEmail(),
            'tags' => $this->faker->randomElement([
                ['VIP'], ['Suporte'], ['Lead Frio'], ['Novo Lead'], ['VIP', 'Suporte'], null,
            ]),
            'notes' => $this->faker->optional()->sentence(),
            'last_message_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
