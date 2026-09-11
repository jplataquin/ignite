@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Cron Job & Scheduler Logs</h1>
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
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Available Jobs Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark">Available Scheduled Commands</h5>
            <p class="text-muted small mb-4">The following artisan commands are scheduled to run in the background. You can trigger them manually on demand to verify logic or test execution logging.</p>
            
            <div class="row row-cols-1 row-cols-md-2 g-3">
                @foreach($availableJobs as $job)
                    <div class="col">
                        <div class="border rounded p-3 h-100 bg-white shadow-sm d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold text-dark mb-0">{{ $job['name'] }}</h6>
                                    <span class="badge bg-secondary text-uppercase small" style="font-size: 0.65rem;">{{ $job['frequency'] }}</span>
                                </div>
                                <code class="d-block text-primary small mb-2" style="font-size: 0.8rem;">php artisan {{ $job['command'] }}</code>
                                <p class="text-muted mb-3" style="font-size: 0.82rem;">{{ $job['description'] }}</p>
                            </div>
                            
                            <div>
                                <form method="POST" action="{{ route('admin.cron-logs.run', $job['command']) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-1.5 px-3 py-1.5" style="border-radius: 6px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-play-fill" viewBox="0 0 16 16">
                                            <path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/>
                                        </svg>
                                        Run Command
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Execution Logs Table Section -->
<div class="row">
    <div class="col-12">
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark">Artisan Execution History</h5>
            <p class="text-muted small mb-4">View the detailed real-time execution results of both automated scheduler calls and manual on-demand triggers.</p>

            @if($logs->isEmpty())
                <div class="text-center py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-terminal-dash text-secondary mb-3" viewBox="0 0 16 16">
                        <path d="M2 3a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zm1 1h10a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/>
                        <path d="M5.146 6.146a.5.5 0 1 1 .708.708L4.207 8.5l1.647 1.646a.5.5 0 0 1-.708.708l-2-2a.5.5 0 0 1 0-.708zM7 9.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                    </svg>
                    <p class="fw-semibold text-dark mb-1">No execution logs found</p>
                    <p class="text-muted small mb-0">Run a command above or wait for background crons to complete.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle border-top" style="font-size: 0.88rem;">
                        <thead>
                            <tr class="table-light">
                                <th scope="col" class="py-2.5">ID</th>
                                <th scope="col" class="py-2.5">Command</th>
                                <th scope="col" class="py-2.5">Status</th>
                                <th scope="col" class="py-2.5">Started At</th>
                                <th scope="col" class="py-2.5">Completed At</th>
                                <th scope="col" class="py-2.5">Duration</th>
                                <th scope="col" class="py-2.5" style="width: 120px;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="fw-bold">#{{ $log->id }}</td>
                                    <td>
                                        <code class="text-dark bg-light px-2 py-1 rounded small border">{{ $log->command }}</code>
                                    </td>
                                    <td>
                                        @if($log->status === 'success')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Success</span>
                                        @elseif($log->status === 'failed')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">Failed</span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1">Running</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $log->started_at->format('M d, Y H:i:s') }}</td>
                                    <td class="text-muted">
                                        {{ $log->completed_at ? $log->completed_at->format('M d, Y H:i:s') : '—' }}
                                    </td>
                                    <td>
                                        @if($log->duration_ms !== null)
                                            <span class="fw-semibold">{{ $log->duration_ms >= 1000 ? round($log->duration_ms / 1000, 2) . 's' : $log->duration_ms . 'ms' }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-outline-secondary py-1 px-2.5" style="font-size: 0.75rem; border-radius: 4px;" type="button" data-bs-toggle="collapse" data-bs-target="#log-output-{{ $log->id }}" aria-expanded="false" aria-controls="log-output-{{ $log->id }}">
                                            Toggle Log
                                        </button>
                                    </td>
                                </tr>
                                <!-- Collapsible Log Content -->
                                <tr class="collapse border-0" id="log-output-{{ $log->id }}">
                                    <td colspan="7" class="p-0 border-0">
                                        <div class="p-3 rounded shadow-inner m-2 border" style="font-family: monospace; font-size: 0.82rem; max-height: 250px; overflow-y: auto; background-color: #f8fafc !important; border-color: #cbd5e1 !important;">
                                            @if($log->error)
                                                <div class="text-danger fw-bold mb-2" style="color: #b91c1c !important;">EXCEPTION ERROR:</div>
                                                <div class="mb-3" style="white-space: pre-wrap; color: #ef4444 !important;">{{ $log->error }}</div>
                                            @endif
                                            
                                            @if($log->output)
                                                <div class="text-primary fw-bold mb-1" style="color: #0f172a !important;">CONSOLE OUTPUT:</div>
                                                <div class="text-dark" style="white-space: pre-wrap; color: #334155 !important;">{{ $log->output }}</div>
                                            @else
                                                <div class="text-muted small">No console output recorded.</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
