@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-sm me-3 d-flex align-items-center" style="min-height: 38px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
        </a>
        <h1 class="h2 fw-bold text-dark mb-0">{{ $ticket->ticket_number }}</h1>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle-fill text-success me-2" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle-fill text-danger me-2" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4 mb-4">
    <!-- Main Detail Panel -->
    <div class="col-12 col-lg-8">
        <div class="card fd-card p-4 shadow-sm mb-4">
            <h4 class="fw-bold text-dark mb-2">{{ $ticket->title }}</h4>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <span class="badge bg-light text-dark border px-3 py-1.5 fw-semibold">{{ $ticket->ticketType->name ?? 'N/A' }}</span>
                
                <!-- Status Badge -->
                @if(($ticket->status->slug ?? '') === 'open')
                    <span class="badge badge-open rounded-pill px-3 py-1.5 fw-semibold">Open</span>
                @elseif(($ticket->status->slug ?? '') === 'assigned')
                    <span class="badge badge-assigned rounded-pill px-3 py-1.5 fw-semibold">Assigned</span>
                @elseif(($ticket->status->slug ?? '') === 'review')
                    <span class="badge badge-review rounded-pill px-3 py-1.5 fw-semibold">Review</span>
                @elseif(($ticket->status->slug ?? '') === 'closed')
                    <span class="badge badge-closed rounded-pill px-3 py-1.5 fw-semibold">Closed</span>
                @elseif(($ticket->status->slug ?? '') === 'canceled')
                    <span class="badge badge-canceled rounded-pill px-3 py-1.5 fw-semibold">Canceled</span>
                @else
                    <span class="badge rounded-pill px-3 py-1.5 fw-semibold text-white" style="background-color: {{ $ticket->status->color_code ?? '#6b7280' }};">
                        {{ $ticket->status->name ?? 'Unknown' }}
                    </span>
                @endif

                <!-- Priority Badge -->
                @if(($ticket->priorityOption->name ?? '') === 'Critical')
                    <span class="badge badge-critical rounded-pill px-3 py-1.5 fw-semibold">Priority: Critical</span>
                @elseif(($ticket->priorityOption->name ?? '') === 'High')
                    <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-semibold text-white">Priority: High</span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3 py-1.5 fw-semibold text-white">Priority: Low</span>
                @endif
            </div>

            <!-- Description Card (Flat Material Style) -->
            <div class="mb-4 bg-white p-4 rounded-4 border shadow-none" style="border-color: #e2e8f0 !important;">
                <span class="text-secondary small d-block mb-2 fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem; letter-spacing: 0.8px; color: #64748b !important;">Description</span>
                <p class="text-dark mb-0" style="white-space: pre-line; line-height: 1.6; font-size: 0.95rem; color: #334155 !important;">{{ $ticket->description ?: 'No detailed description provided.' }}</p>
            </div>

            <!-- File Attachments List (Flat Material Style) -->
            @if($ticket->attachments->count() > 0)
                <div class="mb-4 bg-white p-4 rounded-4 border shadow-none" style="border-color: #e2e8f0 !important;">
                    <span class="text-secondary small d-block mb-3 fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem; letter-spacing: 0.8px; color: #64748b !important;">File Attachments</span>
                    <div class="row row-cols-1 g-3">
                        @foreach($ticket->attachments as $index => $attachment)
                            <div class="d-flex flex-column p-3 rounded-3 mb-2" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center previewable-attachment" style="cursor: pointer;"
                                         data-url="{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}"
                                         data-name="{{ $attachment->file_name }}"
                                         data-mime="{{ $attachment->mime_type }}">

                                        <!-- Flat Thumbnail -->
                                        <div class="flex-shrink-0 border bg-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; overflow: hidden; border-radius: 8px; border-color: #cbd5e1 !important;">
                                            @php
                                                $isImage = str_starts_with($attachment->mime_type ?? '', 'image/') || 
                                                           in_array(strtolower(pathinfo($attachment->file_name ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            @endphp
                                            @if($isImage)
                                                <img src="{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}" class="w-100 h-100" style="object-fit: cover;">
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text text-secondary" viewBox="0 0 16 16">
                                                    <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                                                    <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                                                </svg>
                                            @endif
                                        </div>

                                        <div class="overflow-hidden">
                                            <span class="d-block fw-bold text-dark text-truncate" style="max-width: 250px; font-size: 0.88rem; color: #1e293b !important;">
                                                <span class="badge bg-secondary me-1.5" style="font-size: 0.7rem; font-weight: 500; background-color: #64748b !important;">Attachment #{{ $index + 1 }}</span>
                                                {{ $attachment->file_name }}
                                            </span>
                                            <span class="text-muted d-block mt-0.5" style="font-size: 0.72rem; color: #64748b !important;">{{ round($attachment->file_size / 1024, 1) }} KB</span>
                                        </div>
                                    </div>

                                    <!-- Download Button (Flat) -->
                                    <a href="{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}" download="{{ $attachment->file_name }}" class="btn btn-sm btn-light p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 8px; background-color: #f1f5f9; border: 1px solid #e2e8f0; color: #475569;" title="Download file">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                        </svg>
                                    </a>
                                </div>
                                @if($attachment->note)
                                    <div class="mt-2.5 pt-2 border-top text-muted" style="font-size: 0.82rem; line-height: 1.5; white-space: pre-wrap; border-top-color: #e2e8f0 !important; color: #475569 !important;">
                                        <strong class="text-dark small d-block mb-0.5" style="color: #334155 !important; font-weight: 600;">Note:</strong>
                                        {{ $attachment->note }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <h6 class="fw-bold text-dark mb-3">Incident Properties</h6>
            <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
                <div>
                    <span class="text-muted small d-block">Date Created</span>
                    <span class="fw-semibold text-dark">{{ $ticket->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block">Priority</span>
                    <span class="fw-semibold text-dark">{{ $ticket->priorityOption->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block">Division</span>
                    <span class="fw-semibold text-dark">{{ $ticket->division->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block">Department</span>
                    <span class="fw-semibold text-dark">{{ $ticket->department->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block">Category 1</span>
                    <span class="fw-semibold text-dark">{{ $ticket->category1->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block">Category 2</span>
                    <span class="fw-semibold text-dark">{{ $ticket->category2->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block">Category 3</span>
                    <span class="fw-semibold text-dark">{{ $ticket->category3->name ?? '-' }}</span>
                </div>
                @if($ticket->toUser)
                <div>
                    <span class="text-muted small d-block">Intended User</span>
                    <span class="fw-semibold text-dark text-danger">{{ $ticket->toUser->name }}</span>
                </div>
                @endif
                <div>
                    <span class="text-muted small d-block">Deadline SLA</span>
                    <span class="fw-semibold text-dark">{{ $ticket->deadline_date ? $ticket->deadline_date->format('M d, Y H:i') : 'No SLA Threshold Set' }}</span>
                </div>
            </div>
        </div>

        <!-- Comments / Thread Area -->
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold text-dark mb-4">Comments & History</h5>

            <!-- Comment Submission Form (for involved actors only) -->
            @if(Auth::user()->user_type === 'admin' || 
                $ticket->created_by === Auth::id() || 
                $ticket->assigned_to === Auth::id() || 
                $ticket->to_user_id === Auth::id())
                <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <textarea class="form-control form-control-sm" name="content" rows="3" placeholder="Write a comment..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 btn-sm" style="min-height: auto;">Add Comment</button>
                    </div>
                </form>
                <hr class="my-4 text-muted">
            @endif
            
            <div class="mb-4">
                @forelse($ticket->comments as $comment)
                    <div class="d-flex mb-3">
                        @if($comment->type === 'system_event')
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 flex-shrink-0" style="width: 38px; height: 38px; background-color: #fef3c7 !important;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16">
                                    <path d="M9.405 1.02c.63-1.026 2.01-1.026 2.64 0l.445.725c.196.32.556.518.932.518h.853c1.205 0 2.102 1.173 1.635 2.274l-.326.77a1.002 1.002 0 0 0 .153 1.054l.582.72c.81.99.274 2.476-.948 2.6l-.888.093a1.002 1.002 0 0 0-.85.73l-.225.86c-.347 1.155-1.637 1.603-2.585.876l-.682-.544a1.002 1.002 0 0 0-1.127-.08l-.804.43c-1.122.6-2.457-.35-2.22-1.57l.18-.94a1.002 1.002 0 0 0-.616-1.1l-.856-.4a1.1 1.1 0 0 1-.58-1.55l.43-.804a1.002 1.002 0 0 0-.08-1.127l-.544-.682c-.727-.948-.28-2.238.876-2.585l.86-.225a1.002 1.002 0 0 0 .73-.85l.093-.888c.123-1.222 1.61-1.758 2.6-1.048l.72.582zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                </svg>
                            </div>
                            <div class="bg-light p-3 rounded-3 w-100 border border-warning" style="background-color: #fffbef !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">System Event</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mb-0 text-muted small fw-semibold">{{ $comment->content }}</p>
                            </div>
                        @else
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 flex-shrink-0" style="width: 38px; height: 38px;">
                                {{ substr($comment->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="bg-light p-3 rounded-3 w-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">{{ $comment->user->name ?? 'System' }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mb-0 text-muted small">{{ $comment->content }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">No comments yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar Detail Panel -->
    <div class="col-12 col-lg-4">
        <!-- Assignee & Owner Card -->
        <div class="card fd-card p-4 shadow-sm mb-4">
            <h5 class="fw-bold text-dark mb-3">Actors</h5>
            <div class="mb-3">
                <span class="text-muted small d-block mb-1">Assigned Support Staff</span>
                @if($ticket->assignee)
                    <div class="d-flex align-items-center">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.85rem;">
                            {{ substr($ticket->assignee->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">{{ $ticket->assignee->name }}</div>
                            <span class="text-muted" style="font-size: 0.75rem;">Staff Specialist</span>
                        </div>
                    </div>
                @else
                    <span class="text-warning fw-semibold small d-inline-flex align-items-center mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-exclamation-triangle-fill me-1" viewBox="0 0 16 16">
                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                        </svg>
                        Unassigned Queue
                    </span>

                    @if(!$ticket->to_user_id || $ticket->to_user_id === Auth::id())
                        <form action="{{ route('tickets.accept', $ticket) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center" style="min-height: 38px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-lg me-1.5" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-5.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                                Accept Ticket
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning border-0 p-2.5 rounded text-dark small mt-2 mb-0 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-lock-fill text-warning me-1.5" viewBox="0 0 16 16">
                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                            </svg>
                            Reserved for {{ $ticket->toUser->name }}
                        </div>
                    @endif
                @endif
            </div>

            <hr class="my-3 text-muted">

            <div>
                <span class="text-muted small d-block mb-1">Created By</span>
                <div class="d-flex align-items-center">
                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.85rem;">
                        {{ substr($ticket->creator->name ?? 'S', 0, 1) }}
                    </div>
                    <div>
                        <div class="fw-bold text-dark small">{{ $ticket->creator->name ?? 'System' }}</div>
                        <span class="text-muted" style="font-size: 0.75rem;">Requester</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
