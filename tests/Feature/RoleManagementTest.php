<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test that guests cannot access role management.
     */
    public function test_guests_cannot_access_role_management(): void
    {
        $response = $this->get('/admin/roles');

        $response->assertRedirect('/login');
    }

    /**
     * Test that non-admin users cannot access role management.
     */
    public function test_non_admins_cannot_access_role_management(): void
    {
        $user = User::factory()->create(['user_type' => 'regular']);

        $response = $this->actingAs($user)->get('/admin/roles');

        $response->assertStatus(403);
    }

    /**
     * Test that admins can access role management.
     */
    public function test_admins_can_access_role_management(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/roles');

        $response->assertStatus(200);
        $response->assertSee('Roles');
    }

    /**
     * Test that admins can view creation screen.
     */
    public function test_admins_can_view_role_creation_screen(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/roles/create');

        $response->assertStatus(200);
        $response->assertSee('Create Role');
    }

    /**
     * Test that admins can store a new role.
     */
    public function test_admins_can_store_new_role(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/roles', [
            'name' => 'Database Administrator',
            'description' => 'Manages DB structures',
        ]);

        $response->assertRedirect('/admin/roles');
        $this->assertDatabaseHas('roles', [
            'name' => 'Database Administrator',
            'slug' => 'database-administrator',
            'description' => 'Manages DB structures',
        ]);
    }

    /**
     * Test that admins can view edit screen.
     */
    public function test_admins_can_view_role_edit_screen(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $role = Role::create([
            'name' => 'Support Agent',
            'slug' => 'support-agent'
        ]);

        $response = $this->actingAs($admin)->get("/admin/roles/{$role->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Role');
    }

    /**
     * Test that admins can update a role.
     */
    public function test_admins_can_update_role(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $role = Role::create([
            'name' => 'Old Role',
            'slug' => 'old-role'
        ]);

        $response = $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
            'name' => 'New Role Name',
            'description' => 'Updated Description',
        ]);

        $response->assertRedirect('/admin/roles');
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'New Role Name',
            'slug' => 'new-role-name',
            'description' => 'Updated Description',
        ]);
    }

    /**
     * Test that admins can delete an unused role.
     */
    public function test_admins_can_delete_unused_role(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $role = Role::create([
            'name' => 'Delete Me',
            'slug' => 'delete-me'
        ]);

        $response = $this->actingAs($admin)->delete("/admin/roles/{$role->id}");

        $response->assertRedirect('/admin/roles');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    /**
     * Test that admins cannot delete a role currently being used by any users.
     */
    public function test_admins_cannot_delete_used_role(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $role = Role::create([
            'name' => 'In-Use Role',
            'slug' => 'in-use-role'
        ]);

        // Assign user to role
        $role->users()->attach($admin->id);

        $response = $this->actingAs($admin)->delete("/admin/roles/{$role->id}");

        $response->assertRedirect('/admin/roles');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }
}
