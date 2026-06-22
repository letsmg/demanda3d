<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\withSession;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('security page is displayed', function () {
    if (!Features::enabled(Features::twoFactorAuthentication())) {
        $this->markTestSkipped('Two factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]);

    $user = User::factory()->create();

    actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertInertia(fn(Assert $page) => $page
            ->component('settings/Security')
            ->where('canManageTwoFactor', true)
            ->where('twoFactorEnabled', false)
        );
});

test('security page requires password confirmation when enabled', function () {
    if (!Features::enabled(Features::twoFactorAuthentication())) {
        $this->markTestSkipped('Two factor authentication is not enabled.');
    }

    $user = User::factory()->create();

    Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]);

    $response = actingAs($user)->get(route('security.edit'));

    $response->assertRedirect(route('password.confirm'));
});

test('security page renders without two factor when feature is disabled', function () {
    if (!Features::enabled(Features::twoFactorAuthentication())) {
        $this->markTestSkipped('Two factor authentication is not enabled.');
    }

    config(['fortify.features' => []]);

    $user = User::factory()->create();

    actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk()
        ->assertInertia(fn(Assert $page) => $page
            ->component('settings/Security')
            ->where('canManageTwoFactor', false)
            ->missing('twoFactorEnabled')
            ->missing('requiresConfirmation')
        );
});

test('password can be updated', function () {
    $user = User::factory()->create();

    $response = from(route('security.edit'))
        ->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('security.edit'));

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = from(route('security.edit'))
        ->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertSessionHasErrors('current_password')->assertRedirect(route('security.edit'));
});