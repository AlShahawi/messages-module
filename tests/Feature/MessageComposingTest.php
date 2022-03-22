<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessageComposingTest extends TestCase
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
            ->send($this->msg1 = 'Hi, Mohamed. Can you please investigate the previous issue reported on Rollbar?');

        Message::compose()
            ->from($this->mohamed)
            ->to($this->ahmed)
            ->send($this->msg2 = 'Hi, Ahmed. Okay I will handle it.');

        Message::compose()
            ->from($this->ahmed)
            ->to($this->mohamed)
            ->send($this->msg3 = 'Great thank you.');

        Message::compose()
            ->from($this->ahmed)
            ->to(User::factory()->create())
            ->send($this->faker->sentence);

    }

    public function test_it_send_a_message_to_conversation()
    {
        $this->actingAs($this->ahmed);

        $conversation = Conversation::first();

        $messagesCountBeforeSendNewMessage = Message::count();

        $response = $this->postJson(route('v1.conversations.messages.send', $conversation), [
            'content' => $this->faker->sentence,
        ]);

        $response
            ->assertSuccessful()
            ->assertJsonStructure([
            'data' => [
                'id',
                'sender' => [
                    'id',
                    'name',
                ],
                'content',
                'read_at',
                'created_at',
            ],
        ]);
        $this->assertEquals($messagesCountBeforeSendNewMessage + 1, Message::count());
    }

    public function test_it_opens_a_conversation_and_post_a_message_to_it()
    {
        $this->actingAs($this->ahmed);

        $newUser = User::factory()->create();

        $conversationsCountBeforeSendNewMessage = Conversation::count();
        $messagesCountBeforeSendNewMessage = Message::count();

        $response = $this->postJson(route('v1.conversations.new'), [
            'participant_id' => $newUser->id,
            'content' => $this->faker->sentence,
        ]);

        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                ],
            ]);
        $this->assertEquals($conversationsCountBeforeSendNewMessage + 1, Conversation::count());
        $this->assertEquals($messagesCountBeforeSendNewMessage + 1, Message::count());
    }
}
