<?php

namespace Tests\Feature;

use App\Models\TicketStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test guest access restrictions.
     */
    public function test_guests_cannot_access_stage_management(): void
    {
        $this->get('/admin/ticket-stages')->assertRedirect('/login');
    }

    /**
     * Test non-admin access restrictions.
     */
    public function test_non_admins_cannot_access_stage_management(): void
    {
        $user = User::factory()->create(['user_type' => 'regular']);

        $this->actingAs($user)->get('/admin/ticket-stages')->assertStatus(403);
    }

    /**
     * Test ticket stages CRUD operations.
     */
    public function test_ticket_stage_crud_operations(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Create Stage
        $this->actingAs($admin)->post('/admin/ticket-stages', [
            'name' => 'Awaiting Verification',
            'color_code' => '#FFAA00',
        ])->assertRedirect('/admin/ticket-stages');

        $this->assertDatabaseHas('ticket_stages', [
            'name' => 'Awaiting Verification',
            'slug' => 'awaiting-verification',
            'color_code' => '#FFAA00',
        ]);

        $stage = TicketStage::where('slug', 'awaiting-verification')->first();

        // Edit View
        $this->actingAs($admin)->get("/admin/ticket-stages/{$stage->id}/edit")
            ->assertStatus(200)
            ->assertSee('Awaiting Verification');

        // Update Stage
        $this->actingAs($admin)->put("/admin/ticket-stages/{$stage->id}", [
            'name' => 'Awaiting Customer Verification',
            'color_code' => '#00AAFF',
        ])->assertRedirect('/admin/ticket-stages');

        $this->assertDatabaseHas('ticket_stages', [
            'id' => $stage->id,
            'name' => 'Awaiting Customer Verification',
            'slug' => 'awaiting-customer-verification',
            'color_code' => '#00AAFF',
        ]);

        // Delete Stage
        $this->actingAs($admin)->delete("/admin/ticket-stages/{$stage->id}")
            ->assertRedirect('/admin/ticket-stages');

        $this->assertDatabaseMissing('ticket_stages', [
            'id' => $stage->id,
        ]);
    }

    /**
     * Test that core stages cannot be deleted.
     */
    public function test_core_stages_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $openStage = TicketStage::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#123456']);

        $this->actingAs($admin)->delete("/admin/ticket-stages/{$openStage->id}")
            ->assertRedirect('/admin/ticket-stages')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('ticket_stages', ['id' => $openStage->id]);
    }
}
