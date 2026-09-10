<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test that guests cannot access or edit system settings.
     */
    public function test_guests_cannot_access_or_edit_system_settings(): void
    {
        $response = $this->get('/admin/settings');
        $response->assertRedirect('/login');

        $response = $this->put('/admin/settings', [
            'sla_days_low' => 10,
        ]);
        $response->assertRedirect('/login');
    }

    /**
     * Test that non-admin users cannot access or edit system settings.
     */
    public function test_non_admins_cannot_access_or_edit_system_settings(): void
    {
        $user = User::factory()->create(['user_type' => 'regular']);

        $response = $this->actingAs($user)->get('/admin/settings');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->put('/admin/settings', [
            'sla_days_low' => 10,
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test that administrators can view system settings.
     */
    public function test_admins_can_view_system_settings(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Seed settings
        Setting::create(['key' => 'sla_days_low', 'value' => '5']);
        Setting::create(['key' => 'sla_days_high', 'value' => '3']);
        Setting::create(['key' => 'sla_days_critical', 'value' => '1']);
        Setting::create(['key' => 'sla_days_assign_low', 'value' => '2']);
        Setting::create(['key' => 'sla_days_assign_high', 'value' => '1']);
        Setting::create(['key' => 'sla_days_assign_critical', 'value' => '0']);

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertSee('System Settings');
        $response->assertSee('sla_days_low');
        $response->assertSee('sla_days_high');
        $response->assertSee('sla_days_critical');
    }

    /**
     * Test that administrators can update system settings.
     */
    public function test_admins_can_update_system_settings(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->put('/admin/settings', [
            'sla_days_low' => '15',
            'sla_days_high' => '10',
            'sla_days_critical' => '5',
            'sla_days_assign_low' => '6',
            'sla_days_assign_high' => '4',
            'sla_days_assign_critical' => '2',
        ]);

        $response->assertRedirect('/admin/settings');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('settings', ['key' => 'sla_days_low', 'value' => '15']);
        $this->assertDatabaseHas('settings', ['key' => 'sla_days_high', 'value' => '10']);
        $this->assertDatabaseHas('settings', ['key' => 'sla_days_critical', 'value' => '5']);
        $this->assertDatabaseHas('settings', ['key' => 'sla_days_assign_low', 'value' => '6']);
        $this->assertDatabaseHas('settings', ['key' => 'sla_days_assign_high', 'value' => '4']);
        $this->assertDatabaseHas('settings', ['key' => 'sla_days_assign_critical', 'value' => '2']);
    }

    /**
     * Test validation rules for system settings.
     */
    public function test_system_settings_validation(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Test missing values
        $response = $this->actingAs($admin)->put('/admin/settings', [
            'sla_days_low' => '',
        ]);
        $response->assertSessionHasErrors(['sla_days_low', 'sla_days_high', 'sla_days_critical']);

        // Test non-integer and negative values
        $response = $this->actingAs($admin)->put('/admin/settings', [
            'sla_days_low' => -5,
            'sla_days_high' => 'not-an-integer',
            'sla_days_critical' => 1,
            'sla_days_assign_low' => 2,
            'sla_days_assign_high' => 1,
            'sla_days_assign_critical' => 0,
        ]);
        $response->assertSessionHasErrors(['sla_days_low', 'sla_days_high']);
    }
}
