<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationAntiBotTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name'            => 'Jordan',
            'last_name'             => 'McDonald',
            'organization'          => 'Springfield High Athletics',
            'phone'                 => '555-123-4567',
            'sport'                 => 'Basketball',
            'email'                 => 'coach.jordan@example.com',
            'email_confirmation'    => 'coach.jordan@example.com',
            'password'              => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'sales_rep'             => 'Earl Smith',
            'website'               => '',
            'form_loaded_at'        => encrypt((string) now()->subSeconds(10)->timestamp),
        ], $overrides);
    }

    public function test_honeypot_blocks_bot_registration(): void
    {
        $response = $this->post('/register', $this->validPayload([
            'website' => 'https://spam-bot.example.com',
        ]));

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'coach.jordan@example.com']);
    }

    public function test_instant_submission_under_3_seconds_is_blocked(): void
    {
        $response = $this->post('/register', $this->validPayload([
            'form_loaded_at' => encrypt((string) now()->timestamp),
        ]));

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'coach.jordan@example.com']);
    }

    public function test_random_mixed_case_gibberish_names_are_blocked(): void
    {
        $response = $this->post('/register', $this->validPayload([
            'first_name'   => 'jDKgizQYWtstJhMKJMvQya',
            'last_name'    => 'veaNGhxbojeMivdXSuN',
            'organization' => 'Efkazk LLC',
            'sales_rep'    => 'UuyCaRPmkvsyVCAYQqtegdOt',
            'email'        => 'az.iba.duqu.87@gmail.com',
            'email_confirmation' => 'az.iba.duqu.87@gmail.com',
        ]));

        $response->assertSessionHasErrors('first_name');
        $this->assertDatabaseMissing('users', ['email' => 'az.iba.duqu.87@gmail.com']);
    }

    public function test_legitimate_coach_registration_succeeds_after_normal_fill_time(): void
    {
        $this->assertFalse(AuthController::isLikelyBotGibberish('McDonald'));
        $this->assertFalse(AuthController::isLikelyBotGibberish('DeLaCruz'));
        $this->assertFalse(AuthController::isLikelyBotGibberish('Jean-Baptiste'));
        $this->assertTrue(AuthController::isLikelyBotGibberish('jDKgizQYWtstJhMKJMvQya'));
        $this->assertTrue(AuthController::isLikelyBotGibberish('veaNGhxbojeMivdXSuN'));
        $this->assertTrue(AuthController::isLikelyBotGibberish('UuyCaRPmkvsyVCAYQqtegdOt'));

        $response = $this->post('/register', $this->validPayload());

        $response->assertRedirect('/coach/dashboard');
        $this->assertDatabaseHas('users', [
            'email'      => 'coach.jordan@example.com',
            'first_name' => 'Jordan',
            'last_name'  => 'McDonald',
            'role'       => 'coach',
            'status'     => 'active',
        ]);
    }
}
