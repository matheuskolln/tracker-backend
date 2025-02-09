<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_sends_reset_link_successfully()
    {
        $user = User::factory()->create();

        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->postJson('/api/forgot-password', ['email' => $user->email]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Recuperation email sent.']);
    }

    /** @test */
    public function it_fails_to_send_reset_link()
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::INVALID_USER);

        $response = $this->postJson('/api/forgot-password', ['email' => 'invalid@example.com']);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Error sending email.']);
    }

    /** @test */
    public function it_resets_password_successfully()
    {
        $user = User::factory()->create();


        Password::shouldReceive('reset')
        ->once()
        ->withArgs(function ($credentials, $callback) use ($user) {
            $callback($user, 'newpassword');
            return true;
        })
        ->andReturn(Password::PASSWORD_RESET);

        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => 'fake-token',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password reset successfully!']);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }

    /** @test */
    public function it_fails_to_reset_password()
    {
        Password::shouldReceive('reset')
            ->once()
            ->andReturn(Password::INVALID_TOKEN);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'test@example.com',
            'token' => 'invalid-token',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Error resetting password.']);
    }
}
