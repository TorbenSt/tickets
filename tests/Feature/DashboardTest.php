<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('developers are redirected to tickets index', function () {
    $developer = User::factory()->developer()->create();
    $this->actingAs($developer);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('tickets.index'));
});

test('customers are redirected to projects index', function () {
    $customer = User::factory()->customer()->create();
    $this->actingAs($customer);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('projects.index'));
});