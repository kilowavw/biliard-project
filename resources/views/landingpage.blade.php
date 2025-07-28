<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            height: 100vh;
        }
    </style>
</head>

<body>
    <div class="d-flex align-items-center justify-content-center text-center text-white hero">
        <div>
            <h1 class="display-3 fw-bold">Sistem Kasir Modern</h1>
            <p class="lead col-md-8 mx-auto">Solusi manajemen penjualan yang cepat, andal, dan mudah digunakan untuk bisnis Anda.</p>
            <a href="{{ route('login') }}" class="btn btn-lg btn-light fw-bold mt-3">Mulai Sekarang</a>
        </div>
    </div>
</body>

</html>