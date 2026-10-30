<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketStage;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([PreventRequestForgery::class]);
    }

    /**
     * Test that guests cannot access ticket type management.
     */
    public function test_guests_cannot_access_ticket_type_management(): void
    {
        $response = $this->get('/admin/ticket-types');

        $response->assertRedirect('/login');
    }

    /**
     * Test that non-admin users cannot access ticket type management.
     */
    public function test_non_admins_cannot_access_ticket_type_management(): void
    {
        $user = User::factory()->create(['user_type' => 'regular']);

        $response = $this->actingAs($user)->get('/admin/ticket-types');

        $response->assertStatus(403);
    }

    /**
     * Test that admins can access ticket type management.
     */
    public function test_admins_can_access_ticket_type_management(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/ticket-types');

        $response->assertStatus(200);
        $response->assertSee('Ticket Types');
    }

    /**
     * Test that admins can view creation screen.
     */
    public function test_admins_can_view_ticket_type_creation_screen(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/ticket-types/create');

        $response->assertStatus(200);
        $response->assertSee('Create Ticket Type');
    }

    /**
     * Test that admins can store a new ticket type.
     */
    public function test_admins_can_store_new_ticket_type(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/ticket-types', [
            'name' => 'Urgent Request',
            'code' => 'URG',
            'description' => 'For extreme requests',
            'threshold_days' => 2,
        ]);

        $response->assertRedirect('/admin/ticket-types');
        $this->assertDatabaseHas('ticket_types', [
            'name' => 'Urgent Request',
            'code' => 'URG',
            'description' => 'For extreme requests',
            'threshold_days' => 2,
        ]);
    }

    /**
     * Test that admins can view edit screen.
     */
    public function test_admins_can_view_ticket_type_edit_screen(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type = TicketType::create(['name' => 'Original Type', 'code' => 'ORG']);

        $response = $this->actingAs($admin)->get("/admin/ticket-types/{$type->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Ticket Type');
    }

    /**
     * Test that admins can update a ticket type.
     */
    public function test_admins_can_update_ticket_type(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type = TicketType::create(['name' => 'Old Type', 'code' => 'OLD']);

        $response = $this->actingAs($admin)->put("/admin/ticket-types/{$type->id}", [
            'name' => 'New Name',
            'code' => 'NEW',
            'description' => 'Updated Description',
            'threshold_days' => 5,
        ]);

        $response->assertRedirect('/admin/ticket-types');
        $this->assertDatabaseHas('ticket_types', [
            'id' => $type->id,
            'name' => 'New Name',
            'code' => 'NEW',
            'description' => 'Updated Description',
            'threshold_days' => 5,
        ]);
    }

    /**
     * Test that admins can delete an unused ticket type.
     */
    public function test_admins_can_delete_unused_ticket_type(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type = TicketType::create(['name' => 'To Be Deleted']);

        $response = $this->actingAs($admin)->delete("/admin/ticket-types/{$type->id}");

        $response->assertRedirect('/admin/ticket-types');
        $this->assertDatabaseMissing('ticket_types', ['id' => $type->id]);
    }

    /**
     * Test that admins cannot delete a ticket type currently being used.
     */
    public function test_admins_cannot_delete_used_ticket_type(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type = TicketType::create(['name' => 'In-Use Type']);

        // Seed other resources to create a ticket
        $stage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Low', 'level' => 1]);
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $type->id]);

        Ticket::create([
            'ticket_number' => 'FLR-2026-8888',
            'title' => 'An active ticket',
            'ticket_type_id' => $type->id,
            'priority_option_id' => $priorityOption->id,
            'stage_id' => $stage->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'category_1_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/ticket-types/{$type->id}");

        $response->assertRedirect('/admin/ticket-types');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('ticket_types', ['id' => $type->id]);
    }

    /**
     * Test that different ticket types can have repeating codes.
     */
    public function test_ticket_type_codes_can_be_repeated(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // First ticket type with 'AUD'
        $response1 = $this->actingAs($admin)->post('/admin/ticket-types', [
            'name' => 'Internal Audit',
            'code' => 'AUD',
            'description' => 'First audit type',
        ]);
        $response1->assertRedirect('/admin/ticket-types');

        // Second ticket type also with 'AUD'
        $response2 = $this->actingAs($admin)->post('/admin/ticket-types', [
            'name' => 'External Audit',
            'code' => 'AUD',
            'description' => 'Second audit type',
        ]);
        $response2->assertRedirect('/admin/ticket-types');

        $this->assertDatabaseHas('ticket_types', [
            'name' => 'Internal Audit',
            'code' => 'AUD',
        ]);
        $this->assertDatabaseHas('ticket_types', [
            'name' => 'External Audit',
            'code' => 'AUD',
        ]);
    }

    /**
     * Test that admin can store and update ticket type with template_json.
     */
    public function test_admin_can_store_and_update_ticket_type_with_template(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $template = [
            'fields' => [
                'title' => ['value' => 'Pre-filled Title', 'locked' => true],
                'description' => ['value' => 'Pre-filled Description', 'locked' => false],
            ],
        ];

        // Store with template
        $response = $this->actingAs($admin)->post('/admin/ticket-types', [
            'name' => 'Templated Type',
            'code' => 'TPL',
            'template_json' => json_encode($template),
        ]);

        $response->assertRedirect('/admin/ticket-types');
        $type = TicketType::where('name', 'Templated Type')->first();
        $this->assertNotNull($type);
        $this->assertEquals($template, $type->template_json);

        // Update template
        $updatedTemplate = [
            'fields' => [
                'title' => ['value' => 'Updated Title', 'locked' => false],
            ],
        ];

        $updateResponse = $this->actingAs($admin)->put("/admin/ticket-types/{$type->id}", [
            'name' => 'Templated Type',
            'code' => 'TPL',
            'template_json' => json_encode($updatedTemplate),
        ]);

        $updateResponse->assertRedirect('/admin/ticket-types');
        $this->assertEquals($updatedTemplate, $type->fresh()->template_json);
    }

    /**
     * Test that ticket type create and edit screens render the visual template builder.
     */
    public function test_ticket_type_screens_render_visual_template_builder(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $type = TicketType::create([
            'name' => 'Onboarding Type',
            'code' => 'ONB',
        ]);

        $createResponse = $this->actingAs($admin)->get('/admin/ticket-types/create');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Ticket Form Template (Visual Builder)');

        $editResponse = $this->actingAs($admin)->get("/admin/ticket-types/{$type->id}/edit");
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Ticket Form Template (Visual Builder)');
    }

    /**
     * Test template builder renders text searchable user input and textarea for checklist description.
     */
    public function test_ticket_type_template_builder_renders_searchable_users_and_textarea_description(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/ticket-types/create');
        $response->assertStatus(200);

        // Assert searchable user input and autocomplete container
        $response->assertSee('id="tpl_approver_search"', false);
        $response->assertSee('id="tpl-approver-autocomplete"', false);

        // Assert textarea for checklist description
        $response->assertSee('<textarea id="tpl_approver_desc"', false);
    }

    /**
     * Test admin can configure custom stage route in ticket type template.
     */
    public function test_admin_can_configure_custom_stage_route_in_ticket_type_template(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $stage1 = TicketStage::create(['name' => 'Custom Stage 1', 'slug' => 'custom-1', 'color_code' => '#123456']);
        $stage2 = TicketStage::create(['name' => 'Custom Stage 2', 'slug' => 'custom-2', 'color_code' => '#654321']);

        // Assert builder renders the stage route section
        $createResponse = $this->actingAs($admin)->get('/admin/ticket-types/create');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Custom Stage Route');
        $createResponse->assertSee('id="tpl_enable_stage_route"', false);
        $createResponse->assertSee('id="tpl_btn_add_step"', false);

        $stage3 = TicketStage::create(['name' => 'Custom Stage 3', 'slug' => 'custom-3', 'color_code' => '#aabbcc']);

        // Store ticket type with a multi-stage step route
        $response = $this->actingAs($admin)->post('/admin/ticket-types', [
            'name' => 'Custom Routed Ticket Type',
            'code' => 'CRT',
            'template_json' => json_encode([
                'steps' => [
                    [
                        'step_number' => 1,
                        'stages' => [
                            ['id' => $stage1->id, 'name' => $stage1->name, 'color_code' => $stage1->color_code],
                        ],
                    ],
                    [
                        'step_number' => 2,
                        'stages' => [
                            ['id' => $stage2->id, 'name' => $stage2->name, 'color_code' => $stage2->color_code],
                            ['id' => $stage3->id, 'name' => $stage3->name, 'color_code' => $stage3->color_code],
                        ],
                    ],
                ],
            ]),
        ]);

        $response->assertRedirect('/admin/ticket-types');

        $ticketType = TicketType::where('name', 'Custom Routed Ticket Type')->first();
        $this->assertNotNull($ticketType);
        $this->assertIsArray($ticketType->template_json);
        $this->assertArrayHasKey('steps', $ticketType->template_json);
        $this->assertCount(2, $ticketType->template_json['steps']);
        $this->assertCount(2, $ticketType->template_json['steps'][1]['stages']);
    }
}
