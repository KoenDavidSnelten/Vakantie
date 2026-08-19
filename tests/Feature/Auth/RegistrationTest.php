<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng-P@ssw0rd!',
            'password_confirmation' => 'Str0ng-P@ssw0rd!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_requires_correct_code_when_configured(): void
    {
        config(['auth.registration_code' => 'geheim123']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng-P@ssw0rd!',
            'password_confirmation' => 'Str0ng-P@ssw0rd!',
            'registration_code' => 'fout',
        ]);

        $response->assertSessionHasErrors('registration_code');
        $this->assertGuest();
    }

    public function test_live_code_check_reports_validity(): void
    {
        config(['auth.registration_code' => 'geheim123']);

        $this->postJson(route('register.check-code'), ['registration_code' => 'fout'])
            ->assertOk()
            ->assertJson(['required' => true, 'valid' => false]);

        $this->postJson(route('register.check-code'), ['registration_code' => 'geheim123'])
            ->assertOk()
            ->assertJson(['required' => true, 'valid' => true]);
    }

    public function test_live_email_check_reports_availability(): void
    {
        \App\Models\User::factory()->create(['email' => 'bezet@example.com']);

        $this->postJson(route('register.check-email'), ['email' => 'niet-geldig'])
            ->assertOk()
            ->assertJson(['valid' => false]);

        $this->postJson(route('register.check-email'), ['email' => 'bezet@example.com'])
            ->assertOk()
            ->assertJson(['valid' => true, 'available' => false]);

        $this->postJson(route('register.check-email'), ['email' => 'vrij@example.com'])
            ->assertOk()
            ->assertJson(['valid' => true, 'available' => true]);
    }
}
