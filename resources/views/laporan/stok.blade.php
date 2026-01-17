@extends('default')

@section('title', 'Laporan Stok Barang/Layanan')

@section('content')

<?php
// Variabel PHP sudah disediakan dari controller:
// $services

$stokHeaders = [
    'ID' => 'id',
    'Nama Barang/Layanan' => 'nama',
    'Kategori' => 'kategori',
    'Harga Satuan' => 'harga',
    'Stok Tersedia' => 'stok',
];

// Siapkan data untuk Alpine.js
$servicesData = $services->toArray();
?>

<div class="container mx-auto p-4 md:p-6 bg-[#121212] text-white min-h-screen">
    <h1 class="text-3xl font-bold mb-6 text-white">Laporan Stok Barang & Layanan</h1>

    {{-- Navigasi Laporan --}}
    <form action="#" method="GET" class="mb-6 flex flex-col md:flex-row items-center gap-4">
        <a href="{{ route('laporan.harian') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.harian') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-day mr-3"></i> Laporan Harian
                </a>
        <a href="{{ route('laporan.bulanan') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.bulanan') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-days mr-3"></i> Laporan Bulanan
                </a>
        <a href="{{ route('laporan.tahunan') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.tahunan') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-days mr-3"></i> Laporan Tahunan
                </a>
        <a href="{{ route('laporan.stok') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.stok') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-boxes-stacked mr-3"></i> Laporan Stok
                </a>
    </form>
    {{-- Akhir Navigasi Laporan --}}

    {{-- Search Input untuk Stok --}}
    <div class="mb-4">
        <input type="text" x-model="searchStok" placeholder="Cari barang/layanan..."
               class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-4">
    </div>

    {{-- Tabel Laporan Stok --}}
    <div x-data="{
        services: {{ json_encode($servicesData) }},
        searchStok: '',
        sortColumnStok: 'id',
        sortDirectionStok: 'asc',

        get filteredServices() {
            return this.services.filter(item => {
                const search = this.searchStok.toLowerCase();
                return item.nama.toLowerCase().includes(search) ||
                       item.kategori.toLowerCase().includes(search);
            }).sort((a, b) => {
                let valA = a[this.sortColumnStok];
                let valB = b[this.sortColumnStok];

                if (valA === null || valA === undefined) valA = '';
                if (valB === null || valB === undefined) valB = '';

                if (typeof valA === 'string') {
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }

                if (valA < valB) {
                    return this.sortDirectionStok === 'asc' ? -1 : 1;
                }
                if (valA > valB) {
                    return this.sortDirectionStok === 'asc' ? 1 : -1;
                }
                return 0;
            });
        },

        sortByStok(column) {
            if (this.sortColumnStok === column) {
                this.sortDirectionStok = this.sortDirectionStok === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumnStok = column;
                this.sortDirectionStok = 'asc';
            }
        },

        getSortIconStok(column) {
            if (this.sortColumnStok === column) {
                return this.sortDirectionStok === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
            }
            return 'fas fa-sort'; // Default sort icon
        }
    }" class="relative overflow-x-auto shadow-md sm:rounded-lg mb-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-700 text-white">
                <tr>
                    @foreach($stokHeaders as $header => $column)
                        <th scope="col" class="px-6 py-3 cursor-pointer hover:bg-gray-600"
                            @click="sortByStok('{{ $column }}')">
                            {{ $header }}
                            <i class="ml-1" :class="getSortIconStok('{{ $column }}')"></i>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <template x-if="filteredServices.length === 0">
                    <tr>
                        <td colspan="{{ count($stokHeaders) }}" class="px-6 py-4 text-center text-gray-400">Tidak ada data stok ditemukan.</td>
                    </tr>
                </template>
                <template x-for="item in filteredServices" :key="item.id">
                    <tr class="bg-[#1e1e1e] border-b border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                        <td class="px-6 py-4" x-text="item.id"></td>
                        <td class="px-6 py-4" x-text="item.nama"></td>
                        <td class="px-6 py-4" x-text="item.kategori"></td>
                        <td class="px-6 py-4" x-text="fmtRp(item.harga)"></td>
                        <td class="px-6 py-4" x-text="item.stok"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

</div>
@endsection

@section('script')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
{{-- Note: Chart.js dan ApexCharts tidak diperlukan untuk laporan stok sederhana,
     tapi jika ingin menambahkan visualisasi stok, bisa diaktifkan lagi. --}}
<script>
    const fmtRp = (amount) => {
        if (amount === null || amount === undefined) return 'Rp 0';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    };

    // Tidak perlu formatDateTime di sini karena tidak ada kolom tanggal/waktu yang ditampilkan
    // Kecuali jika Anda menambahkannya nanti.

    // Tidak ada Chart.js atau ApexCharts untuk laporan stok ini,
    // Jadi bagian script Chart.js dan ApexCharts di sini bisa kosong atau dihapus
    // jika Anda tidak berencana menambahkannya.
</script>
@endsection