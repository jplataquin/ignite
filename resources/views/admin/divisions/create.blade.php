@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Create Division</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.divisions.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back to Divisions
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark">Division Properties</h5>
            <p class="text-muted small mb-4">Define a new structural division. Divisions represent broad organizational units that contain multiple functional departments.</p>

            <form method="POST" action="{{ route('admin.divisions.store') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="form-label fw-semibold text-dark small">Division Name</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Technology, Operations, Legal, HR">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.divisions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Create Division</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
