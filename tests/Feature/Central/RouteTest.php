<?php

test('central route guest home screen can be rendered', function () {
    $response = $this->get(route('central.home'));
    $response->assertOk();
});

test('central route guest login screen can be rendered', function () {
    $response = $this->get(route('central.login'));
    $response->assertOk();
});

test('central route guest dashboard redirects to login screen', function () {
    $response = $this->get(route('central.dashboard'));
    $response->assertRedirect(route('central.login'));
});
