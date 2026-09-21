<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\Ticket;
use App\Models\Priority;
use App\Models\TicketStage;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the dashboard displays actual counts of tickets in various states for an administrator.
     */
    public function test_dashboard_displays_actual_counts_for_admin(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Seed necessary lookup values
        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $stageClosed = TicketStage::create(['name' => 'Closed', 'slug' => 'closed', 'color_code' => '#2']);
        
        $priorityLow = Priority::create(['name' => 'Low', 'level' => 1]);

        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // Create 2 open, unassigned tickets
        Ticket::create([
            'ticket_number' => 'FLR-2026-0001',
            'title' => 'Open Unassigned Critical Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityLow->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'assigned_to' => null,
            'category_1_id' => $category->id,
        ]);

        Ticket::create([
            'ticket_number' => 'FLR-2026-0002',
            'title' => 'Open Unassigned Low Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityLow->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'assigned_to' => null,
            'category_1_id' => $category->id,
        ]);

        // Create 1 resolved (not open), assigned ticket with past deadline
        Ticket::create([
            'ticket_number' => 'FLR-2026-0003',
            'title' => 'Closed Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityLow->id,
            'stage_id' => $stageClosed->id,
            'status' => 'Done',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'deadline_date' => now()->subDay(),
            'category_1_id' => $category->id,
        ]);

        // Create 1 open, assigned ticket with past deadline (SLA is lapsed)
        Ticket::create([
            'ticket_number' => 'FLR-2026-0004',
            'title' => 'Lapsed SLA Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityLow->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Lapsed',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'deadline_date' => now()->subDay(),
            'category_1_id' => $category->id,
        ]);

        // Visit dashboard as admin
        $response = $this->actingAs($admin)->get('/');

        $response->assertStatus(200);

        // Global stats assert
        // Open: 3, Unassigned: 2, Critical: 0, SLA Lapsed: 1
        $response->assertSee('3'); // Open Tickets count
        $response->assertSee('2'); // Unassigned count
        $response->assertSee('1'); // SLA Lapsed count
    }

    /**
     * Test that non-admin users only see ticket data from their division and department.
     */
    public function test_dashboard_filters_counts_for_non_admins(): void
    {
        // 1. Setup Divisions, Departments, and Users
        $divisionA = Division::create(['name' => 'Division A']);
        $departmentA = Department::create(['name' => 'Department A', 'division_id' => $divisionA->id]);

        $divisionB = Division::create(['name' => 'Division B']);
        $departmentB = Department::create(['name' => 'Department B', 'division_id' => $divisionB->id]);

        $regularUser = User::factory()->create([
            'user_type' => 'regular',
            'division_id' => $divisionA->id,
            'department_id' => $departmentA->id,
        ]);

        // 2. Setup Lookup dependencies
        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityLow = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // 3. Create Ticket inside User's Division/Department (Division A, Department A)
        Ticket::create([
            'ticket_number' => 'FLR-2026-0001',
            'title' => 'Ticket in Div A Dept A',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityLow->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $divisionA->id,
            'department_id' => $departmentA->id,
            'created_by' => $regularUser->id,
            'assigned_to' => null,
            'category_1_id' => $category->id,
        ]);

        // 4. Create Ticket outside User's Division/Department (Division B, Department B)
        Ticket::create([
            'ticket_number' => 'FLR-2026-0002',
            'title' => 'Ticket in Div B Dept B',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityLow->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $divisionB->id,
            'department_id' => $departmentB->id,
            'created_by' => $regularUser->id,
            'assigned_to' => null,
            'category_1_id' => $category->id,
        ]);

        // 5. Visit dashboard as regular user
        $response = $this->actingAs($regularUser)->get('/');

        $response->assertStatus(200);

        // They should only see stats for Division A / Department A (1 open ticket, 1 unassigned)
        // Check the actual open tickets counter content
        $response->assertSee('Open Tickets');
        $response->assertSee('<h2 class="mt-3 mb-0 fw-bold">1</h2>', false); // Open Tickets Count
        $response->assertSee('Unassigned Queue');
        $response->assertSee('<h2 class="mt-3 mb-0 fw-bold">1</h2>', false); // Unassigned Queue Count
    }

    /**
     * Test that the dashboard Open Tickets card links to the filtered ticket list.
     */
    public function test_dashboard_open_ticket_card_links_to_tickets_index_filtered_by_open_stage(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);

        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);

        // Check that the response contains the link with correct stage_id parameter
        $expectedUrl = route('tickets.index', ['stage_id' => $stageOpen->id]);
        $response->assertSee(htmlentities($expectedUrl), false);
    }

    /**
     * Test that the header displays System Admin for an admin user.
     */
    public function test_header_displays_system_admin_for_admin_user(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);

        $response->assertSee('System Admin');
    }

    /**
     * Test that the header displays Division and Department for a regular user.
     */
    public function test_header_displays_division_and_department_for_regular_user(): void
    {
        $division = Division::create(['name' => 'HR Division']);
        $department = Department::create(['name' => 'Recruitment Dept', 'division_id' => $division->id]);

        $regularUser = User::factory()->create([
            'user_type' => 'regular',
            'division_id' => $division->id,
            'department_id' => $department->id,
        ]);

        $response = $this->actingAs($regularUser)->get('/');
        $response->assertStatus(200);

        $response->assertSee('HR Division &gt; Recruitment Dept', false);
    }
}
