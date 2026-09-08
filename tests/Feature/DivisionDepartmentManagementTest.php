<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DivisionDepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test that guests cannot access division or department management.
     */
    public function test_guests_cannot_access_division_and_department_management(): void
    {
        $this->get('/admin/divisions')->assertRedirect('/login');
        $this->get('/admin/departments')->assertRedirect('/login');
    }

    /**
     * Test that non-admin users cannot access division or department management.
     */
    public function test_non_admins_cannot_access_division_and_department_management(): void
    {
        $user = User::factory()->create(['user_type' => 'regular']);

        $this->actingAs($user)->get('/admin/divisions')->assertStatus(403);
        $this->actingAs($user)->get('/admin/departments')->assertStatus(403);
    }

    /**
     * Test that admins can access division and department lists.
     */
    public function test_admins_can_access_division_and_department_lists(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $responseDiv = $this->actingAs($admin)->get('/admin/divisions');
        $responseDiv->assertStatus(200);
        $responseDiv->assertSee('Divisions');

        $responseDept = $this->actingAs($admin)->get('/admin/departments');
        $responseDept->assertStatus(200);
        $responseDept->assertSee('Departments');
    }

    /**
     * Test that admins can create, edit, and delete divisions.
     */
    public function test_division_crud_operations(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Create Division
        $this->actingAs($admin)->post('/admin/divisions', [
            'name' => 'Innovation Division',
        ])->assertRedirect('/admin/divisions');

        $this->assertDatabaseHas('divisions', [
            'name' => 'Innovation Division',
        ]);

        $division = Division::where('name', 'Innovation Division')->first();

        // View Edit Screen
        $this->actingAs($admin)->get("/admin/divisions/{$division->id}/edit")
            ->assertStatus(200)
            ->assertSee('Edit Division');

        // Update Division
        $this->actingAs($admin)->put("/admin/divisions/{$division->id}", [
            'name' => 'Research & Innovation',
        ])->assertRedirect('/admin/divisions');

        $this->assertDatabaseHas('divisions', [
            'id' => $division->id,
            'name' => 'Research & Innovation',
        ]);

        // Delete Division
        $this->actingAs($admin)->delete("/admin/divisions/{$division->id}")
            ->assertRedirect('/admin/divisions');

        $this->assertDatabaseMissing('divisions', [
            'id' => $division->id,
        ]);
    }

    /**
     * Test that admins cannot delete a division with active departments.
     */
    public function test_cannot_delete_division_with_departments(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $division = Division::create(['name' => 'Finance Division']);
        $department = Department::create(['name' => 'Accounting', 'division_id' => $division->id]);

        $this->actingAs($admin)->delete("/admin/divisions/{$division->id}")
            ->assertRedirect('/admin/divisions')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('divisions', ['id' => $division->id]);
    }

    /**
     * Test that admins can create, edit, and delete departments.
     */
    public function test_department_crud_operations(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $division = Division::create(['name' => 'HR Division']);

        // Create Department
        $this->actingAs($admin)->post('/admin/departments', [
            'name' => 'Recruitment',
            'division_id' => $division->id,
        ])->assertRedirect('/admin/departments');

        $this->assertDatabaseHas('departments', [
            'name' => 'Recruitment',
            'division_id' => $division->id,
        ]);

        $department = Department::where('name', 'Recruitment')->first();

        // View Edit Screen
        $this->actingAs($admin)->get("/admin/departments/{$department->id}/edit")
            ->assertStatus(200)
            ->assertSee('Edit Department');

        // Update Department
        $this->actingAs($admin)->put("/admin/departments/{$department->id}", [
            'name' => 'Talent Acquisition',
            'division_id' => $division->id,
        ])->assertRedirect('/admin/departments');

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Talent Acquisition',
            'division_id' => $division->id,
        ]);

        // Delete Department
        $this->actingAs($admin)->delete("/admin/departments/{$department->id}")
            ->assertRedirect('/admin/departments');

        $this->assertDatabaseMissing('departments', [
            'id' => $department->id,
        ]);
    }

    /**
     * Test that admins cannot delete a department with active users.
     */
    public function test_cannot_delete_department_with_users(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $division = Division::create(['name' => 'Operations Division']);
        $department = Department::create(['name' => 'Logistics', 'division_id' => $division->id]);
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->actingAs($admin)->delete("/admin/departments/{$department->id}")
            ->assertRedirect('/admin/departments')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    /**
     * Test that admins can create a user with a department assigned.
     */
    public function test_create_user_with_department(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $division = Division::create(['name' => 'Tech Division']);
        $department = Department::create(['name' => 'DevOps', 'division_id' => $division->id]);

        $this->actingAs($admin)->get('/admin/users/create')
            ->assertStatus(200)
            ->assertSee('Department');

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'John Programmer',
            'email' => 'john.p@example.com',
            'user_type' => 'regular',
            'password' => 'SecurePass123!',
            'division_id' => $division->id,
            'department_id' => $department->id,
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'name' => 'John Programmer',
            'email' => 'john.p@example.com',
            'division_id' => $division->id,
            'department_id' => $department->id,
        ]);
    }

    /**
     * Test that regular users cannot be created without a division_id.
     */
    public function test_regular_user_requires_division_to_be_created(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Post without division_id for a regular user
        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'No Division User',
            'email' => 'nodiv@example.com',
            'user_type' => 'regular',
            'password' => 'SecurePass123!',
        ]);

        $response->assertSessionHasErrors(['division_id']);
        $this->assertDatabaseMissing('users', [
            'email' => 'nodiv@example.com',
        ]);
    }

    /**
     * Test that regular users can be created with a division but without a department (department is optional).
     */
    public function test_regular_user_can_be_created_with_only_division(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $division = Division::create(['name' => 'Only Division Div']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Only Div User',
            'email' => 'onlydiv@example.com',
            'user_type' => 'regular',
            'password' => 'SecurePass123!',
            'division_id' => $division->id,
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'onlydiv@example.com',
            'division_id' => $division->id,
            'department_id' => null,
        ]);
    }

    /**
     * Test that the department must belong to the selected division.
     */
    public function test_selected_department_must_belong_to_the_selected_division(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $division1 = Division::create(['name' => 'Div 1']);
        $division2 = Division::create(['name' => 'Div 2']);
        $departmentFromDiv2 = Department::create(['name' => 'Dept of Div 2', 'division_id' => $division2->id]);

        // Attempt to create a user in Div 1 but with Dept of Div 2
        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Mismatched User',
            'email' => 'mismatch@example.com',
            'user_type' => 'regular',
            'password' => 'SecurePass123!',
            'division_id' => $division1->id,
            'department_id' => $departmentFromDiv2->id,
        ]);

        $response->assertSessionHasErrors(['department_id']);
        $this->assertDatabaseMissing('users', [
            'email' => 'mismatch@example.com',
        ]);
    }
}
