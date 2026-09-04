<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Ignite') }}</title>

    <!-- Vite Assets -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg fd-header navbar-dark shadow-sm">
        <div class="container-fluid">
            <!-- Mobile Toggle -->
            <button class="navbar-toggler me-2 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <a class="navbar-brand text-white fw-bold" href="#">
                <span class="text-danger">Ignite</span>
            </a>

            <!-- Notification Bell Hub -->
            <div class="d-flex align-items-center ms-auto">
                <button class="btn btn-link text-white position-relative p-0" id="notificationBell">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-bell-fill" viewBox="0 0 16 16">
                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.995-14.901a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901z"/>
                    </svg>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                </button>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar / Offcanvas -->
            <nav id="offcanvasSidebar" class="col-lg-2 d-lg-block bg-white sidebar collapse offcanvas-lg offcanvas-start border-end fd-card" tabindex="-1">
                <div class="offcanvas-header d-lg-none">
                    <h5 class="offcanvas-title fw-bold">Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasSidebar" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column pt-lg-3">
                    <ul class="nav flex-column mb-auto">
                        <li class="nav-item">
                            <a class="nav-link text-dark {{ Request::routeIs('dashboard') ? 'active fw-bold' : '' }}" href="{{ route('dashboard') }}">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark {{ Request::routeIs('tickets.*') ? 'active fw-bold' : '' }}" href="{{ route('tickets.index') }}">
                                Tickets
                            </a>
                        </li>
                        @if(Auth::user() && Auth::user()->user_type === 'admin')
                            <li class="nav-item">
                                <a class="nav-link text-dark {{ Request::routeIs('admin.users.*') ? 'active fw-bold' : '' }}" href="{{ route('admin.users.index') }}">
                                    Users
                                </a>
                            </li>
                        @endif
                    </ul>
                    
                    <!-- User Profile & Logout -->
                    <div class="border-top pt-3 mt-auto">
                        <div class="d-flex align-items-center mb-3 px-3">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate small" style="max-width: 130px;">{{ Auth::user()->name ?? 'User' }}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.75rem; max-width: 130px;">{{ Auth::user()->email ?? '' }}</div>
                            </div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center px-3" style="min-height: 44px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right me-2" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                                    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-lg-10 ms-sm-auto px-md-4 pt-3">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
