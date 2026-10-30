@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center gap-2">
        <h1 class="h2 fw-bold text-dark mb-0">Edit Clearance: {{ $ticket->ticket_number }}</h1>
        <span class="badge bg-primary text-white">Approval / Clearance</span>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back to Clearance
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card fd-card p-4 shadow-sm mb-4">
            <h5 class="fw-bold mb-3 text-dark">Clearance Details</h5>
            <p class="text-muted small mb-4">Update the details of this approval and clearance ticket.</p>

            <form method="POST" action="{{ route('tickets.update', $ticket) }}">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold text-dark small">Ticket Title</label>
                    <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $ticket->title) }}" required placeholder="Describe the clearance briefly...">
                    @error('title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold text-dark small">Description</label>
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" rows="5" placeholder="Provide detailed specifications for this clearance..." required>{{ old('description', $ticket->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Type and Priority -->
                <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
                    <div>
                        <label for="ticket_type_id" class="form-label fw-semibold text-dark small">Ticket Type</label>
                        <select id="ticket_type_id" class="form-select @error('ticket_type_id') is-invalid @enderror" name="ticket_type_id" required>
                            @foreach($ticketTypes as $type)
                                <option value="{{ $type->id }}" {{ old('ticket_type_id', $ticket->ticket_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('ticket_type_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div>
                        <label for="priority_option_id" class="form-label fw-semibold text-dark small">Priority</label>
                        <select id="priority_option_id" class="form-select @error('priority_option_id') is-invalid @enderror" name="priority_option_id" required>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}" {{ old('priority_option_id', $ticket->priority_option_id) == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                            @endforeach
                        </select>
                        @error('priority_option_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <!-- Categories -->
                <div class="row row-cols-1 row-cols-md-3 g-3 mb-3">
                    <div>
                        <label for="category_1_id" class="form-label fw-semibold text-dark small">Category 1</label>
                        <select id="category_1_id" class="form-select @error('category_1_id') is-invalid @enderror" name="category_1_id" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_1_id', $ticket->category_1_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="category_2_id" class="form-label fw-semibold text-dark small">Category 2</label>
                        <select id="category_2_id" class="form-select" name="category_2_id">
                            <option value="">None</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_2_id', $ticket->category_2_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="category_3_id" class="form-label fw-semibold text-dark small">Category 3</label>
                        <select id="category_3_id" class="form-select" name="category_3_id">
                            <option value="">None</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_3_id', $ticket->category_3_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Division, Department, Location in one row on desktop (Optional) -->
                <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
                    <div>
                        <label for="division_id" class="form-label fw-semibold text-dark small">Division (Optional)</label>
                        <select id="division_id" class="form-select" name="division_id">
                            <option value="">None</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id', $ticket->division_id) == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="department_id" class="form-label fw-semibold text-dark small">Department (Optional)</label>
                        <select id="department_id" class="form-select" name="department_id">
                            <option value="">None</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $ticket->department_id) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="location_id" class="form-label fw-semibold text-dark small">Location (Optional)</label>
                        <select id="location_id" class="form-select" name="location_id">
                            <option value="">None</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ old('location_id', $ticket->location_id) == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Approvers Checklist (Read-only Overview) -->
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-dark small">Configured Approvers ({{ ucfirst($ticket->approval_mode ?? 'sequential') }})</span>
                        <span class="badge bg-secondary-subtle text-secondary small">Checklist</span>
                    </div>
                    <ul class="list-group list-group-flush border rounded bg-white">
                        @forelse($ticket->approvals as $approval)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <span class="badge bg-secondary me-2">#{{ $approval->sequence_order }}</span>
                                    <span class="fw-semibold text-dark small">{{ $approval->user->name ?? 'User' }}</span>
                                    @if($approval->signoff_description)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-normal ms-2" style="font-size: 0.75rem;">
                                            {{ $approval->signoff_description }}
                                        </span>
                                    @endif
                                </div>
                                <span class="badge {{ $approval->status === 'approved' ? 'bg-success' : ($approval->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }} small">
                                    {{ ucfirst($approval->status) }}
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted small py-2 text-center">No approvers configured.</li>
                        @endforelse
                    </ul>
                </div>

                @if(Auth::user() && Auth::user()->user_type === 'admin')
                    <!-- Admin Overrides -->
                    <div class="border-top pt-3 mb-4">
                        <h6 class="fw-bold text-danger mb-2">Admin Overrides</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="stage_id" class="form-label small fw-semibold text-dark">Stage</label>
                                <select id="stage_id" class="form-select form-select-sm" name="stage_id">
                                    @foreach($stages as $stage)
                                        <option value="{{ $stage->id }}" {{ old('stage_id', $ticket->stage_id) == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Update Clearance</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
