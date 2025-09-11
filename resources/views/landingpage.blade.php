<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir Modern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Navbar */
        .navbar {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(10px);
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand img {
            height: 40px;
            transition: transform 0.4s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.1) rotate(3deg);
        }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, #0f172a, #1e293b, #0f172a);
            background-size: 300% 300%;
            animation: gradientBG 15s ease infinite;
            height: 100vh;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

         .hero-logo {
            width: 150px;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.3));
            animation: fadeInDown 1s ease forwards;
            transition: transform 0.4s ease, filter 0.4s ease;
        }

        .hero-logo:hover {
            transform: scale(1.1) translateY(-5px);
            filter: drop-shadow(0 0 15px rgba(56, 189, 248, 0.6));
        }

        .hero h1 {
            animation: fadeInUp 1.2s ease forwards;
        }

        .hero p {
            animation: fadeInUp 1.5s ease forwards;
        }

        .btn-custom {
            background: #38bdf8;
            border: none;
            color: #fff;
            transition: 0.3s ease;
            animation: fadeInUp 1.8s ease forwards;
            position: relative;
            overflow: hidden;
        }

        .btn-custom:hover {
            background: #0ea5e9;
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.5);
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
            transition: width 0.4s ease, height 0.4s ease;
        }

        .btn-custom:active::after {
            width: 300%;
            height: 300%;
            transition: 0s;
        }

        /* Scroll indicator */
        .scroll-down {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 2rem;
            color: white;
            opacity: 0.7;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translate(-50%, 0); }
            50% { transform: translate(-50%, -12px); }
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
                <span class="ms-2 text-white">Kasir Modern</span>
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <div>
            <img src="{{ asset('images/logo.png') }}" alt="Logo Perusahaan" class="hero-logo">
            <h1 class="display-4 fw-bold">Sistem Kasir Modern</h1>
            <p class="lead col-md-8 mx-auto mt-3">
                Solusi manajemen penjualan yang cepat, andal, dan mudah digunakan untuk bisnis Anda.
            </p>
            <a href="{{ route('login') }}" class="btn btn-lg btn-custom fw-bold mt-4 px-5 py-3">
                Mulai Sekarang
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener("scroll", function () {
            const nav = document.querySelector(".navbar");
            nav.classList.toggle("scrolled", window.scrollY > 20);
        });
    </script>
</body>

</html>
