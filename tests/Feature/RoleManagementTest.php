<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\TicketType;
use App\Models\Division;
use App\Models\Department;
use App\Models\Priority;
use App\Models\TicketStatus;
use App\Models\Category;
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

    /**
     * Test that admins can store a role with allowed ticket types.
     */
    public function test_admins_can_store_role_with_ticket_types(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type1 = TicketType::create(['name' => 'Incident']);
        $type2 = TicketType::create(['name' => 'Service Request']);

        $response = $this->actingAs($admin)->post('/admin/roles', [
            'name' => 'Technician',
            'ticket_types' => [$type1->id, $type2->id],
        ]);

        $response->assertRedirect('/admin/roles');
        $role = Role::where('name', 'Technician')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->ticketTypes->contains($type1->id));
        $this->assertTrue($role->ticketTypes->contains($type2->id));
    }

    /**
     * Test that ticket creation filters and enforces allowed ticket types based on user roles.
     */
    public function test_role_ticket_type_enforcement_on_creation(): void
    {
        $user = User::factory()->create(['user_type' => 'regular']);
        $role = Role::create(['name' => 'Junior Rep', 'slug' => 'junior-rep']);
        $user->roles()->attach($role->id);

        $allowedType = TicketType::create(['name' => 'Allowed Type']);
        $disallowedType = TicketType::create(['name' => 'Disallowed Type']);

        $role->ticketTypes()->attach($allowedType->id);

        // Core ticket setup data
        $division = Division::create(['name' => 'Tech Division']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#F59E0B']);
        $category = Category::create(['name' => 'Software Issue', 'ticket_type_id' => $allowedType->id]);

        // 1. Check create view filters list
        $response = $this->actingAs($user)->get('/tickets/create');
        $response->assertStatus(200);
        $response->assertSee('Allowed Type');
        $response->assertDontSee('Disallowed Type');

        // 2. Try creating allowed ticket type (should pass)
        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Allowed Ticket',
            'description' => 'Allowed ticket description',
            'ticket_type_id' => $allowedType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'title' => 'Allowed Ticket',
            'ticket_type_id' => $allowedType->id,
        ]);

        // 3. Try creating disallowed ticket type (should fail backend validation)
        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Disallowed Ticket',
            'description' => 'Disallowed ticket description',
            'ticket_type_id' => $disallowedType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
        ]);

        $response->assertSessionHasErrors(['ticket_type_id']);
        $this->assertDatabaseMissing('tickets', [
            'title' => 'Disallowed Ticket',
        ]);
    }
}
