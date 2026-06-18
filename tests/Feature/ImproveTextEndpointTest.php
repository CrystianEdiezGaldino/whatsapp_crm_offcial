<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Contact;

class ImproveTextEndpointTest extends TestCase
{
    public function test_improve_text_with_grammar_type()
    {
        $user = User::factory()->create(['role' => 'agent']);
        $contact = Contact::factory()->create();
        $conversation = Conversation::factory()->create(['contact_id' => $contact->id]);

        $response = $this->actingAs($user)->postJson('/conversations/' . $conversation->id . '/improve-text', [
            'content' => 'test content here',
            'type' => 'grammar',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'original',
                'improved',
                'type',
            ]);
    }

    public function test_improve_text_requires_authentication()
    {
        $conversation = Conversation::factory()->create();

        $response = $this->postJson('/conversations/' . $conversation->id . '/improve-text', [
            'content' => 'test',
            'type' => 'grammar',
        ]);

        $response->assertStatus(401);
    }

    public function test_improve_text_validates_type()
    {
        $user = User::factory()->create(['role' => 'agent']);
        $conversation = Conversation::factory()->create();

        $response = $this->actingAs($user)->postJson('/conversations/' . $conversation->id . '/improve-text', [
            'content' => 'test text',
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422);
    }

    public function test_improve_text_rejects_empty_content()
    {
        $user = User::factory()->create(['role' => 'agent']);
        $conversation = Conversation::factory()->create();

        $response = $this->actingAs($user)->postJson('/conversations/' . $conversation->id . '/improve-text', [
            'content' => '',
            'type' => 'grammar',
        ]);

        $response->assertStatus(422);
    }
}
