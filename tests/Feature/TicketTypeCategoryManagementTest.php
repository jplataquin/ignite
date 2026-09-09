<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryClosure;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test guest and regular user restrictions.
     */
    public function test_non_admins_cannot_access_category_management(): void
    {
        $ticketType = TicketType::create(['name' => 'Support Request']);

        // Guests
        $this->get("/admin/ticket-types/{$ticketType->id}/categories")
            ->assertRedirect('/login');

        // Regular Users
        $regularUser = User::factory()->create(['user_type' => 'regular']);
        $this->actingAs($regularUser)->get("/admin/ticket-types/{$ticketType->id}/categories")
            ->assertStatus(403);
    }

    /**
     * Test admin can access category management.
     */
    public function test_admins_can_access_category_management(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $ticketType = TicketType::create(['name' => 'Support Request']);

        $response = $this->actingAs($admin)->get("/admin/ticket-types/{$ticketType->id}/categories");

        $response->assertStatus(200)
            ->assertSee('Manage Categories')
            ->assertSee('Support Request');
    }

    /**
     * Test admin can store category tree structure.
     */
    public function test_admins_can_store_category_tree(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $ticketType = TicketType::create(['name' => 'Support Request']);

        $tree = [
            [
                'name' => 'Hardware',
                'children' => [
                    [
                        'name' => 'Laptops',
                        'children' => [
                            ['name' => 'Macbook Air'],
                            ['name' => 'ThinkPad T14']
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Software',
                'children' => []
            ]
        ];

        $response = $this->actingAs($admin)->post("/admin/ticket-types/{$ticketType->id}/categories", [
            'categories_json' => json_stringify($tree)
        ]);

        $response->assertRedirect('/admin/ticket-types');

        // Verify Categories exist
        $this->assertDatabaseHas('categories', [
            'name' => 'Hardware',
            'ticket_type_id' => $ticketType->id
        ]);
        $this->assertDatabaseHas('categories', [
            'name' => 'Laptops',
            'ticket_type_id' => $ticketType->id
        ]);
        $this->assertDatabaseHas('categories', [
            'name' => 'Macbook Air',
            'ticket_type_id' => $ticketType->id
        ]);
        $this->assertDatabaseHas('categories', [
            'name' => 'Software',
            'ticket_type_id' => $ticketType->id
        ]);

        // Verify Closure Records
        $catHardware = Category::where('name', 'Hardware')->first();
        $catLaptops = Category::where('name', 'Laptops')->first();
        $catMacbook = Category::where('name', 'Macbook Air')->first();

        // Hardware is self-closed
        $this->assertDatabaseHas('category_closures', [
            'ancestor_id' => $catHardware->id,
            'descendant_id' => $catHardware->id,
            'depth' => 0
        ]);

        // Hardware is ancestor of Laptops with depth 1
        $this->assertDatabaseHas('category_closures', [
            'ancestor_id' => $catHardware->id,
            'descendant_id' => $catLaptops->id,
            'depth' => 1
        ]);

        // Hardware is ancestor of Macbook with depth 2
        $this->assertDatabaseHas('category_closures', [
            'ancestor_id' => $catHardware->id,
            'descendant_id' => $catMacbook->id,
            'depth' => 2
        ]);

        // Laptops is ancestor of Macbook with depth 1
        $this->assertDatabaseHas('category_closures', [
            'ancestor_id' => $catLaptops->id,
            'descendant_id' => $catMacbook->id,
            'depth' => 1
        ]);
    }

    /**
     * Test tree depth restriction of max 3 levels.
     */
    public function test_category_tree_rejects_excessive_depth(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $ticketType = TicketType::create(['name' => 'Support Request']);

        // 4 Levels (exceeds max depth of 3)
        $tree = [
            [
                'name' => 'Level 1',
                'children' => [
                    [
                        'name' => 'Level 2',
                        'children' => [
                            [
                                'name' => 'Level 3',
                                'children' => [
                                    ['name' => 'Level 4']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($admin)->post("/admin/ticket-types/{$ticketType->id}/categories", [
            'categories_json' => json_stringify($tree)
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('categories', [
            'name' => 'Level 1'
        ]);
    }
}

// Global helper for clean tests
if (!function_exists('json_stringify')) {
    function json_stringify($data) {
        return json_encode($data);
    }
}
