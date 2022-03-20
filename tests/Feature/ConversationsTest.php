<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConversationsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ahmed = User::factory()->create([
            'name' => 'Ahmed Shahawi',
        ]);

        $this->mohamed = User::factory()->create([
            'name' => 'Mohamed Shahawi',
        ]);

        Message::compose()
            ->from($this->ahmed)
            ->to($this->mohamed)
            ->send('Hi, Mohamed. Can you please investigate the previous issue reported on Rollbar?');

        Message::compose()
            ->from($this->mohamed)
            ->to($this->ahmed)
            ->send('Hi, Ahmed. Okay I will handle it.');

        Message::compose()
            ->from($this->ahmed)
            ->to($this->mohamed)
            ->send('Great thank you.');

        Message::compose()
            ->from($this->ahmed)
            ->to(User::factory()->create())
            ->send($this->faker->sentence);

    }

    public function test_it_lists_conversations()
    {
        $this->actingAs($this->ahmed);

        $response = $this->getJson(route('v1.conversations.index'));

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['*' => [
            'id',
            'name',
            'last_message' => ['id', 'content', 'is_read', 'created_at'],
        ]]]);
        $response->assertJsonPath('data.0.name', 'Mohamed Shahawi');
        $response->assertJsonPath('data.0.last_message.content', 'Great thank you.');
    }
}
