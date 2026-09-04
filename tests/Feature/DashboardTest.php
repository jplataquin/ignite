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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the dashboard displays actual counts of tickets in various states.
     */
    public function test_dashboard_displays_actual_counts(): void
    {
        $user = User::factory()->create();

        // Seed necessary lookup values
        $statusOpen = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $statusClosed = TicketStatus::create(['name' => 'Closed', 'slug' => 'closed', 'color_code' => '#2']);
        
        $priorityLow = TicketPriority::create(['name' => 'Low', 'level' => 1]);
        $priorityCritical = TicketPriority::create(['name' => 'Critical', 'level' => 4]);

        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // Create 2 open, unassigned tickets (one of which has Critical priority)
        Ticket::create([
            'ticket_number' => 'FLR-2026-0001',
            'title' => 'Open Unassigned Critical Ticket',
            'ticket_type_id' => $type->id,
            'priority_id' => $priorityCritical->id,
            'status_id' => $statusOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $user->id,
            'assigned_to' => null,
            'category_1_id' => $category->id,
        ]);

        Ticket::create([
            'ticket_number' => 'FLR-2026-0002',
            'title' => 'Open Unassigned Low Ticket',
            'ticket_type_id' => $type->id,
            'priority_id' => $priorityLow->id,
            'status_id' => $statusOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $user->id,
            'assigned_to' => null,
            'category_1_id' => $category->id,
        ]);

        // Create 1 resolved (not open), assigned ticket with past deadline (SLA is not lapsed because it's resolved/closed)
        Ticket::create([
            'ticket_number' => 'FLR-2026-0003',
            'title' => 'Closed Ticket',
            'ticket_type_id' => $type->id,
            'priority_id' => $priorityLow->id,
            'status_id' => $statusClosed->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'deadline_date' => now()->subDay(),
            'category_1_id' => $category->id,
        ]);

        // Create 1 open, assigned ticket with past deadline (SLA is lapsed)
        Ticket::create([
            'ticket_number' => 'FLR-2026-0004',
            'title' => 'Lapsed SLA Ticket',
            'ticket_type_id' => $type->id,
            'priority_id' => $priorityLow->id,
            'status_id' => $statusOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'deadline_date' => now()->subDay(),
            'category_1_id' => $category->id,
        ]);

        // Visit dashboard
        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);

        // Assert that actual counts are displayed
        // Open tickets: 3 (FLR-0001, FLR-0002, FLR-0004)
        // Unassigned Queue: 2 (FLR-0001, FLR-0002)
        // Critical Alerts: 1 (FLR-0001)
        // SLA Lapsed: 1 (FLR-0004)
        $response->assertSee('3'); // Open Tickets count
        $response->assertSee('2'); // Unassigned count
        $response->assertSee('1'); // Critical Alert & SLA Lapsed count
    }
}
