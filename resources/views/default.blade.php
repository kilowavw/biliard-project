<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <!-- DataTables CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind -->
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
    </style>
</head>

<body class="bg-black text-white font-sans">

    <div class="flex min-h-screen">
        <!-- Sidebar Spotify Style -->
        <aside id="sidebar"
            class="bg-[#121212] text-gray-300 w-64 fixed md:relative z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out">

            <!-- Logo -->
            <div class="px-6 py-4 text-white text-2xl font-bold tracking-wide">
                <i class="fa-solid fa-pool-8-ball mr-2"></i> Billiard-APP
            </div>

            <!-- Navigation -->
            <nav class="px-4 space-y-1">
                <a href="#" class="flex items-center px-4 py-2 rounded text-white bg-[#282828]">
                    <i class="fa fa-home mr-3"></i> Home
                </a>

                @if(auth()->user()->role == 'admin' )
                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2 rounded text-white bg-[#282828]">
                    <i class="fa-solid fa-users mr-3"></i> Users
                </a>
                @endif

                <a href="#" class="flex items-center px-4 py-2 rounded hover:bg-[#282828]">
                    <i class="fa fa-search mr-3"></i> Search
                </a>
                <a href="#" class="flex items-center px-4 py-2 rounded hover:bg-[#282828]">
                    <i class="fa fa-book mr-3"></i> Your Library
                </a>
            </nav>

            <!-- Playlist Section -->
            <div class="px-4 mt-6 text-sm text-gray-400">
                System
            </div>
            <nav class="px-4 py-2 space-y-1">
                <a href="#" class="flex items-center px-4 py-2 rounded hover:bg-[#282828]">
                    <i class="fa fa-plus mr-3"></i> Create Playlist
                </a>
                <a onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center px-4 py-2 rounded hover:bg-[#282828]">
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
        </aside>

        <!-- Toggle Button (Mobile) -->
        <div class="md:hidden fixed top-4 left-4 z-50">
            <button id="sidebarToggle" class="text-white text-xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
        </div>

        <!-- Header Topbar -->
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


        <!-- Dummy Main Content -->
        <div class="flex-1 p-6 ">
            @yield('content')
            <!-- Kosong sesuai permintaan -->
        </div>
    </div>

    <!-- Toggle Script -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });

        // Tutup saat klik luar (opsional)
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && window.innerWidth < 768) {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>


    @yield('script')

</body>

</html>