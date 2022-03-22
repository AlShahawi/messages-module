<?php

namespace Tests\Feature;

use App\Models\Conversation;
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

    public function test_it_lists_conversations()
    {
        $this->actingAs($this->ahmed);

        $response = $this->getJson(route('v1.conversations.index'));

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['*' => [
            'id',
            'name',
            'last_message' => [
                'id',
                'sender' => [
                    'id',
                    'name',
                ],
                'content',
                'read_at',
                'created_at',
            ],
        ]]]);
        $response->assertJsonPath('data.0.name', 'Mohamed Shahawi');
        $response->assertJsonPath('data.0.last_message.content', 'Great thank you.');
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

    public function test_it_lists_conversation_messages()
    {
        $this->actingAs($this->ahmed);

        $response = $this->getJson(route('v1.conversations.messages.index', Conversation::first()->id));

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['*' => [
            'id',
            'sender' => ['id', 'name'],
            'content',
            'read_at',
            'created_at',
        ]]]);
        $response->assertJsonPath('data.0.content', $this->msg3);
        $response->assertJsonPath('data.1.content', $this->msg2);
        $response->assertJsonPath('data.2.content', $this->msg1);
    }

    public function test_it_mark_message_as_read_alongside_all_preceding_messages()
    {
        $this->actingAs($this->mohamed);

        $conversation = Conversation::first();
        $lastMessage = $conversation->messages->last();
        $response = $this->postJson(
            route('v1.conversations.messages.markAsRead', [$conversation->id, $lastMessage->id])
        );
        $response->assertNoContent();
        $lastMessage->refresh();
        $this->assertNotNull($lastMessage->read_at);

        // Assert that messages sent to Mohamed marked as read.
        $messagesReadByMohamed = Message::query()
            ->whereSenderIs($this->ahmed)
            ->whereRead()
            ->count();

        $this->assertEquals(2, $messagesReadByMohamed);

        // Assert that message sent to Ahmed remain unread.
        $messagesReadByAhmed = Message::query()
            ->whereSenderIs($this->mohamed)
            ->whereRead()
            ->count();

        $this->assertEquals(0, $messagesReadByAhmed);

    }
}
