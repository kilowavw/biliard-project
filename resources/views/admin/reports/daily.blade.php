@extends('default')

@section('title', 'Laporan Harian Admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Laporan Harian</h1>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-8">
        <form action="{{ route('admin.reports.daily') }}" method="GET" class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
            <div class="flex-grow">
                <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Tanggal</label>
                <input type="date" id="date" name="date" value="{{ $date }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            </div>
            <div class="flex-shrink-0">
                <button type="submit" class="w-full md:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
            <div class="flex-shrink-0">
                <button type="button" onclick="exportToExcel()" class="w-full md:w-auto px-4 py-2 text-sm font-medium text-white bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4">Total Pendapatan</h3>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4">Jumlah Penyewaan</h3>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $penyewaans->count() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4">Rata-rata Pendapatan / Penyewaan</h3>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">Rp {{ number_format($penyewaans->avg('total_bayar'), 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4">Pendapatan per Jam</h3>
            <div id="pendapatanPerJamChart"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4">Pendapatan per Kategori Layanan</h3>
            <div id="pendapatanPerKategoriServiceChart"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4">Distribusi Jenis Penyewaan</h3>
            <div id="distribusiJenisPenyewaanChart"></div>
        </div>
    </div>


    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Detail Transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400" id="reportTable">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">Waktu Mulai</th>
                        <th scope="col" class="px-6 py-3">Waktu Selesai</th>
                        <th scope="col" class="px-6 py-3">Penyewa</th>
                        <th scope="col" class="px-6 py-3">Meja</th>
                        <th scope="col" class="px-6 py-3">Durasi (Jam)</th>
                        <th scope="col" class="px-6 py-3">Harga/Jam</th>
                        <th scope="col" class="px-6 py-3">Total Sewa Meja</th>
                        <th scope="col" class="px-6 py-3">Layanan</th>
                        <th scope="col" class="px-6 py-3">Total Layanan</th>
                        <th scope="col" class="px-6 py-3">Kupon</th>
                        <th scope="col" class="px-6 py-3">Diskon (%)</th>
                        <th scope="col" class="px-6 py-3">Total Bayar</th>
                        <th scope="col" class="px-6 py-3">Kasir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penyewaans as $penyewaan)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $penyewaan->id }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $penyewaan->waktu_mulai->format('H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $penyewaan->waktu_selesai ? $penyewaan->waktu_selesai->format('H:i') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $penyewaan->nama_penyewa }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $penyewaan->meja->nama_meja }}
                            @if ($penyewaan->paket_id)
                                <span class="text-xs text-blue-500 dark:text-blue-400">(Paket: {{ $penyewaan->paket->nama_paket ?? 'N/A' }})</span>
                            @elseif (is_null($penyewaan->waktu_selesai) && $penyewaan->durasi_jam == 0)
                                <span class="text-xs text-gray-500 dark:text-gray-400">(Sepuasnya)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{ number_format($penyewaan->durasi_jam, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            Rp {{ number_format($penyewaan->harga_per_jam, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            Rp {{ number_format($penyewaan->durasi_jam * $penyewaan->harga_per_jam, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-xs">
                            @if ($penyewaan->service_detail)
                                @foreach ($penyewaan->service_detail as $service)
                                    {{ $service['nama'] }} ({{ $service['jumlah'] }}) - Rp {{ number_format($service['subtotal'], 0, ',', '.') }}<br>
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            Rp {{ number_format($penyewaan->total_service, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $penyewaan->kode_kupon ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $penyewaan->diskon_persen ?? 0 }}%
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($penyewaan->total_bayar, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $penyewaan->kasir->name ?? 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data laporan untuk tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th colspan="12" class="px-6 py-3 text-right">Total Pendapatan Akhir:</th>
                        <th class="px-6 py-3 font-bold text-gray-900 dark:text-white">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/flowbite@1.4.0/dist/flowbite.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script>
    // Data dari Controller ke JavaScript
    const pendapatanPerJamData = @json($pendapatanPerJam);
    const serviceCategoriesData = @json($serviceCategoriesData);
    const penyewaanJenisData = @json($penyewaanJenis);

    // Dark mode check for chart colors
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#FFFFFF' : '#374151';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    // Chart 1: Pendapatan per Jam (Bar Chart)
    const setupPendapatanPerJamChart = () => {
        const labels = Array.from({ length: 24 }, (_, i) => `${String(i).padStart(2, '0')}:00`);
        const dataPoints = labels.map(label => {
            const found = pendapatanPerJamData.find(item => item.hour === label);
            return found ? parseFloat(found.total) : 0;
        });

        const ctx = document.createElement('canvas');
        document.getElementById('pendapatanPerJamChart').appendChild(ctx);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: dataPoints,
                    backgroundColor: isDarkMode ? '#1e40af' : '#3b82f6', // blue-700 / blue-500
                    borderColor: isDarkMode ? '#1c3d8a' : '#2563eb', // blue-800 / blue-600
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: textColor
                        },
                        grid: {
                            color: gridColor
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            }
        });
    };

    // Chart 2: Pendapatan per Kategori Layanan (Pie Chart)
    const setupPendapatanPerKategoriServiceChart = () => {
        const labels = Object.keys(serviceCategoriesData);
        const dataPoints = Object.values(serviceCategoriesData);

        const backgroundColors = [
            '#ef4444', '#f97316', '#eab308', '#22c55e', '#0ea5e9', '#6366f1', '#a855f7', '#ec4899', '#f43f5e', '#84cc16'
        ].map(color => isDarkMode ? adjustColorForDark(color) : color);

        const ctx = document.createElement('canvas');
        document.getElementById('pendapatanPerKategoriServiceChart').appendChild(ctx);

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: dataPoints,
                    backgroundColor: backgroundColors,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                }
            }
        });
    };

    // Chart 3: Distribusi Jenis Penyewaan (Doughnut Chart)
    const setupDistribusiJenisPenyewaanChart = () => {
        const labels = {
            'durasi_tetap': 'Durasi Tetap',
            'sepuasnya': 'Main Sepuasnya',
            'paket': 'Paket'
        };
        const chartLabels = Object.keys(penyewaanJenisData).map(key => labels[key] || key);
        const dataPoints = Object.values(penyewaanJenisData);

        const backgroundColors = [
            isDarkMode ? '#059669' : '#10b981', // emerald-600 / emerald-500
            isDarkMode ? '#ef4444' : '#f87171', // red-600 / red-400
            isDarkMode ? '#3b82f6' : '#60a5fa'  // blue-600 / blue-400
        ];

        const ctx = document.createElement('canvas');
        document.getElementById('distribusiJenisPenyewaanChart').appendChild(ctx);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Jumlah Penyewaan',
                    data: dataPoints,
                    backgroundColor: backgroundColors,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                }
            }
        });
    };

    // Fungsi untuk menyesuaikan warna agar lebih terlihat di dark mode (opsional)
    function adjustColorForDark(hex) {
        // Contoh sederhana: membuat warna sedikit lebih cerah atau mengubah hue
        // Anda bisa menggunakan library seperti 'color-scheme-adapter' untuk penyesuaian yang lebih baik
        const rgb = parseInt(hex.slice(1), 16);
        const r = (rgb >> 16) & 0xff;
        const g = (rgb >> 8) & 0xff;
        const b = (rgb >> 0) & 0xff;
        return `rgb(${Math.min(255, r + 50)}, ${Math.min(255, g + 50)}, ${Math.min(255, b + 50)})`;
    }

    // Inisialisasi Chart saat DOM siap
    document.addEventListener('DOMContentLoaded', () => {
        setupPendapatanPerJamChart();
        setupPendapatanPerKategoriServiceChart();
        setupDistribusiJenisPenyewaanChart();
    });

    // Fungsi Ekspor ke Excel
    function exportToExcel() {
        const table = document.getElementById('reportTable');
        const ws = XLSX.utils.table_to_sheet(table);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Laporan Harian");
        XLSX.writeFile(wb, `Laporan_Harian_${document.getElementById('date').value}.xlsx`);
    }
</script>
@endpush