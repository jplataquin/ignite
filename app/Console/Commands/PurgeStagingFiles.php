<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PurgeStagingFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staging:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge temporary file chunks older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stagingDirs = Storage::directories('staging');
        $now = Carbon::now();
        $count = 0;

        foreach ($stagingDirs as $dir) {
            $lastModified = Storage::lastModified($dir);
            $modifiedDate = Carbon::createFromTimestamp($lastModified);
            
            if ($now->diffInHours($modifiedDate) >= 24) {
                Storage::deleteDirectory($dir);
                $count++;
            }
        }

        $this->info("Purged {$count} stale staging directories.");
        return Command::SUCCESS;
    }
}
