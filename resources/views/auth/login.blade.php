<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log In - {{ config('app.name', 'Ignite') }}</title>

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
            background-color: #be123c; /* Darker rose/crimson */
            border-color: #be123c;
            box-shadow: 0 0 0 0.25rem rgba(225, 29, 72, 0.25);
        }
        .form-control:focus {
            border-color: var(--fd-primary);
            box-shadow: 0 0 0 0.25rem rgba(225, 29, 72, 0.25);
        }
        .form-check-input:checked {
            background-color: var(--fd-primary);
            border-color: var(--fd-primary);
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center px-3">
        <div class="card fd-card auth-card shadow-lg">
            <div class="auth-logo text-danger">
                Ignite
            </div>

            <h4 class="fw-bold text-dark text-center mb-1">Welcome Back</h4>
            <p class="text-muted text-center mb-4 small">Log in to manage your tickets</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-dark small">Email Address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@example.com">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold text-dark small">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-input-label text-muted small" for="remember">
                        Remember me on this device
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg fs-6">
                        Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
