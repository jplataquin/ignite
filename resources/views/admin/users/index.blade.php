@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">User Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill me-2" viewBox="0 0 16 16">
                <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
            </svg>
            Create New User
        </a>
    </div>
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

<div class="card fd-card mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="px-4 py-3 text-muted fw-bold text-uppercase small">Name</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Email</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">User Type</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Status</th>
                    <th scope="col" class="py-3 text-muted fw-bold text-uppercase small">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="px-4 py-3 fw-bold text-dark d-flex align-items-center">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            {{ $user->name }}
                        </td>
                        <td class="py-3 text-muted">{{ $user->email }}</td>
                        <td class="py-3">
                            @if($user->user_type === 'admin')
                                <span class="badge bg-dark rounded-pill px-3 py-1.5 fw-semibold">Admin</span>
                            @elseif($user->user_type === 'moderator')
                                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-semibold">Moderator</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-1.5 fw-semibold">Regular</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($user->must_reset_password)
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-key-fill me-1" viewBox="0 0 16 16">
                                        <path d="M3.5 11.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0m5-3a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                                        <path d="M0 12.5A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5V6.854a1.5 1.5 0 0 0-.44-1.06L9.88 1.112A1.5 1.5 0 0 0 8.818 1H1.5A1.5 1.5 0 0 0 0 2.5zM1.5 2h7.318a.5.5 0 0 1 .353.146l3.682 3.682a.5.5 0 0 1 .146.353V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5"/>
                                    </svg>
                                    Temp Password
                                </span>
                            @else
                                <span class="badge bg-success rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-check-lg me-1" viewBox="0 0 16 16">
                                        <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-5.5a.733.733 0 0 1 1.02 0z"/>
                                    </svg>
                                    Active
                                </span>
                            @endif
                        </td>
                        <td class="py-3 text-muted small">{{ $user->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
