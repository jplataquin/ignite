@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Edit User Profile</h1>
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
        <div class="card fd-card p-4 shadow-sm mb-4">
            <h5 class="fw-bold mb-3 text-dark">User Profile Details</h5>
            <p class="text-muted small mb-4">Update the profile settings, select department grouping, and manage roles for this user account.</p>

            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold text-dark small">Full Name</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required placeholder="e.g. Jane Doe">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-dark small">Email Address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required placeholder="e.g. jane.doe@example.com">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- User Type -->
                <div class="mb-3">
                    <label for="user_type" class="form-label fw-semibold text-dark small">User Type / Privilege</label>
                    <select id="user_type" class="form-select @error('user_type') is-invalid @enderror" name="user_type" required>
                        <option value="regular" {{ old('user_type', $user->user_type) === 'regular' ? 'selected' : '' }}>Regular User</option>
                        <option value="moderator" {{ old('user_type', $user->user_type) === 'moderator' ? 'selected' : '' }}>Moderator</option>
                        <option value="admin" {{ old('user_type', $user->user_type) === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    @error('user_type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Department -->
                <div class="mb-4">
                    <label for="department_id" class="form-label fw-semibold text-dark small">Department</label>
                    <select id="department_id" class="form-select @error('department_id') is-invalid @enderror" name="department_id">
                        <option value="">No Department / Unassigned</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }} ({{ $department->division->name ?? 'No Division' }})
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Roles Section -->
                <div class="border-top pt-4 mb-4">
                    <h6 class="fw-bold text-dark mb-2">Role Assignments</h6>
                    <p class="text-muted small mb-3">Roles define specific cross-department privileges and access properties. Select the roles that apply to this user.</p>
                    
                    <div class="row g-3">
                        @forelse($roles as $role)
                            <div class="col-12 col-md-6">
                                <div class="form-check border rounded p-3 h-100 bg-light d-flex align-items-start">
                                    <input class="form-check-input me-2 mt-1" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}" {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="role_{{ $role->id }}">
                                        <span class="fw-bold text-dark d-block small mb-1">{{ $role->name }}</span>
                                        <span class="text-muted d-block" style="font-size: 0.75rem; line-height: 1.3;">{{ $role->description ?: 'No description provided.' }}</span>
                                    </label>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-3 text-muted border border-dashed rounded">
                                <span class="small d-block">No roles available in the system yet.</span>
                                <a href="{{ route('admin.roles.create') }}" class="small fw-semibold text-decoration-none">Create a new role</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2 border-top pt-4">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
