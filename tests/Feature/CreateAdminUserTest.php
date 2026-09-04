<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the admin user can be created via artisan command.
     */
    public function test_admin_user_can_be_created_via_artisan(): void
    {
        $this->artisan('make:admin --name="Admin User" --email="admin@example.com" --password="password123"')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'name' => 'Admin User',
            'user_type' => 'admin',
            'must_reset_password' => false,
        ]);
    }
}
