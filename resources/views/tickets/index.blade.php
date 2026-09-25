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

<!-- Filters Card -->
<div class="card fd-card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('tickets.index') }}" class="row g-2 align-items-end">
            <input type="hidden" name="tab" value="{{ request('tab', 'all') }}">
            <div class="col-md-2">
                <label for="priority_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Priority</label>
                <select name="priority_id" id="priority_id" class="form-select form-select-sm">
                    <option value="">All Priorities</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>
                            {{ $priority->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="stage_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Stage</label>
                <select name="stage_id" id="stage_id" class="form-select form-select-sm">
                    <option value="">All Stages</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                            {{ $stage->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label small fw-bold text-muted text-uppercase mb-1">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(Auth::user() && Auth::user()->user_type === 'admin')
                <div class="col-md-2">
                    <label for="division_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Division</label>
                    <select name="division_id" id="division_id" class="form-select form-select-sm">
                        <option value="">All Divisions</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="department_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Department</label>
                    <select name="department_id" id="department_id" class="form-select form-select-sm" disabled>
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" data-division-id="{{ $department->division_id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="to_user_search" class="form-label small fw-bold text-muted text-uppercase mb-1">Assigned User</label>
                    <input type="text" name="to_user_search" id="to_user_search" class="form-control form-control-sm" placeholder="Search name..." value="{{ request('to_user_search') }}">
                </div>
            @endif
            <div class="col-md-2">
                <label for="date_created" class="form-label small fw-bold text-muted text-uppercase mb-1">Date Created</label>
                <input type="date" name="date_created" id="date_created" class="form-control form-control-sm" value="{{ request('date_created') }}">
            </div>
            <div class="col-md-2 d-flex gap-2 ms-auto">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                @if(request()->filled('priority_id') || request()->filled('stage_id') || request()->filled('status') || request()->filled('date_created') || request()->filled('division_id') || request()->filled('department_id') || request()->filled('to_user_search'))
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card fd-card mb-4">
    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
        <ul class="nav nav-tabs border-bottom">
            <li class="nav-item">
                <a class="nav-link {{ request('tab', 'all') === 'all' ? 'active fw-bold text-danger' : 'text-muted' }}" href="{{ route('tickets.index', array_merge(request()->query(), ['tab' => 'all'])) }}">
                    All Tickets
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center {{ request('tab') === 'my_tickets' ? 'active fw-bold text-danger' : 'text-muted' }}" href="{{ route('tickets.index', array_merge(request()->query(), ['tab' => 'my_tickets'])) }}">
                    <span>My Tickets</span>
                    @php
                        $myTicketsCountQuery = \App\Models\Ticket::query();
                        if (Auth::user()->user_type === 'regular') {
                            $myTicketsCountQuery->where(function ($q) {
                                if (Auth::user()->division_id) {
                                    $q->orWhere('division_id', Auth::user()->division_id);
                                }
                                if (Auth::user()->department_id) {
                                    $q->orWhere('department_id', Auth::user()->department_id);
                                }
                            });
                        }
                        $myTicketsCount = $myTicketsCountQuery->where(function ($q) {
                            $q->orWhere('assigned_id', Auth::id());
                            $q->orWhere(function ($sub) {
                                $sub->where('created_by', Auth::id())
                                    ->whereHas('stage', function ($sq) {
                                        $sq->where('slug', 'review');
                                    });
                            });
                        })->count();
                    @endphp
                    @if($myTicketsCount > 0)
                        <span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.75rem; padding: 0.25em 0.5em;">{{ $myTicketsCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="px-4 py-3 text-muted fw-bold text-uppercase small">Ticket Number</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Title</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Priority</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Stage</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Status</th>
                    @if(Auth::user() && Auth::user()->user_type === 'admin')
                        <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Division</th>
                        <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Department</th>
                    @endif
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
                        <!-- Priority Badge -->
                        <td class="py-3">
                            @if(($ticket->priorityOption->name ?? '') === 'Critical')
                                <span class="badge badge-critical rounded-pill px-3 py-1.5 fw-semibold">Critical</span>
                            @elseif(($ticket->priorityOption->name ?? '') === 'High')
                                <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-semibold text-white">High</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-1.5 fw-semibold text-white">Low</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if(($ticket->stage->slug ?? '') === 'open')
                                <span class="badge badge-open rounded-pill px-3 py-1.5 fw-semibold">Open</span>
                            @elseif(($ticket->stage->slug ?? '') === 'assigned')
                                <span class="badge badge-assigned rounded-pill px-3 py-1.5 fw-semibold">Assigned</span>
                            @elseif(($ticket->stage->slug ?? '') === 'review')
                                <span class="badge badge-review rounded-pill px-3 py-1.5 fw-semibold">Review</span>
                            @elseif(($ticket->stage->slug ?? '') === 'closed')
                                <span class="badge badge-closed rounded-pill px-3 py-1.5 fw-semibold">Closed</span>
                            @elseif(($ticket->stage->slug ?? '') === 'canceled')
                                <span class="badge badge-canceled rounded-pill px-3 py-1.5 fw-semibold">Canceled</span>
                            @else
                                <span class="badge rounded-pill px-3 py-1.5 fw-semibold text-white" style="background-color: {{ $ticket->stage->color_code ?? '#6b7280' }};">
                                    {{ $ticket->stage->name ?? 'Unknown' }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($ticket->status === 'Valid')
                                <span class="badge bg-light text-success border border-success rounded-pill px-3 py-1.5 fw-semibold">Valid</span>
                            @elseif($ticket->status === 'Done')
                                <span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-semibold">Done</span>
                            @elseif($ticket->status === 'Lapsed')
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-semibold">Lapsed</span>
                            @else
                                <span class="badge bg-secondary text-white rounded-pill px-3 py-1.5 fw-semibold">{{ $ticket->status }}</span>
                            @endif
                        </td>
                        @if(Auth::user() && Auth::user()->user_type === 'admin')
                            <td class="py-3 text-muted small">{{ $ticket->division->name ?? 'N/A' }}</td>
                            <td class="py-3 text-muted small">{{ $ticket->department->name ?? 'N/A' }}</td>
                        @endif
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
                        <td colspan="{{ Auth::user() && Auth::user()->user_type === 'admin' ? 10 : 8 }}" class="text-center py-5 text-muted">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const divisionSelect = document.getElementById('division_id');
        const departmentSelect = document.getElementById('department_id');

        if (divisionSelect && departmentSelect) {
            const originalDepartmentOptions = Array.from(departmentSelect.options);

            function filterDepartments(initial = false) {
                const selectedDivisionId = divisionSelect.value;
                const previousVal = initial ? "{{ request('department_id') }}" : departmentSelect.value;

                // Clear and rebuild options
                departmentSelect.innerHTML = '';

                // Add default/placeholder option
                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.textContent = selectedDivisionId ? 'All Departments' : 'Select Division First';
                departmentSelect.appendChild(placeholderOption);

                if (selectedDivisionId) {
                    departmentSelect.disabled = false;
                    originalDepartmentOptions.forEach(option => {
                        if (option.getAttribute('data-division-id') === selectedDivisionId) {
                            const clonedOpt = option.cloneNode(true);
                            if (clonedOpt.value === previousVal) {
                                clonedOpt.selected = true;
                            }
                            departmentSelect.appendChild(clonedOpt);
                        }
                    });
                } else {
                    departmentSelect.disabled = true;
                    departmentSelect.value = '';
                }
            }

            divisionSelect.addEventListener('change', () => {
                filterDepartments(false);
            });

            // Run on initial load
            filterDepartments(true);
        }
    });
</script>
@endpush
