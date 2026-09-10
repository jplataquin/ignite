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
                'mime_type' => 'application/pdf'
            ],
            [
                'temp_token' => $token2,
                'total_chunks' => 1,
                'file_name' => 'photo.jpg',
                'mime_type' => 'image/jpeg'
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
            'file_name' => 'report.pdf'
        ]);
        $this->assertDatabaseHas('attachments', [
            'ticket_id' => $ticket->id,
            'file_name' => 'photo.jpg'
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
    }
}
