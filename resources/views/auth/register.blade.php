<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - {{ config('app.name', 'Ignite') }}</title>

    <!-- Vite Assets -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background-color: var(--fd-canvas-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            max-width: 460px;
            width: 100%;
            padding: 2.5rem;
            border-radius: 12px;
        }
        .auth-logo {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn-primary {
            background-color: var(--fd-primary);
            border-color: var(--fd-primary);
            font-weight: 600;
            transition: all 0.2s ease-in-out;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #be123c; /* Darker rose/crimson */
            border-color: #be123c;
            box-shadow: 0 0 0 0.25rem rgba(225, 29, 72, 0.25);
        }
        .form-control:focus {
            border-color: var(--fd-primary);
            box-shadow: 0 0 0 0.25rem rgba(225, 29, 72, 0.25);
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center px-3">
        <div class="card fd-card auth-card shadow-lg">
            <div class="auth-logo text-danger">
                Ignite
            </div>

            <h4 class="fw-bold text-dark text-center mb-1">Create an Account</h4>
            <p class="text-muted text-center mb-4 small">Register to submit and track support tickets</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Full Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold text-dark small">Full Name</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Jane Doe">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-dark small">Email Address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Division -->
                <div class="mb-3">
                    <label id="division_label" for="division_id" class="form-label fw-semibold text-dark small">Division (Optional)</label>
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

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold text-dark small">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Min. 8 characters">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password-confirm" class="form-label fw-semibold text-dark small">Confirm Password</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-lg fs-6">
                        Register Account
                    </button>
                </div>

                <!-- Alternative Link -->
                <div class="text-center">
                    <span class="text-muted small">Already have an account? </span>
                    <a href="{{ route('login') }}" class="text-danger fw-semibold small text-decoration-none">Sign In</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const divisionSelect = document.getElementById('division_id');
        const departmentSelect = document.getElementById('department_id');
        const originalDepartmentOptions = Array.from(departmentSelect.options);

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

        divisionSelect.addEventListener('change', () => filterDepartments(false));

        // Run on initial load
        filterDepartments(true);
    </script>
</body>
</html>
