<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_unauthenticated_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_unauthenticated_any_protected_route_redirects_to_login(): void
    {
        $this->get('/products')->assertRedirect('/login');
        $this->get('/transactions')->assertRedirect('/login');
        $this->get('/inventory')->assertRedirect('/login');
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->post('/login', [
            'email'    => $this->admin->email,
            'password' => 'password',
        ])->assertRedirect('/');
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $this->post('/login', [
            'email'    => $this->admin->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $this->actingAs($this->admin)
             ->post('/logout')
             ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $this->actingAs($this->admin)
             ->get('/')
             ->assertOk();
    }
}
