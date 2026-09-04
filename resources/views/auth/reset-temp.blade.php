<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - {{ config('app.name', 'Ignite') }}</title>

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
            max-width: 440px;
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
            background-color: #be123c;
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

            <h4 class="fw-bold text-dark text-center mb-1">Reset Your Password</h4>
            <p class="text-muted text-center mb-4 small">You are logged in with a temporary password. Please set a new secure password before continuing.</p>

            <form method="POST" action="{{ route('password.reset.temp') }}">
                @csrf

                <!-- New Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold text-dark small">New Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="••••••••">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password-confirm" class="form-label fw-semibold text-dark small">Confirm New Password</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="••••••••">
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-lg fs-6">
                        Update Password & Continue
                    </button>
                </div>

                <div class="text-center">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-link text-muted text-decoration-none small">
                            Cancel & Sign Out
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
