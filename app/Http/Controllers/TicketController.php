<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Attachment;
use App\Models\TicketComment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\Location;
use App\Models\TicketStage;
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
    public function index(Request $request)
    {
        $query = Ticket::with(['ticketType', 'priorityOption', 'stage', 'creator', 'assignee']);

        if ($request->filled('priority_id')) {
            $query->where('priority_option_id', $request->input('priority_id'));
        }

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->input('stage_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_created')) {
            $query->whereDate('created_at', $request->input('date_created'));
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();

        $priorities = Priority::orderBy('level')->get();
        $stages = TicketStage::all();
        $statuses = ['Valid', 'Done', 'Lapsed'];

        return view('tickets.index', compact('tickets', 'priorities', 'stages', 'statuses'));
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
        $stages = TicketStage::all();
        $divisions = Division::all();
        $departments = Department::all();
        $locations = Location::orderBy('name')->get();
        $categories = Category::all();
        $users = User::orderBy('name')->get();

        return view('tickets.create', compact(
            'ticketTypes', 'priorities', 'stages', 'divisions', 'departments', 'locations', 'categories', 'users'
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
            'stage_id' => 'nullable|exists:ticket_stages,id',
            'division_id' => 'required|exists:divisions,id',
            'department_id' => 'nullable|exists:departments,id',
            'location_id' => 'required|exists:locations,id',
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
            $allowedExtensions = ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'csv', 'doc', 'docx', 'odt', 'txt', 'rtf'];
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
            
            $ticketType = TicketType::find($validated['ticket_type_id']);
            $prefix = ($ticketType && !empty($ticketType->code)) ? $ticketType->code : 'FLR';
            $ticketNumber = $prefix . '-' . Carbon::now()->format('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Default to Open stage if stage_id is not specified
            $stageId = $validated['stage_id'] ?? null;
            if (!$stageId) {
                $openStage = TicketStage::where('slug', 'open')->first();
                $stageId = $openStage ? $openStage->id : null;
            }

            if (!$stageId) {
                throw new \Exception('Default Open stage not found in database.');
            }

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'ticket_type_id' => $validated['ticket_type_id'],
                'priority_option_id' => $validated['priority_option_id'],
                'stage_id' => $stageId,
                'status' => 'Valid',
                'division_id' => $validated['division_id'],
                'department_id' => $validated['department_id'] ?? null,
                'location_id' => $validated['location_id'],
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
            'ticketType', 'stage', 'division', 'department', 'location', 'creator', 'assignee', 
            'category1', 'category2', 'category3', 'attachments', 'comments.user', 'comments.attachments'
        ]);

        $divisions = collect();
        $departments = collect();
        if ($ticket->stage?->slug === 'review' && $ticket->created_by === Auth::id()) {
            $divisions = \App\Models\Division::orderBy('name')->get();
            $departments = \App\Models\Department::orderBy('name')->get();
        }

        return view('tickets.show', compact('ticket', 'divisions', 'departments'));
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit(Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user || ($user->user_type !== 'admin' && $ticket->created_by !== $user->id)) {
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
        $stages = TicketStage::all();
        $divisions = Division::all();
        $departments = Department::all();
        $locations = Location::orderBy('name')->get();
        
        // Only load categories belonging to the selected ticket type
        $categories = Category::where('ticket_type_id', $ticket->ticket_type_id)->get();
        
        $users = User::orderBy('name')->get();

        return view('tickets.edit', compact(
            'ticket', 'ticketTypes', 'priorities', 'stages', 'divisions', 'departments', 'locations', 'categories', 'users'
        ));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user || ($user->user_type !== 'admin' && $ticket->created_by !== $user->id)) {
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
            'department_id' => 'nullable|exists:departments,id',
            'location_id' => 'required|exists:locations,id',
            'category_1_id' => 'required|exists:categories,id',
            'category_2_id' => 'nullable|exists:categories,id',
            'category_3_id' => 'nullable|exists:categories,id',
            'to_user_id' => 'nullable|exists:users,id',
            'stage_id' => 'nullable|exists:ticket_stages,id',
            'assigned_to' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($ticket, $user) {
                    if ($user->user_type !== 'admin' && $value == $ticket->created_by) {
                        $fail('The ticket cannot be assigned to its creator.');
                    }
                }
            ],
            'deadline_date' => 'nullable|date',
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
            $allowedExtensions = ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'csv', 'doc', 'docx', 'odt', 'txt', 'rtf'];
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
            $updateData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'ticket_type_id' => $validated['ticket_type_id'],
                'priority_option_id' => $validated['priority_option_id'],
                'division_id' => $validated['division_id'],
                'department_id' => $validated['department_id'] ?? null,
                'location_id' => $validated['location_id'],
                'category_1_id' => $validated['category_1_id'],
                'category_2_id' => $validated['category_2_id'] ?? null,
                'category_3_id' => $validated['category_3_id'] ?? null,
                'to_user_id' => $validated['to_user_id'] ?? null,
            ];

            if ($user->user_type === 'admin') {
                $updateData['stage_id'] = $validated['stage_id'] ?? $ticket->stage_id;
                $updateData['assigned_to'] = array_key_exists('assigned_to', $validated) ? $validated['assigned_to'] : $ticket->assigned_to;
                $updateData['deadline_date'] = $request->filled('deadline_date') ? \Carbon\Carbon::parse($request->input('deadline_date')) : null;
            }

            $ticket->update($updateData);

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
        $ticketId = $request->query('ticket_id');

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

        if ($ticketId) {
            $ticket = Ticket::find($ticketId);
            if ($ticket) {
                $query->where('id', '!=', $ticket->created_by);
            }
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

        if ($ticket->created_by === $user->id) {
            return redirect()->back()->with('error', 'You cannot accept your own ticket.');
        }

        if ($ticket->assigned_to) {
            return redirect()->back()->with('error', 'This ticket has already been accepted/assigned.');
        }

        if ($ticket->to_user_id && $ticket->to_user_id !== $user->id) {
            return redirect()->back()->with('error', 'This ticket is intended for another user and can only be accepted by them.');
        }

        $assignedStage = TicketStage::where('slug', 'assigned')->first();

        $ticket->update([
            'assigned_to' => $user->id,
            'stage_id' => $assignedStage ? $assignedStage->id : $ticket->stage_id,
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
            $allowedExtensions = ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'csv', 'doc', 'docx', 'odt', 'txt', 'rtf'];
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

        // The ticket must currently be in the 'Assigned' stage
        if ($ticket->stage?->slug !== 'assigned') {
            return redirect()->back()->with('error', 'Only assigned tickets can be submitted for review.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:1',
        ]);

        $reviewStage = TicketStage::where('slug', 'review')->first();
        if (!$reviewStage) {
            return redirect()->back()->with('error', 'Review stage not found.');
        }

        return DB::transaction(function () use ($validated, $ticket, $reviewStage, $user) {
            $ticket->update([
                'assigned_to' => $ticket->created_by, // Assign back to the author
                'stage_id' => $reviewStage->id,
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

    /**
     * Close the ticket from the review status.
     */
    public function closeReview(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user || $ticket->created_by !== $user->id) {
            abort(403, 'You are not authorized to close this ticket.');
        }

        if ($ticket->stage?->slug !== 'review') {
            return redirect()->back()->with('error', 'Only tickets in review can be closed.');
        }

        $validated = $request->validate([
            'comment' => 'required|string|min:1',
        ]);

        $closedStage = TicketStage::where('slug', 'closed')->first();
        if (!$closedStage) {
            return redirect()->back()->with('error', 'Closed stage not found.');
        }

        return DB::transaction(function () use ($validated, $ticket, $closedStage, $user) {
            $ticket->update([
                'stage_id' => $closedStage->id,
                'status' => 'Done',
                'assigned_to' => null,
            ]);

            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => 'comment',
                'content' => $validated['comment'],
            ]);

            return redirect()->back()->with('success', 'Ticket closed successfully.');
        });
    }

    /**
     * Cancel the ticket from the review status.
     */
    public function cancelReview(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user || $ticket->created_by !== $user->id) {
            abort(403, 'You are not authorized to cancel this ticket.');
        }

        if ($ticket->stage?->slug !== 'review') {
            return redirect()->back()->with('error', 'Only tickets in review can be canceled.');
        }

        $validated = $request->validate([
            'comment' => 'required|string|min:1',
        ]);

        $canceledStage = TicketStage::where('slug', 'canceled')->first();
        if (!$canceledStage) {
            return redirect()->back()->with('error', 'Canceled stage not found.');
        }

        return DB::transaction(function () use ($validated, $ticket, $canceledStage, $user) {
            $ticket->update([
                'stage_id' => $canceledStage->id,
                'status' => 'Done',
                'assigned_to' => null,
            ]);

            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => 'comment',
                'content' => $validated['comment'],
            ]);

            return redirect()->back()->with('success', 'Ticket canceled successfully.');
        });
    }

    /**
     * Reassign the ticket from the review status.
     */
    public function reassignReview(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user || $ticket->created_by !== $user->id) {
            abort(403, 'You are not authorized to reassign this ticket.');
        }

        if ($ticket->stage?->slug !== 'review') {
            return redirect()->back()->with('error', 'Only tickets in review can be reassigned.');
        }

        $validated = $request->validate([
            'comment' => 'required|string|min:1',
            'assignee_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($ticket) {
                    if ($value == $ticket->created_by) {
                        $fail('The ticket cannot be assigned to its creator.');
                    }
                }
            ],
        ]);

        $assignedStage = TicketStage::where('slug', 'assigned')->first();
        if (!$assignedStage) {
            return redirect()->back()->with('error', 'Assigned stage not found.');
        }

        return DB::transaction(function () use ($validated, $ticket, $assignedStage, $user) {
            $ticket->update([
                'stage_id' => $assignedStage->id,
                'assigned_to' => $validated['assignee_id'],
            ]);

            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => 'comment',
                'content' => "Ticket reassigned from review. Comment: " . $validated['comment'],
            ]);

            return redirect()->back()->with('success', 'Ticket reassigned successfully.');
        });
    }
}
