<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials_and_receive_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'demo@library.test',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'demo@library.test',
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login berhasil.')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'phpunit',
        ]);

        // The issued token really works on protected endpoints.
        $this->withHeader('Authorization', 'Bearer '.$response->json('data.token'))
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'demo@library.test');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'demo@library.test',
            'password' => 'password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'demo@library.test',
            'password' => 'password-salah',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_can_logout_and_the_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout berhasil. Token telah dicabut.');

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Clear the cached guard so the revoked token is re-validated from scratch.
        $this->app['auth']->forgetGuards();

        // The revoked token can no longer access protected endpoints.
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/books')
            ->assertUnauthorized();
    }

    public function test_books_endpoint_returns_401_without_token(): void
    {
        $this->getJson('/api/books')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated. Token tidak valid atau tidak disertakan.');
    }

    public function test_books_endpoint_returns_401_with_invalid_token(): void
    {
        $this->withHeader('Authorization', 'Bearer token-yang-tidak-valid')
            ->getJson('/api/books')
            ->assertUnauthorized();
    }
}
