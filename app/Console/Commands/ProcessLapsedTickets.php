<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessLapsedTickets extends Command
{
    use LogsExecution;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:process-lapsed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and mark open tickets as lapsed if they exceed SLA thresholds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->startLogging();

        try {
            $now = Carbon::now('UTC');

            // Find tickets with system status 'Valid' that have lapsed
            Ticket::where('status', 'Valid')
                  ->with('ticketType')
                  ->chunkById(100, function ($tickets) use ($now) {
                      foreach ($tickets as $ticket) {
                          DB::transaction(function () use ($ticket, $now) {
                              // Lock the row for update
                              $lockedTicket = Ticket::where('id', $ticket->id)->lockForUpdate()->first();
                              
                              if (!$lockedTicket) {
                                  return; // Ticket might have been deleted
                              }

                              $cutoff = $lockedTicket->calculated_deadline;
                                                
                              if ($cutoff && $now->greaterThanOrEqualTo($cutoff)) {
                                  // It lapsed!
                                  $lockedTicket->status = 'Lapsed';
                                  $lockedTicket->save();
                                  
                                  TicketComment::create([
                                      'ticket_id' => $lockedTicket->id,
                                      'user_id' => null, // System event
                                      'type' => 'system_event',
                                      'content' => 'SLA threshold exceeded. Ticket marked as Lapsed automatically.',
                                  ]);
                                  
                                  // Observers should handle notification dispatch
                                  $this->info("Ticket {$lockedTicket->ticket_number} marked as lapsed.");
                              }
                          });
                      }
                  });

            $this->finishLogging('success');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->finishLogging('failed', $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }
}
