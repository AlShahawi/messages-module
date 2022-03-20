<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageSenderTest extends TestCase
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
    }

    public function test_it_create_a_conversation()
    {
        Message::compose()
            ->from($this->ahmed)
            ->to($this->mohamed)
            ->send('Welcome, Mohamed');

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('conversation_participants', 2);
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_it_creates_single_conversation_for_multiple_messages_between_same_users()
    {
        Message::compose()
            ->from($this->ahmed)
            ->to($this->mohamed)
            ->send('Welcome, Mohamed');

        Message::compose()
            ->from($this->mohamed)
            ->to($this->ahmed)
            ->send('Welcome back, Ahmed');

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('conversation_participants', 2);
        $this->assertDatabaseCount('messages', 2);
    }

    public function test_it_creates_many_conversations_for_multiple_messages_between_different_users()
    {
        Message::compose()
            ->from($this->ahmed)
            ->to($this->mohamed)
            ->send('Welcome, Mohamed');


        Message::compose()
            ->from($this->ahmed)
            ->to(User::factory()->create())
            ->send($this->faker->sentence);


        $this->assertDatabaseCount('conversations', 2);
        $this->assertDatabaseCount('conversation_participants', 4);
        $this->assertDatabaseCount('messages', 2);
    }
}
