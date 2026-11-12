@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold text-dark mb-1">Ticket Reports and Analytics</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-2 d-print-none">
        <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer me-1" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Print
        </button>
        <a href="{{ route('reports.export', request()->all()) }}" class="btn btn-sm btn-outline-primary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-spreadsheet me-1" viewBox="0 0 16 16">
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V9H3V2a1 1 0 0 1 1-1h5.5v2zM3 12v-2h2v2H3zm0 1h2v2H4a1 1 0 0 1-1-1v-1zm3 2v-2h3v2H6zm4 0v-2h3v1a1 1 0 0 1-1 1h-2zm3-3h-3v-2h3v2zm-7 0v-2h3v2H6z"/>
            </svg>
            Export CSV
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card fd-card mb-4 d-print-none shadow-sm">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end" id="reportFilterForm">
            <div class="col-md-3">
                <label for="filter_division_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Division</label>
                <select name="division_id" id="filter_division_id" class="form-select form-select-sm">
                    <option value="">All Divisions</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->id }}" {{ (string) $filters['division_id'] === (string) $div->id ? 'selected' : '' }}>
                            {{ $div->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_department_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Department</label>
                <select name="department_id" id="filter_department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" data-division-id="{{ $dept->division_id }}" {{ (string) $filters['department_id'] === (string) $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_ticket_type_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Ticket Type</label>
                <select name="ticket_type_id" id="filter_ticket_type_id" class="form-select form-select-sm">
                    <option value="">All Ticket Types</option>
                    @foreach($ticketTypes as $type)
                        <option value="{{ $type->id }}" {{ (string) $filters['ticket_type_id'] === (string) $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_location_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Location</label>
                <select name="location_id" id="filter_location_id" class="form-select form-select-sm">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ (string) $filters['location_id'] === (string) $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_priority_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Priority</label>
                <select name="priority_id" id="filter_priority_id" class="form-select form-select-sm">
                    <option value="">All Priorities</option>
                    @foreach($priorities as $prio)
                        <option value="{{ $prio->id }}" {{ (string) $filters['priority_id'] === (string) $prio->id ? 'selected' : '' }}>
                            {{ $prio->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_status" class="form-label small fw-bold text-muted text-uppercase mb-1">Status</label>
                <select name="status" id="filter_status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ $filters['status'] === $st ? 'selected' : '' }}>
                            {{ $st }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_date_from" class="form-label small fw-bold text-muted text-uppercase mb-1">Created From</label>
                <input type="date" name="date_from" id="filter_date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
            </div>
            <div class="col-md-2">
                <label for="filter_date_to" class="form-label small fw-bold text-muted text-uppercase mb-1">Created To</label>
                <input type="date" name="date_to" id="filter_date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
            </div>
            <div class="col-md-3 d-flex gap-2 ms-auto mt-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Apply Filters</button>
                @if(array_filter($filters))
                    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Nested Divs: Division -> Department -> Table -->
@forelse($reportData['divisions'] as $division)
    <div class="division-block card fd-card mb-4 p-4 shadow-sm">
        <div class="division-title h4 fw-bold text-dark border-bottom pb-2 mb-3">
            {{ $division['name'] }}
        </div>

        <!-- Division Status Summary Card -->
        <div class="card mb-4 border shadow-xs">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="small fw-bold text-uppercase text-muted">Status Summary</span>
                <span class="small text-muted fw-semibold">{{ $division['total_tickets'] }} Total {{ Str::plural('Ticket', $division['total_tickets']) }}</span>
            </div>
            <div class="card-body p-3">
                <div class="row row-cols-1 row-cols-sm-3 g-3">
                    @foreach($division['breakdown']['statuses'] as $st)
                        @php
                            $textColor = match($st['status']) {
                                'Valid' => 'text-success',
                                'Done' => 'text-secondary',
                                'Lapsed' => 'text-dark',
                                default => 'text-dark',
                            };
                            $badgeClass = match($st['status']) {
                                'Valid' => 'bg-success',
                                'Done' => 'bg-secondary',
                                'Lapsed' => 'badge-lapsed',
                                default => 'bg-dark',
                            };
                        @endphp
                        <div class="col">
                            <div class="p-3 bg-light rounded border h-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge {{ $badgeClass }} rounded-pill">{{ $st['status'] }}</span>
                                    <span class="small text-muted fw-semibold">{{ $st['count'] }} {{ Str::plural('ticket', $st['count']) }}</span>
                                </div>
                                <div class="h3 fw-bold mb-1 {{ $textColor }}">{{ $st['percentage'] }}%</div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar {{ $badgeClass }}" role="progressbar" style="width: {{ $st['percentage'] }}%;" aria-valuenow="{{ $st['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @forelse($division['departments'] as $dept)
            <div class="department-block mb-4 p-3 bg-light rounded border">
                <div class="department-title h5 fw-semibold text-dark mb-3">
                    {{ $dept['name'] }}
                </div>

                <div class="table-responsive bg-white rounded shadow-xs">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="py-2.5 px-3">Ticket Type</th>
                                <th scope="col" class="py-2.5 px-3">Location</th>
                                <th scope="col" class="py-2.5 px-3">Priority</th>
                                <th scope="col" class="py-2.5 px-3">Status</th>
                                <th scope="col" class="py-2.5 px-3 text-end" style="width: 120px;">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dept['rows'] as $row)
                                <tr>
                                    <td class="px-3">{{ $row['ticket_type'] }}</td>
                                    <td class="px-3">{{ $row['location'] }}</td>
                                    <td class="px-3">
                                        @php
                                            $prioBadge = match(strtolower($row['priority'])) {
                                                'critical' => 'badge-critical',
                                                'high' => 'bg-warning text-dark',
                                                'low' => 'bg-info text-dark',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $prioBadge }} rounded-pill">{{ $row['priority'] }}</span>
                                    </td>
                                    <td class="px-3">
                                        @php
                                            $statusBadge = match($row['status']) {
                                                'Valid' => 'bg-success',
                                                'Done' => 'bg-secondary',
                                                'Lapsed' => 'badge-lapsed',
                                                default => 'bg-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge }} rounded-pill">{{ $row['status'] }}</span>
                                    </td>
                                    <td class="px-3 text-end fw-bold">{{ $row['count'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No tickets recorded for this department.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($dept['rows']) > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="px-3 text-end fw-bold">Total</th>
                                    <th class="px-3 text-end fw-bold">{{ $dept['total_tickets'] }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @empty
            <div class="text-muted small py-2 px-3 bg-light rounded">
                No departments found in this division.
            </div>
        @endforelse
    </div>
@empty
    <div class="card fd-card p-5 text-center shadow-sm">
        <h5 class="fw-bold text-dark mb-2">No Report Data Found</h5>
        <p class="text-muted mb-3">No tickets or divisions match your selected filter criteria.</p>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-sm">Reset All Filters</a>
        </div>
    </div>
@endforelse

<script>
document.addEventListener('DOMContentLoaded', function () {
    const divisionSelect = document.getElementById('filter_division_id');
    const deptSelect = document.getElementById('filter_department_id');

    if (divisionSelect && deptSelect) {
        const allDeptOptions = Array.from(deptSelect.querySelectorAll('option[data-division-id]'));

        function updateDeptOptions() {
            const selectedDivisionId = divisionSelect.value;
            const currentSelectedDept = deptSelect.value;

            allDeptOptions.forEach(function (option) {
                const optDivisionId = option.getAttribute('data-division-id');
                if (!selectedDivisionId || optDivisionId === selectedDivisionId) {
                    option.style.display = '';
                    option.disabled = false;
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                    if (option.value === currentSelectedDept) {
                        deptSelect.value = '';
                    }
                }
            });
        }

        divisionSelect.addEventListener('change', updateDeptOptions);
        updateDeptOptions();
    }
});
</script>
@endsection
