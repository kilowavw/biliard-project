<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Company Profile - PT Billiard Jaya</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Logo -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">

  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

  <style>
    /* Background pattern bola billiard */
    body {
      background-image: url('https://i.ibb.co/Y8T9B0R/billiard-pattern.png'); /* ganti dengan pattern kamu */
      background-repeat: repeat;
      scroll-behavior: smooth; /* smooth scroll */
    }
  </style>
</head>
<body class="bg-gray-900 text-white">

 <!-- Navbar -->
<header class="fixed w-full bg-gray-800/70 backdrop-blur-md shadow-lg z-50 transition-all duration-300">
  <div class="max-w-7xl mx-auto flex justify-between items-center p-4">
    <!-- Logo -->
    <h1 class="text-2xl font-extrabold text-green-400 tracking-wide hover:text-green-300 transition-colors duration-300">
      PT Billiard Jaya
    </h1>

    <!-- Navigation -->
    <nav class="space-x-6 text-gray-300 font-medium">
      <a href="#about" 
         class="relative group hover:text-green-400 transition-colors duration-300">
         Tentang
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
      <a href="#services" 
         class="relative group hover:text-green-400 transition-colors duration-300">
         Layanan
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
      <a href="#gallery" 
         class="relative group hover:text-green-400 transition-colors duration-300">
         Galeri
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
      <a href="#contact" 
         class="relative group hover:text-green-400 transition-colors duration-300">
         Kontak
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
    </nav>

    <!-- Hamburger Menu for Mobile -->
    <div class="md:hidden flex items-center">
      <button id="mobile-menu-button" class="text-gray-300 hover:text-green-400 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>
</header>

<!-- Optional: Mobile Menu -->
<div id="mobile-menu" class="hidden fixed inset-0 bg-gray-900/90 backdrop-blur-md flex flex-col justify-center items-center space-y-6 text-xl text-green-400 z-40 transition-all">
  <a href="#about" class="hover:text-green-300">Tentang</a>
  <a href="#services" class="hover:text-green-300">Layanan</a>
  <a href="#gallery" class="hover:text-green-300">Galeri</a>
  <a href="#contact" class="hover:text-green-300">Kontak</a>
