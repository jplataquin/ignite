<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Attachment;
use App\Models\TicketComment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\Priority;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketController extends Controller
{
    /**
     * Display a listing of the tickets.
     */
    public function index()
    {
        $tickets = Ticket::with(['ticketType', 'priorityOption', 'status', 'creator', 'assignee'])->latest()->paginate(10);
        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $user = Auth::user();
        if ($user && $user->user_type === 'admin') {
            $ticketTypes = TicketType::all();
        } else {
            $ticketTypes = $user->roles()
                ->with('ticketTypes')
                ->get()
                ->pluck('ticketTypes')
                ->collapse()
                ->unique('id')
                ->values();
        }
        
        $priorities = Priority::orderBy('level')->get();
        $statuses = TicketStatus::all();
        $divisions = Division::all();
        $departments = Department::all();
        $categories = Category::all();
        $users = User::orderBy('name')->get();

        return view('tickets.create', compact(
            'ticketTypes', 'priorities', 'statuses', 'divisions', 'departments', 'categories', 'users'
        ));
    }

    /**
     * Store a newly created ticket with atomic chunks merge.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ticket_type_id' => [
                'required',
                'exists:ticket_types,id',
                function ($attribute, $value, $fail) {
                    $user = Auth::user();
                    if ($user && $user->user_type !== 'admin') {
                        $allowedTypeIds = $user->roles()
                            ->with('ticketTypes')
                            ->get()
                            ->pluck('ticketTypes')
                            ->collapse()
                            ->pluck('id')
                            ->toArray();
                        if (!in_array((int)$value, $allowedTypeIds)) {
                            $fail('You do not have permission to create tickets of this type.');
                        }
                    }
                }
            ],
            'priority_option_id' => 'required|exists:priorities,id',
            'status_id' => 'nullable|exists:ticket_statuses,id',
            'division_id' => 'required|exists:divisions,id',
            'department_id' => 'required|exists:departments,id',
            'category_1_id' => 'required|exists:categories,id',
            'category_2_id' => 'nullable|exists:categories,id',
            'category_3_id' => 'nullable|exists:categories,id',
            'to_user_id' => 'nullable|exists:users,id',
            'attachments_json' => 'nullable|string',
        ]);

        $attachments = [];
        if ($request->filled('attachments_json')) {
            $attachments = json_decode($request->input('attachments_json'), true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($attachments)) {
                return redirect()->back()->with('error', 'Invalid attachments data.')->withInput();
            }

            // Validate Extensions (photos, pdf, excel, documents)
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'csv', 'doc', 'docx', 'odt', 'txt', 'rtf'];
            foreach ($attachments as $attachment) {
                if (empty($attachment['temp_token']) || empty($attachment['total_chunks']) || empty($attachment['file_name'])) {
                    return redirect()->back()->with('error', 'Incomplete attachment details.')->withInput();
                }

                $extension = strtolower(pathinfo($attachment['file_name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    return redirect()->back()->with('error', "File type '{$extension}' is not allowed. Allowed types are photos, pdf, excel, and documents.") ->withInput();
                }
            }
        }

        return DB::transaction(function () use ($validated, $attachments) {
            // Generate ticket number with lock
            $latest = Ticket::lockForUpdate()->latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $ticketNumber = 'FLR-' . Carbon::now()->format('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Default to Open status if status_id is not specified
            $statusId = $validated['status_id'] ?? null;
            if (!$statusId) {
                $openStatus = TicketStatus::where('slug', 'open')->first();
                $statusId = $openStatus ? $openStatus->id : null;
            }

            if (!$statusId) {
                throw new \Exception('Default Open status not found in database.');
            }

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'ticket_type_id' => $validated['ticket_type_id'],
                'priority_option_id' => $validated['priority_option_id'],
                'status_id' => $statusId,
                'division_id' => $validated['division_id'],
                'department_id' => $validated['department_id'],
                'created_by' => Auth::id() ?? 1, // Fallback to 1 for tests/system
                'category_1_id' => $validated['category_1_id'],
                'category_2_id' => $validated['category_2_id'] ?? null,
                'category_3_id' => $validated['category_3_id'] ?? null,
                'to_user_id' => $validated['to_user_id'] ?? null,
            ]);

            // Merge File Chunks for each attachment
            foreach ($attachments as $attachment) {
                $tempToken = $attachment['temp_token'];
                $totalChunks = (int)$attachment['total_chunks'];
                $stagingDir = 'staging/' . $tempToken;
                
                $finalFileName = $attachment['file_name'];
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
                    'mime_type' => $attachment['mime_type'] ?? 'application/octet-stream',
                    'uploaded_by' => Auth::id() ?? 1,
                    'note' => $attachment['note'] ?? null,
                ]);
            }

            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully.');
        });
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load([
            'ticketType', 'status', 'division', 'department', 'creator', 'assignee', 
            'category1', 'category2', 'category3', 'attachments', 'comments.user', 'comments.attachments'
        ]);
        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit(Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user || $ticket->created_by !== $user->id) {
            abort(403, 'You are not authorized to edit this ticket.');
        }

        if ($user->user_type === 'admin') {
            $ticketTypes = TicketType::all();
        } else {
            $ticketTypes = $user->roles()
                ->with('ticketTypes')
                ->get()
                ->pluck('ticketTypes')
                ->collapse()
                ->unique('id')
                ->values();
        }
        
        $priorities = Priority::orderBy('level')->get();
        $statuses = TicketStatus::all();
        $divisions = Division::all();
        $departments = Department::all();
        
        // Only load categories belonging to the selected ticket type
        $categories = Category::where('ticket_type_id', $ticket->ticket_type_id)->get();
        
        $users = User::orderBy('name')->get();

        return view('tickets.edit', compact(
            'ticket', 'ticketTypes', 'priorities', 'statuses', 'divisions', 'departments', 'categories', 'users'
        ));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user || $ticket->created_by !== $user->id) {
            abort(403, 'You are not authorized to edit this ticket.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ticket_type_id' => [
                'required',
                'exists:ticket_types,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($user->user_type !== 'admin') {
                        $allowedTypeIds = $user->roles()
                            ->with('ticketTypes')
                            ->get()
                            ->pluck('ticketTypes')
                            ->collapse()
                            ->pluck('id')
                            ->toArray();
                        if (!in_array((int)$value, $allowedTypeIds)) {
                            $fail('You do not have permission to use this ticket type.');
                        }
                    }
                }
            ],
            'priority_option_id' => 'required|exists:priorities,id',
            'division_id' => 'required|exists:divisions,id',
            'department_id' => 'required|exists:departments,id',
            'category_1_id' => 'required|exists:categories,id',
            'category_2_id' => 'nullable|exists:categories,id',
            'category_3_id' => 'nullable|exists:categories,id',
            'to_user_id' => 'nullable|exists:users,id',
            'attachments_json' => 'nullable|string',
            'deleted_attachments' => 'nullable|string', // JSON array of attachment IDs
            'existing_notes' => 'nullable|array',
            'existing_notes.*' => 'nullable|string',
        ]);

        $attachments = [];
        if ($request->filled('attachments_json')) {
            $attachments = json_decode($request->input('attachments_json'), true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($attachments)) {
                return redirect()->back()->with('error', 'Invalid attachments data.')->withInput();
            }

            // Validate Extensions (photos, pdf, excel, documents)
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'csv', 'doc', 'docx', 'odt', 'txt', 'rtf'];
            foreach ($attachments as $attachment) {
                if (empty($attachment['temp_token']) || empty($attachment['total_chunks']) || empty($attachment['file_name'])) {
                    return redirect()->back()->with('error', 'Incomplete attachment details.')->withInput();
                }

                $extension = strtolower(pathinfo($attachment['file_name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    return redirect()->back()->with('error', "File type '{$extension}' is not allowed. Allowed types are photos, pdf, excel, and documents.") ->withInput();
                }
            }
        }

        $deletedAttachmentIds = [];
        if ($request->filled('deleted_attachments')) {
            $deletedAttachmentIds = json_decode($request->input('deleted_attachments'), true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($deletedAttachmentIds)) {
                return redirect()->back()->with('error', 'Invalid deleted attachments data.')->withInput();
            }
        }

        return DB::transaction(function () use ($request, $validated, $attachments, $deletedAttachmentIds, $ticket, $user) {
            $ticket->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'ticket_type_id' => $validated['ticket_type_id'],
                'priority_option_id' => $validated['priority_option_id'],
                'division_id' => $validated['division_id'],
                'department_id' => $validated['department_id'],
                'category_1_id' => $validated['category_1_id'],
                'category_2_id' => $validated['category_2_id'] ?? null,
                'category_3_id' => $validated['category_3_id'] ?? null,
                'to_user_id' => $validated['to_user_id'] ?? null,
            ]);

            // Handle deleted attachments
            if (!empty($deletedAttachmentIds)) {
                $attachmentsToDelete = Attachment::whereIn('id', $deletedAttachmentIds)
                    ->where('ticket_id', $ticket->id)
                    ->get();

                foreach ($attachmentsToDelete as $attachment) {
                    if (Storage::exists($attachment->file_path)) {
                        Storage::delete($attachment->file_path);
                    }
                    $attachment->delete();
                }
                
                if ($attachmentsToDelete->count() > 0) {
                    $ticket->temp_system_comment = ($ticket->temp_system_comment ? $ticket->temp_system_comment . "\n" : "Ticket details updated:\n") . "- " . $attachmentsToDelete->count() . " attachment(s) removed";
                }
            }

            // Handle existing notes updates
            if ($request->filled('existing_notes')) {
                foreach ($request->input('existing_notes') as $attachmentId => $note) {
                    if (!in_array($attachmentId, $deletedAttachmentIds)) {
                        $attachment = Attachment::where('id', $attachmentId)
                            ->where('ticket_id', $ticket->id)
                            ->first();
                        
                        if ($attachment && $attachment->note !== $note) {
                            $attachment->update(['note' => $note]);
                            $ticket->temp_system_comment = ($ticket->temp_system_comment ? $ticket->temp_system_comment . "\n" : "Ticket details updated:\n") . "- Attachment '{$attachment->file_name}' note updated";
                        }
                    }
                }
            }

            // Handle new attachments
            $newAttachmentsCount = 0;
            foreach ($attachments as $attachment) {
                $tempToken = $attachment['temp_token'];
                $totalChunks = (int)$attachment['total_chunks'];
                $stagingDir = 'staging/' . $tempToken;
                
                $finalFileName = $attachment['file_name'];
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
                    'mime_type' => $attachment['mime_type'] ?? 'application/octet-stream',
                    'uploaded_by' => Auth::id() ?? 1,
                    'note' => $attachment['note'] ?? null,
                ]);
                $newAttachmentsCount++;
            }

            if ($newAttachmentsCount > 0) {
                $ticket->temp_system_comment = ($ticket->temp_system_comment ? $ticket->temp_system_comment . "\n" : "Ticket details updated:\n") . "- {$newAttachmentsCount} new attachment(s) added";
            }
            
            // Create comment directly if the observer did not run
            if (!empty($ticket->temp_system_comment)) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => null, // Logged by system
                    'type' => 'system_event',
                    'content' => $ticket->temp_system_comment,
                ]);
                unset($ticket->temp_system_comment);
            }

            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated successfully.');
        });
    }

    /**
     * Get categories based on Ticket Type or Parent Category (AJAX API).
     */
    public function getCategories(Request $request)
    {
        $ticketTypeId = $request->query('ticket_type_id');
        $parentId = $request->query('parent_id');

        if ($parentId) {
            // Fetch descendants with depth = 1
            $categories = Category::whereHas('ancestorClosures', function ($query) use ($parentId) {
                $query->where('ancestor_id', $parentId)
                      ->where('depth', 1);
            })->get(['id', 'name']);
        } elseif ($ticketTypeId) {
            // Fetch Category 1s (no ancestors of depth > 0)
            $categories = Category::where('ticket_type_id', $ticketTypeId)
                ->whereDoesntHave('ancestorClosures', function ($query) {
                    $query->where('depth', '>', 0);
                })->get(['id', 'name']);
        } else {
            $categories = collect();
        }

        return response()->json($categories);
    }

    /**
     * Get users filtered by division and department (AJAX API).
     */
    public function getUsers(Request $request)
    {
        $divisionId = $request->query('division_id');
        $departmentId = $request->query('department_id');
        $search = $request->query('q');

        $query = User::query();

        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $users = $query->orderBy('name')->get(['id', 'name', 'user_type']);

        return response()->json($users);
    }

    /**
     * Accept/Assign a ticket to the authenticated user.
     */
    public function accept(Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        if ($ticket->assigned_to) {
            return redirect()->back()->with('error', 'This ticket has already been accepted/assigned.');
        }

        if ($ticket->to_user_id && $ticket->to_user_id !== $user->id) {
            return redirect()->back()->with('error', 'This ticket is intended for another user and can only be accepted by them.');
        }

        $assignedStatus = TicketStatus::where('slug', 'assigned')->first();

        $ticket->update([
            'assigned_to' => $user->id,
            'status_id' => $assignedStatus ? $assignedStatus->id : $ticket->status_id,
        ]);

        return redirect()->back()->with('success', 'Ticket accepted successfully.');
    }

    /**
     * Securely serve a ticket attachment file.
     */
    public function serveAttachment(Ticket $ticket, Attachment $attachment)
    {
        if ($attachment->ticket_id !== $ticket->id) {
            abort(404);
        }

        if (!Storage::exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::response($attachment->file_path);
    }

    /**
     * Add a comment to the specified ticket.
     */
    public function storeComment(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:1',
            'attachments_json' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        // Involved users are: the creator, assigned support staff, intended user, and admin users
        $isInvolved = $user->user_type === 'admin' ||
                      $ticket->created_by === $user->id ||
                      $ticket->assigned_to === $user->id ||
                      $ticket->to_user_id === $user->id;

        if (!$isInvolved) {
            return redirect()->back()->with('error', 'You are not authorized to comment on this ticket.');
        }

        $attachments = [];
        if ($request->filled('attachments_json')) {
            $attachments = json_decode($request->input('attachments_json'), true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($attachments)) {
                return redirect()->back()->with('error', 'Invalid attachments data.')->withInput();
            }

            // Validate Extensions (photos, pdf, excel, documents)
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'csv', 'doc', 'docx', 'odt', 'txt', 'rtf'];
            foreach ($attachments as $attachment) {
                if (empty($attachment['temp_token']) || empty($attachment['total_chunks']) || empty($attachment['file_name'])) {
                    return redirect()->back()->with('error', 'Incomplete attachment details.')->withInput();
                }

                $extension = strtolower(pathinfo($attachment['file_name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    return redirect()->back()->with('error', "File type '{$extension}' is not allowed. Allowed types are photos, pdf, excel, and documents.")->withInput();
                }
            }
        }

        return DB::transaction(function () use ($validated, $ticket, $user, $attachments) {
            $comment = TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => 'comment',
                'content' => $validated['content'],
            ]);

            // Merge File Chunks for each attachment
            foreach ($attachments as $attachment) {
                $tempToken = $attachment['temp_token'];
                $totalChunks = (int)$attachment['total_chunks'];
                $stagingDir = 'staging/' . $tempToken;
                
                $finalFileName = $attachment['file_name'];
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
                    'comment_id' => $comment->id,
                    'file_name' => $finalFileName,
                    'file_path' => $finalPath,
                    'file_size' => Storage::size($finalPath),
                    'mime_type' => $attachment['mime_type'] ?? 'application/octet-stream',
                    'uploaded_by' => $user->id,
                    'note' => $attachment['note'] ?? null,
                ]);
            }

            return redirect()->back()->with('success', 'Comment added successfully.');
        });
    }

    /**
     * Reassign the ticket back to the author with status "Review".
     */
    public function forReview(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        // Only the assigned user can transition the ticket to review
        if ($ticket->assigned_to !== $user->id) {
            abort(403, 'You are not authorized to submit this ticket for review.');
        }

        // The ticket must currently be in the 'Assigned' status
        if ($ticket->status?->slug !== 'assigned') {
            return redirect()->back()->with('error', 'Only assigned tickets can be submitted for review.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:1',
        ]);

        $reviewStatus = TicketStatus::where('slug', 'review')->first();
        if (!$reviewStatus) {
            return redirect()->back()->with('error', 'Review status not found.');
        }

        return DB::transaction(function () use ($validated, $ticket, $reviewStatus, $user) {
            $ticket->update([
                'assigned_to' => $ticket->created_by, // Assign back to the author
                'status_id' => $reviewStatus->id,
            ]);

            // Create comment with the review message
            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => 'comment',
                'content' => $validated['message'],
            ]);

            return redirect()->back()->with('success', 'Ticket has been successfully submitted for review and assigned back to the author.');
        });
    }
}
