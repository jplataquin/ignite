<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Division;
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
     * Test that guests can view the registration page.
     */
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create an Account');
    }

    /**
     * Test that unregistered users can register but cannot login without approval.
     */
    public function test_unregistered_users_can_register_but_cannot_login_without_approval(): void
    {
        $response = $this->post('/register', [
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', [
            'email' => 'pending@example.com',
            'name' => 'Pending User',
            'is_approved' => false,
        ]);

        // Attempt login
        $loginResponse = $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $loginResponse->assertSessionHasErrors('email');
        $this->assertFalse(\Illuminate\Support\Facades\Auth::check());
    }

    /**
     * Test that admin can approve a pending user.
     */
    public function test_admin_can_approve_pending_user(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $pendingUser = User::factory()->create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => bcrypt('password123'),
            'is_approved' => false,
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$pendingUser->id}/approve");

        $response->assertRedirect('/admin/users');
        $this->assertTrue($pendingUser->fresh()->is_approved);

        // Attempt login as approved user
        $loginResponse = $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect('/');
    }

    /**
     * Test that admin can reject and delete a pending user.
     */
    public function test_admin_can_reject_pending_user(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $pendingUser = User::factory()->create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$pendingUser->id}/reject");

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', [
            'id' => $pendingUser->id,
        ]);
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
        $division = Division::create(['name' => 'HR Division']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'user_type' => 'regular',
            'password' => 'TempPassword123!',
            'division_id' => $division->id,
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
            'user_type' => 'regular',
            'division_id' => $division->id,
            'must_reset_password' => true,
        ]);
    }

    /**
     * Test that admins can create other users and assign roles to them upon creation.
     */
    public function test_admins_can_create_users_and_assign_roles(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);
        $division = Division::create(['name' => 'Support Division']);
        $role1 = Role::create(['name' => 'Support Rep', 'slug' => 'support-rep']);
        $role2 = Role::create(['name' => 'Billing Agent', 'slug' => 'billing-agent']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Staff User',
            'email' => 'staffuser@example.com',
            'user_type' => 'moderator',
            'password' => 'TempPassword123!',
            'division_id' => $division->id,
            'roles' => [$role1->id, $role2->id],
        ]);

        $response->assertRedirect('/admin/users');
        
        $newUser = User::where('email', 'staffuser@example.com')->firstOrFail();
        
        $this->assertEquals('New Staff User', $newUser->name);
        $this->assertEquals('moderator', $newUser->user_type);
        $this->assertTrue($newUser->roles->contains($role1->id));
        $this->assertTrue($newUser->roles->contains($role2->id));
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

    /**
     * Test that admins can view the user edit screen.
     */
    public function test_admins_can_view_user_edit_screen(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $user = User::factory()->create(['user_type' => 'regular']);

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit User Profile');
    }

    /**
     * Test that admins can update a user and assign roles to them.
     */
    public function test_admins_can_update_user_and_assign_roles(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $user = User::factory()->create(['user_type' => 'regular']);
        $role1 = Role::create(['name' => 'Support Rep', 'slug' => 'support-rep']);
        $role2 = Role::create(['name' => 'Billing Agent', 'slug' => 'billing-agent']);

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'user_type' => 'moderator',
            'roles' => [$role1->id, $role2->id],
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'user_type' => 'moderator',
        ]);

        $this->assertTrue($user->fresh()->roles->contains($role1->id));
        $this->assertTrue($user->fresh()->roles->contains($role2->id));
    }

    /**
     * Test that non-admins cannot update user profiles.
     */
    public function test_non_admins_cannot_update_user_profiles(): void
    {
        $nonAdmin = User::factory()->create(['user_type' => 'regular']);
        $user = User::factory()->create(['user_type' => 'regular']);

        $response = $this->actingAs($nonAdmin)->put("/admin/users/{$user->id}", [
            'name' => 'Malicious Update',
            'email' => 'malicious@example.com',
            'user_type' => 'admin',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'name' => 'Malicious Update',
        ]);
    }

    /**
     * Test that registering users can select division and department, and admin receives notification.
     */
    public function test_registering_user_can_select_division_and_department_and_admin_is_notified(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $division = \App\Models\Division::create(['name' => 'HR Division']);
        $department = \App\Models\Department::create(['name' => 'Recruiting', 'division_id' => $division->id]);

        $response = $this->post('/register', [
            'name' => 'Pending Staff',
            'email' => 'pendingstaff@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'division_id' => $division->id,
            'department_id' => $department->id,
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('users', [
            'email' => 'pendingstaff@example.com',
            'name' => 'Pending Staff',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'is_approved' => false,
        ]);

        $newUser = User::where('email', 'pendingstaff@example.com')->first();

        // Assert notification was sent to admin
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $admin,
            \App\Notifications\PendingUserRegisteredNotification::class,
            function ($notification, $channels) use ($newUser) {
                return $notification->user->id === $newUser->id;
            }
        );
    }
}
