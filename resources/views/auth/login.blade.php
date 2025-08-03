@extends('layouts.app')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-100) 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-xl border-0">
                    <div class="card-body p-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="bg-primary text-white p-3 rounded-circle d-inline-block mb-3"
                                 style="width: 72px; height: 72px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-bullseye" style="font-size: 2rem;"></i>
                            </div>
                            <h2 class="fw-bold mb-1 text-dark">{{ __('Target Management') }}</h2>
                            <p class="text-muted mb-0">{{ __('Please sign in to continue') }}</p>
                        </div>

                        <!-- Error Messages -->
                        @if ($errors->any())
                            <x-alert type="danger" class="mb-4" :dismissible="false">
                                <strong>{{ __('Login Failed') }}</strong>
                                <ul class="mb-0 mt-1 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </x-alert>
                        @endif

                        <!-- Login Form -->
                        <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                            @csrf
                            
                            <div class="mb-3">
                                <label for="username" class="form-label fw-medium">
                                    <i class="bi bi-person me-1"></i>{{ __('Username') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-start-0 @error('username') is-invalid @enderror" 
                                           id="username" 
                                           name="username" 
                                           value="{{ old('username') }}" 
                                           placeholder="{{ __('Enter your username') }}"
                                           required 
                                           autofocus>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-medium">
                                    <i class="bi bi-lock me-1"></i>{{ __('Password') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="{{ __('Enter your password') }}"
                                           required>
                                    <button class="btn btn-outline-secondary border-start-0" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label text-muted" for="remember">
                                    <i class="bi bi-clock me-1"></i>{{ __('Remember me') }}
                                </label>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary py-2 fw-medium" style="display: block !important; visibility: visible !important; background: #4f46e5 !important; color: white !important; border: 2px solid #4f46e5 !important;">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('Sign In') }}
                                </button>
                            </div>
                        </form>

                        <!-- Footer -->
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>{{ __('Secure login with SSL encryption') }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- System Info -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ __('System Version') }}: {{ config('app.version', '1.0.0') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-light {
    background-color: var(--light-bg) !important;
}

.card {
    border-radius: 1rem;
    backdrop-filter: blur(10px);
}

.input-group-text {
    border-color: var(--border-color);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%) !important;
    border: none !important;
    transition: all 0.3s ease;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    color: white !important;
    font-weight: 500 !important;
    padding: 0.5rem 1rem !important;
    border-radius: 0.375rem !important;
    text-decoration: none !important;
    margin-top: 1rem !important;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #3730a3 0%, #312e81 100%) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3) !important;
    color: white !important;
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.alert {
    border-radius: 0.5rem;
}

/* Animation for card entrance */
.card {
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 2rem !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (togglePassword && password && eyeIcon) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            eyeIcon.classList.toggle('bi-eye');
            eyeIcon.classList.toggle('bi-eye-slash');
        });
    }

    // Form validation
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }

    // Auto-focus on username field
    const usernameField = document.getElementById('username');
    if (usernameField) {
        usernameField.focus();
    }

    // Add loading state to submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
            submitBtn.disabled = true;
        });
    }
});
</script>
@endsection 