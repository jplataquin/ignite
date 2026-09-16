<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\Location;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test that guests cannot access location management.
     */
    public function test_guests_cannot_access_location_management(): void
    {
        $this->get(route('admin.locations.index'))->assertRedirect(route('login'));
        $this->get(route('admin.locations.create'))->assertRedirect(route('login'));
        $this->post(route('admin.locations.store'), ['name' => 'Test Location'])->assertRedirect(route('login'));
    }

    /**
     * Test that non-admin users cannot access location management.
     */
    public function test_non_admins_cannot_access_location_management(): void
    {
        $user = User::factory()->create(['user_type' => 'agent']);

        $this->actingAs($user)->get(route('admin.locations.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.locations.create'))->assertStatus(403);
        $this->actingAs($user)->post(route('admin.locations.store'), ['name' => 'Test Location'])->assertStatus(403);
    }

    /**
     * Test that admin users can CRUD locations.
     */
    public function test_admins_can_manage_locations(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // 1. List Locations (Index)
        $response = $this->actingAs($admin)->get(route('admin.locations.index'));
        $response->assertStatus(200);
        $response->assertSee('Locations');

        // 2. Create Location (Create Screen)
        $response = $this->actingAs($admin)->get(route('admin.locations.create'));
        $response->assertStatus(200);

        // 3. Store Location (Store Action)
        $response = $this->actingAs($admin)->post(route('admin.locations.store'), [
            'name' => 'New York Office',
        ]);
        $response->assertRedirect(route('admin.locations.index'));
        $this->assertDatabaseHas('locations', ['name' => 'New York Office']);

        $location = Location::where('name', 'New York Office')->first();

        // 4. Edit Location (Edit Screen)
        $response = $this->actingAs($admin)->get(route('admin.locations.edit', $location));
        $response->assertStatus(200);
        $response->assertSee('New York Office');

        // 5. Update Location (Update Action)
        $response = $this->actingAs($admin)->put(route('admin.locations.update', $location), [
            'name' => 'London Branch',
        ]);
        $response->assertRedirect(route('admin.locations.index'));
        $this->assertDatabaseHas('locations', ['name' => 'London Branch']);
        $this->assertDatabaseMissing('locations', ['name' => 'New York Office']);

        // 6. Delete Location (Destroy Action)
        $response = $this->actingAs($admin)->delete(route('admin.locations.destroy', $location));
        $response->assertRedirect(route('admin.locations.index'));
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    /**
     * Test that unique validation is enforced on locations.
     */
    public function test_unique_location_name_validation(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        Location::create(['name' => 'Existing Location']);

        $response = $this->actingAs($admin)->post(route('admin.locations.store'), [
            'name' => 'Existing Location',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test that locations with tickets cannot be deleted.
     */
    public function test_locations_with_tickets_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $location = Location::create(['name' => 'Main Office']);

        // Seed dependencies
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $type = TicketType::create(['name' => 'Incident']);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        // Create a ticket referencing this location
        Ticket::create([
            'ticket_number' => 'FLR-2026-0001',
            'title' => 'Sample Ticket',
            'description' => 'Help needed.',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'created_by' => $admin->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.locations.destroy', $location));
        $response->assertRedirect(route('admin.locations.index'));
        $response->assertSessionHas('error', "Cannot delete Location '{$location->name}' because it has active tickets associated with it.");
        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }
}
