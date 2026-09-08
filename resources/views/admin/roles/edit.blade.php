@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Edit Role</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back to Roles
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark">Role Properties</h5>
            <p class="text-muted small mb-4">Edit the properties for the selected user role category.</p>

            <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold text-dark small">Role Name</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $role->name) }}" required autofocus placeholder="e.g. IT Engineer, HR Generalist, Support Lead">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold text-dark small">Description</label>
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" rows="3" placeholder="Provide a brief description of what this role represents...">{{ old('description', $role->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Ticket Type Permissions -->
                <div class="border-top pt-4 mb-4">
                    <h6 class="fw-bold text-dark mb-2">Allowed Ticket Types</h6>
                    <p class="text-muted small mb-3">Specify which ticket types can be created by users assigned to this role.</p>

                    <div class="row g-3">
                        @forelse($ticketTypes as $type)
                            <div class="col-12 col-md-6">
                                <div class="form-check border rounded p-3 h-100 bg-light d-flex align-items-start">
                                    <input class="form-check-input me-2 mt-1" type="checkbox" name="ticket_types[]" value="{{ $type->id }}" id="type_{{ $type->id }}" {{ $role->ticketTypes->contains($type->id) ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="type_{{ $type->id }}">
                                        <span class="fw-bold text-dark d-block small mb-1">{{ $type->name }}</span>
                                        <span class="text-muted d-block" style="font-size: 0.75rem; line-height: 1.3;">{{ $type->description ?: 'No description provided.' }}</span>
                                    </label>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-3 text-muted border border-dashed rounded">
                                <span class="small d-block">No ticket types available in the system yet.</span>
                                <a href="{{ route('admin.ticket-types.create') }}" class="small fw-semibold text-decoration-none">Create a new ticket type</a>
                            </div>
                        @endforelse
                    </div>
                    @error('ticket_types')
                        <span class="text-danger small mt-2 d-block">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
