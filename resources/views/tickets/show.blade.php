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
                @elseif(($ticket->status->slug ?? '') === 'in-progress')
                    <span class="badge badge-progress rounded-pill px-3 py-1.5 fw-semibold">In Progress</span>
                @elseif(($ticket->status->slug ?? '') === 'resolved')
                    <span class="badge badge-resolved rounded-pill px-3 py-1.5 fw-semibold">Resolved</span>
                @else
                    <span class="badge badge-lapsed rounded-pill px-3 py-1.5 fw-semibold">Closed</span>
                @endif

                <!-- Priority Badge -->
                @if(($ticket->priority->level ?? 0) >= 4)
                    <span class="badge badge-critical rounded-pill px-3 py-1.5 fw-semibold">Critical</span>
                @elseif(($ticket->priority->level ?? 0) === 3)
                    <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-semibold text-white">High</span>
                @elseif(($ticket->priority->level ?? 0) === 2)
                    <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-semibold text-white">Medium</span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3 py-1.5 fw-semibold text-white">Low</span>
                @endif
            </div>

            <h6 class="fw-bold text-dark mb-3">Incident Properties</h6>
            <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
                <div>
                    <span class="text-muted small d-block">Division</span>
                    <span class="fw-semibold text-dark">{{ $ticket->division->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block">Department</span>
                    <span class="fw-semibold text-dark">{{ $ticket->department->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block">Primary Category</span>
                    <span class="fw-semibold text-dark">{{ $ticket->category1->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block">Deadline SLA</span>
                    <span class="fw-semibold text-dark">{{ $ticket->deadline_date ? $ticket->deadline_date->format('M d, Y H:i') : 'No SLA Threshold Set' }}</span>
                </div>
            </div>
        </div>

        <!-- Comments / Thread Area -->
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold text-dark mb-4">Comments & History</h5>
            
            <div class="mb-4">
                @forelse($ticket->comments as $comment)
                    <div class="d-flex mb-3">
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 flex-shrink-0" style="width: 38px; height: 38px;">
                            {{ substr($comment->user->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="bg-light p-3 rounded-3 w-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark small">{{ $comment->user->name ?? 'System' }}</span>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mb-0 text-muted small">{{ $comment->comment }}</p>
                        </div>
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
                    <span class="text-warning fw-semibold small d-inline-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-exclamation-triangle-fill me-1" viewBox="0 0 16 16">
                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                        </svg>
                        Unassigned Queue
                    </span>
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

        <!-- Attachments Card -->
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold text-dark mb-3">Attachments</h5>
            <div>
                @forelse($ticket->attachments as $attachment)
                    <div class="d-flex align-items-center mb-2 p-2 bg-light rounded-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark-arrow-down text-danger me-2" viewBox="0 0 16 16">
                            <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z"/>
                            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                        </svg>
                        <div class="overflow-hidden">
                            <span class="d-block fw-semibold text-dark text-truncate small" style="max-width: 180px;">{{ $attachment->file_name }}</span>
                            <span class="text-muted" style="font-size: 0.75rem;">{{ round($attachment->file_size / 1024, 1) }} KB</span>
                        </div>
                    </div>
                @empty
                    <span class="text-muted small">No attachments uploaded for this ticket.</span>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
