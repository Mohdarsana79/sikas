<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
</head>

<body>
    <div class="container-fluid d-flex justify-content-center align-items-center min-vh-100 login-bg">
        <div class="card p-4 shadow-lg login-card">
            <h2 class="text-center mb-4">Login</h2>
            <form id="loginForm" action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="identity" class="form-label">Email atau Username</label>
                    <input type="text" class="form-control @error('identity') is-invalid @enderror" id="identity"
                        name="identity" value="{{ old('identity') }}" required autofocus>
                    @error('identity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" autocomplete="current-password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Ingat Saya</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
                <p class="text-center mt-3">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></p>
            </form>
        </div>
    </div>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
    <script>
        // Notifikasi Success
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000
            });
        @endif

        // Notifikasi Error
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '{{ session('error') }}',
            });
        @endif

        // Notifikasi Validasi Error
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: `{!! implode('<br>', $errors->all()) !!}`,
            });
        @endif
    </script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');

                // Reset error state
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').text('');

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...'
                );

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).text('Login');

                        if (xhr.status === 422) { // Validation error
                            var errors = xhr.responseJSON.errors;
                            for (var field in errors) {
                                var input = form.find('[name="' + field + '"]');
                                input.addClass('is-invalid');
                                input.next('.invalid-feedback').text(errors[field][0]);
                            }

                            // Tampilkan SweetAlert untuk error validasi
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal',
                                html: Object.values(errors).join('<br>'),
                            });
                        } else { // Other errors
                            Swal.fire({
                                icon: 'error',
                                title: 'Login Gagal',
                                text: xhr.responseJSON.message ||
                                    'Terjadi kesalahan saat login',
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
