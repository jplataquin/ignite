<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketComment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessLapsedTickets extends Command
{
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
        $now = Carbon::now('UTC');
        
        $lapsedStatus = TicketStatus::where('slug', 'lapsed')->first();
        if (!$lapsedStatus) {
            $this->error('Lapsed status not found.');
            return Command::FAILURE;
        }

        $closedStatuses = TicketStatus::whereIn('slug', ['closed', 'archived', 'resolved'])->pluck('id')->toArray();

        // Process in chunks to handle large datasets efficiently
        Ticket::whereNotIn('status_id', $closedStatuses)
              ->where('status_id', '!=', $lapsedStatus->id)
              ->with('ticketType')
              ->chunkById(100, function ($tickets) use ($now, $lapsedStatus) {
                  foreach ($tickets as $ticket) {
                      DB::transaction(function () use ($ticket, $now, $lapsedStatus) {
                          // Lock the row for update
                          $lockedTicket = Ticket::where('id', $ticket->id)->lockForUpdate()->first();
                          
                          if (!$lockedTicket) {
                              return; // Ticket might have been deleted
                          }

                          $cutoff = $lockedTicket->deadline_date 
                                      ? $lockedTicket->deadline_date 
                                      : ($lockedTicket->ticketType && $lockedTicket->ticketType->threshold_days 
                                            ? $lockedTicket->created_at->copy()->addDays($lockedTicket->ticketType->threshold_days) 
                                            : null);
                                            
                          if ($cutoff && $now->greaterThanOrEqualTo($cutoff)) {
                              // It lapsed!
                              $lockedTicket->status_id = $lapsedStatus->id;
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

        return Command::SUCCESS;
    }
}
