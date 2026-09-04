@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar" viewBox="0 0 16 16">
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
            </svg>
            This week
        </button>
    </div>
</div>

<!-- KPI Grids -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
    <div class="col">
        <div class="card fd-card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0 fw-semibold">Open Tickets</h6>
                <span class="badge badge-open rounded-pill">Open</span>
            </div>
            <h2 class="mt-3 mb-0 fw-bold">{{ $openTicketsCount }}</h2>
            <small class="text-muted">Currently active</small>
        </div>
    </div>
    <div class="col">
        <div class="card fd-card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0 fw-semibold">Unassigned Queue</h6>
                <span class="badge badge-progress rounded-pill">Queue</span>
            </div>
            <h2 class="mt-3 mb-0 fw-bold">{{ $unassignedTicketsCount }}</h2>
            <small class="text-muted">Requires action</small>
        </div>
    </div>
    <div class="col">
        <div class="card fd-card h-100 p-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0 fw-semibold">Critical Alerts</h6>
                <span class="badge badge-critical rounded-pill">Critical</span>
            </div>
            <h2 class="mt-3 mb-0 fw-bold text-danger">{{ $criticalTicketsCount }}</h2>
            <small class="text-danger">Immediate attention</small>
        </div>
    </div>
    <div class="col">
        <div class="card fd-card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0 fw-semibold">SLA Lapsed</h6>
                <span class="badge badge-lapsed rounded-pill">Lapsed</span>
            </div>
            <h2 class="mt-3 mb-0 fw-bold">{{ $slaLapsedCount }}</h2>
            <small class="text-muted">Exceeded threshold</small>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content Area -->
    <div class="col-12">
        <div class="card fd-card p-4">
            <h5 class="fw-bold mb-3">Recent Activity</h5>
            <!-- Placeholder for Chart.js or Table -->
            <div style="height: 300px; background-color: var(--fd-canvas-bg); border-radius: 8px;" class="d-flex align-items-center justify-content-center text-muted">
                Chart.js Analytics Area
            </div>
        </div>
    </div>
</div>
@endsection
