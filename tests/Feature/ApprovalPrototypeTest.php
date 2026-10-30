<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\Location;
use App\Models\Priority;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\TicketStage;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalPrototypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([PreventRequestForgery::class]);
    }

    private function createDependencies(): array
    {
        $division = Division::create(['name' => 'Operations']);
        $otherDivision = Division::create(['name' => 'Finance']);
        $department = Department::create(['name' => 'Clearance Dept', 'division_id' => $division->id]);
        $location = Location::create(['name' => 'HQ Main']);
        $priority = Priority::create(['name' => 'High', 'level' => 2]);

        $approvalType = TicketType::create([
            'name' => 'Audit Clearance',
            'code' => 'AUD',
            'prototype' => 'approval_clearance',
            'description' => 'Multi-user clearance checklist',
        ]);

        $category = Category::create([
            'name' => 'Sign-off Audit',
            'ticket_type_id' => $approvalType->id,
        ]);

        $creator = User::factory()->create([
            'user_type' => 'regular',
            'is_approved' => true,
            'division_id' => $division->id,
            'department_id' => $department->id,
        ]);

        $role = Role::create(['name' => 'Clearance Officer', 'slug' => 'clearance-officer']);
        $role->ticketTypes()->attach($approvalType->id);
        $creator->roles()->attach($role->id);

        $approver1 = User::factory()->create([
            'user_type' => 'regular',
            'is_approved' => true,
            'division_id' => $division->id,
            'department_id' => $department->id,
        ]);

        $approver2 = User::factory()->create([
            'user_type' => 'regular',
            'is_approved' => true,
            'division_id' => $otherDivision->id, // in a completely different division!
        ]);

        return compact('division', 'otherDivision', 'department', 'location', 'priority', 'approvalType', 'category', 'creator', 'approver1', 'approver2');
    }

    /**
     * Test creating an Approval/Clearance ticket with sequential approvers without division, department, and location.
     */
    public function test_can_create_approval_clearance_ticket_with_approvers(): void
    {
        $d = $this->createDependencies();

        // Approval clearance prototype does not require division, department, or location
        $response = $this->actingAs($d['creator'])->post('/tickets', [
            'title' => 'Year-end Clearance',
            'description' => 'Please review and sign off on clearance.',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'category_1_id' => $d['category']->id,
            'approval_mode' => 'sequential',
            'approver_ids' => [$d['approver1']->id, $d['approver2']->id],
        ]);

        $response->assertRedirect();

        $ticket = Ticket::where('title', 'Year-end Clearance')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('sequential', $ticket->approval_mode);
        $this->assertEquals('pending-approval', $ticket->stage->slug);
        $this->assertNull($ticket->division_id);
        $this->assertNull($ticket->department_id);
        $this->assertNull($ticket->location_id);
        $this->assertEquals(2, $ticket->approvals()->count());

        $firstApproval = $ticket->approvals()->where('sequence_order', 1)->first();
        $this->assertEquals($d['approver1']->id, $firstApproval->user_id);
        $this->assertEquals('pending', $firstApproval->status);

        $secondApproval = $ticket->approvals()->where('sequence_order', 2)->first();
        $this->assertEquals($d['approver2']->id, $secondApproval->user_id);
        $this->assertEquals('pending', $secondApproval->status);
    }

    /**
     * Test ticket create view contains division-department-location section for prototype toggling.
     */
    public function test_ticket_create_view_contains_division_department_location_section(): void
    {
        $d = $this->createDependencies();

        $response = $this->actingAs($d['creator'])->get('/tickets/create');
        $response->assertStatus(200);
        $response->assertSee('id="division-department-location-section"', false);
    }

    /**
     * Test dedicated views are loaded for approval clearance prototype tickets.
     */
    public function test_dedicated_approval_views_are_returned_for_show_and_edit(): void
    {
        $d = $this->createDependencies();

        $ticket = Ticket::create([
            'ticket_number' => 'AUD-2026-0001',
            'title' => 'Inspection Clearance',
            'description' => 'Clearance notes',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'stage_id' => TicketStage::where('slug', 'pending-approval')->first()->id,
            'status' => 'Valid',
            'approval_mode' => 'sequential',
            'division_id' => $d['division']->id,
            'department_id' => $d['department']->id,
            'location_id' => $d['location']->id,
            'category_1_id' => $d['category']->id,
            'created_by' => $d['creator']->id,
        ]);

        $approval = TicketApproval::create([
            'ticket_id' => $ticket->id,
            'user_id' => $d['approver1']->id,
            'sequence_order' => 1,
            'status' => 'pending',
        ]);

        // Show view
        $showResponse = $this->actingAs($d['creator'])->get("/tickets/{$ticket->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertViewIs('tickets.show-approval');
        $showResponse->assertSee('Clearance Sign-off Checklist');
        $showResponse->assertSee('Copy Link');
        $showResponse->assertSee('btn-copy-ticket-link');

        // Edit view
        $editResponse = $this->actingAs($d['creator'])->get("/tickets/{$ticket->id}/edit");
        $editResponse->assertStatus(200);
        $editResponse->assertViewIs('tickets.edit-approval');
        $editResponse->assertSee('Edit Clearance:');
    }

    /**
     * Test approver from another division can view the ticket in tab=all.
     */
    public function test_approver_outside_division_can_view_ticket(): void
    {
        $d = $this->createDependencies();

        $ticket = Ticket::create([
            'ticket_number' => 'AUD-2026-0002',
            'title' => 'Finance Cross-Division Clearance',
            'description' => 'Needs finance approval',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'stage_id' => TicketStage::where('slug', 'pending-approval')->first()->id,
            'status' => 'Valid',
            'approval_mode' => 'parallel',
            'division_id' => $d['division']->id,
            'location_id' => $d['location']->id,
            'category_1_id' => $d['category']->id,
            'created_by' => $d['creator']->id,
        ]);

        TicketApproval::create([
            'ticket_id' => $ticket->id,
            'user_id' => $d['approver2']->id, // In other division
            'sequence_order' => 1,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($d['approver2'])->get('/tickets?tab=all');
        $response->assertStatus(200);
        $response->assertSee('Finance Cross-Division Clearance');
    }

    /**
     * Test sequential approval logic and auto-completion.
     */
    public function test_sequential_approval_enforces_order_and_completes_when_all_sign_off(): void
    {
        $d = $this->createDependencies();

        $pendingStage = TicketStage::where('slug', 'pending-approval')->first();

        $ticket = Ticket::create([
            'ticket_number' => 'AUD-2026-0003',
            'title' => 'Multi-step Clearance',
            'description' => 'Sequential approval test',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'stage_id' => $pendingStage->id,
            'status' => 'Valid',
            'approval_mode' => 'sequential',
            'division_id' => $d['division']->id,
            'location_id' => $d['location']->id,
            'category_1_id' => $d['category']->id,
            'created_by' => $d['creator']->id,
        ]);

        $approval1 = TicketApproval::create([
            'ticket_id' => $ticket->id,
            'user_id' => $d['approver1']->id,
            'sequence_order' => 1,
            'status' => 'pending',
        ]);

        $approval2 = TicketApproval::create([
            'ticket_id' => $ticket->id,
            'user_id' => $d['approver2']->id,
            'sequence_order' => 2,
            'status' => 'pending',
        ]);

        // 1. Approver 2 tries to sign off early -> should fail
        $earlyResponse = $this->actingAs($d['approver2'])->post("/tickets/{$ticket->id}/approvals/{$approval2->id}/approve", [
            'comment' => 'Approving out of turn',
        ]);
        $earlyResponse->assertRedirect();
        $earlyResponse->assertSessionHas('error');
        $this->assertEquals('pending', $approval2->fresh()->status);

        // 2. Approver 1 signs off -> success
        $appr1Response = $this->actingAs($d['approver1'])->post("/tickets/{$ticket->id}/approvals/{$approval1->id}/approve", [
            'comment' => 'Step 1 complete',
        ]);
        $appr1Response->assertRedirect();
        $appr1Response->assertSessionHas('success');
        $this->assertEquals('approved', $approval1->fresh()->status);
        $this->assertEquals('pending-approval', $ticket->fresh()->stage->slug);

        // 3. Approver 2 now signs off -> final approval transitions stage to approved
        $appr2Response = $this->actingAs($d['approver2'])->post("/tickets/{$ticket->id}/approvals/{$approval2->id}/approve", [
            'comment' => 'Step 2 final sign-off',
        ]);
        $appr2Response->assertRedirect();
        $appr2Response->assertSessionHas('success');
        $this->assertEquals('approved', $approval2->fresh()->status);

        $freshTicket = $ticket->fresh();
        $this->assertEquals('approved', $freshTicket->stage->slug);
        $this->assertEquals('Done', $freshTicket->status);
    }

    /**
     * Test rejection immediately marks clearance as rejected and Done.
     */
    public function test_rejection_marks_clearance_as_rejected(): void
    {
        $d = $this->createDependencies();

        $pendingStage = TicketStage::where('slug', 'pending-approval')->first();

        $ticket = Ticket::create([
            'ticket_number' => 'AUD-2026-0004',
            'title' => 'Failed Clearance',
            'description' => 'Will be rejected',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'stage_id' => $pendingStage->id,
            'status' => 'Valid',
            'approval_mode' => 'parallel',
            'division_id' => $d['division']->id,
            'location_id' => $d['location']->id,
            'category_1_id' => $d['category']->id,
            'created_by' => $d['creator']->id,
        ]);

        $approval1 = TicketApproval::create([
            'ticket_id' => $ticket->id,
            'user_id' => $d['approver1']->id,
            'sequence_order' => 1,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($d['approver1'])->post("/tickets/{$ticket->id}/approvals/{$approval1->id}/reject", [
            'comment' => 'Missing safety documents',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('rejected', $approval1->fresh()->status);

        $freshTicket = $ticket->fresh();
        $this->assertEquals('rejected', $freshTicket->stage->slug);
        $this->assertEquals('Done', $freshTicket->status);
    }

    /**
     * Test admin can configure Approval & Clearance checklist in template.
     */
    public function test_admin_can_configure_approval_checklist_in_ticket_type_template(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $approverUser = User::factory()->create(['name' => 'Department Head']);

        $template = [
            'approval' => [
                'has_mode' => true,
                'mode' => 'parallel',
                'mode_locked' => true,
                'has_approvers' => true,
                'approvers' => [
                    ['id' => $approverUser->id, 'name' => $approverUser->name, 'sequence_order' => 1],
                ],
                'approvers_locked' => true,
            ],
        ];

        $response = $this->actingAs($admin)->post('/admin/ticket-types', [
            'name' => 'Clearance With Template',
            'code' => 'CWT',
            'prototype' => 'approval_clearance',
            'template_json' => json_encode($template),
        ]);

        $response->assertRedirect('/admin/ticket-types');

        $type = TicketType::where('name', 'Clearance With Template')->first();
        $this->assertNotNull($type);
        $this->assertEquals($template, $type->template_json);

        // Verify edit view renders the saved template and approval section
        $editResponse = $this->actingAs($admin)->get("/admin/ticket-types/{$type->id}/edit");
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Approval & Clearance Checklist', false);
        $editResponse->assertSee('id="tpl-approval-checklist-section"', false);
    }

    /**
     * Test locked approval checklist template enforces approval_mode and approvers on ticket store.
     */
    public function test_locked_approval_checklist_template_is_enforced_on_ticket_creation(): void
    {
        $d = $this->createDependencies();

        $enforcedApprover = User::factory()->create(['name' => 'Mandatory Auditor']);
        $otherUser = User::factory()->create(['name' => 'Attempted Rogue Approver']);

        $d['approvalType']->update([
            'template_json' => [
                'fields' => [
                    'title' => ['value' => 'Templated Clearance Request', 'locked' => true],
                ],
                'approval' => [
                    'has_mode' => true,
                    'mode' => 'parallel',
                    'mode_locked' => true,
                    'has_approvers' => true,
                    'approvers' => [
                        ['id' => $enforcedApprover->id, 'name' => $enforcedApprover->name, 'sequence_order' => 1, 'signoff_description' => 'Mandatory Audit Sign-off'],
                    ],
                    'approvers_locked' => true,
                ],
            ],
        ]);

        // Submit form with tampered mode, approvers, and signoff descriptions
        $response = $this->actingAs($d['creator'])->post('/tickets', [
            'title' => 'Tampered Title',
            'description' => 'Clearance notes',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'category_1_id' => $d['category']->id,
            'approval_mode' => 'sequential', // Tampered! Should be overwritten with parallel
            'approver_ids' => [$otherUser->id], // Tampered! Should be overwritten with enforcedApprover
            'signoff_descriptions' => ['Tampered Description'],
        ]);

        $response->assertRedirect();

        $ticket = Ticket::where('ticket_type_id', $d['approvalType']->id)->latest('id')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('Templated Clearance Request', $ticket->title);
        $this->assertEquals('parallel', $ticket->approval_mode);
        $this->assertEquals(1, $ticket->approvals()->count());

        $approval = $ticket->approvals()->first();
        $this->assertEquals($enforcedApprover->id, $approval->user_id);
        $this->assertEquals('Mandatory Audit Sign-off', $approval->signoff_description);
    }

    /**
     * Test user can create ticket with sign-off descriptions for each person in checklist sequence.
     */
    public function test_user_can_create_ticket_with_signoff_descriptions_for_each_approver(): void
    {
        $d = $this->createDependencies();

        $approverA = User::factory()->create(['name' => 'IT Inspector']);
        $approverB = User::factory()->create(['name' => 'Finance Auditor']);

        $response = $this->actingAs($d['creator'])->post('/tickets', [
            'title' => 'Employee Exit Clearance',
            'description' => 'Offboarding checklist',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'category_1_id' => $d['category']->id,
            'approval_mode' => 'sequential',
            'approver_ids' => [$approverA->id, $approverB->id],
            'signoff_descriptions' => ['Laptop & Asset Return', 'Final Payroll Clearance'],
        ]);

        $response->assertRedirect();

        $ticket = Ticket::where('title', 'Employee Exit Clearance')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals(2, $ticket->approvals()->count());

        $approvals = $ticket->approvals()->orderBy('sequence_order')->get();
        $this->assertEquals($approverA->id, $approvals[0]->user_id);
        $this->assertEquals('Laptop & Asset Return', $approvals[0]->signoff_description);
        $this->assertEquals(1, $approvals[0]->sequence_order);

        $this->assertEquals($approverB->id, $approvals[1]->user_id);
        $this->assertEquals('Final Payroll Clearance', $approvals[1]->signoff_description);
        $this->assertEquals(2, $approvals[1]->sequence_order);

        // Verify the show and edit views render the sign-off descriptions
        $showResponse = $this->actingAs($d['creator'])->get("/tickets/{$ticket->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Laptop &amp; Asset Return', false);
        $showResponse->assertSee('Final Payroll Clearance');

        $editResponse = $this->actingAs($d['creator'])->get("/tickets/{$ticket->id}/edit");
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Laptop &amp; Asset Return', false);
        $editResponse->assertSee('Final Payroll Clearance');
    }

    /**
     * Test template maker UI renders the lock add approvers checkbox.
     */
    public function test_template_maker_ui_renders_lock_add_approvers_checkbox(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/ticket-types/create');
        $response->assertStatus(200);
        $response->assertSee('id="tpl_lock_add_approvers"', false);
        $response->assertSee('Lock Adding Users (Do not allow adding more users)');
    }

    /**
     * Test ticket creation enforces lock_add_approvers by rejecting additional unauthorized approvers.
     */
    public function test_ticket_creation_enforces_lock_add_approvers_by_disallowing_extra_approvers(): void
    {
        $d = $this->createDependencies();

        $enforcedApprover = User::factory()->create(['name' => 'Designated Auditor']);
        $unauthorizedApprover = User::factory()->create(['name' => 'Unauthorized Extra User']);

        $d['approvalType']->update([
            'template_json' => [
                'approval' => [
                    'has_approvers' => true,
                    'approvers' => [
                        ['id' => $enforcedApprover->id, 'name' => $enforcedApprover->name, 'sequence_order' => 1, 'signoff_description' => 'Security Audit'],
                    ],
                    'lock_add_approvers' => true,
                    'approvers_locked' => false,
                ],
            ],
        ]);

        // Submit form attempting to add an unauthorized extra approver
        $response = $this->actingAs($d['creator'])->post('/tickets', [
            'title' => 'Clearance Request with Extra User Attempt',
            'description' => 'Testing lock_add_approvers enforcement',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'category_1_id' => $d['category']->id,
            'approval_mode' => 'sequential',
            'approver_ids' => [$enforcedApprover->id, $unauthorizedApprover->id],
            'signoff_descriptions' => ['Security Audit', 'Unauthorized Signoff'],
        ]);

        $response->assertRedirect();

        $ticket = Ticket::where('title', 'Clearance Request with Extra User Attempt')->first();
        $this->assertNotNull($ticket);
        // Only the allowed template approver should be persisted; extra user filtered out
        $this->assertEquals(1, $ticket->approvals()->count());
        $this->assertEquals($enforcedApprover->id, $ticket->approvals()->first()->user_id);
    }

    /**
     * Test ticket creation page renders searchable users and handles multiline checklist descriptions.
     */
    public function test_ticket_creation_renders_searchable_approvers_and_supports_multiline_descriptions(): void
    {
        $d = $this->createDependencies();

        $response = $this->actingAs($d['creator'])->get('/tickets/create');
        $response->assertStatus(200);
        $response->assertSee('id="approver_search"', false);
        $response->assertSee('<textarea id="approver_signoff_desc"', false);

        $multilineDesc = "1. Return company laptop and badge.\n2. Revoke AWS and GitHub access.\n3. Complete exit interview.";
        $postResponse = $this->actingAs($d['creator'])->post('/tickets', [
            'title' => 'Multiline Clearance Test',
            'description' => 'Testing multiline checklist descriptions',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'category_1_id' => $d['category']->id,
            'approval_mode' => 'sequential',
            'approver_ids' => [$d['creator']->id],
            'signoff_descriptions' => [$multilineDesc],
        ]);

        $postResponse->assertRedirect();

        $ticket = Ticket::where('title', 'Multiline Clearance Test')->first();
        $this->assertNotNull($ticket);
        $approval = $ticket->approvals()->first();
        $this->assertEquals($multilineDesc, $approval->signoff_description);
    }
}
