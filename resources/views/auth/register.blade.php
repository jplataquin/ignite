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
            max-width: 420px;
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

            <h4 class="fw-bold text-dark text-center mb-1">Create Account</h4>
            <p class="text-muted text-center mb-4 small">Get started with your ticketing profile</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold text-dark small">Full Name</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="John Doe">
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

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold text-dark small">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="••••••••">
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
                        Register
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center">
                    <span class="text-muted small">Already have an account?</span>
                    <a href="{{ route('login') }}" class="text-danger small fw-semibold text-decoration-none">Sign in instead</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
