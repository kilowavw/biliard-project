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
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.4.1/datepicker.min.js"></script>

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
                <i class="fa-solid fa-pool-8-ball mr-2"></i> Billiard-APP
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
                        case 'admin': $homeRoute = route('dashboard.kasir'); break;
                        case 'bos': $homeRoute = route('dashboard.bos'); break;
                        case 'supervisor': $homeRoute = route('dashboard.kasir'); break;
                        case 'kasir': $homeRoute = route('dashboard.kasir'); break;
                        default: $homeRoute = route('login');
                    }
                } else { $homeRoute = route('login'); }

                $currentRouteName = Route::currentRouteName();
            @endphp

            <a href="{{ $homeRoute }}" class="flex items-center px-4 py-2 rounded
                {{ in_array($currentRouteName, ['dashboard.admin','dashboard.bos','dashboard.supervisor','dashboard.kasir']) ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa fa-home mr-3"></i> Home
            </a>

            @if(auth()->user()->role == 'admin')
            <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2 rounded
                {{ request()->routeIs('admin.users*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa-solid fa-users mr-3"></i> Users
            </a>
            <a href="{{ route('admin.mejas.index') }}" class="flex items-center px-4 py-2 rounded
                {{ request()->routeIs('admin.mejas.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa-solid fa-table-tennis-paddle-ball mr-3"></i> Meja
            </a>
            <a href="{{ route('admin.kupons.index') }}" class="flex items-center px-4 py-2 rounded
                {{ request()->routeIs('admin.kupons.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa-solid fa-tags mr-3"></i> Kupon
            </a>
            <a href="{{ route('members.index') }}" class="flex items-center px-4 py-2 rounded
                {{ request()->routeIs('members.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa-solid fa-user-group-crown mr-3"></i> Members
            </a>
            <a href="{{ route('admin.services.index') }}" class="flex items-center px-4 py-2 rounded
                {{ request()->routeIs('admin.services.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa-solid fa-mug-hot mr-3"></i> Layanan
            </a>
            <a href="{{ route('admin.pakets.index') }}" class="flex items-center px-4 py-2 rounded
                {{ request()->routeIs('admin.pakets.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa-solid fa-box-open mr-3"></i> Paket
            </a>
            @endif

            @if(auth()->user()->role == 'kasir')
            <a href="{{ route('kasir.serviceOrderIndex') }}" class="flex items-center px-4 py-2 rounded
                {{ request()->routeIs('kasir.serviceOrderIndex') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa-solid fa-mug-hot mr-3"></i> Layanan
            </a>
            @endif

            @if(auth()->user()->role == 'admin' || auth()->user()->role == 'bos')
            <a href="{{ route('admin.harga_settings.index') }}" class="flex items-center px-4 py-2 rounded
                {{ request()->routeIs('admin.harga_settings.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa-solid fa-money-bill-wave mr-3"></i> Harga
            </a>
            @endif

            <a href="{{ route('laporan.harian') }}" class="flex items-center px-4 py-2 rounded
                {{ request()->routeIs('laporan.harian') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                <i class="fa-solid fa-receipt mr-3"></i> Rekap Transaksi
            </a>
        </nav>

        <!-- System Section -->
            <div class="px-2 mt-6 text-sm text-gray-400">
                System
            </div>
            <nav class="px-2 py-2 space-y-1">
                {{-- Ini masih dummy, mungkin akan dihapus atau diganti --}}
                <a href="#" class="flex items-center px-4 py-2 rounded hover:bg-[#282828]">
                    <i class="fa fa-solid fa-lightbulb mr-3"></i> Lamp Control
                </a>
                <a onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
               class="flex items-center px-4 py-2 rounded hover:bg-[#282828] cursor-pointer">
                <i class="fa-solid fa-right-from-bracket mr-3 text-red-500"></i> Log Out
            </a>
            </nav>

            <!-- Optional: Scrollable playlist list -->
            <div class="px-6 py-4 text-sm text-gray-500 hidden md:block">
                <ul class="space-y-1">
                    <li><a href="#" class="hover:text-white">Made by Rakasnsh</a></li>
                </ul>
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
    @yield('script')

</body>
</html>
