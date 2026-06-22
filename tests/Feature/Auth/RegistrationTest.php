<?php

use Laravel\Fortify\Features;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    if (!Features::enabled(Features::registration())) {
        $this->markTestSkipped('Registration is not enabled.');
    }
});

test('registration screen can be rendered', function () {
    $response = get(route('register'));
    $response->assertOk();
});

test('new users can register', function () {
    $response = post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(auth()->check())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false));
});