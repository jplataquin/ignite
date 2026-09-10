@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">System Settings</h1>
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

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark">SLA & Assignment Threshold Configurations</h5>
            <p class="text-muted small mb-4">Define the operational service level agreement (SLA) threshold durations in days. These rules determine the deadline dates for completing and assigning support tickets based on their priority level.</p>

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                <!-- Section 1: Ticket SLA Days -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Ticket Completion SLA (Days)</h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="sla_days_low" class="form-label fw-semibold text-dark small">Low Priority SLA</label>
                            <div class="input-group">
                                <input id="sla_days_low" type="number" min="0" class="form-control @error('sla_days_low') is-invalid @enderror" name="sla_days_low" value="{{ old('sla_days_low', $settings['sla_days_low']) }}" required>
                                <span class="input-group-text">days</span>
                            </div>
                            @error('sla_days_low')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="sla_days_high" class="form-label fw-semibold text-dark small">High Priority SLA</label>
                            <div class="input-group">
                                <input id="sla_days_high" type="number" min="0" class="form-control @error('sla_days_high') is-invalid @enderror" name="sla_days_high" value="{{ old('sla_days_high', $settings['sla_days_high']) }}" required>
                                <span class="input-group-text">days</span>
                            </div>
                            @error('sla_days_high')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="sla_days_critical" class="form-label fw-semibold text-dark small">Critical Priority SLA</label>
                            <div class="input-group">
                                <input id="sla_days_critical" type="number" min="0" class="form-control @error('sla_days_critical') is-invalid @enderror" name="sla_days_critical" value="{{ old('sla_days_critical', $settings['sla_days_critical']) }}" required>
                                <span class="input-group-text">days</span>
                            </div>
                            @error('sla_days_critical')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Assignment SLA Days -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Ticket Assignment SLA (Days)</h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="sla_days_assign_low" class="form-label fw-semibold text-dark small">Low Priority Assignment</label>
                            <div class="input-group">
                                <input id="sla_days_assign_low" type="number" min="0" class="form-control @error('sla_days_assign_low') is-invalid @enderror" name="sla_days_assign_low" value="{{ old('sla_days_assign_low', $settings['sla_days_assign_low']) }}" required>
                                <span class="input-group-text">days</span>
                            </div>
                            @error('sla_days_assign_low')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="sla_days_assign_high" class="form-label fw-semibold text-dark small">High Priority Assignment</label>
                            <div class="input-group">
                                <input id="sla_days_assign_high" type="number" min="0" class="form-control @error('sla_days_assign_high') is-invalid @enderror" name="sla_days_assign_high" value="{{ old('sla_days_assign_high', $settings['sla_days_assign_high']) }}" required>
                                <span class="input-group-text">days</span>
                            </div>
                            @error('sla_days_assign_high')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="sla_days_assign_critical" class="form-label fw-semibold text-dark small">Critical Priority Assignment</label>
                            <div class="input-group">
                                <input id="sla_days_assign_critical" type="number" min="0" class="form-control @error('sla_days_assign_critical') is-invalid @enderror" name="sla_days_assign_critical" value="{{ old('sla_days_assign_critical', $settings['sla_days_assign_critical']) }}" required>
                                <span class="input-group-text">days</span>
                            </div>
                            @error('sla_days_assign_critical')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2 border-top pt-4">
                    <button type="submit" class="btn btn-primary px-4">Save Configurations</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
