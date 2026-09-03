<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketController extends Controller
{
    /**
     * Store a newly created ticket with atomic chunks merge.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'status_id' => 'required|exists:ticket_statuses,id',
            'division_id' => 'required|exists:divisions,id',
            'department_id' => 'required|exists:departments,id',
            'category_1_id' => 'required|exists:categories,id',
            'temp_token' => 'nullable|string',
            'total_chunks' => 'nullable|integer',
            'file_name' => 'nullable|string',
            'mime_type' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            // Generate ticket number with lock
            $latest = Ticket::lockForUpdate()->latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $ticketNumber = 'FLR-' . Carbon::now()->format('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'title' => $validated['title'],
                'ticket_type_id' => $validated['ticket_type_id'],
                'priority_id' => $validated['priority_id'],
                'status_id' => $validated['status_id'],
                'division_id' => $validated['division_id'],
                'department_id' => $validated['department_id'],
                'created_by' => Auth::id() ?? 1, // Fallback to 1 for tests/system
                'category_1_id' => $validated['category_1_id'],
            ]);

            // Merge File Chunks
            if (!empty($validated['temp_token']) && !empty($validated['total_chunks'])) {
                $tempToken = $validated['temp_token'];
                $totalChunks = (int)$validated['total_chunks'];
                $stagingDir = 'staging/' . $tempToken;
                
                $finalFileName = $validated['file_name'] ?? 'attachment_' . time();
                $finalPath = 'attachments/' . $ticket->id . '/' . $finalFileName;
                
                Storage::makeDirectory('attachments/' . $ticket->id);
                
                $finalContent = '';
                for ($i = 1; $i <= $totalChunks; $i++) {
                    $chunkPath = $stagingDir . '/' . $i . '.part';
                    if (Storage::exists($chunkPath)) {
                        $finalContent .= Storage::get($chunkPath);
                        Storage::delete($chunkPath);
                    } else {
                        throw new \Exception('Missing chunk ' . $i);
                    }
                }
                
                Storage::put($finalPath, $finalContent);
                Storage::deleteDirectory($stagingDir);
                
                Attachment::create([
                    'ticket_id' => $ticket->id,
                    'file_name' => $finalFileName,
                    'file_path' => $finalPath,
                    'file_size' => Storage::size($finalPath),
                    'mime_type' => $validated['mime_type'] ?? 'application/octet-stream',
                    'uploaded_by' => Auth::id() ?? 1,
                ]);
            }

            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully.');
        });
    }
}
