<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Company Profile - PT Billiard Jaya</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Logo -->
  <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Bungee&display=swap" rel="stylesheet">

  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

  <style>
    /* Background pattern bola billiard */x
    body {
    /* Gradient khas billiard: hijau tua - hitam */
    background: radial-gradient(circle at top left, #064e3b, #000000 80%);
    color: #ffffff;
    scroll-behavior: smooth;
    position: relative;
    overflow-x: hidden;
  }

  /* Aksen bola billiard samar */
  body::before, body::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.15;
    z-index: -1;
  }

  body::before {
    width: 300px;
    height: 300px;
    background: #22c55e; /* hijau neon billiard */
    top: -50px;
    left: -80px;
  }

  body::after {
    width: 400px;
    height: 400px;
    background: #0ea5e9; /* biru lembut */
    bottom: -100px;
    right: -120px;
  }
  </style>
</head>
<body class="bg-gray-900 text-white">

 <!-- Navbar -->
<header class="sticky top-0 w-full bg-gray-900/80 backdrop-blur-md shadow-lg z-50 transition-all duration-300">
  <div class="max-w-7xl mx-auto flex justify-between items-center px-6 py-3">
    
    <!-- Logo -->
    <a href="/" class="flex items-center space-x-3">
      <img src="{{ asset('images/logo_fiks.png') }}" 
           alt="Cimahi Billiard Centre" 
           class="h-14 md:h-16 lg:h-20 w-auto object-contain hover:opacity-90 transition duration-300">
      <span class="hidden md:block text-xl font-bold text-green-400 tracking-wide">
        Cimahi Billiard Centre
      </span>
    </a>

    <!-- Navigation (Desktop) -->
    <nav class="hidden md:flex space-x-8 text-gray-300 font-medium text-base lg:text-lg">
      <a href="{{ route('home') }}#about" 
         class="relative group {{ request()->is('/') ? 'text-green-400 font-semibold' : 'hover:text-green-400' }}">
         Tentang
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
      </a>
      <a href="{{ route('home') }}#services" 
         class="relative group hover:text-green-400 transition">
         Layanan
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
      </a>
      <a href="{{ route('home') }}#harga" 
         class="relative group hover:text-green-400 transition">
         Harga
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
      </a>
      <a href="{{ route('home') }}#contact" 
         class="relative group hover:text-green-400 transition">
         Kontak
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
      </a>
      <a href="{{ route('events.index') }}" 
        class="relative group {{ request()->routeIs('events.*') ? 'text-green-400 font-semibold' : 'hover:text-green-400' }}">
          Event
          <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
      </a>
      <a href="{{ route('pelayanan.index') }}" 
        class="relative group {{ request()->routeIs('pelayanan.*') ? 'text-green-400 font-semibold' : 'hover:text-green-400' }}">
          Pelayanan
          <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
      </a>
    </nav>

    <!-- Hamburger Menu (Mobile) -->
    <div class="md:hidden flex items-center">
      <button id="mobile-menu-button" class="text-gray-300 hover:text-green-400 focus:outline-none">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>

  </div>
</header>


<!-- Optional: Mobile Menu -->
<div id="mobile-menu" class="hidden fixed inset-0 bg-gray-900/90 backdrop-blur-md flex flex-col justify-center items-center space-y-6 text-xl text-green-400 z-40 transition-all">
  <a href="{{ route('home') }}#about" 
         class="relative group hover:text-green-400 transition-colors duration-300">
         Tentang
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
      <a href="{{ route('home') }}#services" 
         class="relative group hover:text-green-400 transition-colors duration-300">
         Layanan
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
      <a href="{{ route('home') }}#harga" 
         class="relative group hover:text-green-400 transition-colors duration-300">
         Harga
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
      <a href="{{ route('home') }}#contact" 
         class="relative group hover:text-green-400 transition-colors duration-300">
         Kontak
         <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
      <a href="{{ route('events.index') }}" 
        class="relative group transition-colors duration-300 {{ request()->routeIs('events.*') ? 'text-green-400 font-semibold' : 'hover:text-green-400' }}">
          Event
          <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
      <a href="{{ route('pelayanan.index') }}" 
        class="relative group transition-colors duration-300 {{ request()->routeIs('pelayanan.*') ? 'text-green-400 font-semibold' : 'hover:text-green-400' }}">
          Pelayanan
          <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-green-400 transition-all group-hover:w-full"></span>
      </a>
</div>

 {{-- Content Section --}}
    <main class="p-8">
        @yield('content')
    </main>

    
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
  document.addEventListener('DOMContentLoaded', function() {
    const desktopNav = document.querySelector('header nav');
    const mobileBtn = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    function toggleMobileView() {
      if(window.innerWidth < 768){
        // Mobile: hide desktop nav, show hamburger button
        if(desktopNav) desktopNav.classList.add('hidden');
        if(mobileBtn) mobileBtn.classList.remove('hidden');
        if(mobileMenu) mobileMenu.classList.add('hidden'); // pastikan menu tetap tersembunyi
      } else {
        // Desktop: show nav, hide mobile menu & button
        if(desktopNav) desktopNav.classList.remove('hidden');
        if(mobileBtn) mobileBtn.classList.add('hidden');
        if(mobileMenu) mobileMenu.classList.add('hidden');
      }
    }

    // Initial check
    toggleMobileView();

    // Re-check on window resize
    window.addEventListener('resize', toggleMobileView);

    // Toggle mobile menu ketika hamburger diklik
    if(mobileBtn && mobileMenu){
      mobileBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
      });
    }
  });

</script>


</body>
</html>
