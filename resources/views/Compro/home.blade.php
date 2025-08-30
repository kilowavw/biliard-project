@extends('compro.profile')

@section('content')
<main class="flex flex-col items-center justify-center w-full">
    <!-- Hero Slider Ultra Smooth -->
    <section class="relative h-[80vh] w-full overflow-hidden bg-gray-900">
    <div class="swiper h-full w-full">
        <div class="swiper-wrapper">
        @foreach($data['images'] as $image)
        <div class="swiper-slide relative overflow-hidden">
            <img src="{{ $image }}" class="w-full h-[80vh] object-cover transition-transform duration-1000 transform scale-105 hover:scale-110">
            
            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/30 to-black/10 flex flex-col items-center justify-center text-center px-6">
            <h2 class="text-4xl md:text-6xl font-extrabold text-green-400 drop-shadow-lg opacity-0 translate-x-[-50px] animate-slideFromLeft">
                {{ $data['company']['nama'] }}
            </h2>
            <p class="mt-6 text-xl md:text-2xl font-sans font-bold text-gray-100 tracking-normal leading-relaxed drop-shadow-lg opacity-0 translate-x-[40px]
            animate-slideFromRight delay-200">
                {{ $data['company']['tagline'] }}
            </p>
            </div>
        </div>
        @endforeach
        </div>

        <!-- Pagination Bullets -->
        <div class="swiper-pagination absolute bottom-6 left-0 w-full flex justify-center space-x-3 z-20"></div>
    </div>
    </section>


    <!-- Tentang Kami -->
    <section id="about" class="py-24 bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 relative overflow-hidden">
    <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center px-6">
        <!-- Gambar -->
        <div class="relative" data-aos="fade-right">
        <div class="rounded-3xl overflow-hidden shadow-2xl transform transition duration-700 hover:scale-105 group cursor-pointer">
            <img src="{{ $data['images'][0] }}" 
                alt="Tentang Kami" 
                class="w-full object-cover h-96 transition-transform duration-1000">
            <div class="absolute inset-0 bg-gradient-to-t from-green-500/20 via-transparent to-green-500/10 backdrop-blur-sm rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        </div>
        </div>

        <!-- Konten -->
        <div data-aos="fade-left">
        <div class="bg-gray-800/70 backdrop-blur-md p-8 rounded-3xl shadow-2xl transform transition duration-700 hover:scale-[1.02] group">
            <h2 class="text-3xl md:text-4xl font-extrabold text-green-400 mb-6 transition-all duration-500 group-hover:drop-shadow-[0_0_15px_rgba(34,197,94,0.7)]">
            Tentang Kami
            </h2>
            <p class="text-gray-300 leading-relaxed text-lg md:text-base transition-all duration-500 group-hover:text-green-200">
            {{ $data['company']['deskripsi'] }}
            </p>
            <a href="#contact" 
            class="mt-6 inline-block px-6 py-3 bg-gradient-to-r from-green-500 via-green-600 to-green-500
                    text-white font-semibold rounded-full shadow-lg hover:from-green-600 hover:via-green-700 hover:to-green-600
                    transition-all duration-500 transform hover:-translate-y-1 hover:scale-105">
            Hubungi Kami
            </a>
        </div>
        </div>
    </div>
    </section>

    <!-- Layanan -->
    <section id="services" class="py-24 bg-gray-800 relative">
    <div class="text-center w-full px-6">
        <h2 class="text-3xl md:text-4xl font-bold text-green-400 mb-12" data-aos="fade-up">Layanan Kami</h2>
        <div class="grid md:grid-cols-3 gap-8">
        @foreach($data['layanan'] as $layanan)
        <div class="service-card bg-gray-900 p-6 rounded-3xl shadow-lg transform opacity-0 translate-y-8 transition-all duration-500 hover:-translate-y-2 hover:shadow-gradient parallax-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 150 }}">
            <img src="{{ asset($layanan['image']) }}" alt="{{ $layanan['judul'] }}" class="w-full h-44 object-cover rounded-xl mb-4 parallax-img">
            <h3 class="text-xl font-bold mb-3 text-green-400">{{ $layanan['judul'] }}</h3>
            <p class="text-gray-400 leading-relaxed">{{ $layanan['deskripsi'] }}</p>
        </div>
        @endforeach
        </div>
    </div>
    </section>

    <!-- Harga -->
    <section id="harga" class="py-24 bg-gray-800 relative">
    <div class="text-center w-full px-6">
        <h2 class="text-3xl md:text-4xl font-bold text-green-400 mb-12" data-aos="fade-up">Harga</h2>
        <div class="grid md:grid-cols-3 gap-8">
        @foreach($data['harga'] as $harga)
        <div class="service-card bg-gray-900 p-4 rounded-3xl shadow-lg transform opacity-0 translate-y-8 transition-all duration-500 hover:-translate-y-2 hover:shadow-gradient parallax-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 150 }}">
            <a href="{{ $harga['link_gambar'] ?? '#' }}">
            <img src="{{ asset($harga['image']) }}" 
                alt="{{ $harga['judul'] }}" 
                class="w-full h-auto object-contain rounded-xl mb-4 parallax-img">
            </a>
            <h3 class="text-xl font-bold text-green-400">{{ $harga['judul'] }}</h3>
            <p class="text-gray-400 leading-relaxed">{{ $harga['deskripsi'] }}</p>
        </div>
        @endforeach
        </div>
    </div>
    </section>


    <!-- Galeri -->
    <section id="gallery" class="py-24 bg-gray-900">
    <h2 class="text-3xl md:text-3xl font-bold text-green-400 text-center mb-12" data-aos="fade-up">
        Galeri
    </h2>
    <div class="grid md:grid-cols-3 gap-8 w-full max-w-6xl mx-auto px-6">
        @foreach($data['images'] as $image)
        <div class="gallery-card group overflow-hidden rounded-2xl shadow-md bg-gray-800 transform opacity-0 translate-y-6 transition-all duration-700 hover:-translate-y-2 hover:shadow-xl" 
            data-aos="zoom-in" 
            data-aos-delay="{{ $loop->index * 100 }}">
            <div class="aspect-[16/9] overflow-hidden">
            <img src="{{ $image }}" 
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                alt="Gallery Image">
            </div>
        </div>
        @endforeach
    </div>
    </section>


    <!-- Kontak -->
    <section id="contact" class="py-24 bg-gray-900 text-center w-full">
    <h2 class="text-4xl md:text-3xl font-extrabold text-green-400 mb-12" data-aos="fade-down">Kontak Kami</h2>

    <div class="grid md:grid-cols-3 gap-8 text-left px-6">
        @php
            $kontakList = [
            ['icon'=>'fa-map-location','title'=>'Alamat','value'=>'Pasar Antri Baru, Jl. Sriwijaya II No.008, Cimahi Tengah, Cimahi, West Java'],
            ['icon'=>'fa-envelope','title'=>'Email','value'=>$data['kontak']['email'] ?? 'info@billiardjaya.com'],
            ['icon'=>'fa-phone','title'=>'Telepon','value'=>$data['kontak']['telepon'] ?? '+62 811 2345 6789']
            ];
        @endphp
        @foreach($kontakList as $kontak)
        <div class="bg-gray-800 p-6 rounded-3xl shadow-lg flex items-start space-x-4 hover:scale-105 transition-transform duration-300 hover:shadow-gradient" data-aos="fade-up" data-aos-delay="{{ $loop->index*100 }}">
            <i class="fa-solid {{ $kontak['icon'] }} text-green-400 text-3xl mt-1"></i>
            <div>
            <h3 class="text-xl font-semibold text-green-400 mb-2">{{ $kontak['title'] }}</h3>
            <p class="text-gray-300">{{ $kontak['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-16 rounded-3xl overflow-hidden shadow-lg w-full px-6" data-aos="fade-up" data-aos-delay="400">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.123456789!2d107.5463!3d-6.8765!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e8f8c1c3d0f3%3A0xabcdef1234567890!2sPasar%20Antri%20Baru%2C%20Jl.%20Sriwijaya%20II%20No.008%2C%20Setiamanah%2C%20Cimahi%20Tengah%2C%20Cimahi%2C%20West%20Java%2040522!5e0!3m2!1sid!2sid!4v1692960000000!5m2!1sid!2sid"
            class="w-full h-96 border-0 rounded-3xl" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
    </section>
</main>

<style>
/* Animasi fade up */
@keyframes fadeUp {0% {opacity:0; transform:translateY(20px);} 100% {opacity:1; transform:translateY(0);} }
.animate-fadeUp {animation: fadeUp 0.8s ease forwards;}

/* Smooth fade & slide for slider text */
/* Teks slide dari kiri */
/* Animasi teks masuk */
@keyframes slideFromLeft { 0% {opacity:0; transform:translateX(-50px);} 100% {opacity:1; transform:translateX(0);} }
@keyframes slideFromRight { 0% {opacity:0; transform:translateX(50px);} 100% {opacity:1; transform:translateX(0);} }
@keyframes slideFadeUp { 0% {opacity:0; transform:translateY(20px);} 100% {opacity:1; transform:translateY(0);} }

.animate-slideFromLeft { animation: slideFromLeft 1s ease forwards; }
.animate-slideFromRight { animation: slideFromRight 1s ease forwards; }
.animate-slideFadeUp { animation: slideFadeUp 1s ease forwards; }
.animate-slideFadeUp.delay-200 { animation-delay: 0.2s; }
.animate-slideFadeUp.delay-400 { animation-delay: 0.4s; }

/* Hover scale gambar */
.swiper-slide img { transition: transform 1s ease; }
.swiper-slide:hover img { transform: scale(1.1); }

/* Pagination bullets */
.swiper-pagination-bullet {
  width: 12px;
  height: 12px;
  background-color: rgba(34,197,94,0.5);
  opacity:1;
}
.swiper-pagination-bullet-active {
  background-color: #22c55e;
  box-shadow: 0 0 8px #22c55e;
}

/* Responsif untuk mobile & tablet */
@media (max-width: 768px) {
  .swiper-slide img { height: 60vh; }
  .animate-slideFromLeft, .animate-slideFromRight { transform: translateX(0); }
  h2 { font-size: 2.5rem; }
  p { font-size: 1rem; }
}
/* Overlay dan efek blur */
.slider-overlay {
  backdrop-filter: blur(3px);
}

/* Swiper navigation style */
.swiper-button-next, .swiper-button-prev {
  color: #22c55e; /* Hijau premium */
  transition: transform 0.3s ease;
}
.swiper-button-next:hover, .swiper-button-prev:hover {
  transform: scale(1.2);
}
.swiper-pagination-bullet {
  background-color: rgba(34,197,94,0.5);
  opacity:1;
}
.swiper-pagination-bullet-active {
  background-color: #22c55e;
}

/* Shadow & hover premium */
.service-card, .gallery-card {
    box-shadow: 0 12px 30px rgba(0,0,0,0.2), 0 0 30px rgba(72,187,120,0.2);
    transition: transform 0.3s ease, box-shadow 0.5s ease;
}
.service-card:hover, .gallery-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.3), 0 0 40px rgba(72,187,120,0.3);
}

/* Parallax images */
.parallax-img {
    transition: transform 0.3s ease-out;
}
.parallax-card:hover .parallax-img {
    transform: scale(1.05);
}

/* Dynamic shadow gradient on scroll */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const imagesCount = {{ count($data['images']) }};
  
  const isMobile = window.innerWidth < 768; // deteksi device mobile
  const autoplayDelay = isMobile ? 4000 : 5000; // lebih cepat di mobile
  
  const swiper = new Swiper('.swiper', {
    loop: imagesCount > 1,
    effect: 'fade',
    fadeEffect: { crossFade: true },
    speed: 1000,
    autoplay: { delay: autoplayDelay, disableOnInteraction: false },
    pagination: { el: '.swiper-pagination', clickable: true },
    navigation: false,
    grabCursor: true // swipe gesture smooth
  });
});

  // ===== Animate cards on scroll (staggered & smooth) =====
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        const cards = entry.target.querySelectorAll('.service-card, .gallery-card');
        cards.forEach((card, index) => {
          setTimeout(() => {
            card.classList.remove('opacity-0','translate-y-6','translate-y-8');
            card.classList.add('animate-fadeUp');
          }, index * 100); // Delay 100ms per card
        });
        observer.unobserve(entry.target); // Stop observing setelah animasi
      }
    });
  }, {threshold:0.1});

  // Observasi section layanan & galeri
  document.querySelectorAll('#services, #gallery').forEach(section => observer.observe(section));

  // ===== Dynamic shadow gradient on scroll =====
  window.addEventListener('scroll', () => {
    const scrollPos = window.scrollY;
    const greenShift = Math.min(187 + scrollPos * 0.05, 255);
    const cards = document.querySelectorAll('.service-card, .gallery-card');
    cards.forEach(card => {
        card.style.boxShadow = `0 12px 30px rgba(0,0,0,0.2), 0 0 30px rgba(72, ${greenShift}, 120, 0.3)`;
    });
  });

  // ===== Initialize AOS (Animate On Scroll) =====
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 800,
      once: true,
    });
  }

});
</script>


@endsection
