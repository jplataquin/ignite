@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Ticket Types</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.ticket-types.create') }}" class="btn btn-primary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill me-2" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
            </svg>
            Add Ticket Type
        </a>
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

<div class="card fd-card mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="px-4 py-3 text-muted fw-bold text-uppercase small">Name</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Description</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">SLA (Days)</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small text-center">Active Tickets</th>
                    <th scope="col" class="px-4 py-3 text-muted fw-bold text-uppercase small text-end" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ticketTypes as $type)
                    <tr>
                        <td class="px-4 py-3 fw-bold text-dark">
                            {{ $type->name }}
                        </td>
                        <td class="py-3 text-muted">
                            {{ $type->description ?: 'No description provided.' }}
                        </td>
                        <td class="py-3">
                            @if($type->threshold_days)
                                <span class="badge bg-secondary rounded-pill px-3 py-1.5 fw-semibold">{{ $type->threshold_days }} Days</span>
                            @else
                                <span class="text-muted small">No Threshold Set</span>
                            @endif
                        </td>
                        <td class="py-3 text-center fw-bold text-muted">
                            {{ $type->tickets_count }}
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.ticket-types.edit', $type) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center" style="min-height: 34px;">
                                    Edit
                                </a>
                                <form action="{{ route('admin.ticket-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center" style="min-height: 34px;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No ticket types found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($ticketTypes->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
            {{ $ticketTypes->links() }}
        </div>
    @endif
</div>
@endsection
