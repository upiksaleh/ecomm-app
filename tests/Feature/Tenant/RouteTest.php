<?php

test('tenant route guest home screen can be rendered', function () {
    $response = $this->get(route('tenant.home'));
    $response->assertOk();
});

test('tenant route guest login screen can be rendered', function () {
    $response = $this->get(route('tenant.login'));
    $response->assertOk();
});

test('tenant route guest dashboard redirects to login screen', function () {
    $response = $this->get(route('tenant.dashboard'));
    $response->assertRedirect(route('tenant.login'));
});
