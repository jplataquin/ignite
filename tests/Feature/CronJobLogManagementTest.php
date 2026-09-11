<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\CronJobLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CronJobLogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Test that guests cannot access or run cron jobs.
     */
    public function test_guests_cannot_access_or_run_cron_jobs(): void
    {
        $response = $this->get('/admin/cron-logs');
        $response->assertRedirect('/login');

        $response = $this->post('/admin/cron-logs/tickets:process-lapsed/run');
        $response->assertRedirect('/login');
    }

    /**
     * Test that non-admin users cannot access or run cron jobs.
     */
    public function test_non_admins_cannot_access_or_run_cron_jobs(): void
    {
        $user = User::factory()->create(['user_type' => 'regular']);

        $response = $this->actingAs($user)->get('/admin/cron-logs');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->post('/admin/cron-logs/tickets:process-lapsed/run');
        $response->assertStatus(403);
    }

    /**
     * Test that admins can view the cron logs page.
     */
    public function test_admins_can_view_cron_logs_page(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Seed a log
        CronJobLog::create([
            'command' => 'tickets:process-lapsed',
            'status' => 'success',
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
            'duration_ms' => 1234,
            'output' => 'Completed processing lapsed tickets.',
        ]);

        $response = $this->actingAs($admin)->get('/admin/cron-logs');
        
        $response->assertStatus(200);
        $response->assertSee('Process Lapsed Tickets');
        $response->assertSee('Purge Staging Files');
        $response->assertSee('tickets:process-lapsed');
        $response->assertSee('Completed processing lapsed tickets.');
    }

    /**
     * Test that executing a command through artisan gets correctly logged.
     */
    public function test_artisan_command_execution_gets_logged(): void
    {
        // Execute the command directly using Artisan facade to test the trait
        Artisan::call('staging:purge');

        $log = CronJobLog::where('command', 'staging:purge')->first();

        $this->assertNotNull($log);
        $this->assertEquals('success', $log->status);
        $this->assertNotNull($log->completed_at);
        $this->assertGreaterThan(0, $log->duration_ms);
        $this->assertStringContainsString('Purged 0 stale staging directories.', $log->output);
    }

    /**
     * Test that admins can manually trigger a cron job run from the panel.
     */
    public function test_admins_can_manually_trigger_cron_run(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/cron-logs/staging:purge/run');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert database log was written
        $log = CronJobLog::where('command', 'staging:purge')->first();
        $this->assertNotNull($log);
        $this->assertEquals('success', $log->status);
    }
}
