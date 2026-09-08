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
            'department_id' => $department->id,
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'name' => 'John Programmer',
            'email' => 'john.p@example.com',
            'department_id' => $department->id,
        ]);
    }
}
