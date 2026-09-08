@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Create Ticket Status</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.ticket-statuses.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back to Statuses
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark">Status Properties</h5>
            <p class="text-muted small mb-4">Define a new operational state for support tickets. Ensure the color represents the state clearly (e.g. green for resolved, yellow for open).</p>

            <form method="POST" action="{{ route('admin.ticket-statuses.store') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold text-dark small">Status Name</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. On Hold, Waiting on Client, Escalated">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Color Code -->
                <div class="mb-4">
                    <label for="color_code" class="form-label fw-semibold text-dark small">Status Color</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" id="color_picker" class="form-control-color border rounded" style="width: 46px; height: 38px; cursor: pointer;" value="{{ old('color_code', '#6b7280') }}">
                        <input id="color_code" type="text" class="form-control @error('color_code') is-invalid @enderror" name="color_code" value="{{ old('color_code', '#6B7280') }}" required placeholder="e.g. #EF4444" style="text-transform: uppercase; width: 150px;">
                    </div>
                    @error('color_code')
                        <span class="text-danger small mt-1 d-block">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2 border-top pt-4">
                    <a href="{{ route('admin.ticket-statuses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Create Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const colorPicker = document.getElementById('color_picker');
    const colorInput = document.getElementById('color_code');

    colorPicker.addEventListener('input', function() {
        colorInput.value = colorPicker.value.toUpperCase();
    });

    colorInput.addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(colorInput.value)) {
            colorPicker.value = colorInput.value;
        }
    });
</script>
@endpush
@endsection
