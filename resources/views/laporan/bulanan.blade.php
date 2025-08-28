@extends('default')

@section('title', 'Laporan Bulanan')

@section('content')

<?php
// Variabel PHP sudah disediakan dari controller:
// $penyewaansPaginated, $totalPendapatanNett, $paymentMethodDistribution, $pendapatanPerMeja
// $topKasir, $topPemandu, $chartLabelsDaily, $chartDataDaily, $year, $month, $months, $years
// $TOP_PERFORMERS_LIMIT (dari konstanta controller)

$headers = [
    'Tipe', 'ID', 'Nama', 'Meja/Layanan', 'Durasi/Qty','Harga Jam', 'Diskon', 'Total Bayar',
    'Waktu Mulai/Transaksi', 'Waktu Selesai', 'Status', 'Metode Bayar', 'Kasir', 'Pemandu'
];
?>

<div class="container mx-auto p-4 md:p-6 bg-[#121212] text-white min-h-screen">
    <h1 class="text-3xl font-bold mb-6 text-white">Laporan Bulanan Penyewaan & Layanan</h1>

    <form id="month-year-form" action="{{ route('laporan.bulanan') }}" method="GET" class="mb-6 flex flex-col md:flex-row items-center gap-4">
    {{-- Link untuk laporan harian --}}
        <a href="{{ route('laporan.harian') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.harian') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-day mr-3"></i> Laporan Harian
                </a>

        {{-- Link untuk laporan tahunan --}}
        <a href="{{ route('laporan.bulanan') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.bulanan') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-days mr-3"></i> Laporan Bulanan
                </a>
        {{-- Link untuk laporan tahunan --}}
        <a href="{{ route('laporan.tahunan') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.tahunan') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-days mr-3"></i> Laporan Tahunan
                </a>

        <label for="report-month" class="block text-sm font-medium text-gray-300">Pilih Bulan:</label>
        <select id="report-month" name="month"
                class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-auto p-2.5">
            @foreach($months as $key => $name)
                <option value="{{ $key }}" {{ $key == $month ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>

        <label for="report-year" class="block text-sm font-medium text-gray-300">Pilih Tahun:</label>
        <select id="report-year" name="year"
                class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-auto p-2.5">
            @foreach($years as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <button type="submit" class="hidden">Submit</button>
    </form>

    {{-- Total Pendapatan Bulanan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md flex items-center justify-between border border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-400">Total Pendapatan (Nett) {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</p>
                <p id="total-pendapatan" class="text-3xl font-bold text-white mt-1">Rp {{ number_format($totalPendapatanNett, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-blue-900 rounded-full">
                <i class="fa-solid fa-rupiah-sign text-blue-300 text-2xl"></i>
            </div>
        </div>
    </div>

    {{-- Grafik Tren Pendapatan Harian (ApexCharts Bar) --}}
    <div class="w-full bg-[#1e1e1e] rounded-lg shadow dark:bg-gray-800 p-4 md:p-6 mb-8 border border-gray-700">
        <div class="flex justify-between mb-3">
            <div class="flex justify-center items-center">
                <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white pe-1">Tren Pendapatan {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }} (Per Hari)</h5>
            </div>
        </div>
        <div id="monthly-daily-revenue-chart"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Grafik Distribusi Pendapatan (Chart.js Doughnut) --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md border border-gray-700">
            <h2 class="text-xl font-semibold mb-4 text-white">Distribusi Pendapatan {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
            <div class="chart-container w-full h-80 flex items-center justify-center">
                <canvas id="paymentMethodChart"></canvas>
            </div>
            <p class="text-sm text-gray-400 mt-4 italic">
                *Data distribusi Cash & QRIS diambil dari kedua jenis transaksi.
            </p>
        </div>

        {{-- Grafik Kinerja Kasir (ApexCharts Horizontal Bar) --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md border border-gray-700">
            <h2 class="text-xl font-semibold mb-4 text-white">Kinerja Kasir {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
            <div id="kasir-chart"></div>
            <p class="text-sm text-gray-400 mt-4 italic">
                *Top {{ $TOP_PERFORMERS_LIMIT }} kasir berdasarkan total pendapatan.
            </p>
        </div>
    </div>

    {{-- Daftar Card Pendapatan Per Kasir --}}
    <h2 class="text-xl font-semibold mb-4 text-white">Pendapatan Per Kasir {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
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
        <h2 class="text-xl font-semibold mb-4 text-white">Kinerja Pemandu {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
        <div id="pemandu-chart"></div>
        <p class="text-sm text-gray-400 mt-4 italic">
            *Top {{ $TOP_PERFORMERS_LIMIT }} pemandu berdasarkan jumlah meja yang dilayani.
        </p>
    </div>

    {{-- Daftar Card Jumlah Meja Per Pemandu --}}
    <h2 class="text-xl font-semibold mb-4 text-white">Jumlah Meja Per Pemandu {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
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

    <h2 class="text-xl font-semibold mb-4 text-white">Pendapatan Per Meja {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
    <div id="pendapatan-meja-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
        @forelse ($pendapatanPerMeja as $meja)
            <div class="bg-[#1e1e1e] p-4 rounded-lg shadow-md border border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                <h3 class="text-lg font-semibold text-white">{{ $meja['nama_meja'] }}</h3>
                <p class="text-xl font-bold text-green-400 mt-2">Rp {{ number_format($meja['total_pendapatan'], 0, ',', '.') }}</p>
            </div>
        @empty
            <p class="text-gray-400 col-span-full">Tidak ada pendapatan tercatat untuk meja pada bulan ini.</p>
        @endforelse
    </div>

    <h2 class="text-xl font-semibold mb-4 text-white">Detail Transaksi {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-700 text-white">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-6 py-3">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($penyewaansPaginated as $item) {{-- $item bisa Penyewaan atau ServiceTransaction --}}
                    <tr class="bg-[#1e1e1e] border-b border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $item->type == 'Penyewaan' ? 'bg-blue-600 text-blue-100' : 'bg-purple-600 text-purple-100' }}">
                                {{ $item->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $item->id }}</td>
                        <td class="px-6 py-4">{{ $item->nama_penyewa ?? $item->customer_name }}</td>
                        <td class="px-6 py-4">{{ $item->meja->nama_meja ?? ($item->service_detail ? 'Layanan Lain' : '-') }}</td>
                        <td class="px-6 py-4">{{ $item->durasi_jam ?? ($item->service_detail ? count($item->service_detail) . ' item' : '-') }}</td>
                        <td class="px-6 py-4">{{ number_format($item->harga_per_jam ?? ($item->service_detail ? collect($item->service_detail)->sum('subtotal') / count($item->service_detail) : 0), 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ number_format($item->diskon_amount ?? ($item->diskon_persen ? ($item->diskon_persen/100 * $item->total_bayar) : 0), 0, ',', '.') }} ({{ $item->diskon_persen ?? 0 }}%)</td>
                        <td class="px-6 py-4">{{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ ($item->waktu_mulai ?? $item->transaction_time)->format('d/m/Y H:i:s') }}</td>
                        <td class="px-6 py-4">{{ $item->waktu_selesai ? $item->waktu_selesai->format('d/m/Y H:i:s') : '-' }}</td>
                        <td class="px-6 py-4">{{ $item->status ?? $item->payment_status }}</td>
                        <td class="px-6 py-4">{{ $item->is_qris ? 'QRIS' : ($item->payment_method ?? 'Cash') }}</td>
                        <td class="px-6 py-4">{{ $item->kasir->name ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $item->pemandu->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" class="px-6 py-4 text-center text-gray-400">Tidak ada data transaksi untuk bulan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tautan Pagination --}}
    <div class="mt-4">
        {{ $penyewaansPaginated->appends(request()->query())->links() }}
    </div>

</div>
@endsection

@section('script')
{{-- Chart.js untuk Doughnut Chart --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
{{-- ApexCharts untuk Bar Charts --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>

<script>
    const fmtRp = (amount) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
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
                        backgroundColor: '#333333', // Dark background for tooltip
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

    // --- Script untuk ApexCharts (Bar Chart: Tren Pendapatan Harian) ---
    const chartLabelsDaily = @json($chartLabelsDaily);
    const chartDataDaily = @json($chartDataDaily);

    const dailyRevenueChartOptions = {
        series: [{
            name: "Pendapatan",
            data: chartDataDaily,
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
            theme: 'dark', // Tema gelap untuk tooltip
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
            categories: chartLabelsDaily,
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

        if (document.getElementById("monthly-daily-revenue-chart") && typeof ApexCharts !== 'undefined') {
            const chart = new ApexCharts(document.getElementById("monthly-daily-revenue-chart"), dailyRevenueChartOptions);
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

        const monthSelect = document.getElementById('report-month');
        const yearSelect = document.getElementById('report-year');

        monthSelect.addEventListener('change', () => {
            document.getElementById('month-year-form').submit();
        });
        yearSelect.addEventListener('change', () => {
            document.getElementById('month-year-form').submit();
        });
    });
</script>
@endsection