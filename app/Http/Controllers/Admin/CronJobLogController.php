<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CronJobLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class CronJobLogController extends Controller
{
    /**
     * Display a listing of cron job logs and their current statuses.
     */
    public function index()
    {
        $logs = CronJobLog::latest('id')->paginate(20);
        
        $availableJobs = [
            [
                'command' => 'tickets:process-lapsed',
                'name' => 'Process Lapsed Tickets',
                'description' => 'Process open tickets and mark them as lapsed if SLA has expired.',
                'frequency' => 'Hourly',
            ],
            [
                'command' => 'staging:purge',
                'name' => 'Purge Staging Files',
                'description' => 'Purge temporary file chunks older than 24 hours from storage.',
                'frequency' => 'Daily',
            ],
        ];

        return view('admin.cron-logs.index', compact('logs', 'availableJobs'));
    }

    /**
     * Force run a scheduled job manually on demand.
     */
    public function run(string $command)
    {
        $allowedCommands = ['tickets:process-lapsed', 'staging:purge'];
        
        if (!in_array($command, $allowedCommands)) {
            return redirect()->back()->with('error', 'Unauthorized command execution.');
        }

        try {
            // Execute the Artisan command
            Artisan::call($command);
            
            return redirect()->back()->with('success', "Command '{$command}' executed successfully. Logs have been updated.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Failed to execute '{$command}': " . $e->getMessage());
        }
    }
}
