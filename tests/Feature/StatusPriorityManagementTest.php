<?php

namespace Tests\Feature;

use App\Models\TicketStatus;
use App\Models\TicketPriority;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Division;
use App\Models\Department;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusPriorityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test guest access restrictions.
     */
    public function test_guests_cannot_access_status_and_priority_management(): void
    {
        $this->get('/admin/ticket-statuses')->assertRedirect('/login');
        $this->get('/admin/ticket-priorities')->assertRedirect('/login');
    }

    /**
     * Test non-admin access restrictions.
     */
    public function test_non_admins_cannot_access_status_and_priority_management(): void
    {
        $user = User::factory()->create(['user_type' => 'regular']);

        $this->actingAs($user)->get('/admin/ticket-statuses')->assertStatus(403);
        $this->actingAs($user)->get('/admin/ticket-priorities')->assertStatus(403);
    }

    /**
     * Test ticket statuses CRUD operations.
     */
    public function test_ticket_status_crud_operations(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Create Status
        $this->actingAs($admin)->post('/admin/ticket-statuses', [
            'name' => 'Awaiting Verification',
            'color_code' => '#FFAA00',
        ])->assertRedirect('/admin/ticket-statuses');

        $this->assertDatabaseHas('ticket_statuses', [
            'name' => 'Awaiting Verification',
            'slug' => 'awaiting-verification',
            'color_code' => '#FFAA00',
        ]);

        $status = TicketStatus::where('slug', 'awaiting-verification')->first();

        // Edit View
        $this->actingAs($admin)->get("/admin/ticket-statuses/{$status->id}/edit")
            ->assertStatus(200)
            ->assertSee('Awaiting Verification');

        // Update Status
        $this->actingAs($admin)->put("/admin/ticket-statuses/{$status->id}", [
            'name' => 'Awaiting Customer Verification',
            'color_code' => '#00AAFF',
        ])->assertRedirect('/admin/ticket-statuses');

        $this->assertDatabaseHas('ticket_statuses', [
            'id' => $status->id,
            'name' => 'Awaiting Customer Verification',
            'slug' => 'awaiting-customer-verification',
            'color_code' => '#00AAFF',
        ]);

        // Delete Status
        $this->actingAs($admin)->delete("/admin/ticket-statuses/{$status->id}")
            ->assertRedirect('/admin/ticket-statuses');

        $this->assertDatabaseMissing('ticket_statuses', [
            'id' => $status->id,
        ]);
    }

    /**
     * Test that core statuses cannot be deleted.
     */
    public function test_core_statuses_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $openStatus = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#123456']);

        $this->actingAs($admin)->delete("/admin/ticket-statuses/{$openStatus->id}")
            ->assertRedirect('/admin/ticket-statuses')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('ticket_statuses', ['id' => $openStatus->id]);
    }

    /**
     * Test ticket priorities CRUD operations.
     */
    public function test_ticket_priority_crud_operations(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Create Priority
        $this->actingAs($admin)->post('/admin/ticket-priorities', [
            'name' => 'Backlog Priority',
            'level' => 10,
        ])->assertRedirect('/admin/ticket-priorities');

        $this->assertDatabaseHas('ticket_priorities', [
            'name' => 'Backlog Priority',
            'level' => 10,
        ]);

        $priority = TicketPriority::where('level', 10)->first();

        // Edit View
        $this->actingAs($admin)->get("/admin/ticket-priorities/{$priority->id}/edit")
            ->assertStatus(200)
            ->assertSee('Backlog Priority');

        // Update Priority
        $this->actingAs($admin)->put("/admin/ticket-priorities/{$priority->id}", [
            'name' => 'Someday Priority',
            'level' => 12,
        ])->assertRedirect('/admin/ticket-priorities');

        $this->assertDatabaseHas('ticket_priorities', [
            'id' => $priority->id,
            'name' => 'Someday Priority',
            'level' => 12,
        ]);

        // Delete Priority
        $this->actingAs($admin)->delete("/admin/ticket-priorities/{$priority->id}")
            ->assertRedirect('/admin/ticket-priorities');

        $this->assertDatabaseMissing('ticket_priorities', [
            'id' => $priority->id,
        ]);
    }
}
