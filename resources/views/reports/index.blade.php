@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold text-dark mb-1">Ticket Reports and Analytics</h1>
        <p class="text-muted small mb-0">Summary of ticket counts per Ticket Type, Location, Priority, and Status, grouped per Division and Department.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-2 d-print-none">
        <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer me-1" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Print Report
        </button>
        <a href="{{ route('reports.export', request()->all()) }}" class="btn btn-sm btn-outline-primary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-spreadsheet me-1" viewBox="0 0 16 16">
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V9H3V2a1 1 0 0 1 1-1h5.5v2zM3 12v-2h2v2H3zm0 1h2v2H4a1 1 0 0 1-1-1v-1zm3 2v-2h3v2H6zm4 0v-2h3v1a1 1 0 0 1-1 1h-2zm3-3h-3v-2h3v2zm-7 0v-2h3v2H6z"/>
            </svg>
            Export CSV
        </a>
    </div>
</div>

<!-- Global KPI Cards -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
    <div class="col">
        <div class="card fd-card h-100 p-3 border-start border-4 border-danger shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0 fw-semibold">Total Tickets</h6>
                <span class="badge bg-danger rounded-pill">Total</span>
            </div>
            <h2 class="mt-2 mb-0 fw-bold text-dark">{{ $reportData['overall']['total'] }}</h2>
            <small class="text-muted">Across {{ $reportData['total_divisions_count'] }} divisions & {{ $reportData['total_departments_count'] }} departments</small>
        </div>
    </div>
    @php
        $statusMap = collect($reportData['overall']['statuses'])->keyBy('status');
        $validCount = $statusMap['Valid']['count'] ?? 0;
        $doneCount = $statusMap['Done']['count'] ?? 0;
        $lapsedCount = $statusMap['Lapsed']['count'] ?? 0;
    @endphp
    <div class="col">
        <div class="card fd-card h-100 p-3 border-start border-4 border-success shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0 fw-semibold">Valid (Active)</h6>
                <span class="badge bg-success rounded-pill">Valid</span>
            </div>
            <h2 class="mt-2 mb-0 fw-bold text-success">{{ $validCount }}</h2>
            <small class="text-muted">
                {{ $reportData['overall']['total'] > 0 ? round(($validCount / $reportData['overall']['total']) * 100, 1) : 0 }}% of total tickets
            </small>
        </div>
    </div>
    <div class="col">
        <div class="card fd-card h-100 p-3 border-start border-4 border-secondary shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0 fw-semibold">Done (Completed)</h6>
                <span class="badge bg-secondary rounded-pill">Done</span>
            </div>
            <h2 class="mt-2 mb-0 fw-bold text-secondary">{{ $doneCount }}</h2>
            <small class="text-muted">
                {{ $reportData['overall']['total'] > 0 ? round(($doneCount / $reportData['overall']['total']) * 100, 1) : 0 }}% of total tickets
            </small>
        </div>
    </div>
    <div class="col">
        <div class="card fd-card h-100 p-3 border-start border-4 border-warning shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0 fw-semibold">Lapsed (SLA Breached)</h6>
                <span class="badge badge-lapsed rounded-pill">Lapsed</span>
            </div>
            <h2 class="mt-2 mb-0 fw-bold text-dark">{{ $lapsedCount }}</h2>
            <small class="text-muted">
                {{ $reportData['overall']['total'] > 0 ? round(($lapsedCount / $reportData['overall']['total']) * 100, 1) : 0 }}% of total tickets
            </small>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card fd-card mb-4 d-print-none shadow-sm">
    <div class="card-header bg-white border-bottom py-2.5 d-flex justify-content-between align-items-center">
        <div class="fw-bold text-dark small text-uppercase">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-funnel-fill me-1 text-primary" viewBox="0 0 16 16">
                <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z"/>
            </svg>
            Filter Report Parameters
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-xs btn-outline-secondary" id="btnExpandAll" style="font-size: 0.75rem; padding: 2px 8px; min-height: auto;">
                Expand All
            </button>
            <button type="button" class="btn btn-xs btn-outline-secondary" id="btnCollapseAll" style="font-size: 0.75rem; padding: 2px 8px; min-height: auto;">
                Collapse All
            </button>
        </div>
    </div>
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

