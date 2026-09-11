<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryClosure;
use App\Models\Department;
use App\Models\Division;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\Priority;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test that guests cannot access ticket index.
     */
    public function test_guests_cannot_access_ticket_management(): void
    {
        $response = $this->get('/tickets');

        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated users can view tickets index.
     */
    public function test_authenticated_users_can_view_ticket_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertStatus(200);
        $response->assertSee('Tickets');
    }

    /**
     * Test that users can view the ticket creation page.
     */
    public function test_users_can_view_ticket_creation_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tickets/create');

        $response->assertStatus(200);
        $response->assertSee('Create New Ticket');
    }

    /**
     * Test that users can successfully create a new ticket.
     */
    public function test_users_can_create_a_new_ticket(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $user->roles()->attach($role->id);

        // Seed lookups
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'New Ticket Title',
            'description' => 'These are my detailed findings regarding this incident.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
        ]);

        $ticket = Ticket::first();

        $this->assertNotNull($ticket);
        $this->assertEquals('New Ticket Title', $ticket->title);
        $this->assertEquals('These are my detailed findings regarding this incident.', $ticket->description);
        $this->assertEquals($priorityOption->id, $ticket->priority_option_id);
        $response->assertRedirect(route('tickets.show', $ticket));
    }

    /**
     * Test that creating a ticket without providing a status defaults to 'Open'.
     */
    public function test_creating_a_ticket_without_status_defaults_to_open(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $user->roles()->attach($role->id);

        // Seed lookups
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'New Ticket Default Status',
            'description' => 'This is a ticket created without explicitly specifying status.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
            // status_id is omitted
        ]);

        $ticket = Ticket::where('title', 'New Ticket Default Status')->first();

        $this->assertNotNull($ticket);
        $this->assertEquals($status->id, $ticket->status_id);
        $response->assertRedirect(route('tickets.show', $ticket));
    }

    /**
     * Test that users can view ticket details.
     */
    public function test_users_can_view_ticket_details(): void
    {
        $user = User::factory()->create();

        // Seed lookups
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-2026-9999',
            'title' => 'Ticket Under Inspection',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $user->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('tickets.show', $ticket));

        $response->assertStatus(200);
        $response->assertSee('FLR-2026-9999');
        $response->assertSee('Ticket Under Inspection');
    }

    /**
     * Test that authenticated users can fetch categories via AJAX API.
     */
    public function test_users_can_fetch_categories_via_ajax(): void
    {
        $user = User::factory()->create();
        $type = TicketType::create(['name' => 'Incident']);

        // Category 1
        $cat1 = Category::create(['name' => 'Hardware', 'ticket_type_id' => $type->id]);
        CategoryClosure::create(['ancestor_id' => $cat1->id, 'descendant_id' => $cat1->id, 'depth' => 0]);

        // Category 2 under Category 1
        $cat2 = Category::create(['name' => 'Laptops', 'ticket_type_id' => $type->id]);
        CategoryClosure::create(['ancestor_id' => $cat2->id, 'descendant_id' => $cat2->id, 'depth' => 0]);
        CategoryClosure::create(['ancestor_id' => $cat1->id, 'descendant_id' => $cat2->id, 'depth' => 1]);

        // 1. Fetch Category 1s by ticket_type_id
        $response = $this->actingAs($user)->getJson("/api/categories?ticket_type_id={$type->id}");
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $cat1->id, 'name' => 'Hardware']);

        // 2. Fetch Category 2s by parent_id (depth = 1)
        $response = $this->actingAs($user)->getJson("/api/categories?parent_id={$cat1->id}");
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $cat2->id, 'name' => 'Laptops']);
    }

    /**
     * Test that users can create tickets with multiple file attachments.
     */
    public function test_users_can_create_ticket_with_multiple_attachments(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $user->roles()->attach($role->id);
        
        // Seed lookups
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // Stage mock chunks in storage
        \Illuminate\Support\Facades\Storage::fake('local');
        $token1 = 'token_abc123';
        $token2 = 'token_xyz789';
        
        \Illuminate\Support\Facades\Storage::put("staging/{$token1}/1.part", "part1");
        \Illuminate\Support\Facades\Storage::put("staging/{$token2}/1.part", "part2");

        $attachmentsJson = json_encode([
            [
                'temp_token' => $token1,
                'total_chunks' => 1,
                'file_name' => 'report.pdf',
                'mime_type' => 'application/pdf',
                'note' => 'First attachment note'
            ],
            [
                'temp_token' => $token2,
                'total_chunks' => 1,
                'file_name' => 'photo.jpg',
                'mime_type' => 'image/jpeg',
                'note' => 'Second attachment note'
            ]
        ]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Ticket with multiple files',
            'description' => 'See files attached.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
            'attachments_json' => $attachmentsJson
        ]);

        $ticket = Ticket::where('title', 'Ticket with multiple files')->first();
        $this->assertNotNull($ticket);

        $response->assertRedirect(route('tickets.show', $ticket));

        // Assert attachments were created
        $this->assertDatabaseHas('attachments', [
            'ticket_id' => $ticket->id,
            'file_name' => 'report.pdf',
            'note' => 'First attachment note'
        ]);
        $this->assertDatabaseHas('attachments', [
            'ticket_id' => $ticket->id,
            'file_name' => 'photo.jpg',
            'note' => 'Second attachment note'
        ]);
    }

    /**
     * Test that users can create a ticket specifying an intended user (to_user_id).
     */
    public function test_users_can_create_ticket_with_intended_user(): void
    {
        $creator = User::factory()->create();
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $creator->roles()->attach($role->id);
        
        $intendedUser = User::factory()->create();

        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $response = $this->actingAs($creator)->post('/tickets', [
            'title' => 'Intended Ticket',
            'description' => 'This ticket is meant specifically for someone.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
            'to_user_id' => $intendedUser->id,
        ]);

        $ticket = Ticket::where('title', 'Intended Ticket')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($intendedUser->id, $ticket->to_user_id);
    }

    /**
     * Test that only the intended user can accept the ticket if to_user_id is filled.
     */
    public function test_only_intended_user_can_accept_ticket(): void
    {
        $creator = User::factory()->create();
        $intendedUser = User::factory()->create();
        $otherUser = User::factory()->create();

        $statusOpen = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        TicketStatus::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']); // Used when accepted
        
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-2026-1234',
            'title' => 'For Intended User Only',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $statusOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
            'to_user_id' => $intendedUser->id,
        ]);

        // Disable CSRF for requests forgery
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

        // Try accepting as other user - should fail
        $response = $this->actingAs($otherUser)->post(route('tickets.accept', $ticket));
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $ticket->refresh();
        $this->assertNull($ticket->assigned_to);

        // Try accepting as intended user - should succeed
        $response = $this->actingAs($intendedUser)->post(route('tickets.accept', $ticket));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $ticket->refresh();
        $this->assertEquals($intendedUser->id, $ticket->assigned_to);
        $this->assertEquals('assigned', $ticket->status->slug);
    }

    /**
     * Test that any user can accept a ticket if to_user_id is null.
     */
    public function test_any_user_can_accept_unintended_ticket(): void
    {
        $creator = User::factory()->create();
        $acceptor = User::factory()->create();

        $statusOpen = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        TicketStatus::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);
        
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-2026-5678',
            'title' => 'Open To Anyone',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $statusOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
            'to_user_id' => null,
        ]);

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

        $response = $this->actingAs($acceptor)->post(route('tickets.accept', $ticket));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $ticket->refresh();
        $this->assertEquals($acceptor->id, $ticket->assigned_to);
    }

    /**
     * Test that users can fetch users list filtered by division and department via AJAX API.
     */
    public function test_users_can_fetch_filtered_users_via_ajax(): void
    {
        $user = User::factory()->create();

        $div1 = Division::create(['name' => 'Division One']);
        $div2 = Division::create(['name' => 'Division Two']);

        $dept1 = Department::create(['name' => 'Dept One', 'division_id' => $div1->id]);
        $dept2 = Department::create(['name' => 'Dept Two', 'division_id' => $div2->id]);

        $userInDiv1Dept1 = User::factory()->create(['name' => 'John Div1Dept1', 'division_id' => $div1->id, 'department_id' => $dept1->id]);
        $userInDiv2Dept2 = User::factory()->create(['name' => 'Jane Div2Dept2', 'division_id' => $div2->id, 'department_id' => $dept2->id]);

        // 1. Fetch with division filter only
        $response = $this->actingAs($user)->getJson("/api/users?division_id={$div1->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $userInDiv1Dept1->id, 'name' => 'John Div1Dept1'])
            ->assertJsonMissing(['id' => $userInDiv2Dept2->id]);

        // 2. Fetch with department filter only
        $response = $this->actingAs($user)->getJson("/api/users?department_id={$dept2->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $userInDiv2Dept2->id, 'name' => 'Jane Div2Dept2'])
            ->assertJsonMissing(['id' => $userInDiv1Dept1->id]);

        // 3. Fetch with both filters
        $response = $this->actingAs($user)->getJson("/api/users?division_id={$div1->id}&department_id={$dept1->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $userInDiv1Dept1->id, 'name' => 'John Div1Dept1'])
            ->assertJsonMissing(['id' => $userInDiv2Dept2->id]);

        // 4. Fetch with search query filter 'q'
        $response = $this->actingAs($user)->getJson("/api/users?q=Jane");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $userInDiv2Dept2->id, 'name' => 'Jane Div2Dept2'])
            ->assertJsonMissing(['id' => $userInDiv1Dept1->id]);
    }

    /**
     * Test that involved users can comment on an open ticket, but uninvolved users cannot.
     */
    public function test_involved_users_can_comment_on_open_ticket(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $agent = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $unInvolved = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-99881',
            'title' => 'Open status test ticket',
            'description' => 'Test ticket description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'assigned_to' => $agent->id,
            'category_1_id' => $category->id,
        ]);

        // 1. Creator can comment
        $response = $this->actingAs($creator)->post("/tickets/{$ticket->id}/comments", [
            'content' => 'Creator comment'
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $creator->id,
            'content' => 'Creator comment'
        ]);

        // 2. Assigned Agent can comment
        $response = $this->actingAs($agent)->post("/tickets/{$ticket->id}/comments", [
            'content' => 'Agent comment'
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'content' => 'Agent comment'
        ]);

        // 3. Uninvolved user cannot comment
        $response = $this->actingAs($unInvolved)->post("/tickets/{$ticket->id}/comments", [
            'content' => 'Uninvolved user comment'
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $unInvolved->id,
            'content' => 'Uninvolved user comment'
        ]);
    }

    /**
     * Test that involved users can comment on an open ticket with attachments.
     */
    public function test_users_can_comment_with_attachments(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-99882',
            'title' => 'Ticket for comment attachment test',
            'description' => 'Test ticket description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        // Stage mock chunks in storage
        \Illuminate\Support\Facades\Storage::fake('local');
        $token = 'token_comment_123';
        \Illuminate\Support\Facades\Storage::put("staging/{$token}/1.part", "comment part content");

        $attachmentsJson = json_encode([
            [
                'temp_token' => $token,
                'total_chunks' => 1,
                'file_name' => 'comment_doc.pdf',
                'mime_type' => 'application/pdf',
                'note' => 'Comment attachment note'
            ]
        ]);

        $response = $this->actingAs($creator)->post("/tickets/{$ticket->id}/comments", [
            'content' => 'This is a comment with file attached',
            'attachments_json' => $attachmentsJson
        ]);

        $response->assertRedirect();

        // Assert comment was created
        $comment = \App\Models\TicketComment::where('content', 'This is a comment with file attached')->first();
        $this->assertNotNull($comment);

        // Assert attachment was created and associated with the comment and ticket
        $this->assertDatabaseHas('attachments', [
            'ticket_id' => $ticket->id,
            'comment_id' => $comment->id,
            'file_name' => 'comment_doc.pdf',
            'note' => 'Comment attachment note',
            'uploaded_by' => $creator->id
        ]);
    }

    /**
     * Test that ticket creator can view the edit page.
     */
    public function test_ticket_creator_can_view_edit_page(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $creator->roles()->attach($role->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $role->ticketTypes()->attach($ticketType->id);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10001',
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($creator)->get("/tickets/{$ticket->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Ticket');
        $response->assertSee('Initial Title');
    }

    /**
     * Test that non-creator cannot view the edit page.
     */
    public function test_non_ticket_creator_cannot_view_edit_page(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $otherUser = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10002',
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($otherUser)->get("/tickets/{$ticket->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * Test that ticket creator can update the ticket.
     */
    public function test_ticket_creator_can_update_ticket(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $creator->roles()->attach($role->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $role->ticketTypes()->attach($ticketType->id);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10003',
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($creator)->put("/tickets/{$ticket->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));

        $ticket->refresh();
        $this->assertEquals('Updated Title', $ticket->title);
        $this->assertEquals('Updated Description', $ticket->description);
    }

    /**
     * Test that non-creator cannot update the ticket.
     */
    public function test_non_ticket_creator_cannot_update_ticket(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $otherUser = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10004',
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($otherUser)->put("/tickets/{$ticket->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
        ]);

        $response->assertStatus(403);
        
        $ticket->refresh();
        $this->assertEquals('Initial Title', $ticket->title);
    }

    /**
     * Test that ticket update logs automated system comment of type system_event.
     */
    public function test_ticket_update_logs_automated_system_comment(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $creator->roles()->attach($role->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $role->ticketTypes()->attach($ticketType->id);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10005',
            'title' => 'Original Title',
            'description' => 'Original Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        $this->actingAs($creator)->put("/tickets/{$ticket->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
        ]);

        // Assert system comment was created documenting the changes
        $comment = \App\Models\TicketComment::where('ticket_id', $ticket->id)
            ->where('type', 'system_event')
            ->first();

        $this->assertNotNull($comment);
        $this->assertNull($comment->user_id);
        $this->assertStringContainsString("Ticket details updated:", $comment->content);
        $this->assertStringContainsString("Title updated from 'Original Title' to 'Updated Title'", $comment->content);
        $this->assertStringContainsString("Description updated", $comment->content);
    }

    /**
     * Test that creator can delete an existing attachment during update.
     */
    public function test_creator_can_delete_existing_attachment_during_update(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $creator->roles()->attach($role->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $role->ticketTypes()->attach($ticketType->id);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10006',
            'title' => 'Original Title',
            'description' => 'Original Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        $attachment = \App\Models\Attachment::create([
            'ticket_id' => $ticket->id,
            'file_name' => 'original_file.pdf',
            'file_path' => 'attachments/' . $ticket->id . '/original_file.pdf',
            'file_size' => 1234,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $creator->id,
        ]);

        $response = $this->actingAs($creator)->put("/tickets/{$ticket->id}", [
            'title' => 'Original Title',
            'description' => 'Original Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
            'deleted_attachments' => json_encode([$attachment->id]),
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));

        $this->assertDatabaseMissing('attachments', [
            'id' => $attachment->id,
        ]);

        // Assert system comment documenting removal
        $comment = \App\Models\TicketComment::where('ticket_id', $ticket->id)
            ->where('type', 'system_event')
            ->first();

        $this->assertNotNull($comment);
        $this->assertStringContainsString("1 attachment(s) removed", $comment->content);
    }

    /**
     * Test that creator can add a new attachment during update.
     */
    public function test_creator_can_add_new_attachment_during_update(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $creator->roles()->attach($role->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $role->ticketTypes()->attach($ticketType->id);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10007',
            'title' => 'Original Title',
            'description' => 'Original Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        \Illuminate\Support\Facades\Storage::fake('local');
        $token = 'token_edit_456';
        \Illuminate\Support\Facades\Storage::put("staging/{$token}/1.part", "new part content");

        $attachmentsJson = json_encode([
            [
                'temp_token' => $token,
                'total_chunks' => 1,
                'file_name' => 'new_uploaded_file.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'note' => 'New edit attachment note'
            ]
        ]);

        $response = $this->actingAs($creator)->put("/tickets/{$ticket->id}", [
            'title' => 'Original Title',
            'description' => 'Original Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
            'attachments_json' => $attachmentsJson,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));

        $this->assertDatabaseHas('attachments', [
            'ticket_id' => $ticket->id,
            'file_name' => 'new_uploaded_file.xlsx',
            'note' => 'New edit attachment note'
        ]);

        // Assert system comment documenting addition
        $comment = \App\Models\TicketComment::where('ticket_id', $ticket->id)
            ->where('type', 'system_event')
            ->first();

        $this->assertNotNull($comment);
        $this->assertStringContainsString("1 new attachment(s) added", $comment->content);
    }

    /**
     * Test that creator can update an existing attachment note during update.
     */
    public function test_creator_can_update_existing_attachment_note_during_update(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $creator->roles()->attach($role->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $role->ticketTypes()->attach($ticketType->id);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10008',
            'title' => 'Original Title',
            'description' => 'Original Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        $attachment = \App\Models\Attachment::create([
            'ticket_id' => $ticket->id,
            'file_name' => 'note_test.pdf',
            'file_path' => 'attachments/' . $ticket->id . '/note_test.pdf',
            'file_size' => 1234,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $creator->id,
            'note' => 'Original Note',
        ]);

        $response = $this->actingAs($creator)->put("/tickets/{$ticket->id}", [
            'title' => 'Original Title',
            'description' => 'Original Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
            'existing_notes' => [
                $attachment->id => 'Updated Note Content',
            ],
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));

        $attachment->refresh();
        $this->assertEquals('Updated Note Content', $attachment->note);

        // Assert system comment documenting note update
        $comment = \App\Models\TicketComment::where('ticket_id', $ticket->id)
            ->where('type', 'system_event')
            ->first();

        $this->assertNotNull($comment);
        $this->assertStringContainsString("Attachment 'note_test.pdf' note updated", $comment->content);
    }

    /**
     * Test that the assigned user can submit a ticket for review, assigning it back to the author with status "Review".
     */
    public function test_assigned_user_can_submit_ticket_for_review(): void
    {
        $author = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $assignedUser = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        
        $assignedStatus = TicketStatus::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);
        $reviewStatus = TicketStatus::create(['name' => 'Review', 'slug' => 'review', 'color_code' => '#3']);
        
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10009',
            'title' => 'Assigned Ticket',
            'description' => 'Detailed description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $assignedStatus->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $author->id,
            'assigned_to' => $assignedUser->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($assignedUser)->post("/tickets/{$ticket->id}/for-review", [
            'message' => 'Please review my work on this ticket.',
        ]);

        $response->assertRedirect();
        
        $ticket->refresh();
        $this->assertEquals($reviewStatus->id, $ticket->status_id);
        $this->assertEquals($author->id, $ticket->assigned_to);

        // Assert review message comments was created
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $assignedUser->id,
            'content' => 'Please review my work on this ticket.',
            'type' => 'comment',
        ]);
    }

    /**
     * Test that a non-assigned user cannot submit a ticket for review.
     */
    public function test_non_assigned_user_cannot_submit_ticket_for_review(): void
    {
        $author = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $assignedUser = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $otherUser = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        
        $assignedStatus = TicketStatus::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);
        
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10010',
            'title' => 'Assigned Ticket',
            'description' => 'Detailed description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $assignedStatus->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $author->id,
            'assigned_to' => $assignedUser->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($otherUser)->post("/tickets/{$ticket->id}/for-review", [
            'message' => 'Sneaking a review message',
        ]);

        $response->assertStatus(403);
        
        $ticket->refresh();
        $this->assertEquals($assignedStatus->id, $ticket->status_id);
        $this->assertEquals($assignedUser->id, $ticket->assigned_to);
    }
}
