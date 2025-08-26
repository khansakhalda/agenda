<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_guest_to_login(): void
    {
        $response = $this->get('/');

        // Ubah dari assertStatus(200) menjadi assertRedirect('/login')
        $response->assertRedirect(route('login'));
    }
}
