<?php

namespace Tests\Unit;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConversationQueryBuilderTest extends TestCase
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

    public function test_it_fetch_conversation_between_two_users()
    {
        Message::compose()
            ->from($this->ahmed)
            ->to($this->mohamed)
            ->send('Welcome, Mohamed');

        $conversation = Conversation::query()
            ->whereHasExactParticipants([
                $this->ahmed->id,
                $this->mohamed->id,
            ])->sole();

        $this->assertInstanceOf(Conversation::class, $conversation);
    }

    public function test_it_fetch_conversations_for_a_specific_participant()
    {
        Message::compose()
            ->from($this->ahmed)
            ->to($this->mohamed)
            ->send('Welcome, Mohamed');

        Message::compose()
            ->from($this->ahmed)
            ->to(User::factory()->create())
            ->send($this->faker->sentence);

        $conversation = Conversation::query()
            ->forParticipant($this->ahmed)->get();

        $this->assertInstanceOf(Conversation::class, $conversation->first());
    }
}
