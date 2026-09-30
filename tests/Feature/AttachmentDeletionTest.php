<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\Location;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketStage;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentDeletionTest extends TestCase
{
    use RefreshDatabase;

    private User $assignee;

    private User $otherUser;

    private TicketStage $openStage;

    private TicketStage $assignedStage;

    private TicketStage $closedStage;

    private TicketStage $canceledStage;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([PreventRequestForgery::class]);
        Storage::fake('local');

        $division = Division::create(['name' => 'IT Division']);
        $department = Department::create(['name' => 'IT Support', 'division_id' => $division->id]);
        $location = Location::create(['name' => 'HQ']);

        $this->assignee = User::factory()->create([
            'user_type' => 'regular',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'is_approved' => true,
        ]);

        $this->otherUser = User::factory()->create([
            'user_type' => 'regular',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'is_approved' => true,
        ]);

        $this->openStage = TicketStage::firstOrCreate(['slug' => 'open'], ['name' => 'Open', 'color_code' => '#10b981']);
        $this->assignedStage = TicketStage::firstOrCreate(['slug' => 'assigned'], ['name' => 'Assigned', 'color_code' => '#3b82f6']);
        $this->closedStage = TicketStage::firstOrCreate(['slug' => 'closed'], ['name' => 'Closed', 'color_code' => '#6b7280']);
        $this->canceledStage = TicketStage::firstOrCreate(['slug' => 'canceled'], ['name' => 'Canceled', 'color_code' => '#ef4444']);

        $ticketType = TicketType::create(['name' => 'Incident', 'code' => 'INC']);
        $priority = Priority::create(['name' => 'Normal', 'level' => 1]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $this->ticket = Ticket::create([
            'ticket_number' => 'INC-1001',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priority->id,
            'category_1_id' => $category->id,
            'stage_id' => $this->assignedStage->id,
            'status' => 'Valid',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'created_by' => $this->otherUser->id,
            'assigned_id' => $this->assignee->id,
        ]);
    }

    public function test_assignee_can_delete_their_own_attachment_within_two_hours(): void
    {
        $filePath = 'attachments/'.$this->ticket->id.'/test_file.pdf';
        Storage::put($filePath, 'file content');

        $attachment = Attachment::create([
            'ticket_id' => $this->ticket->id,
            'file_name' => 'test_file.pdf',
            'file_path' => $filePath,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->assignee->id,
            'created_at' => Carbon::now()->subMinutes(30),
        ]);

        $response = $this->actingAs($this->assignee)
            ->delete(route('tickets.attachments.destroy', [$this->ticket, $attachment]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Storage::assertMissing($filePath);
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $this->ticket->id,
            'type' => 'system_event',
            'content' => "Attachment 'test_file.pdf' was deleted by {$this->assignee->name}.",
        ]);
    }

    public function test_cannot_delete_attachment_if_ticket_is_closed(): void
    {
        $this->ticket->update(['stage_id' => $this->closedStage->id]);

        $filePath = 'attachments/'.$this->ticket->id.'/test_file.pdf';
        Storage::put($filePath, 'file content');

        $attachment = Attachment::create([
            'ticket_id' => $this->ticket->id,
            'file_name' => 'test_file.pdf',
            'file_path' => $filePath,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->assignee->id,
            'created_at' => Carbon::now()->subMinutes(30),
        ]);

        $response = $this->actingAs($this->assignee)
            ->delete(route('tickets.attachments.destroy', [$this->ticket, $attachment]));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete attachments from a closed or canceled ticket.');

        Storage::assertExists($filePath);
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
    }

    public function test_cannot_delete_attachment_if_ticket_is_canceled(): void
    {
        $this->ticket->update(['stage_id' => $this->canceledStage->id]);

        $filePath = 'attachments/'.$this->ticket->id.'/test_file.pdf';
        Storage::put($filePath, 'file content');

        $attachment = Attachment::create([
            'ticket_id' => $this->ticket->id,
            'file_name' => 'test_file.pdf',
            'file_path' => $filePath,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->assignee->id,
            'created_at' => Carbon::now()->subMinutes(30),
        ]);

        $response = $this->actingAs($this->assignee)
            ->delete(route('tickets.attachments.destroy', [$this->ticket, $attachment]));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete attachments from a closed or canceled ticket.');

        Storage::assertExists($filePath);
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
    }

    public function test_cannot_delete_attachment_if_not_the_uploader(): void
    {
        $filePath = 'attachments/'.$this->ticket->id.'/test_file.pdf';
        Storage::put($filePath, 'file content');

        // Uploaded by otherUser, not assignee
        $attachment = Attachment::create([
            'ticket_id' => $this->ticket->id,
            'file_name' => 'test_file.pdf',
            'file_path' => $filePath,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->otherUser->id,
            'created_at' => Carbon::now()->subMinutes(30),
        ]);

        $response = $this->actingAs($this->assignee)
            ->delete(route('tickets.attachments.destroy', [$this->ticket, $attachment]));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'You can only delete your own attachments.');

        Storage::assertExists($filePath);
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
    }

    public function test_cannot_delete_attachment_if_not_the_current_assignee(): void
    {
        $filePath = 'attachments/'.$this->ticket->id.'/test_file.pdf';
        Storage::put($filePath, 'file content');

        // Uploaded by otherUser, and ticket assigned to otherUser instead of assignee
        $this->ticket->update(['assigned_id' => $this->otherUser->id]);

        $attachment = Attachment::create([
            'ticket_id' => $this->ticket->id,
            'file_name' => 'test_file.pdf',
            'file_path' => $filePath,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->assignee->id,
            'created_at' => Carbon::now()->subMinutes(30),
        ]);

        $response = $this->actingAs($this->assignee)
            ->delete(route('tickets.attachments.destroy', [$this->ticket, $attachment]));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only the current assignee can delete attachments.');

        Storage::assertExists($filePath);
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
    }

    public function test_cannot_delete_attachment_older_than_two_hours(): void
    {
        $filePath = 'attachments/'.$this->ticket->id.'/test_file.pdf';
        Storage::put($filePath, 'file content');

        $attachment = Attachment::create([
            'ticket_id' => $this->ticket->id,
            'file_name' => 'test_file.pdf',
            'file_path' => $filePath,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->assignee->id,
        ]);
        $attachment->created_at = Carbon::now()->subHours(3);
        $attachment->save();

        $response = $this->actingAs($this->assignee)
            ->delete(route('tickets.attachments.destroy', [$this->ticket, $attachment]));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Attachments older than 2 hours cannot be deleted.');

        Storage::assertExists($filePath);
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
    }

    public function test_main_attachments_section_excludes_comment_attachments(): void
    {
        $mainAttachment = Attachment::create([
            'ticket_id' => $this->ticket->id,
            'comment_id' => null,
            'file_name' => 'ticket_main_file.pdf',
            'file_path' => 'attachments/'.$this->ticket->id.'/ticket_main_file.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->assignee->id,
        ]);

        $comment = TicketComment::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->assignee->id,
            'type' => 'comment',
            'content' => 'A test comment',
        ]);

        $commentAttachment = Attachment::create([
            'ticket_id' => $this->ticket->id,
            'comment_id' => $comment->id,
            'file_name' => 'comment_specific_file.pdf',
            'file_path' => 'attachments/'.$this->ticket->id.'/comment_specific_file.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->assignee->id,
        ]);

        $response = $this->actingAs($this->assignee)
            ->get(route('tickets.show', $this->ticket));

        $response->assertOk();
        $response->assertSee('ticket_main_file.pdf');
        $response->assertSee('Attachment #1');
        $response->assertDontSee('Attachment #2');
        $response->assertSee('comment_specific_file.pdf');
    }
}
