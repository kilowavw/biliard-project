@extends('default')

@section('title', 'Laporan Tahunan')

@section('content')

<?php
// Variabel PHP sudah disediakan dari controller:
// $penyewaansPaginated, $serviceTransactionsPaginated, $totalPendapatanNett, $paymentMethodDistribution, $pendapatanPerMeja
// $topKasir, $topPemandu, $chartLabelsMonthly, $chartDataMonthly, $year, $years
// $TOP_PERFORMERS_LIMIT (dari konstanta controller)
// NEW: $totalPendapatanPenyewaanMurni, $totalPendapatanLayananGabungan, $qrisTotal, $cashTotal

$penyewaanHeaders = [
    'ID' => 'id',
    'Nama Penyewa' => 'nama_penyewa',
    'Meja' => 'meja.nama_meja',
    'Paket' => 'paket.nama_paket',
    'Durasi (Jam)' => 'durasi_jam',
    'Harga/Jam' => 'harga_per_jam',
    'Total Bayar' => 'total_bayar',
    'Detail Service' => null,
    'Total Service' => 'total_service',
    'Diskon' => 'diskon_persen',
    'Status' => 'status',
    'Metode Bayar' => 'is_qris',
    'Kasir' => 'kasir.name',
    'Pemandu' => 'pemandu.name',
    'Waktu Mulai' => 'waktu_mulai',
    'Waktu Selesai' => 'waktu_selesai',
];

$serviceTransactionHeaders = [
    'ID' => 'id',
    'Nama Pelanggan' => 'customer_name',
    'Detail Layanan' => null,
    'Diskon' => 'diskon_persen',
    'Total Bayar' => 'total_bayar',
    'Waktu Transaksi' => 'transaction_time',
    'Status Pembayaran' => 'payment_status',
    'Metode Bayar' => 'payment_method',
    'Kasir' => 'kasir.name',
];

// Siapkan data untuk Alpine.js. Pastikan ini adalah array of objects.
$penyewaansData = $penyewaansPaginated->getCollection()->map(function($item) {
    $item->meja_nama_meja = $item->meja->nama_meja ?? null;
    $item->paket_nama_paket = $item->paket->nama_paket ?? null;
    $item->kasir_name = $item->kasir->name ?? null;
    $item->pemandu_name = $item->pemandu->name ?? null;
    $item->service_detail_parsed = is_string($item->service_detail) ? json_decode($item->service_detail, true) : ($item->service_detail ?? []);
    return $item;
})->toArray();

$serviceTransactionsData = $serviceTransactionsPaginated->getCollection()->map(function($item) {
    $item->kasir_name = $item->kasir->name ?? null;
    $item->service_detail_parsed = is_string($item->service_detail) ? json_decode($item->service_detail, true) : ($item->service_detail ?? []);
    return $item;
})->toArray();
?>

