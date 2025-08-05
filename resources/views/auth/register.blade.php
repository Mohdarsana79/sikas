<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
</head>

<body>
    <div class="container-fluid d-flex justify-content-center align-items-center min-vh-100 register-bg">
        <div class="card p-4 shadow-lg register-card">
            <h2 class="text-center mb-4">Daftar Akun</h2>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form id="registerForm" action="{{ route('register.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="fullname" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control @error('fullname') is-invalid @enderror" id="fullname"
                        name="fullname" value="{{ old('fullname') }}" required>
                    @error('fullname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username"
                        name="username" value="{{ old('username') }}" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" autocomplete="new-password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                        autocomplete="new-password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Daftar</button>
                <p class="text-center mt-3">Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></p>
            </form>
        </div>
    </div>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Hapus alert bootstrap jika ada
            $('.alert').remove();

            // Handle form submission
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');

                // Reset error state
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').text('');

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...'
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
                        submitBtn.prop('disabled', false).text('Daftar');

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
                                title: 'Pendaftaran Gagal',
                                text: xhr.responseJSON.message ||
                                    'Terjadi kesalahan saat pendaftaran',
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