</div>

  <!-- Hero dengan slider -->
  <section class="h-screen relative">
    <div class="swiper h-full">
      <div class="swiper-wrapper">
        @foreach($data['images'] as $image)
          <div class="swiper-slide relative">
            <img src="{{ $image }}" class="w-full h-screen object-cover" />
            <div class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center text-center">
              <h2 class="text-4xl md:text-6xl font-bold text-green-400" data-aos="fade-up">{{ $data['company']['nama'] }}</h2>
              <p class="mt-4 text-lg" data-aos="fade-up" data-aos-delay="200">{{ $data['company']['tagline'] }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- Tentang Kami -->
  <section id="about" class="py-20 max-w-6xl mx-auto px-6" data-aos="fade-up">
    <div class="grid md:grid-cols-2 gap-10 ">
        <img src="{{ $data['images'][0] }}" alt="Tentang Kami" class="rounded-2xl shadow-lg" data-aos="fade-right">
        <div data-aos="fade-left">
            <h2 class="text-3xl font-bold text-green-400 mb-4">Tentang Kami</h2>
            <p class="text-gray-300 leading-relaxed">{{ $data['company']['deskripsi'] }}</p>
        </div>
    </div>
  </section>

  <!-- Layanan -->
  <section id="services" class="py-20 bg-gray-800">
    <div class="max-w-6xl mx-auto px-6 text-center">
      <h2 class="text-3xl font-bold text-green-400 mb-12" data-aos="fade-up">Layanan Kami</h2>
      <div class="grid md:grid-cols-3 gap-8">
        @foreach($data['layanan'] as $layanan)
          <div class="bg-gray-900 p-6 rounded-2xl shadow-lg" data-aos="fade-up" data-aos-delay="{{ $loop->index * 200 }}">
            <img src="{{ asset($layanan['image']) }}"
                alt="{{ $layanan['judul'] }}"
                class="w-full h-40 object-cover rounded-lg mb-4">
            <h3 class="text-xl font-bold mb-4">{{ $layanan['judul'] }}</h3>
            <p class="text-gray-400">{{ $layanan['deskripsi'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- Galeri -->
  <section id="gallery" class="py-20 max-w-6xl mx-auto px-6">
    <h2 class="text-3xl font-bold text-green-400 text-center mb-12" data-aos="fade-up">Galeri</h2>
    <div class="grid md:grid-cols-3 gap-6">
        @foreach($data['images'] as $image)
            <img src="{{ $image }}" class="rounded-2xl shadow-lg" data-aos="zoom-in" data-aos-delay="{{ $loop->index * 100 }}">
        @endforeach
    </div>
  </section>

<!-- Kontak -->
<section id="contact" class="py-20 bg-gray-900 text-center" data-aos="fade-up">
    <div class="max-w-6xl mx-auto px-6">
        <!-- Judul -->
        <h2 class="text-4xl font-extrabold text-green-400 mb-12" data-aos="fade-down">Kontak Kami</h2>

        <!-- Logo Billiard -->
        <div class="flex justify-center mb-12" data-aos="zoom-in">
<div class="px-2 py-4 text-white text-2xl font-bold tracking-wide">
                <i class="fa-solid fa-pool-8-ball mr-2"></i> Billiard-APP
            </div>
                </div>

        <!-- Kontak Cards -->
        <div class="grid md:grid-cols-3 gap-8 text-left">
            <!-- Alamat -->
            <div class="bg-gray-800 p-6 rounded-2xl shadow-lg flex items-start space-x-4 hover:scale-105 transition-transform duration-300" data-aos="fade-up" data-aos-delay="100">
            <i class="fa-solid fa-map-location text-green-400 text-3xl mt-1"></i>
                <div>
                    <h3 class="text-xl font-semibold text-green-400 mb-2">Alamat</h3>
                    <p class="text-gray-300">Pasar Antri Baru, Jl. Sriwijaya II No.008 Blok C, Setiamanah, Cimahi Tengah, Cimahi City, West Java 40522</p>
                </div>
            </div>

           <!-- Email -->
            <div class="bg-gray-800 p-6 rounded-2xl shadow-lg flex items-start space-x-4 hover:scale-105 transition-transform duration-300" data-aos="fade-up" data-aos-delay="200">
                <!-- Ganti <img> dengan <i> Font Awesome -->
            <i class="fa-solid fa-envelope text-green-400 text-3xl mt-1"></i>
                <div>
                    <h3 class="text-xl font-semibold text-green-400 mb-2">Email</h3>
                    <p class="text-gray-300">{{ $data['kontak']['email'] ?? 'info@billiardjaya.com' }}</p>
                </div>
            </div>

            <!-- Telepon -->
            <div class="bg-gray-800 p-6 rounded-2xl shadow-lg flex items-start space-x-4 hover:scale-105 transition-transform duration-300" data-aos="fade-up" data-aos-delay="300">
            <i class="fa-solid fa-phone text-green-400 text-3xl mt-1"></i>
                <div>
                    <h3 class="text-xl font-semibold text-green-400 mb-2">Telepon</h3>
                    <p class="text-gray-300">{{ $data['kontak']['telepon'] ?? '+62 811 2345 6789' }}</p>
                </div>
            </div>
        </div>

        <!-- Maps Embed -->
        <div class="mt-16 rounded-2xl overflow-hidden shadow-lg" data-aos="fade-up" data-aos-delay="400">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.123456789!2d107.5463!3d-6.8765!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e8f8c1c3d0f3%3A0xabcdef1234567890!2sPasar%20Antri%20Baru%2C%20Jl.%20Sriwijaya%20II%20No.008%2C%20Setiamanah%2C%20Cimahi%20Tengah%2C%20Cimahi%2C%20West%20Java%2040522!5e0!3m2!1sid!2sid!4v1692960000000!5m2!1sid!2sid"
                class="w-full h-80 border-0"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>



  <!-- Footer -->
  <footer class="bg-gray-900 py-6 text-center text-gray-500">
    <p>&copy; 2025 PT Billiard Jaya. All rights reserved.</p>
  </footer>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    AOS.init({ duration: 1000, once: true });

    // Swiper autoplay
    const swiper = new Swiper('.swiper', {
      loop: true,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      effect: 'slide',
    });
  </script>

  <script>
  // Toggle mobile menu
  const btn = document.getElementById('mobile-menu-button');
  const menu = document.getElementById('mobile-menu');
  btn.addEventListener('click', () => {
    menu.classList.toggle('hidden');
  });
</script>

</body>
</html>
