<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\Priority;
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
        $tickets = Ticket::with(['ticketType', 'priority', 'priorityOption', 'status', 'creator', 'assignee'])->latest()->paginate(10);
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
        
        $severities = TicketPriority::orderBy('level')->get();
        $priorities = Priority::orderBy('level')->get();
        $statuses = TicketStatus::all();
        $divisions = Division::all();
        $departments = Department::all();
        $categories = Category::all();

        return view('tickets.create', compact(
            'ticketTypes', 'severities', 'priorities', 'statuses', 'divisions', 'departments', 'categories'
        ));
    }

    /**
     * Store a newly created ticket with atomic chunks merge.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
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
            'priority_id' => 'required|exists:ticket_priorities,id',
            'priority_option_id' => 'required|exists:priorities,id',
            'status_id' => 'nullable|exists:ticket_statuses,id',
            'division_id' => 'required|exists:divisions,id',
            'department_id' => 'required|exists:departments,id',
            'category_1_id' => 'required|exists:categories,id',
            'category_2_id' => 'nullable|exists:categories,id',
            'category_3_id' => 'nullable|exists:categories,id',
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
                'priority_id' => $validated['priority_id'],
                'priority_option_id' => $validated['priority_option_id'],
                'status_id' => $statusId,
                'division_id' => $validated['division_id'],
                'department_id' => $validated['department_id'],
                'created_by' => Auth::id() ?? 1, // Fallback to 1 for tests/system
                'category_1_id' => $validated['category_1_id'],
                'category_2_id' => $validated['category_2_id'] ?? null,
                'category_3_id' => $validated['category_3_id'] ?? null,
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
        $ticket->load(['ticketType', 'priority', 'status', 'division', 'department', 'creator', 'assignee', 'category1', 'attachments', 'comments.user']);
        return view('tickets.show', compact('ticket'));
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
}
