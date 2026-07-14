<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\from;
use function Pest\Laravel\patch;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;


test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->patch(route('profile.update'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->email)->toBe('test@example.com');
    expect($user->getDecryptedFirstName() . ' ' . $user->getDecryptedLastName())->toBe('Test User');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->patch(route('profile.update'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => $user->email,
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->delete(route('profile.destroy'), [
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect('/');

    assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = from(route('profile.edit'))
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors('password')->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});