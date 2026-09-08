@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Create New User</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card fd-card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark">User Account Details</h5>
            <p class="text-muted small mb-4">Create a new profile with a temporary password. The user will be required to change their password on their first login.</p>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold text-dark small">Full Name</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required placeholder="e.g. Jane Doe">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-dark small">Email Address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required placeholder="e.g. jane.doe@example.com">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- User Type -->
                <div class="mb-3">
                    <label for="user_type" class="form-label fw-semibold text-dark small">User Type / Role</label>
                    <select id="user_type" class="form-select @error('user_type') is-invalid @enderror" name="user_type" required>
                        <option value="regular" {{ old('user_type') === 'regular' ? 'selected' : '' }}>Regular User</option>
                        <option value="moderator" {{ old('user_type') === 'moderator' ? 'selected' : '' }}>Moderator</option>
                        <option value="admin" {{ old('user_type') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    @error('user_type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Division -->
                <div class="mb-3">
                    <label id="division_label" for="division_id" class="form-label fw-semibold text-dark small">Division <span id="division_required_dot" class="text-danger d-none">*</span></label>
                    <select id="division_id" class="form-select @error('division_id') is-invalid @enderror" name="division_id">
                        <option value="">Select Division</option>
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

                <!-- Department -->
                <div class="mb-3">
                    <label for="department_id" class="form-label fw-semibold text-dark small">Department (Optional)</label>
                    <select id="department_id" class="form-select @error('department_id') is-invalid @enderror" name="department_id" disabled>
                        <option value="" data-division-id="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" data-division-id="{{ $department->division_id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Temporary Password -->
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold text-dark small">Temporary Password</label>
                    <div class="input-group">
                        <input id="password" type="text" class="form-control @error('password') is-invalid @enderror" name="password" required value="Welcome123!">
                        <button class="btn btn-outline-secondary" type="button" id="generatePassword">Generate</button>
                    </div>
                    <small class="text-muted d-block mt-1">This is a one-time temporary password. The user must reset this when they sign in.</small>
                    @error('password')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Create User Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('generatePassword').addEventListener('click', function() {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        let password = "";
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('password').value = password;
    });

    const userTypeSelect = document.getElementById('user_type');
    const divisionSelect = document.getElementById('division_id');
    const divisionRequiredDot = document.getElementById('division_required_dot');
    const departmentSelect = document.getElementById('department_id');
    const originalDepartmentOptions = Array.from(departmentSelect.options);

    // Dynamic required division based on user type
    function checkUserType() {
        if (userTypeSelect.value === 'regular') {
            divisionSelect.required = true;
            divisionRequiredDot.classList.remove('d-none');
        } else {
            divisionSelect.required = false;
            divisionRequiredDot.classList.add('d-none');
        }
    }

    // Dynamic filtering of departments based on selected division
    function filterDepartments(initial = false) {
        const selectedDivisionId = divisionSelect.value;
        const previousVal = initial ? "{{ old('department_id') }}" : departmentSelect.value;

        // Clear and rebuild options
        departmentSelect.innerHTML = '';

        // Add default/placeholder option
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = selectedDivisionId ? 'No Department / Unassigned' : 'Select Division First';
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

    userTypeSelect.addEventListener('change', checkUserType);
    divisionSelect.addEventListener('change', () => filterDepartments(false));

    // Run on initial load
    checkUserType();
    filterDepartments(true);
</script>
@endpush
@endsection
