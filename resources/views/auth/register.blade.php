<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="icon" type="image/x-icon" width="100" href="{{ asset('assets/images/large.png') }}">

    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>

    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
        }

        .auth-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #eaeeff 0%, #887f91 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .auth-logo i {
            font-size: 2rem;
            color: white;
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .input-group-text {
            background: transparent;
            border: 2px solid #e5e7eb;
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .input-group .form-control {
            border-right: none;
            border-radius: 12px 0 0 12px;
        }

        .btn-auth {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-auth::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-auth:hover::before {
            left: 100%;
        }

        .password-toggle {
            background: none;
            border: none;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .auth-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 30px 20px;
                margin: 10px;
            }

            .auth-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <!-- Hidden elements for flash messages -->
    @if(session('success'))
    <div data-success-message="{{ session('success') }}"></div>
    @endif

    @if(session('error'))
    <div data-error-message="{{ session('error') }}"></div>
    @endif

    <div class="auth-container">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>

        <div class="auth-card">
            <div class="text-center mb-4">
                <div class="auth-logo">
                    <img src="{{ asset('assets/images/large.png') }}" class="rounded-circle" width="100">
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="text-muted">Join us today and get started</p>
            </div>

            <form id="registerForm" method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <input id="name" name="name" type="text"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter your full name"
                            value="{{ old('name') }}" required>
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <input id="email" name="email" type="email"
                            class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email"
                            value="{{ old('email') }}" required>
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input id="password" name="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="Create a password"
                            required>
                        <button type="button" class="input-group-text password-toggle">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            class="form-control" placeholder="Confirm your password" required>
                        <button type="button" class="input-group-text password-toggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-auth w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i>
                    Create Account
                </button>

                <div class="text-center pt-3 border-top">
                    <p class="text-muted mb-0">
                        Already have an account?
                        <a href="{{ route('login') }}" class="auth-link">Sign in here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Auth JavaScript -->
    <script src="{{ asset('assets/js/auth.js') }}"></script>
</body>

</html>