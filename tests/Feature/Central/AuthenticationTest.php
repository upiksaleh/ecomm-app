<?php

test('central login can authenticate existing user', function () {
    $user = \App\Models\Central\User::create([
        'email' => fake()->email(),
        'name'=>fake()->name(),
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('central.login_store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user, 'central');
});

test('central login fails with invalid credentials', function () {
    $user = \App\Models\Central\User::create([
        'email' => fake()->email(),
        'name'=>fake()->name(),
        'password' => bcrypt('password'),
    ]);

    $response = $this->from(route('central.login'))
        ->post(route('central.login_store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

    $response->assertRedirect(route('central.login'));
    $response->assertSessionHasErrors('email');
    $this->assertGuest('central');
});

test('central route authenticated dashboard screen can be rendered', function () {
    $user = \App\Models\Central\User::create([
        'email' => fake()->email(),
        'name'=>fake()->name(),
        'password' => bcrypt('password'),
    ]);

    $response = $this->actingAs($user, 'central')->get(route('central.dashboard'));
    $response->assertOk();
});
