<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Billiard</title>

    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <!-- CSS dari Font Awesome CDN -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/sharp-solid.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/sharp-regular.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/sharp-light.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery (Wajib sebelum Bootstrap JS dan DataTables JS) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap 5 JS Bundle (Wajib setelah jQuery jika ada komponen yang tergantung jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables CDN (Wajib setelah jQuery dan Bootstrap JS) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
       <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config for Dark Theme -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* CSS untuk menyembunyikan scrollbar pada sidebar */
        #drawer-sidebar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        /* Untuk Webkit browsers (Chrome, Safari, Opera) */
        #drawer-sidebar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-black text-white font-sans">

    <div class="flex min-h-screen">
        <!-- Sidebar (Flowbite Drawer) -->
        <aside id="drawer-sidebar"
            class="bg-[#121212] text-gray-300 w-64 fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto
                   transform -translate-x-full transition-transform duration-200 ease-in-out md:translate-x-0"
            tabindex="-1" aria-labelledby="drawer-label">

            <!-- Logo -->
            <div class="px-2 py-4 text-white text-2xl font-bold tracking-wide">
                <i class="fa-solid fa-pool-8-ball mr-2"></i> Billiard-APP
            </div>

            <!-- Navigation -->
            <nav class="px-2 space-y-1">
                @php
                    $homeRoute = '#'; // Default fallback
                    if (Auth::check()) {
                        switch (Auth::user()->role) {
                            case 'admin':
                                $homeRoute = route('dashboard.admin');
                                break;
                            case 'bos':
                                $homeRoute = route('dashboard.bos');
                                break;
                            case 'supervisor':
                                $homeRoute = route('dashboard.supervisor');
                                break;
                            case 'kasir':
                                $homeRoute = route('dashboard.kasir');
                                break;
                            default:
                                $homeRoute = route('login'); // If no matching role, redirect to login
                        }
                    } else {
                        $homeRoute = route('login'); // If not authenticated, redirect to login
                    }

                    // Get the current route name for active state check
                    $currentRouteName = Route::currentRouteName();
                @endphp

                {{-- Home Menu - Dynamic based on user role --}}
                <a href="{{ $homeRoute }}" class="flex items-center px-4 py-2 rounded
                    {{ ($currentRouteName == 'dashboard.admin' || $currentRouteName == 'dashboard.bos' || $currentRouteName == 'dashboard.supervisor' || $currentRouteName == 'dashboard.kasir') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa fa-home mr-3"></i> Home
                </a>

                @if(auth()->user()->role == 'admin' )
                {{-- Users Menu (hanya untuk admin) --}}
                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.users*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-users mr-3"></i> Users
                </a>

                {{-- Meja Menu --}}
                <a href="{{ route('admin.mejas.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.mejas.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-table-tennis-paddle-ball mr-3"></i> Meja
                </a>
                @endif
                
               

                {{-- Services Menu --}}
                <a href="{{ route('admin.services.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.services.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-mug-hot mr-3"></i> Services
                </a>
                
                {{-- Kupon Menu --}}
                <a href="{{ route('admin.kupons.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('kupons.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-tags mr-3"></i> Kupon
                </a>

                 
                {{-- Paket Menu --}}
                <a href="{{ route('admin.pakets.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('pakets.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-box-open mr-3"></i> Paket
                </a>

                {{-- Pengaturan Harga Menu --}}
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'bos')
                <a href="{{ route('admin.harga_settings.index') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('admin.harga_settings.*') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-money-bill-wave mr-3"></i> Harga
                </a>
                @endif
                
                {{-- Rekap Transaksi Menu --}}
                {{-- CATATAN: Rute 'transaksi.rekap' belum terdefinisi di routes/web.php. Ganti '#' dengan route() yang sesuai. --}}
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
                    <i class="fa fa-plus mr-3"></i> Create Playlist
                </a>
                {{-- Logout Button --}}
                <a onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center px-4 py-2 rounded hover:bg-[#282828] cursor-pointer">
                    <i class="fa-solid fa-right-from-bracket mr-3 text-red-500"></i> Log Out
                </a>
            </nav>

            <!-- Optional: Scrollable playlist list -->
            <div class="px-6 py-4 text-sm text-gray-500 overflow-y-auto h-[calc(100vh-300px)] hidden md:block">
                <ul class="space-y-1">
                    <li><a href="#" class="hover:text-white">Liked from Radio</a></li>
                    <li><a href="#" class="hover:text-white">Discover Weekly</a></li>
                    <!-- More... -->
                </ul>
            </div>
            
            {{-- Close button for mobile --}}
            <button type="button" data-drawer-hide="drawer-sidebar" aria-controls="drawer-sidebar" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white md:hidden">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>
        </aside>

        <!-- Toggle Button (Mobile) -->
        <div class="md:hidden fixed top-4 left-4 z-50">
            <button id="sidebarToggle" type="button" 
                    data-drawer-target="drawer-sidebar" 
                    data-drawer-toggle="drawer-sidebar" 
                    aria-controls="drawer-sidebar" 
                    class="text-white text-xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
        </div>

        <!-- Header Topbar (User Info) -->
        <div class="fixed top-4 right-4 z-50 hidden md:flex items-center space-x-3 bg-[#121212] text-white px-3 py-1 rounded-full shadow border border-gray-700">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=333&color=fff&size=32" class="w-8 h-8 rounded-full" alt="Avatar">
            <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="hover:text-red-400 text-sm">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </div>


        <!-- Main Content -->
        <div class="flex-1 p-6 overflow-x-auto md:ml-64"> 
            @yield('content')
        </div>
    </div>

    <!-- Flowbite JS (Wajib setelah Tailwind CSS CDN script) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>

    @yield('script')

</body>

</html>