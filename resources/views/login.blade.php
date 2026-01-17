<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Kasir Modern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Background gradient animasi */
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b, #0f172a);
            background-size: 300% 300%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", sans-serif;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Card glassmorphism */
        .card {
            background: rgba(255, 255, 255, 0.08);
            border: none;
            border-radius: 20px;
            backdrop-filter: blur(12px);
            color: white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .card h2 {
            font-weight: 700;
            color: #fff;
        }

        /* Input custom */
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 12px;
            padding: 12px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #38bdf8;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.6);
            color: white;
        }

        /* Button custom */
        .btn-custom {
            background: #38bdf8;
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: bold;
            padding: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-custom:hover {
            background: #0ea5e9;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 6px 18px rgba(14, 165, 233, 0.5);
        }

        /* Ripple effect button */
        .btn-custom::after {
            content: "";
            position: absolute;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s ease, height 0.5s ease;
        }

        .btn-custom:active::after {
            width: 300%;
            height: 300%;
            transition: 0s;
        }

        /* Logo */
        .login-logo {
            max-width: 120px; /* atur sesuai kebutuhan */
            height: auto;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.5));
        }

        /* Footer text */
        .login-footer {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card shadow-lg p-5 text-center">
                    <!-- Logo -->
                    <img src="{{ asset('gambar/logo.webp') }}" alt="Logo" class="login-logo mx-auto d-block mb-4">

                    <h2 class="mb-4">Selamat Datang Kembali</h2>

                    <!-- Form Login -->
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3 text-start">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-4 text-start">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-custom">Login</button>
                        </div>
                    </form>

                    <p class="login-footer mt-4">Belum punya akun? <br> Hubungi administrator.</p>
                </div>
            </div>
        </div>
    </div>


    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('login_error'))
        Swal.fire({
            title: 'Login Gagal!',
            text: 'Email atau password yang Anda masukkan salah.',
            icon: 'error',
            confirmButtonColor: '#0ea5e9',
            confirmButtonText: 'Coba Lagi'
        });
        @endif
    </script>
</body>

</html>
