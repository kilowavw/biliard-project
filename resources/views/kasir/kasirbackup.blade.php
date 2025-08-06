@extends('default')

@section('title', 'Dashboard kasir')

@section('content')
<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .font-mono {
        font-family: 'Roboto Mono', monospace;
    }

    /* Style untuk menyembunyikan panah di input number */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<body class="bg-gray-900 text-white">

    <!-- 
      State management utama dengan Alpine.js:
      - isModalOpen: Mengontrol modal tambah layanan.
      - selectedTableName: Menyimpan nama meja yang diklik.
      - services: Array untuk menampung layanan yang akan ditambah di form dinamis.
    -->
    <div x-data="{ 
            isModalOpen: false, 
            selectedTableName: '',
            services: [ { name: '', quantity: 1 } ] 
         }"
        @keydown.escape.window="isModalOpen = false"
        class="relative min-h-screen">

        <div class="container mx-auto p-4 sm:p-6 md:p-8">

            <!-- LIST MEJA TERPAKAI -->
            <h2 class="text-2xl font-bold mb-6 tracking-wide text-gray-300">LIST Meja Terpakai</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 mb-12">

                @for ($i = 1; $i <= 10; $i++)
                    <!-- Card Meja Terpakai -->
                    <div class="bg-gray-800 rounded-2xl p-2 shadow-lg border-8 border-yellow-900 transform hover:-translate-y-1 transition-transform duration-300">
                        <button
                            @click="isModalOpen = true; selectedTableName = 'Meja #{{$i}}'; services = [{ name: '', quantity: 1 }]"
                            class="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center bg-black/40 hover:bg-green-500 border-2 border-gray-500 hover:border-green-400 rounded-full transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                        <div class="bg-green-800 rounded-lg p-4 text-center relative h-full flex flex-col justify-between">
                            <!-- Pool Table Pockets -->
                            <div class="absolute top-0 left-0 w-5 h-5 bg-black rounded-full -m-2"></div>
                            <div class="absolute top-0 right-0 w-5 h-5 bg-black rounded-full -m-2"></div>
                            <div class="absolute bottom-0 left-0 w-5 h-5 bg-black rounded-full -m-2"></div>
                            <div class="absolute bottom-0 right-0 w-5 h-5 bg-black rounded-full -m-2"></div>
                            <div class="absolute top-1/2 left-0 w-5 h-5 bg-black rounded-full -translate-y-1/2 -ml-2"></div>
                            <div class="absolute top-1/2 right-0 w-5 h-5 bg-black rounded-full -translate-y-1/2 -mr-2"></div>

                            <div>
                                <h3 class="font-bold text-xl mb-1">Meja #{{$i}}</h3>
                                <p class="text-sm text-gray-400 mb-4">Waktu</p>
                            </div>
                            <div class="flex flex-col space-y-3">
                                <button class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">Mulai</button>
                                <button class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">Selesai</button>
                            </div>
                        </div>
                    </div>
                    @endfor

            </div>

            <!-- LIST MEJA KOSONG -->
            <h2 class="text-2xl font-bold mb-6 tracking-wide text-gray-300">LIST Meja Kosong</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                @for ($i = 11; $i <= 15; $i++)
                    <!-- Card Meja Kosong -->
                    <div class="relative bg-gray-800/50 rounded-2xl p-2 shadow-lg border-2 border-gray-700 transform hover:-translate-y-1 transition-transform duration-300">
                        <button
                            @click="isModalOpen = true; selectedTableName = 'Meja #{{$i}}'; services = [{ name: '', quantity: 1 }]"
                            class="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center bg-black/40 hover:bg-green-500 border-2 border-gray-500 hover:border-green-400 rounded-full transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                        <div class="bg-emerald-900 rounded-lg p-3 text-center h-full flex flex-col justify-between aspect-[3/4]">
                            <div class="flex-grow flex flex-col justify-center items-center">
                                <h3 class="font-bold text-xl mb-2 text-white">Meja #{{$i}}</h3>
                                <p class="text-gray-400">Tersedia</p>
                            </div>
                            <div class="mt-4">
                                <button class="w-full bg-green-600 hover:bg-green-500 text-white font-semibold py-2.5 px-4 rounded-lg transition duration-300 shadow-md">Mulai</button>
                            </div>
                        </div>
                    </div>
                    @endfor
            </div>
        </div>

        <!-- Modal untuk Tambah Layanan (Service) -->
        <div x-show="isModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="isModalOpen = false" style="display: none;">

            <div x-show="isModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg p-6 border border-gray-700">

                <h3 class="text-2xl font-bold text-white mb-4">
                    Tambah Layanan untuk <span x-text="selectedTableName" class="text-green-400"></span>
                </h3>

                <form action="#" method="POST">
                    <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2">
                        <!-- Template untuk form dinamis -->
                        <template x-for="(service, index) in services" :key="index">
                            <div class="flex items-center space-x-2 p-2 bg-gray-700/50 rounded-lg">
                                <!-- Dropdown Layanan -->
                                <select x-model="service.name" class="w-full bg-gray-700 border-gray-600 text-white rounded-lg p-3 focus:ring-2 focus:ring-green-500 transition">
                                    <option value="" disabled>-- Pilih Layanan --</option>
                                    <option value="F&B">Makanan & Minuman</option>
                                    <option value="Extend">Perpanjang Waktu (30 Menit)</option>
                                    <option value="Clove Cigarette">Rokok Kretek</option>
                                    <option value="White Cigarette">Rokok Putih</option>
                                </select>
                                <!-- Input Jumlah -->
                                <input type="number" x-model.number="service.quantity" min="1" class="w-20 bg-gray-700 border-gray-600 text-white text-center rounded-lg p-3 focus:ring-2 focus:ring-green-500 transition">
                                <!-- Tombol Hapus Baris -->
                                <button type="button" @click="services.splice(index, 1)" x-show="services.length > 1" class="p-3 bg-red-600 hover:bg-red-500 rounded-lg transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Tombol untuk menambah baris form baru -->
                    <button type="button" @click="services.push({ name: '', quantity: 1 })" class="mt-4 w-full text-left py-2 px-3 text-green-400 hover:bg-gray-700 rounded-lg transition">
                        + Tambah Layanan Lain
                    </button>

                    <!-- Tombol Aksi Modal -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" @click="isModalOpen = false" class="py-2 px-5 bg-gray-600 hover:bg-gray-500 text-white rounded-lg transition">Batal</button>
                        <button type="submit" class="py-2 px-5 bg-green-600 hover:bg-green-500 text-white font-bold rounded-lg transition">Simpan Layanan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

@endsection