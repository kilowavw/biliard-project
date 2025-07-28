<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registrasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Selamat Datang Kembali</h2>

                        <!-- Formulir Login -->
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-bold">Login</button>
                            </div>
                        </form>
                        <!-- Saat ini tidak ada registrasi publik sesuai controller, tapi jika diperlukan bisa ditambahkan di sini -->
                    </div>
                </div>
                <p class="text-center text-white-50 mt-3">Belum punya akun? Hubungi administrator.</p>
            </div>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Menampilkan alert jika ada error login
        @if(session('login_error'))
        Swal.fire({
            title: 'Login Gagal!',
            text: 'Email atau password yang Anda masukkan salah.',
            icon: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Coba Lagi'
        });
        @endif
    </script>
</body>

</html>