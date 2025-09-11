@extends('default')

@section('title', 'Panduan Penggunaan')

@section('content')
<div class="container mx-auto p-4 md:p-6 bg-[#121212] text-white">
    <!-- Judul Utama -->
    <h1 class="text-4xl font-extrabold mb-8 text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent">
        Panduan & FAQ
    </h1>
    <p class="text-center text-gray-400 mb-10 max-w-2xl mx-auto">
        Temukan jawaban atas pertanyaan umum seputar <span class="text-green-400">sistem harga</span> dan <span class="text-blue-400">kontrol lampu</span>.
    </p>

    <!-- FAQ -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8" x-data="{ open: null }">

        <!-- Sistem Harga -->
        <div class="bg-[#1e1e1e] p-6 rounded-xl shadow-lg border border-gray-700">
            <div class="flex items-center mb-4">
                <i class="fa-solid fa-money-bill-wave text-green-400 text-2xl mr-3"></i>
                <h2 class="text-2xl font-semibold">Sistem Harga Penyewaan</h2>
            </div>
            <div class="divide-y divide-gray-700">
                <!-- Item 1 -->
                <div class="py-3">
                    <button @click="open === 1 ? open = null : open = 1"
                        class="w-full flex justify-between items-center font-semibold hover:text-green-400 transition">
                        Bagaimana harga sewa dihitung?
                        <i :class="open === 1 ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"></i>
                    </button>
                    <div x-show="open === 1" x-collapse class="mt-2 text-gray-300">
                        Harga sewa meja dihitung berdasarkan <span class="text-yellow-400 font-semibold">waktu pemesanan dimulai </span>. Jika Anda memulai pemesanan sebelum pukul <span class="text-yellow-400 font-semibold"> 17.00 </span>, maka sistem masih akan menggunakan tarif <span class="text-yellow-400 font-semibold">Harga Siang</span> untuk seluruh durasi pemesanan, <span class="text-yellow-400 font-semibold"> meskipun selesainya pada malam hari  </span>.
                        bergitu pun sebaliknya harga akan di hitung berdasarkan waktu pemesanan dimulai
                    </div>
                </div>
                <!-- Item 2 -->
                <div class="py-3">
                    <button @click="open === 2 ? open = null : open = 2"
                        class="w-full flex justify-between items-center font-semibold hover:text-green-400 transition">
                        Apa saja kategori harganya?
                        <i :class="open === 2 ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"></i>
                    </button>
                    <div x-show="open === 2" x-collapse class="mt-2 text-gray-300">
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="text-yellow-400 font-semibold">Harga Siang:</span> hingga pukul 16.59 WIB.</li>
                            <li><span class="text-blue-400 font-semibold">Harga Malam:</span> mulai pukul 17.00 WIB sampai tutup.</li>
                        </ul>
                    </div>
                </div>
                <!-- Item 3 -->
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'bos')
                <div class="py-3">
                    <button @click="open === 3 ? open = null : open = 3"
                        class="w-full flex justify-between items-center font-semibold hover:text-green-400 transition">
                        Harga main sepuasnya berbeda ?
                        <i :class="open === 3 ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"></i>
                    </button>
                    <div x-show="open === 3" x-collapse class="mt-2 text-gray-300">
                        Karena keterbatasan sistem untuk sementara waktu pada pembayaran main sepuasnya ada selisih Rp.200-Rp.500 dengan yang masuk ke rekap nilai
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Kontrol Lampu -->
        <div class="bg-[#1e1e1e] p-6 rounded-xl shadow-lg border border-gray-700">
            <div class="flex items-center mb-4">
                <i class="fa-solid fa-lightbulb text-yellow-400 text-2xl mr-3"></i>
                <h2 class="text-2xl font-semibold">Kontrol Lampu Meja</h2>
            </div>
            <div class="divide-y divide-gray-700">
                <!-- Item 3 -->
                <div class="py-3">
                    <button @click="open === 3 ? open = null : open = 3"
                        class="w-full flex justify-between items-center font-semibold hover:text-yellow-400 transition">
                        Apakah lampu dikontrol secara manual?
                        <i :class="open === 3 ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"></i>
                    </button>
                    <div x-show="open === 3" x-collapse class="mt-2 text-gray-300">
                        Tidak, lampu dikontrol secara <span class="text-yellpw-400 font-semibold">otomatis</span>.  
                        Saat meja aktif, lampu akan otomatis menyala sebagai tanda meja sedang digunakan.
                    </div>
                </div>
                <!-- Item 4 -->
                <div class="py-3">
                    <button @click="open === 4 ? open = null : open = 4"
                        class="w-full flex justify-between items-center font-semibold hover:text-yellow-400 transition">
                        Kenapa lampu tidak menyala padahal sudah dipesan?
                        <i :class="open === 4 ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"></i>
                    </button>
                    <div x-show="open === 4" x-collapse class="mt-2 text-gray-300">
                        Pastikan status pemesanan sudah <span class="text-yellow-400 font-semibold">dimulai</span>.  
                        Jika tetap tidak menyala, kemungkinan ada kendala teknis. Silakan hubungi staf kami.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kontak Bantuan -->
    <div class="mt-12 text-center bg-[#1e1e1e] p-8 rounded-xl shadow-lg border border-gray-700">
        <div class="flex items-center justify-center mb-4">
            <i class="fa-solid fa-headset text-green-400 text-3xl mr-3"></i>
            <h2 class="text-3xl font-semibold">Butuh Bantuan?</h2>
        </div>
        <p class="text-gray-400 mb-6 max-w-xl mx-auto">
            Jika Anda mengalami masalah atau punya pertanyaan lain, hubungi kami:
        </p>
        <a href="https://wa.me/6282120066670" target="_blank"
           class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-full shadow-md transition duration-300">
            <i class="fab fa-whatsapp text-xl mr-2"></i> Hubungi via WhatsApp
        </a>
        <p class="text-sm text-gray-500 mt-4">
            atau telepon langsung ke: <span class="font-semibold text-white">0821-2006-6670</span>
        </p>
    </div>
</div>
@endsection
