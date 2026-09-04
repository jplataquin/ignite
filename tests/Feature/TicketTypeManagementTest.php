<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test that guests cannot access ticket type management.
     */
    public function test_guests_cannot_access_ticket_type_management(): void
    {
        $response = $this->get('/admin/ticket-types');

        $response->assertRedirect('/login');
    }

    /**
     * Test that non-admin users cannot access ticket type management.
     */
    public function test_non_admins_cannot_access_ticket_type_management(): void
    {
        $user = User::factory()->create(['user_type' => 'regular']);

        $response = $this->actingAs($user)->get('/admin/ticket-types');

        $response->assertStatus(403);
    }

    /**
     * Test that admins can access ticket type management.
     */
    public function test_admins_can_access_ticket_type_management(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/ticket-types');

        $response->assertStatus(200);
        $response->assertSee('Ticket Types');
    }

    /**
     * Test that admins can view creation screen.
     */
    public function test_admins_can_view_ticket_type_creation_screen(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/ticket-types/create');

        $response->assertStatus(200);
        $response->assertSee('Create Ticket Type');
    }

    /**
     * Test that admins can store a new ticket type.
     */
    public function test_admins_can_store_new_ticket_type(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/ticket-types', [
            'name' => 'Urgent Request',
            'description' => 'For extreme requests',
            'threshold_days' => 2,
        ]);

        $response->assertRedirect('/admin/ticket-types');
        $this->assertDatabaseHas('ticket_types', [
            'name' => 'Urgent Request',
            'description' => 'For extreme requests',
            'threshold_days' => 2,
        ]);
    }

    /**
     * Test that admins can view edit screen.
     */
    public function test_admins_can_view_ticket_type_edit_screen(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type = TicketType::create(['name' => 'Original Type']);

        $response = $this->actingAs($admin)->get("/admin/ticket-types/{$type->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Ticket Type');
    }

    /**
     * Test that admins can update a ticket type.
     */
    public function test_admins_can_update_ticket_type(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type = TicketType::create(['name' => 'Old Type']);

        $response = $this->actingAs($admin)->put("/admin/ticket-types/{$type->id}", [
            'name' => 'New Name',
            'description' => 'Updated Description',
            'threshold_days' => 5,
        ]);

        $response->assertRedirect('/admin/ticket-types');
        $this->assertDatabaseHas('ticket_types', [
            'id' => $type->id,
            'name' => 'New Name',
            'description' => 'Updated Description',
            'threshold_days' => 5,
        ]);
    }

    /**
     * Test that admins can delete an unused ticket type.
     */
    public function test_admins_can_delete_unused_ticket_type(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type = TicketType::create(['name' => 'To Be Deleted']);

        $response = $this->actingAs($admin)->delete("/admin/ticket-types/{$type->id}");

        $response->assertRedirect('/admin/ticket-types');
        $this->assertDatabaseMissing('ticket_types', ['id' => $type->id]);
    }

    /**
     * Test that admins cannot delete a ticket type currently being used.
     */
    public function test_admins_cannot_delete_used_ticket_type(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type = TicketType::create(['name' => 'In-Use Type']);

        // Seed other resources to create a ticket
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priority = TicketPriority::create(['name' => 'Low', 'level' => 1]);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        Ticket::create([
            'ticket_number' => 'FLR-2026-8888',
            'title' => 'An active ticket',
            'ticket_type_id' => $type->id,
            'priority_id' => $priority->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/ticket-types/{$type->id}");

        $response->assertRedirect('/admin/ticket-types');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('ticket_types', ['id' => $type->id]);
    }
}
