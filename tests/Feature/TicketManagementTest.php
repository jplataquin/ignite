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

        // Seed lookups
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priority = TicketPriority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'New Ticket Title',
            'ticket_type_id' => $type->id,
            'priority_id' => $priority->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
        ]);

        $ticket = Ticket::first();

        $this->assertNotNull($ticket);
        $this->assertEquals('New Ticket Title', $ticket->title);
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
        $priority = TicketPriority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'FLR-2026-9999',
            'title' => 'Ticket Under Inspection',
            'ticket_type_id' => $type->id,
            'priority_id' => $priority->id,
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
}
