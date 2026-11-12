<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\Location;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketStage;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $moderator;

    private User $regularUser;

    private Division $divisionA;

    private Division $divisionB;

    private Department $deptA1;

    private Department $deptA2;

    private Department $deptB1;

    private Location $locationMain;

    private Location $locationBranch;

    private TicketType $typeIncident;

    private TicketType $typeRequest;

    private Priority $priorityLow;

    private Priority $priorityCritical;

    private TicketStage $stageOpen;

    private Category $category1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'user_type' => 'admin',
            'is_approved' => true,
        ]);

        $this->moderator = User::factory()->create([
            'name' => 'Moderator User',
            'user_type' => 'moderator',
            'is_approved' => true,
        ]);

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'user_type' => 'regular',
            'is_approved' => true,
        ]);

        $this->divisionA = Division::create(['name' => 'Information Technology']);
        $this->divisionB = Division::create(['name' => 'Finance & Accounting']);

        $this->deptA1 = Department::create(['name' => 'IT Support', 'division_id' => $this->divisionA->id]);
        $this->deptA2 = Department::create(['name' => 'Infrastructure', 'division_id' => $this->divisionA->id]);
        $this->deptB1 = Department::create(['name' => 'Payroll', 'division_id' => $this->divisionB->id]);

        $this->locationMain = Location::create(['name' => 'Main Headquarters']);
        $this->locationBranch = Location::create(['name' => 'Branch Office']);

        $this->typeIncident = TicketType::create(['name' => 'Incident', 'code' => 'INC']);
        $this->typeRequest = TicketType::create(['name' => 'Service Request', 'code' => 'REQ']);

        $this->priorityLow = Priority::create(['name' => 'Low', 'level' => 1]);
        $this->priorityCritical = Priority::create(['name' => 'Critical', 'level' => 3]);

        $this->stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#F59E0B']);

        $this->category1 = Category::create([
            'name' => 'General Hardware',
            'ticket_type_id' => $this->typeIncident->id,
        ]);
    }

    public function test_guests_are_redirected_to_login_when_accessing_reports(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_are_forbidden_from_accessing_reports(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('reports.index'));

        $response->assertStatus(403);
    }

    public function test_administrators_can_access_reports_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertSee('Ticket Reports and Analytics');
    }

    public function test_moderators_can_access_reports_page(): void
    {
        $response = $this->actingAs($this->moderator)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertSee('Ticket Reports and Analytics');
    }

    public function test_reports_displays_ticket_counts_per_type_location_priority_and_status_grouped_by_division_and_department(): void
    {
        // Ticket 1: Division A -> Dept A1, Type Incident, Location Main, Priority Low, Status Valid
        Ticket::create([
            'ticket_number' => 'INC-2026-0001',
            'title' => 'Laptop screen broken',
            'ticket_type_id' => $this->typeIncident->id,
            'priority_option_id' => $this->priorityLow->id,
            'stage_id' => $this->stageOpen->id,
            'status' => 'Valid',
            'division_id' => $this->divisionA->id,
            'department_id' => $this->deptA1->id,
            'location_id' => $this->locationMain->id,
            'created_by' => $this->admin->id,
            'category_1_id' => $this->category1->id,
        ]);

        // Ticket 2: Division A -> Dept A1, Type Request, Location Main, Priority Critical, Status Done
        Ticket::create([
            'ticket_number' => 'REQ-2026-0002',
            'title' => 'Software license purchase',
            'ticket_type_id' => $this->typeRequest->id,
            'priority_option_id' => $this->priorityCritical->id,
            'stage_id' => $this->stageOpen->id,
            'status' => 'Done',
            'division_id' => $this->divisionA->id,
            'department_id' => $this->deptA1->id,
            'location_id' => $this->locationMain->id,
            'created_by' => $this->admin->id,
            'category_1_id' => $this->category1->id,
        ]);

        // Ticket 3: Division A -> Dept A2, Type Incident, Location Branch, Priority Critical, Status Lapsed
        Ticket::create([
            'ticket_number' => 'INC-2026-0003',
            'title' => 'Server room AC failure',
            'ticket_type_id' => $this->typeIncident->id,
            'priority_option_id' => $this->priorityCritical->id,
            'stage_id' => $this->stageOpen->id,
            'status' => 'Lapsed',
            'division_id' => $this->divisionA->id,
            'department_id' => $this->deptA2->id,
            'location_id' => $this->locationBranch->id,
            'created_by' => $this->admin->id,
            'category_1_id' => $this->category1->id,
        ]);

        // Ticket 4: Division B -> Dept B1, Type Request, Location Branch, Priority Low, Status Valid
        Ticket::create([
            'ticket_number' => 'REQ-2026-0004',
            'title' => 'Payroll export report',
            'ticket_type_id' => $this->typeRequest->id,
            'priority_option_id' => $this->priorityLow->id,
            'stage_id' => $this->stageOpen->id,
            'status' => 'Valid',
            'division_id' => $this->divisionB->id,
            'department_id' => $this->deptB1->id,
            'location_id' => $this->locationBranch->id,
            'created_by' => $this->admin->id,
            'category_1_id' => $this->category1->id,
        ]);

        $response = $this->actingAs($this->moderator)->get(route('reports.index'));

        $response->assertStatus(200);

        // Check overall metrics in view data
        $reportData = $response->viewData('reportData');
        $this->assertEquals(4, $reportData['overall']['total']);

        // Check Division A has 3 tickets, Division B has 1 ticket
        $divA = collect($reportData['divisions'])->firstWhere('id', $this->divisionA->id);
        $this->assertNotNull($divA);
        $this->assertEquals(3, $divA['total_tickets']);

        $divB = collect($reportData['divisions'])->firstWhere('id', $this->divisionB->id);
        $this->assertNotNull($divB);
        $this->assertEquals(1, $divB['total_tickets']);

        // Check Department A1 in Division A has 2 tickets
        $deptA1Data = collect($divA['departments'])->firstWhere('id', $this->deptA1->id);
        $this->assertNotNull($deptA1Data);
        $this->assertEquals(2, $deptA1Data['total_tickets']);

        // Check Dept A1 Breakdown by Type: 1 Incident, 1 Request
        $incTypeInDeptA1 = collect($deptA1Data['breakdown']['ticket_types'])->firstWhere('id', $this->typeIncident->id);
        $this->assertEquals(1, $incTypeInDeptA1['count']);
        $reqTypeInDeptA1 = collect($deptA1Data['breakdown']['ticket_types'])->firstWhere('id', $this->typeRequest->id);
        $this->assertEquals(1, $reqTypeInDeptA1['count']);

        // Check Dept A1 Breakdown by Location: 2 Main Headquarters, 0 Branch
        $mainLocInDeptA1 = collect($deptA1Data['breakdown']['locations'])->firstWhere('id', $this->locationMain->id);
        $this->assertEquals(2, $mainLocInDeptA1['count']);
        $branchLocInDeptA1 = collect($deptA1Data['breakdown']['locations'])->firstWhere('id', $this->locationBranch->id);
        $this->assertEquals(0, $branchLocInDeptA1['count']);

        // Check Dept A1 Breakdown by Priority: 1 Low, 1 Critical
        $lowPrioInDeptA1 = collect($deptA1Data['breakdown']['priorities'])->firstWhere('id', $this->priorityLow->id);
        $this->assertEquals(1, $lowPrioInDeptA1['count']);
        $critPrioInDeptA1 = collect($deptA1Data['breakdown']['priorities'])->firstWhere('id', $this->priorityCritical->id);
        $this->assertEquals(1, $critPrioInDeptA1['count']);

        // Check Dept A1 Breakdown by Status: 1 Valid, 1 Done, 0 Lapsed
        $validStatusInDeptA1 = collect($deptA1Data['breakdown']['statuses'])->firstWhere('status', 'Valid');
        $this->assertEquals(1, $validStatusInDeptA1['count']);
        $doneStatusInDeptA1 = collect($deptA1Data['breakdown']['statuses'])->firstWhere('status', 'Done');
        $this->assertEquals(1, $doneStatusInDeptA1['count']);
        $lapsedStatusInDeptA1 = collect($deptA1Data['breakdown']['statuses'])->firstWhere('status', 'Lapsed');
        $this->assertEquals(0, $lapsedStatusInDeptA1['count']);

        // Verify HTML renders division and department names and table headers
        $response->assertSee('Information Technology');
        $response->assertSee('Finance & Accounting');
        $response->assertSee('IT Support');
        $response->assertSee('Infrastructure');
        $response->assertSee('Payroll');
        $response->assertSee('Ticket Type');
        $response->assertSee('Location');
        $response->assertSee('Priority');
        $response->assertSee('Status');
        $response->assertSee('Count');

        // Check Dept A1 rows contain 2 distinct combinations
        $this->assertCount(2, $deptA1Data['rows']);
        $incidentRow = collect($deptA1Data['rows'])->firstWhere('ticket_type', 'Incident');
        $this->assertEquals('Main Headquarters', $incidentRow['location']);
        $this->assertEquals('Low', $incidentRow['priority']);
        $this->assertEquals('Valid', $incidentRow['status']);
        $this->assertEquals(1, $incidentRow['count']);
    }

    public function test_reports_filtering_by_division_and_department(): void
    {
        Ticket::create([
            'ticket_number' => 'INC-2026-0001',
            'title' => 'Division A Ticket',
            'ticket_type_id' => $this->typeIncident->id,
            'priority_option_id' => $this->priorityLow->id,
            'stage_id' => $this->stageOpen->id,
            'status' => 'Valid',
            'division_id' => $this->divisionA->id,
            'department_id' => $this->deptA1->id,
            'location_id' => $this->locationMain->id,
            'created_by' => $this->admin->id,
            'category_1_id' => $this->category1->id,
        ]);

        Ticket::create([
            'ticket_number' => 'INC-2026-0002',
            'title' => 'Division B Ticket',
            'ticket_type_id' => $this->typeIncident->id,
            'priority_option_id' => $this->priorityLow->id,
            'stage_id' => $this->stageOpen->id,
            'status' => 'Valid',
            'division_id' => $this->divisionB->id,
            'department_id' => $this->deptB1->id,
            'location_id' => $this->locationBranch->id,
            'created_by' => $this->admin->id,
            'category_1_id' => $this->category1->id,
        ]);

        // Filter by Division A only
        $response = $this->actingAs($this->admin)->get(route('reports.index', [
            'division_id' => $this->divisionA->id,
        ]));

        $response->assertStatus(200);
        $reportData = $response->viewData('reportData');

        $this->assertEquals(1, $reportData['overall']['total']);
        $this->assertCount(1, $reportData['divisions']);
        $this->assertEquals($this->divisionA->id, $reportData['divisions'][0]['id']);

        // Filter by Division A and Department A2 (which has 0 tickets)
        $responseDept = $this->actingAs($this->admin)->get(route('reports.index', [
            'division_id' => $this->divisionA->id,
            'department_id' => $this->deptA2->id,
        ]));

        $responseDept->assertStatus(200);
        $reportDataDept = $responseDept->viewData('reportData');
        $this->assertEquals(0, $reportDataDept['overall']['total']);
    }

    public function test_reports_filtering_by_ticket_type_location_priority_and_status(): void
    {
        Ticket::create([
            'ticket_number' => 'INC-2026-0001',
            'title' => 'Critical Incident in Main',
            'ticket_type_id' => $this->typeIncident->id,
            'priority_option_id' => $this->priorityCritical->id,
            'stage_id' => $this->stageOpen->id,
            'status' => 'Valid',
            'division_id' => $this->divisionA->id,
            'department_id' => $this->deptA1->id,
            'location_id' => $this->locationMain->id,
            'created_by' => $this->admin->id,
            'category_1_id' => $this->category1->id,
        ]);

        Ticket::create([
            'ticket_number' => 'REQ-2026-0002',
            'title' => 'Low Request in Branch',
            'ticket_type_id' => $this->typeRequest->id,
            'priority_option_id' => $this->priorityLow->id,
            'stage_id' => $this->stageOpen->id,
            'status' => 'Done',
            'division_id' => $this->divisionA->id,
            'department_id' => $this->deptA1->id,
            'location_id' => $this->locationBranch->id,
            'created_by' => $this->admin->id,
            'category_1_id' => $this->category1->id,
        ]);

        // Filter by Status = Valid
        $responseStatus = $this->actingAs($this->moderator)->get(route('reports.index', ['status' => 'Valid']));
        $responseStatus->assertStatus(200);
        $this->assertEquals(1, $responseStatus->viewData('reportData')['overall']['total']);

        // Filter by Priority = Critical
        $responsePrio = $this->actingAs($this->moderator)->get(route('reports.index', ['priority_id' => $this->priorityCritical->id]));
        $responsePrio->assertStatus(200);
        $this->assertEquals(1, $responsePrio->viewData('reportData')['overall']['total']);

        // Filter by Location = Main Headquarters
        $responseLoc = $this->actingAs($this->moderator)->get(route('reports.index', ['location_id' => $this->locationMain->id]));
        $responseLoc->assertStatus(200);
        $this->assertEquals(1, $responseLoc->viewData('reportData')['overall']['total']);

        // Filter by Ticket Type = Service Request
        $responseType = $this->actingAs($this->moderator)->get(route('reports.index', ['ticket_type_id' => $this->typeRequest->id]));
        $responseType->assertStatus(200);
        $this->assertEquals(1, $responseType->viewData('reportData')['overall']['total']);
    }

    public function test_reports_export_csv_is_accessible_to_admin_and_moderator(): void
    {
        Ticket::create([
            'ticket_number' => 'INC-2026-0001',
            'title' => 'Sample Incident',
            'ticket_type_id' => $this->typeIncident->id,
            'priority_option_id' => $this->priorityLow->id,
            'stage_id' => $this->stageOpen->id,
            'status' => 'Valid',
            'division_id' => $this->divisionA->id,
            'department_id' => $this->deptA1->id,
            'location_id' => $this->locationMain->id,
            'created_by' => $this->admin->id,
            'category_1_id' => $this->category1->id,
        ]);

        // Admin export
        $adminResponse = $this->actingAs($this->admin)->get(route('reports.export'));
        $adminResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $adminResponse->headers->get('Content-Type'));

        // Moderator export
        $modResponse = $this->actingAs($this->moderator)->get(route('reports.export'));
        $modResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $modResponse->headers->get('Content-Type'));

        // Regular user denied
        $regResponse = $this->actingAs($this->regularUser)->get(route('reports.export'));
        $regResponse->assertStatus(403);
    }

    public function test_reports_navigation_link_displayed_for_admin_and_moderator_but_not_regular_user(): void
    {
        // Admin sees Reports link
        $adminResponse = $this->actingAs($this->admin)->get(route('dashboard'));
        $adminResponse->assertSee(route('reports.index'));
        $adminResponse->assertSee('Reports');

        // Moderator sees Reports link
        $modResponse = $this->actingAs($this->moderator)->get(route('dashboard'));
        $modResponse->assertSee(route('reports.index'));
        $modResponse->assertSee('Reports');

        // Regular user does not see Reports link
        $regResponse = $this->actingAs($this->regularUser)->get(route('dashboard'));
        $regResponse->assertDontSee(route('reports.index'));
    }
}
