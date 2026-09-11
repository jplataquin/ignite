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
    @if(Auth::id() === $ticket->created_by)
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

@php
    $statusSlug = $ticket->status?->slug ?? 'open';
    $currentStep = 1;
    if ($statusSlug === 'assigned') {
        $currentStep = 2;
    } elseif ($statusSlug === 'review') {
        $currentStep = 3;
    } elseif (in_array($statusSlug, ['closed', 'canceled', 'lapsed'])) {
        $currentStep = 4;
    }
@endphp

<!-- Ticket Progress Stepper -->
<div class="card fd-card p-4 shadow-sm mb-4">
    <div class="position-relative py-2">
        <!-- Connecting Line Track -->
        <div class="progress position-absolute top-50 start-0 end-0 translate-middle-y" style="height: 4px; z-index: 0; transform: translateY(-50%) !important;">
            <div class="progress-bar {{ $currentStep == 4 && in_array($statusSlug, ['canceled', 'lapsed']) ? 'bg-danger' : 'bg-primary' }}" role="progressbar" style="width: {{ (($currentStep - 1) / 3) * 100 }}%;" aria-valuenow="{{ (($currentStep - 1) / 3) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        
        <!-- Stepper Nodes -->
        <div class="d-flex justify-content-between position-relative" style="z-index: 1;">
            <!-- Step 1: Open -->
            <div class="text-center d-flex flex-column align-items-center" style="width: 80px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 {{ $currentStep >= 1 ? 'bg-primary border-primary text-white' : 'bg-white border-secondary text-muted' }}" style="width: 38px; height: 38px; font-weight: bold; font-size: 0.9rem;">
                    @if($currentStep > 1)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-5.425a.247.247 0 0 1 .02-.022Z"/>
                        </svg>
                    @else
                        1
                    @endif
                </div>
                <span class="mt-2 small fw-bold {{ $currentStep >= 1 ? 'text-primary' : 'text-muted' }}" style="font-size: 0.78rem;">Open</span>
            </div>

            <!-- Step 2: Assigned -->
            <div class="text-center d-flex flex-column align-items-center" style="width: 80px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 {{ $currentStep >= 2 ? 'bg-primary border-primary text-white' : 'bg-white text-muted' }}" style="width: 38px; height: 38px; font-weight: bold; font-size: 0.9rem; border-color: {{ $currentStep >= 2 ? '' : '#cbd5e1 !important' }};">
                    @if($currentStep > 2)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-5.425a.247.247 0 0 1 .02-.022Z"/>
                        </svg>
                    @else
                        2
                    @endif
                </div>
                <span class="mt-2 small fw-bold {{ $currentStep >= 2 ? 'text-primary' : 'text-muted' }}" style="font-size: 0.78rem;">Assigned</span>
            </div>

            <!-- Step 3: Review -->
            <div class="text-center d-flex flex-column align-items-center" style="width: 80px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 {{ $currentStep >= 3 ? 'bg-primary border-primary text-white' : 'bg-white text-muted' }}" style="width: 38px; height: 38px; font-weight: bold; font-size: 0.9rem; border-color: {{ $currentStep >= 3 ? '' : '#cbd5e1 !important' }};">
                    @if($currentStep > 3)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-5.425a.247.247 0 0 1 .02-.022Z"/>
                        </svg>
                    @else
                        3
                    @endif
                </div>
                <span class="mt-2 small fw-bold {{ $currentStep >= 3 ? 'text-primary' : 'text-muted' }}" style="font-size: 0.78rem;">Review</span>
            </div>

            <!-- Step 4: Closed / Canceled -->
            <div class="text-center d-flex flex-column align-items-center" style="width: 80px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 {{ $currentStep >= 4 ? ($statusSlug === 'closed' ? 'bg-success border-success text-white' : 'bg-danger border-danger text-white') : 'bg-white text-muted' }}" style="width: 38px; height: 38px; font-weight: bold; font-size: 0.9rem; border-color: {{ $currentStep >= 4 ? '' : '#cbd5e1 !important' }};">
                    @if($currentStep == 4)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-5.425a.247.247 0 0 1 .02-.022Z"/>
                        </svg>
                    @else
                        4
                    @endif
                </div>
                <span class="mt-2 small fw-bold {{ $currentStep >= 4 ? ($statusSlug === 'closed' ? 'text-success' : 'text-danger') : 'text-muted' }}" style="font-size: 0.78rem;">
                    @if($statusSlug === 'canceled')
                        Canceled
                    @elseif($statusSlug === 'lapsed')
                        Lapsed
                    @else
                        Closed
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

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
                                    <div class="mt-2.5 pt-2 border-top text-muted" style="font-size: 0.82rem; line-height: 1.5; border-top-color: #e2e8f0 !important; color: #475569 !important;">
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
                <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST" class="mb-4" id="comment-form">
                    @csrf
                    <div class="mb-3">
                        <textarea class="form-control form-control-sm" name="content" rows="3" placeholder="Write a comment..." required></textarea>
                    </div>

                    <!-- File Drop Zone for Comment -->
                    <div class="mb-3">
                        <div id="comment-drop-zone" class="p-3 border border-2 border-dashed rounded text-center bg-light" style="cursor: pointer; border-color: #cbd5e1 !important; transition: all 0.2s ease;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-cloud-upload text-secondary mb-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.562 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 9.124 15 8c0-1.124-1.021-2-2.312-2a.5.5 0 0 1-.5-.436C12.16 3.161 10.22 1.5 8 1.5c-1.854 0-3.43 1.15-4.113 2.872a.5.5 0 0 1-.687.238C1.83 3.993 1 5.027 1 6.51 1 8.04 2.222 9.25 3.75 9.25H6a.5.5 0 0 1 0 1H3.75C1.65 10.25 0 8.528 0 6.5c0-1.63 1.05-3.003 2.512-3.488a5.5 5.5 0 0 1 1.894-1.67z"/>
                                <path fill-rule="evenodd" d="M7.646 5.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708z"/>
                            </svg>
                            <p class="mb-0 fw-semibold text-dark small" style="font-size: 0.8rem;">Drag & drop files here, or click to browse</p>
                            <input type="file" id="comment-file-input" class="d-none" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xls,.xlsx,.csv,.doc,.docx,.odt,.txt,.rtf">
                        </div>
                        <!-- Dynamic List of Comment Upload Progresses -->
                        <div id="comment-upload-progress-list" class="mt-2"></div>
                        <input type="hidden" name="attachments_json" id="comment_attachments_json">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" id="comment-submit-btn" class="btn btn-primary px-4 btn-sm" style="min-height: auto;">Add Comment</button>
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

                                @if($comment->attachments->count() > 0)
                                    <div class="mt-3 pt-2.5 border-top border-light-subtle" style="border-top: 1px solid #e2e8f0 !important;">
                                        <div class="row row-cols-1 g-2">
                                            @foreach($comment->attachments as $index => $attachment)
                                                <div class="d-flex flex-column p-2 rounded-2" style="background-color: #f1f5f9; border: 1px solid #e2e8f0; font-size: 0.8rem;">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center previewable-attachment" style="cursor: pointer;"
                                                             data-url="{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}"
                                                             data-name="{{ $attachment->file_name }}"
                                                             data-mime="{{ $attachment->mime_type }}">
                                                            <div class="flex-shrink-0 border rounded bg-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; overflow: hidden; border-color: #cbd5e1 !important;">
                                                                @php
                                                                    $isImage = str_starts_with($attachment->mime_type ?? '', 'image/') ||
                                                                               in_array(strtolower(pathinfo($attachment->file_name ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                                @endphp
                                                                @if($isImage)
                                                                    <img src="{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}" class="w-100 h-100" style="object-fit: cover;">
                                                                @else
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text text-secondary" viewBox="0 0 16 16">
                                                                        <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                                                                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                                                                    </svg>
                                                                @endif
                                                            </div>
                                                            <div class="ms-2.5 min-width-0" style="margin-left: 10px;">
                                                                <span class="fw-semibold text-dark text-truncate d-block" style="max-width: 200px;">{{ $attachment->file_name }}</span>
                                                                <span class="text-muted d-block" style="font-size: 0.7rem;">{{ round($attachment->file_size / 1024, 1) }} KB</span>
                                                            </div>
                                                        </div>
                                                        <a href="{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}" download="{{ $attachment->file_name }}" class="btn btn-sm btn-light p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 6px; background-color: #e2e8f0; border: 1px solid #cbd5e1; color: #475569;" title="Download file">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                    @if($attachment->note)
                                                        <div class="mt-1.5 p-2 rounded bg-white text-muted" style="font-size: 0.72rem; border-left: 2.5px solid #cbd5e1; margin-top: 6px;">
                                                            {{ $attachment->note }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
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
            <h5 class="fw-bold text-dark mb-3">Actions</h5>
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
                    @if($ticket->assigned_to === Auth::id() && $ticket->status?->slug === 'assigned')
                        <button type="button" class="btn btn-sm btn-outline-primary w-100 d-flex align-items-center justify-content-center mt-3" style="min-height: 38px;" data-bs-toggle="modal" data-bs-target="#forReviewModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-send-fill me-1.5" viewBox="0 0 16 16">
                                <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>
                            </svg>
                            For Review
                        </button>
                    @endif
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

            @if($ticket->status?->slug === 'review' && $ticket->created_by === Auth::id())
                <div class="mt-4 border-top pt-3">
                    <h6 class="fw-bold text-dark mb-2">Review Action Required</h6>
                    <p class="text-muted small mb-3">As the creator, please review the resolution details and choose an action:</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-success d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#closeReviewModal" style="min-height: 38px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-circle-fill me-1.5" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                            Close Ticket
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#cancelReviewModal" style="min-height: 38px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-x-circle-fill me-1.5" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                            </svg>
                            Cancel Ticket
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#reassignReviewModal" style="min-height: 38px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-repeat me-1.5" viewBox="0 0 16 16">
                                <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-1.1 2c.135-.786.06-1.594-.253-2.31a.25.25 0 0 1 .01-.25l1.39-2.28a.25.25 0 0 1 .459.13a6.5 6.5 0 0 1-1.606 4.71z"/>
                                <path fill-rule="evenodd" d="M1.534 9H.5a.5.5 0 0 0 0 1h2.5a.5.5 0 0 0 .5-.5V7a.5.5 0 0 0-1 0v1.5a5.503 5.503 0 0 1 9.873-2.583a.5.5 0 1 0 .802-.6a6.5 6.5 0 0 0-11.64 3.083"/>
                            </svg>
                            Reassign Ticket
                        </button>
                    </div>
                </div>
            @endif

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

@if($ticket->assignee && $ticket->assigned_to === Auth::id() && $ticket->status?->slug === 'assigned')
<!-- For Review Message Modal -->
<div class="modal fade" id="forReviewModal" tabindex="-1" aria-labelledby="forReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-light border-bottom-0 pb-1" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title fw-bold text-dark" id="forReviewModalLabel">Submit for Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tickets.for-review', $ticket) }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <p class="text-muted small mb-3">Provide a mandatory message detailing your findings, instructions, or resolution notes for the author before submitting the ticket for review.</p>
                    
                    <div class="mb-3">
                        <label for="review_message" class="form-label fw-semibold text-dark small">Review Message / Findings</label>
                        <textarea id="review_message" name="message" class="form-control" rows="4" placeholder="Type your review note here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($ticket->status?->slug === 'review' && $ticket->created_by === Auth::id())
<!-- Close Review Modal -->
<div class="modal fade" id="closeReviewModal" tabindex="-1" aria-labelledby="closeReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-light border-bottom-0 pb-1" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title fw-bold text-dark" id="closeReviewModalLabel">Close Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tickets.close-review', $ticket) }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <p class="text-muted small mb-3">Provide a mandatory comment detailing the resolution and why you are closing this ticket.</p>
                    <div class="mb-3">
                        <label for="close_comment" class="form-label fw-semibold text-dark small">Closing Comment</label>
                        <textarea id="close_comment" name="comment" class="form-control" rows="4" placeholder="Enter your closing notes here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-3">Close Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Review Modal -->
<div class="modal fade" id="cancelReviewModal" tabindex="-1" aria-labelledby="cancelReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-light border-bottom-0 pb-1" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title fw-bold text-dark" id="cancelReviewModalLabel">Cancel Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tickets.cancel-review', $ticket) }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <p class="text-muted small mb-3">Provide a mandatory comment explaining why you are canceling this ticket.</p>
                    <div class="mb-3">
                        <label for="cancel_comment" class="form-label fw-semibold text-dark small">Cancellation Reason</label>
                        <textarea id="cancel_comment" name="comment" class="form-control" rows="4" placeholder="Enter your cancellation reason here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">Cancel Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reassign Review Modal -->
<div class="modal fade" id="reassignReviewModal" tabindex="-1" aria-labelledby="reassignReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-light border-bottom-0 pb-1" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title fw-bold text-dark" id="reassignReviewModalLabel">Reassign Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tickets.reassign-review', $ticket) }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <p class="text-muted small mb-3">Select a division and department to filter eligible support staff, then search and select a new assignee.</p>
                    
                    <!-- Division -->
                    <div class="mb-3">
                        <label for="reassign_division_id" class="form-label fw-semibold text-dark small">Division</label>
                        <select id="reassign_division_id" name="division_id" class="form-select form-select-sm" required>
                            <option value="">Select Division</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department -->
                    <div class="mb-3">
                        <label for="reassign_department_id" class="form-label fw-semibold text-dark small">Department</label>
                        <select id="reassign_department_id" name="department_id" class="form-select form-select-sm" disabled>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" data-division-id="{{ $dept->division_id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assignee Autosuggest Search -->
                    <div class="mb-3 position-relative">
                        <label for="reassign_user_search" class="form-label fw-semibold text-dark small">Select New Assignee</label>
                        <input type="text" id="reassign_user_search" class="form-control form-control-sm" placeholder="Type to search users..." autocomplete="off" disabled required>
                        <input type="hidden" id="reassign_assignee_id" name="assignee_id">
                        <ul id="reassign-autocomplete-results" class="dropdown-menu w-100 shadow-sm" style="display: none; max-height: 200px; overflow-y: auto;">
                            <!-- Search results will be injected here -->
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label for="reassign_comment" class="form-label fw-semibold text-dark small">Reassignment Instructions / Comment</label>
                        <textarea id="reassign_comment" name="comment" class="form-control" rows="4" placeholder="Type instructions for the new assignee here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Reassign Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropZone = document.getElementById('comment-drop-zone');
        const fileInput = document.getElementById('comment-file-input');
        const progressList = document.getElementById('comment-upload-progress-list');
        const attachmentsJsonInput = document.getElementById('comment_attachments_json');
        const submitBtn = document.getElementById('comment-submit-btn');

        if (!dropZone || !fileInput) return;

        const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB chunks
        const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'csv', 'doc', 'docx', 'odt', 'txt', 'rtf'];

        let completedAttachments = [];
        let activeUploadsCount = 0;

        // Handle Click to Browse
        dropZone.addEventListener('click', () => fileInput.click());

        // Handle Drag & Drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-dark', 'text-white', 'opacity-75');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('bg-dark', 'text-white', 'opacity-75');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-dark', 'text-white', 'opacity-75');
            if (e.dataTransfer.files.length > 0) {
                handleFiles(e.dataTransfer.files);
            }
        });

        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                handleFiles(this.files);
            }
        });

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                const extension = file.name.split('.').pop().toLowerCase();
                if (!ALLOWED_EXTENSIONS.includes(extension)) {
                    alert(`File "${file.name}" is not allowed. Allowed types are photos, pdf, excel, and documents.`);
                    return;
                }
                handleFile(file);
            });
        }

        function handleFile(file) {
            const identifier = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            // Increment active uploads and disable submit button
            activeUploadsCount++;
            submitBtn.disabled = true;

            // Append progress row to progress list
            const progressRowId = `comment-progress-row-${identifier}`;
            const rowHTML = `
                <div id="${progressRowId}" class="p-2 mb-2 bg-white rounded border shadow-sm d-flex flex-column comment-attachment-row" data-identifier="${identifier}">
                    <div class="d-flex gap-3 align-items-center w-100">
                        <!-- Icon / Thumbnail Container -->
                        <div id="comment-preview-${identifier}" class="flex-shrink-0 border rounded bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden;">
                            <!-- Populate via JS -->
                        </div>

                        <!-- Progress Info -->
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-dark small fw-semibold text-truncate" style="max-width: 250px;">
                                    <span class="badge bg-secondary me-1 comment-attachment-number" style="font-size: 0.65rem;">Attachment #1</span>
                                    <span style="font-size: 0.8rem;">${escapeHtml(file.name)}</span>
                                </span>
                                <span id="comment-percentage-${identifier}" class="text-muted small fw-semibold" style="font-size: 0.75rem;">0%</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div id="comment-bar-${identifier}" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div id="comment-status-${identifier}" class="text-muted mt-0.5" style="font-size: 0.7rem;">Preparing upload...</div>
                        </div>

                        <!-- Delete Button -->
                        <div class="flex-shrink-0 ms-2">
                            <button type="button" id="comment-delete-${identifier}" class="btn btn-sm btn-outline-danger d-none d-flex align-items-center justify-content-center p-0 rounded-circle" style="width: 28px; height: 28px;" onclick="deleteCommentAttachment('${identifier}')" title="Delete attachment">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-1.995 0L3.83 3.5h8.34zM5 5.033V13h1V5.033zm4 0V13h1V5.033z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Note Input (Visible on complete) -->
                    <div id="comment-note-container-${identifier}" class="mt-1.5 d-none">
                        <label for="comment-note-${identifier}" class="form-label text-muted small fw-semibold mb-1" style="font-size: 0.75rem;">File Note (Optional)</label>
                        <textarea id="comment-note-${identifier}" class="form-control form-control-sm" rows="1" placeholder="Enter an optional note/description..." style="font-size: 0.75rem;"></textarea>
                    </div>
                </div>
            `;
            progressList.insertAdjacentHTML('beforeend', rowHTML);
            updateAttachmentNumbers();

            // Populate preview container
            const isImage = file.type.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(file.name.split('.').pop().toLowerCase());
            const previewContainer = document.getElementById(`comment-preview-${identifier}`);
            let previewUrl = '';
            if (isImage) {
                const imgUrl = URL.createObjectURL(file);
                previewContainer.innerHTML = `<img src="${imgUrl}" class="w-100 h-100" style="object-fit: cover;">`;
                previewUrl = imgUrl;
            } else {
                previewContainer.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text text-secondary" viewBox="0 0 16 16">
                        <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                    </svg>
                `;
            }

            previewContainer.classList.add('previewable-attachment');
            previewContainer.style.cursor = 'pointer';
            previewContainer.dataset.url = previewUrl;
            previewContainer.dataset.name = file.name;
            previewContainer.dataset.mime = file.type;

            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            uploadNextChunk(file, identifier, 1, totalChunks);
        }

        function uploadNextChunk(file, identifier, chunkNumber, totalChunks) {
            const start = (chunkNumber - 1) * CHUNK_SIZE;
            const end = Math.min(start + CHUNK_SIZE, file.size);
            const chunk = file.slice(start, end);

            const formData = new FormData();
            formData.append('file', chunk);
            formData.append('resumableFilename', file.name);
            formData.append('resumableIdentifier', identifier);
            formData.append('resumableChunkNumber', chunkNumber);
            formData.append('resumableTotalChunks', totalChunks);

            const statusText = document.getElementById(`comment-status-${identifier}`);
            const progressBar = document.getElementById(`comment-bar-${identifier}`);
            const percentageLabel = document.getElementById(`comment-percentage-${identifier}`);

            if (statusText) {
                statusText.textContent = `Uploading chunk ${chunkNumber} of ${totalChunks}...`;
            }

            fetch('/tickets/upload-chunk', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Upload error');
                }
                return response.json();
            })
            .then(data => {
                const percentComplete = Math.round((chunkNumber / totalChunks) * 100);
                if (progressBar) progressBar.style.width = percentComplete + '%';
                if (percentageLabel) percentageLabel.textContent = percentComplete + '%';

                if (chunkNumber < totalChunks) {
                    uploadNextChunk(file, identifier, chunkNumber + 1, totalChunks);
                } else {
                    // Upload Completed
                    if (statusText) {
                        statusText.innerHTML = '<span class="text-success fw-bold">✓ Upload Complete</span>';
                    }
                    if (progressBar) {
                        progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                    }
                    const deleteBtn = document.getElementById(`comment-delete-${identifier}`);
                    if (deleteBtn) {
                        deleteBtn.classList.remove('d-none');
                    }
                    
                    // Save to completedAttachments array
                    completedAttachments.push({
                        temp_token: identifier,
                        total_chunks: totalChunks,
                        file_name: file.name,
                        mime_type: file.type || 'application/octet-stream',
                        note: ''
                    });

                    // Update Hidden Input with Serialized JSON
                    attachmentsJsonInput.value = JSON.stringify(completedAttachments);

                    // Show the Note Input container
                    const noteContainer = document.getElementById(`comment-note-container-${identifier}`);
                    if (noteContainer) {
                        noteContainer.classList.remove('d-none');
                    }

                    // Attach input change listener to Note Input
                    const noteInput = document.getElementById(`comment-note-${identifier}`);
                    if (noteInput) {
                        noteInput.addEventListener('input', function() {
                            const val = this.value;
                            const att = completedAttachments.find(item => item.temp_token === identifier);
                            if (att) {
                                att.note = val;
                                attachmentsJsonInput.value = JSON.stringify(completedAttachments);
                            }
                        });
                    }

                    // Decrement active uploads and check if we can re-enable the submit button
                    activeUploadsCount--;
                    if (activeUploadsCount === 0) {
                        submitBtn.disabled = false;
                    }
                }
            })
            .catch(error => {
                console.error(error);
                if (statusText) {
                    statusText.innerHTML = '<span class="text-danger fw-bold">✗ Upload Failed. Please try again.</span>';
                }
                const deleteBtn = document.getElementById(`comment-delete-${identifier}`);
                if (deleteBtn) {
                    deleteBtn.classList.remove('d-none');
                }
                
                // Decrement active uploads and check if we can re-enable the submit button
                activeUploadsCount--;
                if (activeUploadsCount === 0) {
                    submitBtn.disabled = false;
                }
            });
        }

        function updateAttachmentNumbers() {
            const rows = document.querySelectorAll('#comment-upload-progress-list .comment-attachment-row');
            rows.forEach((row, index) => {
                const numberLabel = row.querySelector('.comment-attachment-number');
                if (numberLabel) {
                    numberLabel.textContent = `Attachment #${index + 1}`;
                }
            });
        }

        window.deleteCommentAttachment = function(identifier) {
            if (confirm("Are you sure you want to remove this attachment?")) {
                const row = document.getElementById(`comment-progress-row-${identifier}`);
                if (row) {
                    row.remove();
                }
                completedAttachments = completedAttachments.filter(item => item.temp_token !== identifier);
                attachmentsJsonInput.value = JSON.stringify(completedAttachments);
                updateAttachmentNumbers();
            }
        };

        // --- REASSIGN MODAL CASCADE & AUTOSUGGEST LOGIC ---
        const reassignDivSelect = document.getElementById('reassign_division_id');
        const reassignDeptSelect = document.getElementById('reassign_department_id');
        const reassignUserSearch = document.getElementById('reassign_user_search');
        const reassignAssigneeId = document.getElementById('reassign_assignee_id');
        const reassignAutocompleteResults = document.getElementById('reassign-autocomplete-results');

        let reassignUsers = [];

        if (reassignDivSelect) {
            // Store original department options
            const originalDeptOptions = Array.from(reassignDeptSelect.options);

            // Handle division change
            reassignDivSelect.addEventListener('change', function () {
                const divisionId = this.value;

                // Clear and reset dependent fields
                reassignAssigneeId.value = '';
                reassignUserSearch.value = '';
                reassignUserSearch.disabled = true;
                reassignAutocompleteResults.style.display = 'none';

                // Filter departments dropdown
                reassignDeptSelect.innerHTML = '';
                
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Select Department';
                reassignDeptSelect.appendChild(placeholder);

                if (divisionId) {
                    reassignDeptSelect.disabled = false;
                    originalDeptOptions.forEach(opt => {
                        if (opt.getAttribute('data-division-id') === divisionId) {
                            reassignDeptSelect.appendChild(opt.cloneNode(true));
                        }
                    });
                    
                    fetchReassignUsers();
                } else {
                    reassignDeptSelect.disabled = true;
                    reassignDeptSelect.value = '';
                    reassignUsers = [];
                }
            });

            // Handle department change
            reassignDeptSelect.addEventListener('change', function () {
                reassignAssigneeId.value = '';
                reassignUserSearch.value = '';
                reassignAutocompleteResults.style.display = 'none';
                
                if (this.value || reassignDivSelect.value) {
                    fetchReassignUsers();
                } else {
                    reassignUserSearch.disabled = true;
                    reassignUsers = [];
                }
            });

            function fetchReassignUsers() {
                const divId = reassignDivSelect.value;
                const deptId = reassignDeptSelect.value;

                if (!divId) {
                    reassignUsers = [];
                    reassignUserSearch.disabled = true;
                    return;
                }

                let url = `/api/users?division_id=${divId}`;
                if (deptId) {
                    url += `&department_id=${deptId}`;
                }

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        reassignUsers = data;
                        reassignUserSearch.disabled = false;
                    })
                    .catch(err => console.error('Error fetching reassign users:', err));
            }

            function renderReassignAutocomplete() {
                const query = reassignUserSearch.value.trim().toLowerCase();
                
                let filtered = reassignUsers;
                if (query) {
                    filtered = reassignUsers.filter(u => u.name.toLowerCase().includes(query));
                }

                if (filtered.length === 0) {
                    reassignAutocompleteResults.innerHTML = '<li class="dropdown-item text-muted disabled py-2" style="min-height: auto;">No users found</li>';
                    reassignAutocompleteResults.style.display = 'block';
                    return;
                }

                let html = '';
                filtered.forEach(u => {
                    html += `
                        <li class="dropdown-item py-2 border-bottom" style="cursor: pointer; min-height: auto;" data-id="${u.id}" data-name="${escapeHtml(u.name)}">
                            <div class="fw-semibold text-dark">${escapeHtml(u.name)}</div>
                            <small class="text-muted" style="font-size: 0.75rem;">Type: ${escapeHtml(u.user_type)}</small>
                        </li>
                    `;
                });

                reassignAutocompleteResults.innerHTML = html;
                reassignAutocompleteResults.style.display = 'block';

                // Attach click handlers
                reassignAutocompleteResults.querySelectorAll('li.dropdown-item').forEach(item => {
                    if (item.classList.contains('disabled')) return;
                    item.addEventListener('click', function () {
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name');
                        reassignAssigneeId.value = id;
                        reassignUserSearch.value = name;
                        reassignAutocompleteResults.style.display = 'none';
                    });
                });
            }

            reassignUserSearch.addEventListener('input', renderReassignAutocomplete);
            reassignUserSearch.addEventListener('focus', renderReassignAutocomplete);
            reassignUserSearch.addEventListener('click', renderReassignAutocomplete);

            // Hide results on click outside
            document.addEventListener('click', function (e) {
                if (e.target !== reassignUserSearch && !reassignAutocompleteResults.contains(e.target)) {
                    reassignAutocompleteResults.style.display = 'none';
                }
            });
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>
@endpush
