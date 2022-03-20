<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_login_the_user_and_return_a_valid_access_token()
    {
        User::factory()->create([
            'name' => 'Ahmed Shahawi',
            'email' => 'alshahawi@outlook.com',
        ]);

        $response = $this->postJson(route('v1.login'), [
            'email' => 'alshahawi@outlook.com',
            'password' => 'password',
            'device_name' => 'Macbook Pro',
        ])->assertSuccessful()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'access_token'],
            ]);

        $this->withHeaders(['Authorization' => 'Bearer '.$response->json('data.access_token')])
            ->getJson(route('v1.profile'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
            ]);
    }

    public function test_it_rejects_invalid_access_token()
    {
        $this->withHeaders(['Authorization' => 'Bearer invalidtoken'])
            ->getJson(route('v1.profile'))
            ->assertUnauthorized();
    }

    public function test_it_does_not_login_with_invalid_password()
    {
        User::factory()->create([
            'name' => 'Ahmed Shahawi',
            'email' => 'alshahawi@outlook.com',
        ]);

        $this->postJson(route('v1.login'), [
            'email' => 'alshahawi@outlook.com',
            'password' => 'wrongpassword',
            'device_name' => 'Macbook Pro',
        ])->assertJsonValidationErrors(['email']);

        $this->postJson(route('v1.login'), [
            'email' => 'not-existed-email@example.com',
            'password' => 'password',
            'device_name' => 'Macbook Pro',
        ])->assertJsonValidationErrors(['email']);
    }
}