<!-- Grouped Sections: Per Division and Per Department -->
@forelse($reportData['divisions'] as $divisionIndex => $division)
    <div class="card fd-card mb-4 shadow-sm division-report-card">
        <!-- Division Header -->
        <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <button class="btn btn-link text-dark p-0 me-2 text-decoration-none d-flex align-items-center" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#collapseDivision_{{ $divisionIndex }}" 
                        aria-expanded="true" 
                        aria-controls="collapseDivision_{{ $divisionIndex }}"
                        style="min-height: auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-chevron-down division-chevron transition-all" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>
                    </svg>
                </button>
                <div>
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
                        <span>{{ $division['name'] }}</span>
                        <span class="badge bg-danger rounded-pill ms-2 fs-6 fw-semibold">
                            {{ $division['total_tickets'] }} {{ Str::plural('Ticket', $division['total_tickets']) }}
                        </span>
                    </h5>
                    <small class="text-muted">
                        {{ count($division['departments']) }} {{ Str::plural('Department', count($division['departments'])) }} recorded
                    </small>
                </div>
            </div>

            <!-- Division Quick Status Pills -->
            <div class="d-flex gap-2 align-items-center flex-wrap">
                @foreach($division['breakdown']['statuses'] as $divStatus)
                    @php
                        $badgeClass = match($divStatus['status']) {
                            'Valid' => 'bg-success',
                            'Done' => 'bg-secondary',
                            'Lapsed' => 'badge-lapsed',
                            default => 'bg-dark',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} rounded-pill px-2.5 py-1 text-white small">
                        {{ $divStatus['status'] }}: {{ $divStatus['count'] }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="collapse show division-collapse" id="collapseDivision_{{ $divisionIndex }}">
            <div class="card-body p-3">
                <!-- Division-Level Summary Card -->
                <div class="card bg-light border mb-4">
                    <div class="card-header bg-transparent border-bottom py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark small text-uppercase">
                            {{ $division['name'] }} Overall Summary
                        </span>
                        <span class="badge bg-white text-dark border">
                            {{ $division['total_tickets'] }} Total
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
                            <!-- 1. Ticket Types -->
                            <div class="col">
                                <div class="bg-white p-3 rounded border h-100 shadow-xs">
                                    <h6 class="fw-bold text-dark border-bottom pb-1 mb-2 small text-uppercase text-muted">
                                        Ticket Types
                                    </h6>
                                    @forelse($division['breakdown']['ticket_types'] as $tt)
                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom-dashed small">
                                            <span class="text-truncate me-2" title="{{ $tt['name'] }}">{{ $tt['name'] }}</span>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark border me-1">{{ $tt['count'] }}</span>
                                                <small class="text-muted" style="min-width: 38px; text-align: right;">{{ $tt['percentage'] }}%</small>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted small py-2">No ticket types defined</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 2. Locations -->
                            <div class="col">
                                <div class="bg-white p-3 rounded border h-100 shadow-xs">
                                    <h6 class="fw-bold text-dark border-bottom pb-1 mb-2 small text-uppercase text-muted">
                                        Locations
                                    </h6>
                                    @forelse($division['breakdown']['locations'] as $loc)
                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom-dashed small">
                                            <span class="text-truncate me-2" title="{{ $loc['name'] }}">{{ $loc['name'] }}</span>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark border me-1">{{ $loc['count'] }}</span>
                                                <small class="text-muted" style="min-width: 38px; text-align: right;">{{ $loc['percentage'] }}%</small>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted small py-2">No locations defined</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 3. Priorities -->
                            <div class="col">
                                <div class="bg-white p-3 rounded border h-100 shadow-xs">
                                    <h6 class="fw-bold text-dark border-bottom pb-1 mb-2 small text-uppercase text-muted">
                                        Priorities
                                    </h6>
                                    @forelse($division['breakdown']['priorities'] as $prio)
                                        @php
                                            $prioBadge = match(strtolower($prio['name'])) {
                                                'critical' => 'badge-critical',
                                                'high' => 'bg-warning text-dark',
                                                'low' => 'bg-info text-dark',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom-dashed small">
                                            <span class="badge {{ $prioBadge }} rounded-pill me-2">{{ $prio['name'] }}</span>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark border me-1">{{ $prio['count'] }}</span>
                                                <small class="text-muted" style="min-width: 38px; text-align: right;">{{ $prio['percentage'] }}%</small>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted small py-2">No priorities defined</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 4. Statuses -->
                            <div class="col">
                                <div class="bg-white p-3 rounded border h-100 shadow-xs">
                                    <h6 class="fw-bold text-dark border-bottom pb-1 mb-2 small text-uppercase text-muted">
                                        Statuses
                                    </h6>
                                    @foreach($division['breakdown']['statuses'] as $st)
                                        @php
                                            $stBadge = match($st['status']) {
                                                'Valid' => 'bg-success',
                                                'Done' => 'bg-secondary',
                                                'Lapsed' => 'badge-lapsed',
                                                default => 'bg-dark',
                                            };
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom-dashed small">
                                            <span class="badge {{ $stBadge }} rounded-pill me-2">{{ $st['status'] }}</span>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark border me-1">{{ $st['count'] }}</span>
                                                <small class="text-muted" style="min-width: 38px; text-align: right;">{{ $st['percentage'] }}%</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departments Breakdown List -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0 text-uppercase small text-muted">
                        Departments Breakdown ({{ count($division['departments']) }})
                    </h6>
                </div>

                @forelse($division['departments'] as $deptIndex => $dept)
                    <div class="card mb-3 border shadow-xs department-card">
                        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                            <div class="d-flex align-items-center">
                                <span class="fw-bold text-dark me-2">{{ $dept['name'] }}</span>
                                <span class="badge {{ $dept['total_tickets'] > 0 ? 'bg-primary' : 'bg-light text-muted border' }} rounded-pill">
                                    {{ $dept['total_tickets'] }} {{ Str::plural('Ticket', $dept['total_tickets']) }}
                                </span>
                            </div>
                            @if($division['total_tickets'] > 0)
                                <small class="text-muted">
                                    {{ round(($dept['total_tickets'] / $division['total_tickets']) * 100, 1) }}% of Division
                                </small>
                            @endif
                        </div>
                        <div class="card-body p-3">
                            @if($dept['total_tickets'] === 0)
                                <div class="text-center py-3 text-muted small">
                                    No tickets recorded for this department.
                                </div>
                            @else
                                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
                                    <!-- Department Ticket Type breakdown -->
                                    <div class="col">
                                        <div class="bg-light p-2.5 rounded border h-100">
                                            <div class="fw-bold text-dark small mb-2 d-flex justify-content-between">
                                                <span>Ticket Type</span>
                                                <span class="text-muted" style="font-size: 0.75rem;">Count</span>
                                            </div>
                                            @foreach($dept['breakdown']['ticket_types'] as $tt)
                                                @if($tt['count'] > 0 || !array_filter($filters))
                                                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom-dashed small">
                                                        <span class="text-truncate me-2" title="{{ $tt['name'] }}">{{ $tt['name'] }}</span>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge {{ $tt['count'] > 0 ? 'bg-dark' : 'bg-light text-muted border' }} rounded-pill me-1" style="font-size: 0.75rem;">
                                                                {{ $tt['count'] }}
                                                            </span>
                                                            <small class="text-muted" style="min-width: 36px; text-align: right; font-size: 0.7rem;">
                                                                {{ $tt['percentage'] }}%
                                                            </small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Department Location breakdown -->
                                    <div class="col">
                                        <div class="bg-light p-2.5 rounded border h-100">
                                            <div class="fw-bold text-dark small mb-2 d-flex justify-content-between">
                                                <span>Location</span>
                                                <span class="text-muted" style="font-size: 0.75rem;">Count</span>
                                            </div>
                                            @foreach($dept['breakdown']['locations'] as $loc)
                                                @if($loc['count'] > 0 || !array_filter($filters))
                                                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom-dashed small">
                                                        <span class="text-truncate me-2" title="{{ $loc['name'] }}">{{ $loc['name'] }}</span>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge {{ $loc['count'] > 0 ? 'bg-dark' : 'bg-light text-muted border' }} rounded-pill me-1" style="font-size: 0.75rem;">
                                                                {{ $loc['count'] }}
                                                            </span>
                                                            <small class="text-muted" style="min-width: 36px; text-align: right; font-size: 0.7rem;">
                                                                {{ $loc['percentage'] }}%
                                                            </small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Department Priority breakdown -->
                                    <div class="col">
                                        <div class="bg-light p-2.5 rounded border h-100">
                                            <div class="fw-bold text-dark small mb-2 d-flex justify-content-between">
                                                <span>Priority</span>
                                                <span class="text-muted" style="font-size: 0.75rem;">Count</span>
                                            </div>
                                            @foreach($dept['breakdown']['priorities'] as $prio)
                                                @php
                                                    $prioBadge = match(strtolower($prio['name'])) {
                                                        'critical' => 'badge-critical',
                                                        'high' => 'bg-warning text-dark',
                                                        'low' => 'bg-info text-dark',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                @if($prio['count'] > 0 || !array_filter($filters))
                                                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom-dashed small">
                                                        <span class="badge {{ $prioBadge }} rounded-pill me-2" style="font-size: 0.75rem;">
                                                            {{ $prio['name'] }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge {{ $prio['count'] > 0 ? 'bg-dark' : 'bg-light text-muted border' }} rounded-pill me-1" style="font-size: 0.75rem;">
                                                                {{ $prio['count'] }}
                                                            </span>
                                                            <small class="text-muted" style="min-width: 36px; text-align: right; font-size: 0.7rem;">
                                                                {{ $prio['percentage'] }}%
                                                            </small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Department Status breakdown -->
                                    <div class="col">
                                        <div class="bg-light p-2.5 rounded border h-100">
                                            <div class="fw-bold text-dark small mb-2 d-flex justify-content-between">
                                                <span>Status</span>
                                                <span class="text-muted" style="font-size: 0.75rem;">Count</span>
                                            </div>
                                            @foreach($dept['breakdown']['statuses'] as $st)
                                                @php
                                                    $stBadge = match($st['status']) {
                                                        'Valid' => 'bg-success',
                                                        'Done' => 'bg-secondary',
                                                        'Lapsed' => 'badge-lapsed',
                                                        default => 'bg-dark',
                                                    };
                                                @endphp
                                                @if($st['count'] > 0 || !array_filter($filters))
                                                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom-dashed small">
                                                        <span class="badge {{ $stBadge }} rounded-pill me-2" style="font-size: 0.75rem;">
                                                            {{ $st['status'] }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge {{ $st['count'] > 0 ? 'bg-dark' : 'bg-light text-muted border' }} rounded-pill me-1" style="font-size: 0.75rem;">
                                                                {{ $st['count'] }}
                                                            </span>
                                                            <small class="text-muted" style="min-width: 36px; text-align: right; font-size: 0.7rem;">
                                                                {{ $st['percentage'] }}%
                                                            </small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3 text-muted small bg-light rounded">
                        No departments found in this division.
                    </div>
                @endforelse
            </div>
        </div>
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

<style>
.border-bottom-dashed {
    border-bottom: 1px dashed #e2e8f0;
}
.border-bottom-dashed:last-child {
    border-bottom: none;
}
.shadow-xs {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.transition-all {
    transition: transform 0.2s ease-in-out;
}
.collapsed .division-chevron {
    transform: rotate(-90deg);
}
@media print {
    .d-print-none, header, nav, #offcanvasSidebar, .btn-toolbar {
        display: none !important;
    }
    .collapse {
        display: block !important;
        height: auto !important;
    }
    .division-collapse {
        display: block !important;
    }
    body {
        background-color: #fff !important;
        color: #000 !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dynamic department dropdown filter based on division
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

    // Expand All / Collapse All functionality
    const btnExpandAll = document.getElementById('btnExpandAll');
    const btnCollapseAll = document.getElementById('btnCollapseAll');

    if (btnExpandAll && btnCollapseAll) {
        btnExpandAll.addEventListener('click', function () {
            document.querySelectorAll('.division-collapse').forEach(function (el) {
                const collapseInstance = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                collapseInstance.show();
            });
        });

        btnCollapseAll.addEventListener('click', function () {
            document.querySelectorAll('.division-collapse').forEach(function (el) {
                const collapseInstance = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                collapseInstance.hide();
            });
        });
    }
});
</script>
@endsection
