<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConversationsSearchTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

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

        $this->anotherUser = User::factory()->create([
            'name' => 'Elsayed Shahawi',
        ]);
        Message::compose()
            ->from($this->ahmed)
            ->to($this->anotherUser)
            ->send("Let's talk about modern physics!");
    }

    public function test_it_searches_for_conversations()
    {
        $this->actingAs($this->ahmed);

        $this->getJson(route('v1.conversations.search', ['query' => 'speak physics']))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.*');

        $this->getJson(route('v1.conversations.search', ['query' => 'Sure will handle.']))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.*');

        $this->getJson(route('v1.conversations.search', ['query' => 'Mohamed']))
            ->assertSuccessful()
            ->assertJsonCount(3, 'data.*');

        $this->getJson(route('v1.conversations.search', ['query' => 'Elsayed']))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.*');
    }
}
