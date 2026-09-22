<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryClosure;
use App\Models\Department;
use App\Models\Division;
use App\Models\Ticket;
use App\Models\TicketStage;
use App\Models\TicketType;
use App\Models\Priority;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    private \App\Models\Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
        $this->location = \App\Models\Location::create(['name' => 'Default Location']);
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

        // Seed lookups and create a ticket to ensure eager loading executes
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        Ticket::create([
            'ticket_number' => 'TCK-123',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'created_by' => $user->id,
            'status' => 'Valid',
        ]);

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertStatus(200);
        $response->assertSee('Tickets');
        $response->assertSee('Test Ticket');
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $location = \App\Models\Location::create(['name' => 'Main Office']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'New Ticket Title',
            'description' => 'These are my detailed findings regarding this incident.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $location = \App\Models\Location::create(['name' => 'Main Office']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'New Ticket Default Status',
            'description' => 'This is a ticket created without explicitly specifying status.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'category_1_id' => $category->id,
            // stage_id is omitted
        ]);

        $ticket = Ticket::where('title', 'New Ticket Default Status')->first();

        $this->assertNotNull($ticket);
        $this->assertEquals($stage->id, $ticket->stage_id);
        $response->assertRedirect(route('tickets.show', $ticket));
    }

    /**
     * Test that users can successfully create a new ticket without a department.
     */
    public function test_creating_a_ticket_without_department_succeeds(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $user->roles()->attach($role->id);

        // Seed lookups
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $location = \App\Models\Location::create(['name' => 'Main Office']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Ticket Without Department',
            'description' => 'No department was selected for this ticket.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'location_id' => $location->id,
            'category_1_id' => $category->id,
            // department_id is omitted
        ]);

        $ticket = Ticket::where('title', 'Ticket Without Department')->first();

        $this->assertNotNull($ticket);
        $this->assertNull($ticket->department_id);
        $response->assertRedirect(route('tickets.show', $ticket));
    }

    /**
     * Test that users can view ticket details.
     */
    public function test_users_can_view_ticket_details(): void
    {
        $user = User::factory()->create();

        // Seed lookups
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
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
            'stage_id' => $stage->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $location = \App\Models\Location::create(['name' => 'Main Office']);
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
                'file_name' => 'photo.webp',
                'mime_type' => 'image/webp',
                'note' => 'Second attachment note'
            ]
        ]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Ticket with multiple files',
            'description' => 'See files attached.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
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
            'file_name' => 'photo.webp',
            'note' => 'Second attachment note'
        ]);
    }

    /**
     * Test that users can successfully create a ticket with a .webp image attachment.
     */
    public function test_users_can_create_ticket_with_webp_attachment(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $user->roles()->attach($role->id);

        // Seed lookups
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $location = \App\Models\Location::create(['name' => 'Main Office']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // Stage mock webp chunk in storage
        \Illuminate\Support\Facades\Storage::fake('local');
        $token = 'token_webp_abc';
        \Illuminate\Support\Facades\Storage::put("staging/{$token}/1.part", "webp_part");

        $attachmentsJson = json_encode([
            [
                'temp_token' => $token,
                'total_chunks' => 1,
                'file_name' => 'image_upload.webp',
                'mime_type' => 'image/webp',
                'note' => 'WEBP Note'
            ]
        ]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Ticket with WEBP file',
            'description' => 'WEBP image test.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'category_1_id' => $category->id,
            'attachments_json' => $attachmentsJson
        ]);

        $ticket = Ticket::where('title', 'Ticket with WEBP file')->first();
        $this->assertNotNull($ticket);

        $response->assertRedirect(route('tickets.show', $ticket));

        // Assert webp attachment was created successfully
        $this->assertDatabaseHas('attachments', [
            'ticket_id' => $ticket->id,
            'file_name' => 'image_upload.webp',
            'note' => 'WEBP Note'
        ]);
    }

    /**
     * Test that users cannot create a ticket with disallowed extensions (e.g., .exe).
     */
    public function test_users_cannot_create_ticket_with_disallowed_extension(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $user->roles()->attach($role->id);

        // Seed lookups
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $location = \App\Models\Location::create(['name' => 'Main Office']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // Stage mock exe chunk in storage
        \Illuminate\Support\Facades\Storage::fake('local');
        $token = 'token_exe_abc';
        \Illuminate\Support\Facades\Storage::put("staging/{$token}/1.part", "exe_part");

        $attachmentsJson = json_encode([
            [
                'temp_token' => $token,
                'total_chunks' => 1,
                'file_name' => 'malicious.exe',
                'mime_type' => 'application/x-msdownload',
                'note' => 'EXE Note'
            ]
        ]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Ticket with EXE file',
            'description' => 'Should fail.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'category_1_id' => $category->id,
            'attachments_json' => $attachmentsJson
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('attachments', [
            'file_name' => 'malicious.exe'
        ]);
    }

    /**
     * Test that users can create a ticket specifying an intended user (assigned_id).
     */
    public function test_users_can_create_ticket_with_intended_user(): void
    {
        $creator = User::factory()->create();
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $creator->roles()->attach($role->id);
        
        $intendedUser = User::factory()->create();

        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $location = \App\Models\Location::create(['name' => 'Main Office']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $response = $this->actingAs($creator)->post('/tickets', [
            'title' => 'Intended Ticket',
            'description' => 'This ticket is meant specifically for someone.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'category_1_id' => $category->id,
            'assigned_id' => $intendedUser->id,
        ]);

        $ticket = Ticket::where('title', 'Intended Ticket')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($intendedUser->id, $ticket->assigned_id);
    }

    /**
     * Test that only the intended user can accept the ticket if assigned_id is filled.
     */
    public function test_only_intended_user_can_accept_ticket(): void
    {
        $creator = User::factory()->create();
        $intendedUser = User::factory()->create();
        $otherUser = User::factory()->create();

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        TicketStage::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']); // Used when accepted
        
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
            'stage_id' => $stageOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
            'assigned_id' => $intendedUser->id,
        ]);

        // Disable CSRF for requests forgery
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

        // Try accepting as other user - should fail
        $response = $this->actingAs($otherUser)->post(route('tickets.accept', $ticket));
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $ticket->refresh();
        $this->assertNull($ticket->assigned_to); // relation assignee is null or assigned_id is original
        $this->assertEquals($intendedUser->id, $ticket->assigned_id);

        // Try accepting as intended user - should succeed
        $response = $this->actingAs($intendedUser)->post(route('tickets.accept', $ticket));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $ticket->refresh();
        $this->assertEquals($intendedUser->id, $ticket->assigned_id);
        $this->assertEquals('assigned', $ticket->stage->slug);
    }

    /**
     * Test that any user can accept a ticket if assigned_id is null.
     */
    public function test_any_user_can_accept_unintended_ticket(): void
    {
        $creator = User::factory()->create();

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        TicketStage::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);
        
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $acceptor = User::factory()->create([
            'division_id' => $division->id,
            'department_id' => $department->id,
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-2026-5678',
            'title' => 'Open To Anyone',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
            'assigned_id' => null,
        ]);

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

        $response = $this->actingAs($acceptor)->post(route('tickets.accept', $ticket));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $ticket->refresh();
        $this->assertEquals($acceptor->id, $ticket->assigned_id);
    }

    /**
     * Test that the creator of a ticket cannot accept it.
     */
    public function test_creator_cannot_accept_own_ticket(): void
    {
        $creator = User::factory()->create();

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        TicketStage::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);
        
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-2026-9999',
            'title' => 'Creator Own Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $this->location->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
            'assigned_id' => null,
        ]);

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

        $response = $this->actingAs($creator)->post(route('tickets.accept', $ticket));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'You cannot accept your own ticket.');
        $ticket->refresh();
        $this->assertNull($ticket->assigned_id);
    }

    /**
     * Test that an admin who is the creator of a ticket cannot accept it via the accept action.
     */
    public function test_admin_creator_cannot_accept_own_ticket(): void
    {
        $adminCreator = User::factory()->create(['user_type' => 'admin']);

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        TicketStage::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);
        
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-2026-9998',
            'title' => 'Admin Creator Own Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $this->location->id,
            'created_by' => $adminCreator->id,
            'category_1_id' => $category->id,
            'assigned_id' => null,
        ]);

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

        $response = $this->actingAs($adminCreator)->post(route('tickets.accept', $ticket));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'You cannot accept your own ticket.');
        $ticket->refresh();
        $this->assertNull($ticket->assigned_id);
    }

    /**
     * Test that admins can assign a ticket to its creator.
     */
    public function test_admin_can_assign_ticket_to_creator(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $creator = User::factory()->create();

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-2026-8888',
            'title' => 'Admin Assign Test',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $this->location->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

        $response = $this->actingAs($admin)->put(route('tickets.update', $ticket), [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'assigned_id' => $creator->id,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $ticket->refresh();
        $this->assertEquals($creator->id, $ticket->assigned_id);
    }

    /**
     * Test that reassignment from review cannot be assigned to the ticket's creator.
     */
    public function test_reassignment_from_review_cannot_be_assigned_to_creator(): void
    {
        $creator = User::factory()->create();

        $stageReview = TicketStage::create(['name' => 'Review', 'slug' => 'review', 'color_code' => '#1']);
        TicketStage::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-2026-7777',
            'title' => 'Review Reassign Test',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageReview->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $this->location->id,
            'created_by' => $creator->id,
            'assigned_id' => $creator->id, // Assigned back to author for review
            'category_1_id' => $category->id,
        ]);

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

        $response = $this->actingAs($creator)->post(route('tickets.reassign-review', $ticket), [
            'comment' => 'Please redo it.',
            'assignee_id' => $creator->id,
        ]);

        $response->assertSessionHasErrors(['assignee_id']);
        $ticket->refresh();
        $this->assertEquals($creator->id, $ticket->assigned_id); // remains author
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-99881',
            'title' => 'Open status test ticket',
            'description' => 'Test ticket description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'assigned_id' => $agent->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-99882',
            'title' => 'Ticket for comment attachment test',
            'description' => 'Test ticket description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
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
            'stage_id' => $stage->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10002',
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
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
            'stage_id' => $stage->id,
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
            'location_id' => $this->location->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10004',
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
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
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
        ]);

        $response->assertStatus(403);
        
        $ticket->refresh();
        $this->assertEquals('Initial Title', $ticket->title);
    }

    /**
     * Test that administrators can view the edit page for any ticket.
     */
    public function test_admin_can_view_edit_page_for_any_ticket(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $admin = User::factory()->create(['user_type' => 'admin', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10005',
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->get("/tickets/{$ticket->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Ticket');
        $response->assertSee('Initial Title');
    }

    /**
     * Test that administrators can successfully update any ticket.
     */
    public function test_admin_can_update_any_ticket(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $admin = User::factory()->create(['user_type' => 'admin', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $stageClosed = TicketStage::create(['name' => 'Closed', 'slug' => 'closed', 'color_code' => '#2']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10006',
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
            'assigned_id' => null,
            'deadline_date' => null,
        ]);

        $newDeadline = now()->addDays(5)->startOfMinute();

        $response = $this->actingAs($admin)->put("/tickets/{$ticket->id}", [
            'title' => 'Admin Updated Title',
            'description' => 'Admin Updated Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'stage_id' => $stageClosed->id,
            'assigned_id' => $admin->id,
            'deadline_date' => $newDeadline->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));

        $ticket->refresh();
        $this->assertEquals('Admin Updated Title', $ticket->title);
        $this->assertEquals('Admin Updated Description', $ticket->description);
        $this->assertEquals($stageClosed->id, $ticket->stage_id);
        $this->assertEquals($admin->id, $ticket->assigned_id);
        $this->assertEquals($newDeadline->format('Y-m-d H:i'), $ticket->deadline_date->format('Y-m-d H:i'));

        // Assert that the system comment log was successfully recorded with the edits
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'type' => 'system_event',
        ]);

        $comment = \App\Models\TicketComment::where('ticket_id', $ticket->id)
            ->where('type', 'system_event')
            ->first();

        $this->assertNotNull($comment);
        $this->assertStringContainsString('Title updated from \'Initial Title\' to \'Admin Updated Title\'', $comment->content);
        $this->assertStringContainsString('Description updated', $comment->content);
        $this->assertStringContainsString('Stage updated from \'Open\' to \'Closed\'', $comment->content);
        $this->assertStringContainsString("Assigned User updated from 'None' to '{$admin->name}'", $comment->content);
        $this->assertStringContainsString('Deadline SLA updated from \'None\' to', $comment->content);
    }

    /**
     * Test that an admin can remove the assigned user from a ticket by leaving assigned_to blank.
     */
    public function test_admin_can_unassign_previously_assigned_ticket_by_leaving_field_blank(): void
    {
        $creator = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $admin = User::factory()->create(['user_type' => 'admin', 'is_approved' => true]);
        $agent = User::factory()->create(['user_type' => 'agent', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10007',
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'category_1_id' => $category->id,
            'assigned_id' => $agent->id,
        ]);

        $response = $this->actingAs($admin)->put("/tickets/{$ticket->id}", [
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'stage_id' => $stageOpen->id,
            'assigned_id' => '', // blank/unassigned
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));

        $ticket->refresh();
        $this->assertNull($ticket->assigned_id);

        // Assert that the system comment log was successfully recorded with the assignee update from 'Agent' to 'None'
        $comment = \App\Models\TicketComment::where('ticket_id', $ticket->id)
            ->where('type', 'system_event')
            ->first();

        $this->assertNotNull($comment);
        $this->assertStringContainsString("Assigned User updated from '{$agent->name}' to 'None'", $comment->content);
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
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
            'stage_id' => $stage->id,
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
            'location_id' => $this->location->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
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
            'stage_id' => $stage->id,
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
            'location_id' => $this->location->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
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
            'stage_id' => $stage->id,
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
            'location_id' => $this->location->id,
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
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
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
            'stage_id' => $stage->id,
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
            'location_id' => $this->location->id,
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
        
        $assignedStage = TicketStage::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);
        $reviewStage = TicketStage::create(['name' => 'Review', 'slug' => 'review', 'color_code' => '#3']);
        
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10009',
            'title' => 'Assigned Ticket',
            'description' => 'Detailed description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $assignedStage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $author->id,
            'assigned_id' => $assignedUser->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($assignedUser)->post("/tickets/{$ticket->id}/for-review", [
            'message' => 'Please review my work on this ticket.',
        ]);

        $response->assertRedirect();
        
        $ticket->refresh();
        $this->assertEquals($reviewStage->id, $ticket->stage_id);
        $this->assertEquals($author->id, $ticket->assigned_id);

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
        
        $assignedStage = TicketStage::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);
        
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10010',
            'title' => 'Assigned Ticket',
            'description' => 'Detailed description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $assignedStage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $author->id,
            'assigned_id' => $assignedUser->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($otherUser)->post("/tickets/{$ticket->id}/for-review", [
            'message' => 'Sneaking a review message',
        ]);

        $response->assertStatus(403);
        
        $ticket->refresh();
        $this->assertEquals($assignedStage->id, $ticket->stage_id);
        $this->assertEquals($assignedUser->id, $ticket->assigned_id);
    }

    /**
     * Test that the author can close a ticket that is in "Review" status.
     */
    public function test_author_can_close_ticket_from_review(): void
    {
        $author = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);

        $reviewStage = TicketStage::create(['name' => 'Review', 'slug' => 'review', 'color_code' => '#3']);
        $closedStage = TicketStage::create(['name' => 'Closed', 'slug' => 'closed', 'color_code' => '#4']);

        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10011',
            'title' => 'Review Ticket',
            'description' => 'Detailed description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $reviewStage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $author->id,
            'assigned_id' => $author->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($author)->post("/tickets/{$ticket->id}/close-review", [
            'comment' => 'Resolving the ticket and closing it.',
        ]);

        $response->assertRedirect();
        
        $ticket->refresh();
        $this->assertEquals($closedStage->id, $ticket->stage_id);
        $this->assertNull($ticket->assigned_id);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $author->id,
            'content' => 'Resolving the ticket and closing it.',
            'type' => 'comment',
        ]);
    }

    /**
     * Test that the author can cancel a ticket that is in "Review" status.
     */
    public function test_author_can_cancel_ticket_from_review(): void
    {
        $author = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);

        $reviewStage = TicketStage::create(['name' => 'Review', 'slug' => 'review', 'color_code' => '#3']);
        $canceledStage = TicketStage::create(['name' => 'Canceled', 'slug' => 'canceled', 'color_code' => '#5']);

        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10012',
            'title' => 'Review Ticket',
            'description' => 'Detailed description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $reviewStage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $author->id,
            'assigned_id' => $author->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($author)->post("/tickets/{$ticket->id}/cancel-review", [
            'comment' => 'No longer needed.',
        ]);

        $response->assertRedirect();
        
        $ticket->refresh();
        $this->assertEquals($canceledStage->id, $ticket->stage_id);
        $this->assertNull($ticket->assigned_id);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $author->id,
            'content' => 'No longer needed.',
            'type' => 'comment',
        ]);
    }

    /**
     * Test that the author can reassign a ticket that is in "Review" status.
     */
    public function test_author_can_reassign_ticket_from_review(): void
    {
        $author = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $newAssignee = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);

        $reviewStage = TicketStage::create(['name' => 'Review', 'slug' => 'review', 'color_code' => '#3']);
        $assignedStage = TicketStage::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);

        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10013',
            'title' => 'Review Ticket',
            'description' => 'Detailed description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $reviewStage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $author->id,
            'assigned_id' => $author->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($author)->post("/tickets/{$ticket->id}/reassign-review", [
            'comment' => 'Please redo the implementation.',
            'assignee_id' => $newAssignee->id,
        ]);

        $response->assertRedirect();
        
        $ticket->refresh();
        $this->assertEquals($assignedStage->id, $ticket->stage_id);
        $this->assertEquals($newAssignee->id, $ticket->assigned_id);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $author->id,
            'content' => 'Ticket reassigned from review. Comment: Please redo the implementation.',
            'type' => 'comment',
        ]);
    }

    /**
     * Test that a non-author cannot perform review actions on a ticket in review status.
     */
    public function test_non_author_cannot_perform_review_actions(): void
    {
        $author = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        $otherUser = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);
        
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);

        $reviewStage = TicketStage::create(['name' => 'Review', 'slug' => 'review', 'color_code' => '#3']);

        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-10014',
            'title' => 'Review Ticket',
            'description' => 'Detailed description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $reviewStage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $author->id,
            'assigned_id' => $author->id,
            'category_1_id' => $category->id,
        ]);

        $response1 = $this->actingAs($otherUser)->post("/tickets/{$ticket->id}/close-review", [
            'comment' => 'Unauthorized close',
        ]);
        $response1->assertStatus(403);

        $response2 = $this->actingAs($otherUser)->post("/tickets/{$ticket->id}/cancel-review", [
            'comment' => 'Unauthorized cancel',
        ]);
        $response2->assertStatus(403);

        $response3 = $this->actingAs($otherUser)->post("/tickets/{$ticket->id}/reassign-review", [
            'comment' => 'Unauthorized reassign',
            'assignee_id' => $otherUser->id,
        ]);
        $response3->assertStatus(403);
    }

    /**
     * Test that tickets can be filtered by priority, stage, status, and date created.
     */
    public function test_tickets_can_be_filtered(): void
    {
        $user = User::factory()->create();

        // Seed values
        $priorityLow = Priority::create(['name' => 'Low', 'level' => 1]);
        $priorityHigh = Priority::create(['name' => 'High', 'level' => 3]);

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $stageAssigned = TicketStage::create(['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#2']);

        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // Ticket 1: Low, Open, Valid, Created 2 days ago
        $ticket1 = Ticket::create([
            'ticket_number' => 'TCK-1',
            'title' => 'Alpha Ticket',
            'description' => 'Test 1',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityLow->id,
            'stage_id' => $stageOpen->id,
            'division_id' => $division->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'created_by' => $user->id,
            'status' => 'Valid',
        ]);
        $ticket1->created_at = \Carbon\Carbon::now()->subDays(2);
        $ticket1->save();

        // Ticket 2: High, Assigned, Done, Created today
        $ticket2 = Ticket::create([
            'ticket_number' => 'TCK-2',
            'title' => 'Beta Ticket',
            'description' => 'Test 2',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityHigh->id,
            'stage_id' => $stageAssigned->id,
            'division_id' => $division->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'created_by' => $user->id,
            'status' => 'Done',
        ]);
        $ticket2->created_at = \Carbon\Carbon::now();
        $ticket2->save();

        // 1. Filter by Priority = High
        $response = $this->actingAs($user)->get("/tickets?priority_id={$priorityHigh->id}");
        $response->assertStatus(200);
        $response->assertSee('Beta Ticket');
        $response->assertDontSee('Alpha Ticket');

        // 2. Filter by Stage = Open
        $response = $this->actingAs($user)->get("/tickets?stage_id={$stageOpen->id}");
        $response->assertStatus(200);
        $response->assertSee('Alpha Ticket');
        $response->assertDontSee('Beta Ticket');

        // 3. Filter by Status = Done
        $response = $this->actingAs($user)->get("/tickets?status=Done");
        $response->assertStatus(200);
        $response->assertSee('Beta Ticket');
        $response->assertDontSee('Alpha Ticket');

        // 4. Filter by Date Created = Today
        $todayStr = \Carbon\Carbon::now()->toDateString();
        $response = $this->actingAs($user)->get("/tickets?date_created={$todayStr}");
        $response->assertStatus(200);
        $response->assertSee('Beta Ticket');
        $response->assertDontSee('Alpha Ticket');

        // 5. Combine multiple filters (High, Done) -> Beta Ticket
        $response = $this->actingAs($user)->get("/tickets?priority_id={$priorityHigh->id}&status=Done");
        $response->assertStatus(200);
        $response->assertSee('Beta Ticket');
        $response->assertDontSee('Alpha Ticket');

        // 6. Combine filters that return nothing (Low, Done) -> No tickets
        $response = $this->actingAs($user)->get("/tickets?priority_id={$priorityLow->id}&status=Done");
        $response->assertStatus(200);
        $response->assertDontSee('Alpha Ticket');
        $response->assertDontSee('Beta Ticket');
    }

    /**
     * Test that ticket numbers are generated with the ticket type's custom code prefix.
     */
    public function test_ticket_number_generation_uses_custom_ticket_type_code(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Support Agent', 'slug' => 'support-agent']);
        $user->roles()->attach($role->id);

        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Audit Request', 'code' => 'AUD']);
        $role->ticketTypes()->attach($type->id);

        $division = Division::create(['name' => 'IT']);
        $location = \App\Models\Location::create(['name' => 'Main Office']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Audit of System Log',
            'description' => 'We need to perform audit',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'location_id' => $location->id,
            'category_1_id' => $category->id,
        ]);

        $ticket = Ticket::where('title', 'Audit of System Log')->first();

        $this->assertNotNull($ticket);
        $this->assertStringStartsWith('AUD-', $ticket->ticket_number);
    }

    /**
     * Test that ticket numbers dynamically update when the ticket type's code is edited.
     */
    public function test_ticket_numbers_dynamically_update_when_ticket_type_code_is_edited(): void
    {
        $user = User::factory()->create();

        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Support Request', 'code' => 'SUP']);

        $division = Division::create(['name' => 'IT']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'SUP-2026-0001',
            'title' => 'My Test Ticket',
            'description' => 'A description',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'created_by' => $user->id,
            'status' => 'Valid',
        ]);

        // Access model property directly - should be SUP-2026-0001
        $this->assertEquals('SUP-2026-0001', $ticket->ticket_number);

        // Edit the ticket type's code to 'AUD'
        $type->code = 'AUD';
        $type->save();

        // Clear relation cache to ensure relationship is reloaded
        $ticket->unsetRelation('ticketType');

        // Access model property directly - should now be AUD-2026-0001
        $this->assertEquals('AUD-2026-0001', $ticket->ticket_number);

        // Access via index request
        $response = $this->actingAs($user)->get('/tickets');
        $response->assertStatus(200);
        $response->assertSee('AUD-2026-0001');
        $response->assertDontSee('SUP-2026-0001');
    }

    /**
     * Test that administrators can see and filter by division and department on index.
     */
    public function test_admin_can_see_and_filter_by_division_and_department_on_index(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        
        $divisionA = Division::create(['name' => 'Division Alpha']);
        $departmentA = Department::create(['name' => 'Department Alpha', 'division_id' => $divisionA->id]);
        
        $divisionB = Division::create(['name' => 'Division Beta']);
        $departmentB = Department::create(['name' => 'Department Beta', 'division_id' => $divisionB->id]);

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // Ticket A
        Ticket::create([
            'ticket_number' => 'FLR-1001',
            'title' => 'Alpha Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $divisionA->id,
            'department_id' => $departmentA->id,
            'created_by' => $admin->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
        ]);

        // Ticket B
        Ticket::create([
            'ticket_number' => 'FLR-1002',
            'title' => 'Beta Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $divisionB->id,
            'department_id' => $departmentB->id,
            'created_by' => $admin->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
        ]);

        // Visit tickets index as admin
        $response = $this->actingAs($admin)->get('/tickets');
        $response->assertStatus(200);

        // Assert that Division and Department headers are visible
        $response->assertSee('Division');
        $response->assertSee('Department');
        $response->assertSee('Division Alpha');
        $response->assertSee('Division Beta');
        $response->assertSee('Department Alpha');
        $response->assertSee('Department Beta');

        // Filter by Division Beta
        $responseFiltered = $this->actingAs($admin)->get('/tickets?division_id=' . $divisionB->id);
        $responseFiltered->assertStatus(200);
        $responseFiltered->assertSee('Beta Ticket');
        $responseFiltered->assertDontSee('Alpha Ticket');
    }

    /**
     * Test that regular users cannot see division and department columns or filters.
     */
    public function test_regular_user_cannot_see_division_and_department_filters_on_index(): void
    {
        $regularUser = User::factory()->create(['user_type' => 'regular']);
        
        $division = Division::create(['name' => 'Division Secret']);
        $department = Department::create(['name' => 'Department Secret', 'division_id' => $division->id]);

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        Ticket::create([
            'ticket_number' => 'FLR-1003',
            'title' => 'Regular View Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $regularUser->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
        ]);

        // Visit tickets index as regular user
        $response = $this->actingAs($regularUser)->get('/tickets');
        $response->assertStatus(200);

        // Assert that Division and Department columns / filters are NOT visible
        $response->assertDontSee('Division Secret');
        $response->assertDontSee('Department Secret');
        
        // Also the actual column headers shouldn't be there as text headers
        // Since we check the specific headers, we can assert we don't see them
        $response->assertDontSee('<th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Division</th>', false);
        $response->assertDontSee('<th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Department</th>', false);
    }

    /**
     * Test that calculated SLA deadline uses setting days based on ticket priority.
     */
    public function test_ticket_calculated_deadline_uses_priority_sla_days_from_settings(): void
    {
        $user = User::factory()->create();

        // Ensure settings are seeded/configured
        \App\Models\Setting::updateOrCreate(['key' => 'sla_days_critical'], ['value' => '1']);
        \App\Models\Setting::updateOrCreate(['key' => 'sla_days_high'], ['value' => '3']);
        \App\Models\Setting::updateOrCreate(['key' => 'sla_days_low'], ['value' => '5']);

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityCritical = Priority::create(['name' => 'Critical', 'level' => 3]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-SLA-TEST',
            'title' => 'Critical SLA Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityCritical->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $user->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'deadline_date' => null, // Explicitly null to test computed attribute
        ]);

        // Explicitly override created_at to control math
        $ticket->created_at = \Carbon\Carbon::parse('2026-09-21 12:00:00');
        $ticket->save();

        $expectedDeadline = \Carbon\Carbon::parse('2026-09-22 12:00:00');
        
        $this->assertEquals($expectedDeadline->format('Y-m-d H:i:s'), $ticket->calculated_deadline->format('Y-m-d H:i:s'));
    }

    /**
     * Test that process-lapsed command runs correctly using calculated fallback deadline.
     */
    public function test_process_lapsed_tickets_uses_calculated_deadline_fallback(): void
    {
        $user = User::factory()->create();

        \App\Models\Setting::updateOrCreate(['key' => 'sla_days_critical'], ['value' => '1']);

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityCritical = Priority::create(['name' => 'Critical', 'level' => 3]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-SLA-LAPSED',
            'title' => 'Critical SLA Lapsed Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityCritical->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $user->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'deadline_date' => null, // null to test fallback
        ]);

        // Put created_at 2 days ago (cutoff is 1 day ago)
        $ticket->created_at = now()->subDays(2);
        $ticket->save();

        $this->artisan('tickets:process-lapsed');

        $this->assertEquals('Lapsed', $ticket->fresh()->status);
    }

    /**
     * Test that administrators can see and search/filter by Assigned User on the index.
     */
    public function test_admin_can_see_and_filter_by_intended_user_on_index(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Alice Smith']);

        $division = Division::create(['name' => 'IT Department']);
        $department = Department::create(['name' => 'Assistance', 'division_id' => $division->id]);

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // Ticket 1: Intended for John Doe
        Ticket::create([
            'ticket_number' => 'FLR-INT-001',
            'title' => 'Johns Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'assigned_id' => $user1->id,
        ]);

        // Ticket 2: Intended for Alice Smith
        Ticket::create([
            'ticket_number' => 'FLR-INT-002',
            'title' => 'Alices Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'assigned_id' => $user2->id,
        ]);

        // Visit tickets index as admin
        $response = $this->actingAs($admin)->get('/tickets');
        $response->assertStatus(200);

        // Assert that Assigned User column header and names are visible
        $response->assertSee('Assigned User');
        $response->assertSee('John Doe');
        $response->assertSee('Alice Smith');

        // Filter search by "Smith"
        $responseFiltered = $this->actingAs($admin)->get('/tickets?to_user_search=Smith');
        $responseFiltered->assertStatus(200);
        $responseFiltered->assertSee('Alices Ticket');
        $responseFiltered->assertDontSee('Johns Ticket');
    }

    /**
     * Test that regular users cannot see the Assigned User search filter.
     */
    public function test_regular_user_cannot_see_intended_user_filters_on_index(): void
    {
        $regularUser = User::factory()->create(['user_type' => 'regular']);
        $intendedUser = User::factory()->create(['name' => 'Secret Intended User']);

        $division = Division::create(['name' => 'IT Department']);
        $department = Department::create(['name' => 'Assistance', 'division_id' => $division->id]);

        $stageOpen = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        Ticket::create([
            'ticket_number' => 'FLR-INT-003',
            'title' => 'Regular Ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stageOpen->id,
            'status' => 'Valid',
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $regularUser->id,
            'location_id' => $this->location->id,
            'category_1_id' => $category->id,
            'assigned_id' => $intendedUser->id,
        ]);

        // Visit tickets index as regular user
        $response = $this->actingAs($regularUser)->get('/tickets');
        $response->assertStatus(200);

        // Assert that Assigned User search filter is NOT visible
        $response->assertDontSee('name="to_user_search"', false);
    }
}