<div class="container mx-auto p-4 md:p-6 bg-[#121212] text-white min-h-screen">
    <h1 class="text-3xl font-bold mb-6 text-white">Laporan Tahunan Penyewaan & Layanan</h1>

    <form id="year-form" action="{{ route('laporan.tahunan') }}" method="GET" class="mb-6 flex flex-col md:flex-row items-center gap-4">
    {{-- Link untuk laporan harian --}}
        <a href="{{ route('laporan.harian') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.harian') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-day mr-3"></i> Laporan Harian
                </a>

        {{-- Link untuk laporan bulanan --}}
        <a href="{{ route('laporan.bulanan') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.bulanan') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-days mr-3"></i> Laporan Bulanan
                </a>
        {{-- Link untuk laporan tahunan --}}
        <a href="{{ route('laporan.tahunan') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.tahunan') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-days mr-3"></i> Laporan Tahunan
                </a>

        {{-- Link untuk laporan Stok --}}
        <a href="{{ route('laporan.stok') }}" class="flex items-center px-4 py-2 rounded 
                     {{ request()->routeIs('laporan.stok') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                     <i class="fa-solid fa-boxes-stacked mr-3"></i> Laporan Stok
                </a>
                
        <label for="report-year" class="block text-sm font-medium text-white">Pilih Tahun:</label>
        <select id="report-year" name="year"
                class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-auto p-2.5">
            @foreach($years as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <button type="submit" class="hidden">Submit</button>
    </form>

    {{-- Total Pendapatan & Ringkasan Card --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {{-- Total Pendapatan Nett --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md flex items-center justify-between border border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-400">Total Pendapatan (Nett) Tahun {{ $year }}</p>
                <p class="text-3xl font-bold text-white mt-1">
                    Rp {{ number_format($totalPendapatanNett, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-3 bg-blue-900 rounded-full">
                <i class="fa-solid fa-rupiah-sign text-blue-300 text-2xl"></i>
            </div>
        </div>
    
        {{-- Omzet Billiard Murni --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md flex items-center justify-between border border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-400">Omzet Meja Billiard Murni</p>
                <p class="text-3xl font-bold text-white mt-1">
                    Rp {{ number_format($totalPendapatanPenyewaanMurni, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-3 bg-green-900 rounded-full">
                <i class="fa-solid fa-table-tennis-paddle-ball text-green-300 text-2xl"></i>
            </div>
        </div>
    
        {{-- Omzet Layanan (Gabungan) --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md flex items-center justify-between border border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-400">Omzet Layanan & Produk</p>
                <p class="text-3xl font-bold text-white mt-1">
                    Rp {{ number_format($totalPendapatanLayananGabungan, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-3 bg-yellow-900 rounded-full">
                <i class="fa-solid fa-burger text-yellow-300 text-2xl"></i>
            </div>
        </div>
    
        {{-- Total QRIS --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md flex items-center justify-between border border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-400">Total Pembayaran QRIS</p>
                <p class="text-3xl font-bold text-white mt-1">
                    Rp {{ number_format($qrisTotal, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-3 bg-purple-900 rounded-full">
                <i class="fa-solid fa-qrcode text-purple-300 text-2xl"></i>
            </div>
        </div>
        
        {{-- Total Cash --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md flex items-center justify-between border border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-400">Total Pembayaran Cash</p>
                <p class="text-3xl font-bold text-white mt-1">
                    Rp {{ number_format($cashTotal, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-3 bg-red-900 rounded-full">
                <i class="fa-solid fa-money-bill-wave text-red-300 text-2xl"></i>
            </div>
        </div>
    </div>
    

    {{-- Grafik Tren Pendapatan Bulanan (ApexCharts Bar) --}}
    <div class="w-full bg-[#1e1e1e] rounded-lg shadow dark:bg-gray-800 p-4 md:p-6 mb-8 border border-gray-700">
        <div class="flex justify-between mb-3">
            <div class="flex justify-center items-center">
                <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white pe-1">Tren Pendapatan Tahun {{ $year }} (Per Bulan)</h5>
            </div>
        </div>
        <div id="yearly-monthly-revenue-chart"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Grafik Distribusi Pendapatan (Chart.js Doughnut) --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md border border-gray-700">
            <h2 class="text-xl font-semibold mb-4 text-white">Distribusi Pendapatan Tahun {{ $year }}</h2>
            <div class="chart-container w-full h-80 flex items-center justify-center">
                <canvas id="paymentMethodChart"></canvas>
            </div>
            <p class="text-sm text-gray-400 mt-4 italic">
                *Data distribusi Cash & QRIS diambil dari kedua jenis transaksi.
            </p>
        </div>

        {{-- Grafik Kinerja Kasir (ApexCharts Horizontal Bar) --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md border border-gray-700">
            <h2 class="text-xl font-semibold mb-4 text-white">Kinerja Kasir Tahun {{ $year }}</h2>
            <div id="kasir-chart"></div>
            <p class="text-sm text-gray-400 mt-4 italic">
                *Top {{ $TOP_PERFORMERS_LIMIT }} kasir berdasarkan total pendapatan.
            </p>
        </div>
    </div>

    {{-- Daftar Card Pendapatan Per Kasir --}}
    <h2 class="text-xl font-semibold mb-4 text-white">Pendapatan Per Kasir Tahun {{ $year }}</h2>
    <div id="pendapatan-kasir-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
        @forelse ($topKasir as $name => $revenue)
            <div class="bg-[#1e1e1e] p-4 rounded-lg shadow-md border border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                <h3 class="text-lg font-semibold text-white">{{ $name }}</h3>
                <p class="text-xl font-bold text-green-400 mt-2">Rp {{ number_format($revenue, 0, ',', '.') }}</p>
            </div>
        @empty
            <p class="text-gray-400 col-span-full">Tidak ada pendapatan tercatat untuk kasir pada periode ini.</p>
        @endforelse
    </div>

    {{-- Grafik Kinerja Pemandu (ApexCharts Horizontal Bar) --}}
    <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md border border-gray-700 mb-8">
        <h2 class="text-xl font-semibold mb-4 text-white">Kinerja Pemandu Tahun {{ $year }}</h2>
        <div id="pemandu-chart"></div>
        <p class="text-sm text-gray-400 mt-4 italic">
            *Top {{ $TOP_PERFORMERS_LIMIT }} pemandu berdasarkan jumlah meja yang dilayani.
        </p>
    </div>

    {{-- Daftar Card Jumlah Meja Per Pemandu --}}
    <h2 class="text-xl font-semibold mb-4 text-white">Jumlah Meja Per Pemandu Tahun {{ $year }}</h2>
    <div id="pendapatan-pemandu-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
        @forelse ($topPemandu as $name => $count)
            <div class="bg-[#1e1e1e] p-4 rounded-lg shadow-md border border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                <h3 class="text-lg font-semibold text-white">{{ $name }}</h3>
                <p class="text-xl font-bold text-indigo-400 mt-2">{{ number_format($count, 0, ',', '.') }} Meja</p>
            </div>
        @empty
            <p class="text-gray-400 col-span-full">Tidak ada meja tercatat untuk pemandu pada periode ini.</p>
        @endforelse
    </div>

    <h2 class="text-xl font-semibold mb-4 text-white">Pendapatan Per Meja Tahun {{ $year }}</h2>
    <div id="pendapatan-meja-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
        @forelse ($pendapatanPerMeja as $meja)
            <div class="bg-[#1e1e1e] p-4 rounded-lg shadow-md border border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                <h3 class="text-lg font-semibold text-white">{{ $meja['nama_meja'] }}</h3>
                <p class="text-xl font-bold text-green-400 mt-2">Rp {{ number_format($meja['total_pendapatan'], 0, ',', '.') }}</p>
            </div>
        @empty
            <p class="text-gray-400 col-span-full">Tidak ada pendapatan tercatat untuk meja pada tahun ini.</p>
        @endforelse
    </div>

    {{-- Detail Transaksi Penyewaan --}}
    <h2 class="text-xl font-semibold mb-4 text-white mt-8">Detail Transaksi Penyewaan Tahun {{ $year }}</h2>
    <div class="mb-4">
        <input type="text" x-model="searchPenyewaan" placeholder="Cari penyewaan..."
               class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-4">
    </div>

    <div x-data="{
        penyewaans: {{ json_encode($penyewaansData) }},
        searchPenyewaan: '',
        sortColumnPenyewaan: 'id',
        sortDirectionPenyewaan: 'asc',

        get filteredPenyewaans() {
            return this.penyewaans.filter(item => {
                const search = this.searchPenyewaan.toLowerCase();
                return item.nama_penyewa.toLowerCase().includes(search) ||
                       (item.meja_nama_meja && item.meja_nama_meja.toLowerCase().includes(search)) ||
                       (item.paket_nama_paket && item.paket_nama_paket.toLowerCase().includes(search)) ||
                       item.status.toLowerCase().includes(search) ||
                       (item.kasir_name && item.kasir_name.toLowerCase().includes(search));
            }).sort((a, b) => {
                let valA, valB;
                let column = this.sortColumnPenyewaan;

                if (column === 'meja.nama_meja') {
                    valA = a.meja_nama_meja;
                    valB = b.meja_nama_meja;
                } else if (column === 'paket.nama_paket') {
                    valA = a.paket_nama_paket;
                    valB = b.paket_nama_paket;
                } else if (column === 'kasir.name') {
                    valA = a.kasir_name;
                    valB = b.kasir_name;
                } else if (column === 'pemandu.name') {
                    valA = a.pemandu_name;
                    valB = b.pemandu_name;
                } else {
                    valA = a[column];
                    valB = b[column];
                }

                if (valA === null || valA === undefined) valA = '';
                if (valB === null || valB === undefined) valB = '';

                if (typeof valA === 'string' || typeof valA === 'number') {
                    if (this.sortDirectionPenyewaan === 'asc') {
                        return (valA > valB) ? 1 : ((valA < valB) ? -1 : 0);
                    } else {
                        return (valA < valB) ? 1 : ((valA > valB) ? -1 : 0);
                    }
                }
                return 0;
            });
        },

        sortByPenyewaan(column) {
            if (this.sortColumnPenyewaan === column) {
                this.sortDirectionPenyewaan = this.sortDirectionPenyewaan === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumnPenyewaan = column;
                this.sortDirectionPenyewaan = 'asc';
            }
        },

        getSortIconPenyewaan(column) {
            if (column === null) return '';
            if (this.sortColumnPenyewaan === column) {
                return this.sortDirectionPenyewaan === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
            }
            return 'fas fa-sort';
        }
    }" class="relative overflow-x-auto shadow-md sm:rounded-lg mb-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-700 text-white">
                <tr>
                    @foreach($penyewaanHeaders as $header => $column)
                        <th scope="col" class="px-6 py-3 @if($column) cursor-pointer hover:bg-gray-600 @endif"
                            @if($column) @click="sortByPenyewaan('{{ $column }}')" @endif>
                            {{ $header }}
                            @if($column)
                                <i class="ml-1" :class="getSortIconPenyewaan('{{ $column }}')"></i>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <template x-if="filteredPenyewaans.length === 0">
                    <tr>
                        <td colspan="{{ count($penyewaanHeaders) }}" class="px-6 py-4 text-center text-gray-400">Tidak ada transaksi penyewaan untuk tahun ini atau tidak ditemukan.</td>
                    </tr>
                </template>
                <template x-for="item in filteredPenyewaans" :key="item.id">
                    <tr class="bg-[#1e1e1e] border-b border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                        <td class="px-6 py-4" x-text="item.id"></td>
                        <td class="px-6 py-4" x-text="item.nama_penyewa"></td>
                        <td class="px-6 py-4" x-text="item.meja_nama_meja ? item.meja_nama_meja : item.meja_id"></td>
                        <td class="px-6 py-4" x-text="item.paket_nama_paket ? item.paket_nama_paket : '-'"></td>
                        <td class="px-6 py-4" x-text="item.durasi_jam"></td>
                        <td class="px-6 py-4" x-text="fmtRp(item.harga_per_jam)"></td>
                        <td class="px-6 py-4" x-text="fmtRp(item.total_bayar)"></td>
                        <td class="px-6 py-4">
                            <template x-if="item.service_detail_parsed && item.service_detail_parsed.length > 0">
                                <div x-data="{ showServiceTooltip: false }" class="relative inline-block">
                                    <span x-on:mouseenter="showServiceTooltip = true" x-on:mouseleave="showServiceTooltip = false" class="cursor-pointer text-blue-400 hover:text-blue-200">
                                        Lihat Detail <i class="fas fa-info-circle ml-1"></i>
                                    </span>
                                    <div x-show="showServiceTooltip"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute z-10 w-64 p-3 bg-gray-800 text-white text-xs rounded-lg shadow-lg left-1/2 -translate-x-1/2 bottom-full mb-2"
                                        style="display: none;">
                                        <h4 class="font-bold mb-1">Daftar Layanan:</h4>
                                        <ul class="list-disc list-inside">
                                            <template x-for="service in item.service_detail_parsed" :key="service.id">
                                                <li x-text="`${service.nama} (${service.jumlah}x) - ${fmtRp(service.subtotal)}`"></li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                            <template x-else>-</template>
                        </td>
                        <td class="px-6 py-4" x-text="fmtRp(item.total_service)"></td>
                        <td class="px-6 py-4" x-text="`${item.diskon_persen ?? 0}%`"></td>
                        <td class="px-6 py-4" x-text="item.status"></td>
                        <td class="px-6 py-4" x-text="item.is_qris ? 'QRIS' : 'Cash'"></td>
                        <td class="px-6 py-4" x-text="item.kasir_name ? item.kasir_name : '-'"></td>
                        <td class="px-6 py-4" x-text="item.pemandu_name ? item.pemandu_name : '-'"></td>
                        <td class="px-6 py-4" x-text="formatDateTime(item.waktu_mulai)"></td>
                        <td class="px-6 py-4" x-text="item.waktu_selesai ? formatDateTime(item.waktu_selesai) : '-'"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $penyewaansPaginated->appends(request()->except('penyewaan_page'))->links() }}
    </div>

    {{-- Detail Transaksi Layanan --}}
    <h2 class="text-xl font-semibold mb-4 text-white mt-8">Detail Transaksi Layanan Tahun {{ $year }}</h2>
    <div class="mb-4">
        <input type="text" x-model="searchService" placeholder="Cari transaksi layanan..."
               class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-4">
    </div>

    <div x-data="{
        serviceTransactions: {{ json_encode($serviceTransactionsData) }},
        searchService: '',
        sortColumnService: 'id',
        sortDirectionService: 'asc',

        get filteredServiceTransactions() {
            return this.serviceTransactions.filter(item => {
                const search = this.searchService.toLowerCase();
                return item.customer_name.toLowerCase().includes(search) ||
                       item.payment_method.toLowerCase().includes(search) ||
                       item.payment_status.toLowerCase().includes(search) ||
                       (item.kasir_name && item.kasir_name.toLowerCase().includes(search));
            }).sort((a, b) => {
                let valA, valB;
                let column = this.sortColumnService;

                if (column === 'kasir.name') {
                    valA = a.kasir_name;
                    valB = b.kasir_name;
                }
                else {
                    valA = a[column];
                    valB = b[column];
                }

                if (valA === null || valA === undefined) valA = '';
                if (valB === null || valB === undefined) valB = '';

                if (typeof valA === 'string' || typeof valA === 'number') {
                    if (this.sortDirectionService === 'asc') {
                        return (valA > valB) ? 1 : ((valA < valB) ? -1 : 0);
                    } else {
                        return (valA < valB) ? 1 : ((valA > valB) ? -1 : 0);
                    }
                }
                return 0;
            });
        },

        sortByService(column) {
            if (this.sortColumnService === column) {
                this.sortDirectionService = this.sortDirectionService === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumnService = column;
                this.sortDirectionService = 'asc';
            }
        },

        getSortIconService(column) {
            if (column === null) return '';
            if (this.sortColumnService === column) {
                return this.sortDirectionService === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
            }
            return 'fas fa-sort';
        }
    }" class="relative overflow-x-auto shadow-md sm:rounded-lg mb-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-700 text-white">
                <tr>
                    @foreach($serviceTransactionHeaders as $header => $column)
                        <th scope="col" class="px-6 py-3 @if($column) cursor-pointer hover:bg-gray-600 @endif"
                            @if($column) @click="sortByService('{{ $column }}')" @endif>
                            {{ $header }}
                            @if($column)
                                <i class="ml-1" :class="getSortIconService('{{ $column }}')"></i>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <template x-if="filteredServiceTransactions.length === 0">
                    <tr>
                        <td colspan="{{ count($serviceTransactionHeaders) }}" class="px-6 py-4 text-center text-gray-400">Tidak ada transaksi layanan untuk tahun ini atau tidak ditemukan.</td>
                    </tr>
                </template>
                <template x-for="item in filteredServiceTransactions" :key="item.id">
                    <tr class="bg-[#1e1e1e] border-b border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                        <td class="px-6 py-4" x-text="item.id"></td>
                        <td class="px-6 py-4" x-text="item.customer_name"></td>
                        <td class="px-6 py-4">
                            <template x-if="item.service_detail_parsed && item.service_detail_parsed.length > 0">
                                <div x-data="{ showServiceTooltip: false }" class="relative inline-block">
                                    <span x-on:mouseenter="showServiceTooltip = true" x-on:mouseleave="showServiceTooltip = false" class="cursor-pointer text-blue-400 hover:text-blue-200">
                                        Lihat Detail <i class="fas fa-info-circle ml-1"></i>
                                    </span>
                                    <div x-show="showServiceTooltip"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute z-10 w-64 p-3 bg-gray-800 text-white text-xs rounded-lg shadow-lg left-1/2 -translate-x-1/2 bottom-full mb-2"
                                        style="display: none;">
                                        <h4 class="font-bold mb-1">Daftar Layanan:</h4>
                                        <ul class="list-disc list-inside">
                                            <template x-for="service in item.service_detail_parsed" :key="service.id">
                                                <li x-text="`${service.nama} (${service.jumlah}x) - ${fmtRp(service.subtotal ?? (service.harga_satuan * service.jumlah))}`"></li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                            <template x-else>-</template>
                        </td>
                        <td class="px-6 py-4" x-text="`${item.diskon_persen ?? 0}%`"></td>
                        <td class="px-6 py-4" x-text="fmtRp(item.total_bayar)"></td>
                        <td class="px-6 py-4" x-text="formatDateTime(item.transaction_time)"></td>
                        <td class="px-6 py-4" x-text="item.payment_status"></td>
                        <td class="px-6 py-4" x-text="item.payment_method"></td>
                        <td class="px-6 py-4" x-text="item.kasir_name ? item.kasir_name : '-'"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $serviceTransactionsPaginated->appends(request()->except('service_page'))->links() }}
    </div>

</div>
@endsection

@section('script')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>

<script>
    const fmtRp = (amount) => {
        if (amount === null || amount === undefined) return 'Rp 0';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    };

    const formatDateTime = (datetimeString) => {
        if (!datetimeString) return '-';
        const date = new Date(datetimeString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
    };

    // --- Script untuk Chart.js (Doughnut Chart: Distribusi Pembayaran) ---
    let paymentMethodChartInstance = null;
    const paymentMethodDistributionData = @json($paymentMethodDistribution);

    const renderPaymentMethodChart = (distributionData) => {
        const ctx = document.getElementById('paymentMethodChart');
        if (!ctx) return;

        const labels = distributionData.map(item => item.label);
        const values = distributionData.map(item => item.value);

        if (paymentMethodChartInstance) paymentMethodChartInstance.destroy();

        paymentMethodChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#10B981', '#3B82F6', '#6B7280'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#D1D5DB' }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#333333',
                        titleColor: '#FFFFFF',
                        bodyColor: '#D1D5DB',
                        borderColor: '#4A4A4A',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (context.parsed !== null) {
                                    if (distributionData.some(item => item.value > 0)) {
                                        label += ': ' + fmtRp(context.parsed);
                                    } else {
                                        label = 'Tidak Ada Data';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    };

    // --- Script untuk ApexCharts (Bar Chart: Tren Pendapatan Bulanan) ---
    const chartLabelsMonthly = @json($chartLabelsMonthly);
    const chartDataMonthly = @json($chartDataMonthly);

    const monthlyRevenueChartOptions = {
        series: [{
            name: "Pendapatan",
            data: chartDataMonthly,
        }],
        chart: {
            height: 350,
            type: "bar",
            fontFamily: "Inter, sans-serif",
            toolbar: {
                show: false,
            },
            foreColor: '#D1D5DB'
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "70%",
                borderRadiusApplication: "end",
                borderRadius: 8,
            },
        },
        tooltip: {
            theme: 'dark',
            y: {
                formatter: function (val) {
                    return fmtRp(val);
                }
            }
        },
        states: {
            hover: {
                filter: {
                    type: "darken",
                    value: 1,
                },
            },
        },
        stroke: {
            show: true,
            width: 0,
            colors: ["transparent"],
        },
        grid: {
            show: false,
            strokeDashArray: 4,
            padding: {
                left: 2,
                right: 2,
                top: -14
            },
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
        },
        xaxis: {
            categories: chartLabelsMonthly,
            labels: {
                show: true,
                style: {
                    fontFamily: "Inter, sans-serif",
                    cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
                }
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
        },
        yaxis: {
            show: false,
        },
        fill: {
            opacity: 1,
            colors: ['#3B82F6']
        }
    };

    // --- Script untuk ApexCharts (Horizontal Bar Chart: Kinerja Kasir) ---
    const topKasirData = @json($topKasir);
    const kasirNames = Object.keys(topKasirData).reverse();
    const kasirValues = Object.values(topKasirData).reverse();

    const kasirChartOptions = {
        series: [{
            name: "Pendapatan Dihasilkan",
            data: kasirValues,
        }],
        chart: {
            type: 'bar',
            height: Math.max(300, kasirNames.length * 50),
            toolbar: { show: false },
            foreColor: '#D1D5DB'
        },
        plotOptions: {
            bar: {
                horizontal: true,
                distributed: true,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) { return fmtRp(val); },
            offsetX: 0,
            style: { fontSize: '12px', colors: ['#FFFFFF'] }
        },
        xaxis: {
            categories: kasirNames,
            labels: {
                formatter: function (val) { return fmtRp(val); },
                style: { fontSize: '12px' }
            }
        },
        yaxis: { labels: { style: { fontSize: '12px' } } },
        grid: { show: false },
        tooltip: {
            theme: 'dark',
            y: { formatter: function (val) { return fmtRp(val); } }
        },
        colors: [
            '#F6AD55', '#A0AEC0', '#63B3ED', '#9F7AEA', '#F687B3',
            '#4FD1C5', '#F6E05E', '#C53030', '#2F855A', '#B794F4'
        ]
    };

    // --- Script untuk ApexCharts (Horizontal Bar Chart: Kinerja Pemandu) ---
    const topPemanduData = @json($topPemandu);
    const pemanduNames = Object.keys(topPemanduData).reverse();
    const pemanduValues = Object.values(topPemanduData).reverse();

    const pemanduChartOptions = {
        series: [{
            name: "Jumlah Meja Dilayani",
            data: pemanduValues,
        }],
        chart: {
            type: 'bar',
            height: Math.max(300, pemanduNames.length * 50),
            toolbar: { show: false },
            foreColor: '#D1D5DB'
        },
        plotOptions: {
            bar: {
                horizontal: true,
                distributed: true,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) { return val + ' Meja'; },
            offsetX: 0,
            style: { fontSize: '12px', colors: ['#FFFFFF'] }
        },
        xaxis: {
            categories: pemanduNames,
            labels: {
                formatter: function (val) { return val + ' Meja'; },
                style: { fontSize: '12px' }
            }
        },
        yaxis: { labels: { style: { fontSize: '12px' } } },
        grid: { show: false },
        tooltip: {
            theme: 'dark',
            y: { formatter: function (val) { return val + ' Meja'; } }
        },
        colors: [
            '#38B2AC', '#81E6D9', '#68D391', '#4FD1C5', '#F6AD55',
            '#9F7AEA', '#B794F4', '#667EEA', '#ED64A6', '#C53030'
        ]
    };

    document.addEventListener('DOMContentLoaded', () => {
        renderPaymentMethodChart(paymentMethodDistributionData);

        if (document.getElementById("yearly-monthly-revenue-chart") && typeof ApexCharts !== 'undefined') {
            const chart = new ApexCharts(document.getElementById("yearly-monthly-revenue-chart"), monthlyRevenueChartOptions);
            chart.render();
        }

        if (document.getElementById("kasir-chart") && typeof ApexCharts !== 'undefined') {
            const kasirChart = new ApexCharts(document.getElementById("kasir-chart"), kasirChartOptions);
            kasirChart.render();
        }

        if (document.getElementById("pemandu-chart") && typeof ApexCharts !== 'undefined') {
            const pemanduChart = new ApexCharts(document.getElementById("pemandu-chart"), pemanduChartOptions);
            pemanduChart.render();
        }

        const yearSelect = document.getElementById('report-year');
        yearSelect.addEventListener('change', () => {
            document.getElementById('year-form').submit();
        });
    });
</script>