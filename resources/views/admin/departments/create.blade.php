@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Create Department</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back to Departments
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark">Department Properties</h5>
            <p class="text-muted small mb-4">Define a new functional department and assign it to a parent structural division.</p>

            <form method="POST" action="{{ route('admin.departments.store') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold text-dark small">Department Name</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Software Engineering, Customer Success, Accounting">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Division ID -->
                <div class="mb-4">
                    <label for="division_id" class="form-label fw-semibold text-dark small">Parent Division</label>
                    <select id="division_id" class="form-select @error('division_id') is-invalid @enderror" name="division_id" required>
                        <option value="">Select Parent Division</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('division_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Create Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
