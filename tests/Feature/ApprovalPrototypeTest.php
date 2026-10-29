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
     * Test creating an Approval/Clearance ticket with sequential approvers.
     */
    public function test_can_create_approval_clearance_ticket_with_approvers(): void
    {
        $d = $this->createDependencies();

        $response = $this->actingAs($d['creator'])->post('/tickets', [
            'title' => 'Year-end Clearance',
            'description' => 'Please review and sign off on clearance.',
            'ticket_type_id' => $d['approvalType']->id,
            'priority_option_id' => $d['priority']->id,
            'division_id' => $d['division']->id,
            'department_id' => $d['department']->id,
            'location_id' => $d['location']->id,
            'category_1_id' => $d['category']->id,
            'approval_mode' => 'sequential',
            'approver_ids' => [$d['approver1']->id, $d['approver2']->id],
        ]);

        $response->assertRedirect();

        $ticket = Ticket::where('title', 'Year-end Clearance')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('sequential', $ticket->approval_mode);
        $this->assertEquals('pending-approval', $ticket->stage->slug);
        $this->assertEquals(2, $ticket->approvals()->count());

        $firstApproval = $ticket->approvals()->where('sequence_order', 1)->first();
        $this->assertEquals($d['approver1']->id, $firstApproval->user_id);
        $this->assertEquals('pending', $firstApproval->status);

        $secondApproval = $ticket->approvals()->where('sequence_order', 2)->first();
        $this->assertEquals($d['approver2']->id, $secondApproval->user_id);
        $this->assertEquals('pending', $secondApproval->status);
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
}
