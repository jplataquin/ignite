@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center" style="min-height: 38px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="h2 fw-bold text-dark mb-0">{{ $ticket->ticket_number }}</h1>
                <span class="badge bg-primary text-white">Approval / Clearance</span>
                <span class="badge bg-light text-dark border">{{ ucfirst($ticket->approval_mode ?? 'sequential') }} Flow</span>
            </div>
        </div>
    </div>
    @if(Auth::id() === $ticket->created_by || (Auth::user() && Auth::user()->user_type === 'admin'))
        <div>
            <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-primary d-flex align-items-center gap-2" style="min-height: 38px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                </svg>
                <span>Edit Ticket</span>
            </a>
        </div>
    @endif
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

<!-- Clearance / Sign-off Checklist Card -->
<div class="card fd-card p-4 shadow-sm mb-4 border-start border-4 border-primary">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1">Clearance Sign-off Checklist</h5>
            <p class="text-muted small mb-0">
                @if($ticket->approval_mode === 'sequential')
                    Approvers must sign off in strict sequential order.
                @else
                    Approvers can sign off in any order.
                @endif
            </p>
        </div>
        @php
            $stageSlug = $ticket->stage?->slug ?? 'pending-approval';
            $totalCount = $ticket->approvals->count();
            $approvedCount = $ticket->approvals->where('status', 'approved')->count();
            $rejectedCount = $ticket->approvals->where('status', 'rejected')->count();
        @endphp
        <div class="text-end">
            <span class="badge {{ $rejectedCount > 0 ? 'bg-danger' : ($approvedCount === $totalCount && $totalCount > 0 ? 'bg-success' : 'bg-warning text-dark') }} px-3 py-2 fs-6">
                {{ $ticket->stage->name ?? 'Pending Approval' }}
            </span>
            <div class="small text-muted mt-1">{{ $approvedCount }} of {{ $totalCount }} Approvals Completed</div>
        </div>
    </div>

    <!-- Approvals List -->
    <div class="list-group list-group-flush border rounded-3 bg-white">
        @php
            $previousAllApproved = true;
        @endphp
        @forelse($ticket->approvals as $approval)
            @php
                $isCurrentUser = Auth::id() === $approval->user_id;
                $isAdmin = Auth::user() && Auth::user()->user_type === 'admin';
                $canAct = ($isCurrentUser || $isAdmin) && $approval->status === 'pending';
                if ($ticket->approval_mode === 'sequential' && ! $previousAllApproved) {
                    $canAct = false;
                }
            @endphp
            <div class="list-group-item p-3 {{ $canAct ? 'bg-light-subtle border-primary-subtle' : '' }}">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold {{ $approval->status === 'approved' ? 'bg-success text-white' : ($approval->status === 'rejected' ? 'bg-danger text-white' : 'bg-secondary text-white') }}" style="width: 38px; height: 38px;">
                            #{{ $approval->sequence_order }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $approval->user->name ?? 'Unknown Approver' }}</div>
                            <small class="text-muted">{{ $approval->user->department->name ?? ($approval->user->division->name ?? 'General') }}</small>
                            @if($approval->actioned_at)
                                <div class="text-muted" style="font-size: 0.75rem;">Actioned: {{ $approval->actioned_at->format('M d, Y H:i') }}</div>
                            @endif
                            @if($approval->comment)
                                <div class="mt-1 small text-dark p-2 rounded bg-light border" style="font-size: 0.8rem;">
                                    <em>"{{ $approval->comment }}"</em>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @if($approval->status === 'approved')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fw-semibold">
                                ✓ Approved
                            </span>
                        @elseif($approval->status === 'rejected')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 fw-semibold">
                                ✗ Rejected
                            </span>
                        @else
                            @if($ticket->approval_mode === 'sequential' && ! $previousAllApproved)
                                <span class="badge bg-light text-muted border px-2.5 py-1.5 small">
                                    Waiting for prior sign-offs
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 fw-semibold">
                                    Pending Sign-off
                                </span>
                            @endif

                            @if($canAct)
                                <!-- Trigger Buttons for Approver -->
                                <button type="button" class="btn btn-sm btn-success px-3" data-bs-toggle="collapse" data-bs-target="#action-box-{{ $approval->id }}">
                                    Sign Off / Reject
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                @if($canAct)
                    <!-- Action Form Collapse Box -->
                    <div class="collapse mt-3 pt-3 border-top" id="action-box-{{ $approval->id }}">
                        <div class="card p-3 border-0 bg-white shadow-sm">
                            <h6 class="fw-bold text-dark mb-2">Record Your Sign-off</h6>
                            <div class="mb-2">
                                <label for="comment-{{ $approval->id }}" class="form-label small text-muted">Notes / Comments</label>
                                <textarea id="comment-{{ $approval->id }}" class="form-control form-control-sm" rows="2" placeholder="Optional comments for approval, required for rejection..."></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <form action="{{ route('tickets.approvals.reject', [$ticket, $approval]) }}" method="POST" onsubmit="return confirmReject(this, 'comment-{{ $approval->id }}');">
                                    @csrf
                                    <input type="hidden" name="comment" class="hidden-comment">
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3">Reject</button>
                                </form>
                                <form action="{{ route('tickets.approvals.approve', [$ticket, $approval]) }}" method="POST" onsubmit="return syncApproveComment(this, 'comment-{{ $approval->id }}');">
                                    @csrf
                                    <input type="hidden" name="comment" class="hidden-comment">
                                    <button type="submit" class="btn btn-sm btn-success px-4">Approve & Sign Off</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            @php
                if ($approval->status !== 'approved') {
                    $previousAllApproved = false;
                }
            @endphp
        @empty
            <div class="p-4 text-center text-muted small">No approvers specified for this clearance.</div>
        @endforelse
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Main Ticket Detail Panel -->
    <div class="col-12 col-lg-8">
        <div class="card fd-card p-4 shadow-sm mb-4">
            <h4 class="fw-bold text-dark mb-2">{{ $ticket->title }}</h4>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <span class="badge bg-light text-dark border px-3 py-1.5 fw-semibold">{{ $ticket->ticketType->name ?? 'N/A' }}</span>
                <span class="badge bg-secondary-subtle text-secondary px-3 py-1.5 fw-semibold">Stage: {{ $ticket->stage->name ?? 'N/A' }}</span>
                <span class="badge bg-info-subtle text-info px-3 py-1.5 fw-semibold">{{ $ticket->priorityOption->name ?? 'Normal' }} Priority</span>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark small text-uppercase mb-2">Description</h6>
                <div class="p-3 bg-light rounded-3 text-dark" style="white-space: pre-wrap; font-size: 0.95rem;">{{ $ticket->description }}</div>
            </div>

            <!-- Attachments -->
            @if($ticket->attachments->whereNull('comment_id')->count() > 0)
                <div class="mb-2">
                    <h6 class="fw-bold text-dark small text-uppercase mb-2">Ticket Attachments</h6>
                    <div class="row row-cols-1 row-cols-sm-2 g-2">
                        @foreach($ticket->attachments->whereNull('comment_id') as $attachment)
                            <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light small">
                                <div class="text-truncate me-2">
                                    <span class="fw-semibold text-dark">{{ $attachment->file_name }}</span>
                                    <small class="text-muted d-block">{{ round($attachment->file_size / 1024, 1) }} KB</small>
                                </div>
                                <a href="{{ route('tickets.attachments.serve', [$ticket, $attachment]) }}" download="{{ $attachment->file_name }}" class="btn btn-sm btn-outline-secondary">Download</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Comments & Audit History Area -->
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold text-dark mb-4">Comments & Audit History</h5>

            <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST" class="mb-4" id="comment-form">
                @csrf
                <div class="mb-3">
                    <textarea class="form-control form-control-sm" name="content" rows="3" placeholder="Write a comment or status note..." required></textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4 btn-sm">Add Comment</button>
                </div>
            </form>

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
                                    <span class="fw-bold text-dark small">{{ $comment->user->name ?? 'User' }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mb-0 text-muted small">{{ $comment->content }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">No comments or activity yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Metadata Sidebar -->
    <div class="col-12 col-lg-4">
        <div class="card fd-card p-4 shadow-sm mb-4">
            <h5 class="fw-bold text-dark mb-3">Ticket Information</h5>
            
            <div class="mb-3">
                <span class="text-muted small d-block">Requester</span>
                <span class="fw-semibold text-dark">{{ $ticket->creator->name ?? 'Unknown' }}</span>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">Division</span>
                <span class="fw-semibold text-dark">{{ $ticket->division->name ?? 'N/A' }}</span>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">Department</span>
                <span class="fw-semibold text-dark">{{ $ticket->department->name ?? 'N/A' }}</span>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">Location</span>
                <span class="fw-semibold text-dark">{{ $ticket->location->name ?? 'N/A' }}</span>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">Category 1</span>
                <span class="fw-semibold text-dark">{{ $ticket->category1->name ?? 'N/A' }}</span>
            </div>

            @if($ticket->category2)
                <div class="mb-3">
                    <span class="text-muted small d-block">Category 2</span>
                    <span class="fw-semibold text-dark">{{ $ticket->category2->name }}</span>
                </div>
            @endif

            @if($ticket->category3)
                <div class="mb-3">
                    <span class="text-muted small d-block">Category 3</span>
                    <span class="fw-semibold text-dark">{{ $ticket->category3->name }}</span>
                </div>
            @endif

            <div>
                <span class="text-muted small d-block">Created At</span>
                <span class="fw-semibold text-dark">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
            </div>
        </div>
    </div>
</div>

<script>
    function syncApproveComment(form, textareaId) {
        const text = document.getElementById(textareaId).value;
        form.querySelector('.hidden-comment').value = text;
        return true;
    }

    function confirmReject(form, textareaId) {
        const text = document.getElementById(textareaId).value.trim();
        if (!text) {
            alert('A comment/reason is required to reject an approval.');
            return false;
        }
        if (!confirm('Are you sure you want to reject this ticket approval?')) {
            return false;
        }
        form.querySelector('.hidden-comment').value = text;
        return true;
    }
</script>
@endsection
