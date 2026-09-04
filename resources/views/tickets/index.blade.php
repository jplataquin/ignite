@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Tickets</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tickets.create') }}" class="btn btn-primary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill me-2" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
            </svg>
            Create New Ticket
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

<div class="card fd-card mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="px-4 py-3 text-muted fw-bold text-uppercase small">Ticket Number</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Title</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Priority</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Status</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Assignee</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Created By</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td class="px-4 py-3">
                            <a href="{{ route('tickets.show', $ticket) }}" class="fw-bold text-danger text-decoration-none">
                                {{ $ticket->ticket_number }}
                            </a>
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-dark">{{ $ticket->title }}</div>
                            <span class="text-muted small">{{ $ticket->ticketType->name ?? 'N/A' }}</span>
                        </td>
                        <td class="py-3">
                            @if(($ticket->priority->level ?? 0) >= 4)
                                <span class="badge badge-critical rounded-pill px-3 py-1.5 fw-semibold">Critical</span>
                            @elseif(($ticket->priority->level ?? 0) === 3)
                                <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-semibold text-white">High</span>
                            @elseif(($ticket->priority->level ?? 0) === 2)
                                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-semibold text-white">Medium</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-1.5 fw-semibold text-white">Low</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if(($ticket->status->slug ?? '') === 'open')
                                <span class="badge badge-open rounded-pill px-3 py-1.5 fw-semibold">Open</span>
                            @elseif(($ticket->status->slug ?? '') === 'in-progress')
                                <span class="badge badge-progress rounded-pill px-3 py-1.5 fw-semibold">In Progress</span>
                            @elseif(($ticket->status->slug ?? '') === 'resolved')
                                <span class="badge badge-resolved rounded-pill px-3 py-1.5 fw-semibold">Resolved</span>
                            @else
                                <span class="badge badge-lapsed rounded-pill px-3 py-1.5 fw-semibold">Closed</span>
                            @endif
                        </td>
                        <td class="py-3 text-muted">
                            @if($ticket->assignee)
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 24px; height: 24px; font-size: 0.75rem;">
                                        {{ substr($ticket->assignee->name, 0, 1) }}
                                    </div>
                                    <span class="small">{{ $ticket->assignee->name }}</span>
                                </div>
                            @else
                                <span class="text-warning small fw-semibold">Unassigned</span>
                            @endif
                        </td>
                        <td class="py-3 text-muted small">{{ $ticket->creator->name ?? 'System' }}</td>
                        <td class="py-3 text-muted small">{{ $ticket->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="44" fill="currentColor" class="bi bi-ticket-perforated text-muted opacity-50" viewBox="0 0 16 16">
                                    <path d="M4 4.85v.9h1v-.9zm7 0v.9h1v-.9zm-7 1.8v.9h1v-.9zm7 0v.9h1v-.9zm-7 1.8v.9h1v-.9zm7 0v.9h1v-.9zm-7 1.8v.9h1v-.9zm7 0v.9h1v-.9z"/>
                                    <path d="M1.5 3a.5.5 0 0 0-.5.5v1.05a1.5 1.5 0 0 1 0 2.9v1.1a1.5 1.5 0 0 1 0 2.9v1.05a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-1.05a1.5 1.5 0 0 1 0-2.9v-1.1a1.5 1.5 0 0 1 0-2.9V3.5a.5.5 0 0 0-.5-.5zm0-1h13A1.5 1.5 0 0 1 16 3.5v1.05a.5.5 0 0 0 .196.39l.024.16a2.5 2.5 0 0 0 0 4.8l-.024.16a.5.5 0 0 0-.196.39v1.05A1.5 1.5 0 0 1 14.5 13h-13A1.5 1.5 0 0 1 0 11.5v-1.05a.5.5 0 0 0-.196-.39l-.024-.16a2.5 2.5 0 0 0 0-4.8l.024-.16A.5.5 0 0 0 .196 4.55V3.5A1.5 1.5 0 0 1 1.5 2"/>
                                </svg>
                            </div>
                            No tickets registered yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tickets->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
