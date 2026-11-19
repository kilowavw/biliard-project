<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Billiard</title>

    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">

    <!-- Chart.js & Flowbite Datepicker -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">
    
    <!-- Alphine -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Chart.js untuk Doughnut Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- ApexCharts untuk Bar Charts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        #drawer-sidebar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        #drawer-sidebar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-black text-white font-sans">

    <!-- SIDEBAR -->
    <aside id="drawer-sidebar"
        class="bg-[#121212] text-gray-300 w-64 fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto
               transform -translate-x-full transition-transform duration-200 ease-in-out lg:translate-x-0"
        tabindex="-1"
        data-drawer-backdrop="false">

        <!-- Logo + Close -->
        <div class="flex justify-between items-center px-2 py-4">
            <div class="text-white text-2xl font-bold tracking-wide">
                <i class="fa-solid fa-circle mr-2"></i> Billiard-APP
            </div>
            <!-- Close SELALU ada (mobile + tablet) -->
            <button type="button" data-drawer-hide="drawer-sidebar" aria-controls="drawer-sidebar"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg 
                       text-sm w-8 h-8 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white lg:hidden">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close menu</span>
            </button>
        </div>

        <!-- NAVIGATION -->
        <nav class="px-2 space-y-1">
            @php
                $homeRoute = '#';
                if (Auth::check()) {
                    switch (Auth::user()->role) {
                        case 'admin': $homeRoute = route('dashboard.kasir'); break; // Sesuaikan dengan route dashboard admin
                        case 'bos': $homeRoute = route('dashboard.bos'); break;
                        case 'supervisor': $homeRoute = route('dashboard.kasir'); break;
                        case 'kasir': $homeRoute = route('dashboard.kasir'); break;
                        default: $homeRoute = route('login');
                    }
                } else { $homeRoute = route('login'); }

                $currentRouteName = Route::currentRouteName();
            @endphp

            <nav>
                <a href="{{ $homeRoute }}" class="flex items-center px-4 py-2 rounded
                    {{ in_array($currentRouteName, ['dashboard.admin','dashboard.bos','dashboard.supervisor','dashboard.kasir']) ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa fa-home w-5 text-center mr-3"></i> Home
                </a>
            
                @if(auth()->user()->role == 'admin')
                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.users*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-users w-5 text-center mr-3"></i> Users
                </a>
            
                <a href="{{ route('admin.mejas.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.mejas.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-table-tennis-paddle-ball w-5 text-center mr-3"></i> Meja
                </a>
            
                <a href="{{ route('admin.kupons.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.kupons.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-tags w-5 text-center mr-3"></i> Kupon
                </a>
            
                <a href="{{ route('members.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('members.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-users w-5 text-center mr-3"></i> Members
                </a>
            
                <a href="{{ route('admin.services.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.services.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-mug-hot w-5 text-center mr-3"></i> Layanan
                </a>
            
                <a href="{{ route('admin.pakets.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.pakets.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-box-open w-5 text-center mr-3"></i> Paket
                </a>
            
                <a href="https://cbcbilliard.com/compro" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-building w-5 text-center mr-3"></i> Edit Compro
                </a>
                @endif
            
                @if(auth()->user()->role == 'kasir')
                <a href="{{ route('kasir.serviceOrderIndex') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('kasir.serviceOrderIndex') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-mug-hot w-5 text-center mr-3"></i> Layanan
                </a>
                @endif
            
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'bos')
                <a href="{{ route('admin.harga_settings.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.harga_settings.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-money-bill-wave w-5 text-center mr-3"></i> Harga
                </a>
                @endif
            
                <a href="{{ route('laporan.harian') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.harian') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-receipt w-5 text-center mr-3"></i> Rekap Transaksi
                </a>
            </nav>

        <!-- System Section -->
            <div class="px-2 mt-6 text-sm text-gray-400">
                System
            </div>
            <nav class="px-2 py-2 space-y-1">
                {{-- Ini masih dummy, mungkin akan dihapus atau diganti --}}
                 @if(auth()->user()->role == 'admin' || auth()->user()->role == 'bos')
                <a href="{{ route('lampu.index') }}" class="flex items-center px-4 py-2 rounded hover:bg-[#282828]">
                    <i class="fa fa-solid fa-lightbulb mr-3"></i> Lamp Control
                </a>
                @endif
                <a href="{{ route('panduan') }}" class="flex items-center px-4 py-2 rounded hover:bg-[#282828]">
                    <i class="fa fa-solid fa-circle-info mr-3"></i> Panduan
                </a>
                <a onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
               class="flex items-center px-4 py-2 rounded hover:bg-[#282828] cursor-pointer">
                <i class="fa-solid fa-right-from-bracket mr-3 text-red-500"></i> Log Out
            </a>
            </nav>

            <!-- Optional: Scrollable playlist list -->
             <div class="px-2 mt-6 text-sm space-y-1 text-gray-400">
                Made by
            </div>
    </aside>

    <!-- NAVBAR / TOPBAR -->
    <nav class="fixed top-0 left-0 right-0 h-14 bg-[#121212] border-b border-gray-700 z-30 
                flex items-center justify-between px-4 lg:hidden">
        <!-- Toggle (mobile + tablet) -->
        <button id="sidebarToggle" type="button"
            data-drawer-target="drawer-sidebar" data-drawer-toggle="drawer-sidebar" aria-controls="drawer-sidebar"
            class="text-white text-xl focus:outline-none">
            <i class="fa fa-bars"></i>
        </button>

        <!-- User info (mobile/tablet) -->
        <div class="flex items-center space-x-3">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=333&color=fff&size=32"
                class="w-8 h-8 rounded-full" alt="Avatar">
            <span class="hidden sm:inline text-sm font-medium">{{ Auth::user()->name }}</span>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="text-red-400 text-sm">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </div>
    </nav>

    <!-- USER INFO (DESKTOP SAJA) -->
    <div class="hidden lg:flex fixed top-4 right-4 z-40 items-center space-x-3 bg-[#121212] 
                text-white px-3 py-1 rounded-full shadow border border-gray-700">
        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=333&color=fff&size=32"
            class="w-8 h-8 rounded-full" alt="Avatar">
        <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
        <form id="logout-form-desktop" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        <button onclick="event.preventDefault(); document.getElementById('logout-form-desktop').submit();"
            class="hover:text-red-400 text-sm">
            <i class="fa-solid fa-right-from-bracket"></i>
        </button>
    </div>


    <!-- MAIN CONTENT -->
    <div class="flex-1 pt-16 p-6 overflow-x-auto lg:ml-64">
        @yield('content')
    </div>

    <!-- Flowbite -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        let currentPriceType = '';

        function checkAndUpdatePrice() {
            const now = new Date();
            const hour = now.getHours();

            let newPriceType = '';

            // 09.00-16.59 (Siang)
            if (hour >= 9 && hour < 17) {
                newPriceType = 'siang';
            } 
            // 17.00-24.00 (Malam) atau 00.00-08.59 (juga Malam)
            else { 
                newPriceType = 'malam';
            }

            if (newPriceType !== currentPriceType) {
                console.log(`Waktu berubah. Mengirim permintaan untuk mengatur harga ke ${newPriceType}.`);
                sendPriceUpdateRequest(newPriceType);
                // currentPriceType akan diperbarui di dalam sendPriceUpdateRequest jika sukses.
                // Ini mencegah pengiriman berulang jika ada masalah dengan update server.
            }
        }

        async function sendPriceUpdateRequest(priceType) {
            try {
                const response = await fetch("{{ route('api.update_dynamic_price') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest' // Tambahkan header ini
                    },
                    body: JSON.stringify({ price_type: priceType })
                });

                const data = await response.json();

                if (response.ok) {
                    console.log('Pembaruan harga berhasil:', data.message, 'Harga baru:', data.new_price);
                    currentPriceType = priceType; // Update status hanya jika sukses
                    // Optional: Tampilkan SweetAlert atau notifikasi lain
                    Swal.fire('Berhasil!', `Harga telah diatur ke ${data.new_price} untuk periode ${priceType}.`, 'success');
                } else {
                    console.error('Pembaruan harga gagal:', data.message);
                    Swal.fire('Gagal!', `Gagal memperbarui harga: ${data.message}`, 'error'); // Tampilkan error ke pengguna
                }
            } catch (error) {
                console.error('Terjadi kesalahan saat mengirim permintaan:', error);
                Swal.fire('Error!', 'Terjadi kesalahan saat mengirim permintaan pembaruan harga.', 'error'); // Tampilkan error
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Panggil sekali saat dimuat untuk mengatur harga awal dan currentPriceType
            const now = new Date();
            const hour = now.getHours();
            if (hour >= 9 && hour < 17) {
                currentPriceType = 'siang';
            } else {
                currentPriceType = 'malam';
            }
            // Lalu jalankan pengecekan untuk memastikan update
            checkAndUpdatePrice(); 
            
            setInterval(checkAndUpdatePrice, 60 * 1000); // Periksa setiap menit
        });
    </script>

    @yield('script')

</body>
</html>
