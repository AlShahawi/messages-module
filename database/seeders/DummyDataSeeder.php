<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class DummyDataSeeder extends Seeder
{
    use WithFaker;

    public function __construct()
    {
        $this->setUpFaker();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Creating two dummy users; Ahmed, and Mohamed.');

        $ahmed = User::factory()->create([
            'name' => 'Ahmed Shahawi',
        ]);

        $mohamed = User::factory()->create([
            'name' => 'Mohamed Shahawi',
        ]);

        // Send a message from Ahmed to Mohamed and vice versa.
        Message::compose()
            ->from($ahmed)
            ->to($mohamed)
            ->send('Welcome, Mohamed');

        Message::compose()
            ->from($mohamed)
            ->to($ahmed)
            ->send('Welcome back, Ahmed');

        $this->command->info('Send 2 messages from Ahmed to 100 dummy user.');
        // Send a message from Ahmed to 100 user.
        $otherUsers = User::factory(100)->create();
        $composer = Message::compose()->from($ahmed);
        $greetings = ['Welcome', 'Hello', 'Howdy', 'Hi', 'Hey'];

        $otherUsers->each(function($receiver, $index) use ($greetings, $composer) {
            $composer->to($receiver);
            $composer->send(sprintf('%s, %s', $greetings[$index % 5], $receiver->name));
            $composer->send($this->faker->sentence);
        });

        $this->command->info('Done sending messages from Ahmed to 100 dummy user.');
    }
}
