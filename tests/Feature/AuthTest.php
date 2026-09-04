<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable CSRF verification for testing POST requests
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test that guests can view the login page.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Welcome Back');
    }

    /**
     * Test that guests cannot view the registration page (since it's removed).
     */
    public function test_registration_screen_cannot_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    /**
     * Test that users can authenticate using the login screen.
     */
    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    /**
     * Test that users cannot authenticate with an invalid password.
     */
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /**
     * Test that authenticated users can access the dashboard.
     */
    public function test_authenticated_users_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    /**
     * Test that users can log out.
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /**
     * Test that non-admins cannot access the user management area.
     */
    public function test_non_admins_cannot_access_user_management(): void
    {
        $user = User::factory()->create([
            'user_type' => 'regular',
        ]);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403);
    }

    /**
     * Test that admins can access the user management area.
     */
    public function test_admins_can_access_user_management(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    /**
     * Test that admins can create other users with temporary passwords.
     */
    public function test_admins_can_create_users_with_temporary_passwords(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'user_type' => 'regular',
            'password' => 'TempPassword123!',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
            'user_type' => 'regular',
            'must_reset_password' => true,
        ]);
    }

    /**
     * Test that users requiring reset are redirected to the reset-password page.
     */
    public function test_users_requiring_reset_are_redirected_to_reset_password_page(): void
    {
        $user = User::factory()->create([
            'must_reset_password' => true,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/reset-password');
    }

    /**
     * Test that users can successfully reset their temporary password.
     */
    public function test_users_can_reset_temporary_password(): void
    {
        $user = User::factory()->create([
            'must_reset_password' => true,
        ]);

        $response = $this->actingAs($user)->post('/reset-password', [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertRedirect('/');
        $this->assertFalse($user->fresh()->must_reset_password);
    }
}
